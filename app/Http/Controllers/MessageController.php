<?php

namespace App\Http\Controllers;

use App\Events\ChatReply;
use App\Models\Message;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rules\File;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    /**
     * @param Request $request
     * @param Reservation $reservation
     * @return Collection
     */
    public function index(Request $request, Reservation $reservation): Collection
    {
        /** @var Collection<Message> $messages */
        $messages = Message::query()
            ->where('channel', $reservation->ulid)
            ->limit(30)
            ->get();

        $authId = $request->user()->id;

        foreach ($messages as $message) {
            // Translate only other user's messages.
            $locale = $message->user_id === $authId ? 'raw' : $request->get('locale');

            $message->rendered_content = $message->renderContent(
                ['reservation' => $reservation], $locale
            );
        }

        return $messages->groupBy(
            fn (Message $message) => $message->created_at->format('Y-m-d')
        );
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

        /** @var User $authUser */
        $authUser = $request->user();

        $isTemplate = str_starts_with($attributes['message'], '/blade');

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
                $media[] = $image->store($reservation->ulid);
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
            'content' => ['raw' => $attributes['message']],
            'media' => $media,
        ]);

        ChatReply::dispatch($message);

        return $message;
    }

    /**
     * @return array
     */
    public static function rules(): array
    {
        return [
            'message' => ['required_without:media'],
            'media.*' => [File::image()->max('7mb')],
        ];
    }
}
