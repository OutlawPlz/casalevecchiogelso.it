<?php

namespace App\Http\Controllers;

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
    public function __construct(
        protected MessageRenderer $messageRenderer,
    ) {}

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
            $language = $message->user()->is($authUser) ? '' : $request->input('locale');

            $data = [
                'language' => $language,
                'reservation' => $reservation,
            ];

            $message->rendered_content = $this->messageRenderer->render($message, $data);
        }

        $reservation->visitedBy($authUser)->save();

        return $messages;
    }

    public function show(Request $request, Reservation $reservation, Message $message): Message
    {
        /** @var User $authUser */
        $authUser = $request->user();

        $language = $message->user()->is($authUser) ? '' : $request->input('locale');

        $data = [
            'language' => $language,
            'reservation' => $reservation,
        ];

        $message->rendered_content = $this->messageRenderer->render($message, $data);

        $reservation->visitedBy($authUser)->save();

        return $message;
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request, Reservation $reservation): Message
    {
        $attributes = $request->validate(self::rules());
        // Having an empty string instead of NULL makes the code easier.
        if (is_null($attributes['content'])) {
            $attributes['content'] = '';
        }

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
                'email' => $authUser->email,
            ],
            'content' => ['raw' => $attributes['content']],
            'media' => $media,
        ]);

        ChatReply::dispatch($message);

        $reservation->repliedAt($message->created_at)->save();

        return $message;
    }

    public static function rules(): array
    {
        return [
            'content' => ['required_without:media'],
            'media.*' => [File::image()->max('7mb')],
        ];
    }
}
