<?php

namespace App\Http\Controllers;

use App\Actions\MarkAsReplied;
use App\Actions\MarkAsVisited;
use App\Events\ChatReply;
use App\Models\Message;
use App\Models\Reservation;
use App\Models\User;
use App\Services\MessageRenderer;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    /**
     * @param  MessageRenderer  $messageRenderer
     * @param  MarkAsVisited  $markAsVisited
     * @param  MarkAsReplied  $markAsReplied
     */
    public function __construct(
        protected MessageRenderer $messageRenderer,
        protected MarkAsVisited $markAsVisited,
        protected MarkAsReplied $markAsReplied,
    ) {}

    /**
     * @param Request $request
     * @param Reservation $reservation
     * @return Paginator
     */
    public function index(Request $request, Reservation $reservation): Paginator
    {
        /** @var User $authUser */
        $authUser = $request->user();

        $messages = Message::query()
            ->where('channel', $reservation->ulid)
            ->latest()
            ->simplePaginate();

        /** @var Message $message */
        foreach ($messages as $message) {
            $language = $message->user()->is($authUser) ? '' : $request->get('locale');

            $data = [
                'language' => $language,
                'reservation' => $reservation,
            ];

            $message->rendered_content = $this->messageRenderer->render($message, $data);
        }

        ($this->markAsVisited)($reservation, $authUser);

        return $messages;
    }

    /**
     * @param  Request  $request
     * @param  Reservation  $reservation
     * @param  Message  $message
     * @return Message
     */
    public function show(Request $request, Reservation $reservation, Message $message): Message
    {
        /** @var User $authUser */
        $authUser = $request->user();

        $language = $message->user()->is($authUser) ? '' : $request->get('locale');

        $data = [
            'language' => $language,
            'reservation' => $reservation,
        ];

        $message->rendered_content = $this->messageRenderer->render($message, $data);

        ($this->markAsVisited)($reservation, $authUser);

        return $message;
    }

    /**
     * @param  Request  $request
     * @param  Reservation  $reservation
     * @return Message
     * @throws ValidationException
     */
    public function store(Request $request, Reservation $reservation): Message
    {
        $attributes = $request->validate(self::rules());
        // Having an empty string instead of NULL makes the code easier.
        if (is_null($attributes['content'])) $attributes['content'] = '';

        /** @var User $authUser */
        $authUser = $request->user();

        $isTemplate = str_starts_with($attributes['content'], '/blade');

        // Prevents guest from using templates
        if ($isTemplate && $authUser->isGuest()) {
            throw ValidationException::withMessages([
                'illegal_character' => __('Content cannot begin with the character /'),
            ]);
        }

        // TODO: Should I strip the # headings?

        $media = [];

        if (array_key_exists('media', $attributes)) {
            /** @var UploadedFile $image */
            foreach ($attributes['media'] as $image) {
                $media[] = $image->store($reservation->ulid, 'public');
            }
        }

        /** @var Message $message */
        $message = $reservation->messages()->create([
            'user_id' => $authUser->id,
            'channel' => $reservation->ulid,
            'author' => [
                'name' => $authUser->name,
                'email' => $authUser->email
            ],
            'content' => ['raw' => $attributes['content']],
            'media' => $media,
        ]);

        ChatReply::dispatch($message);

        ($this->markAsReplied)($reservation, $message);

        return $message;
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'content' => ['required_without:media'],
            'media.*' => [File::image()->max('7mb')],
        ];
    }
}
