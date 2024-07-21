<?php

namespace App\Http\Controllers;

use App\Events\ChatReply;
use App\Models\Message;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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

        foreach ($messages as $message) {
            $message->content = $message->renderContent(['reservation' => $reservation]);
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
        $content = $request->validate(self::rules())['message'];
        /** @var User $authUser */
        $authUser = $request->user();

        // Prevents guest from using templates
        if (str_starts_with($content, '/') && $authUser->isGuest()) {
            throw ValidationException::withMessages([
                'illegal_character' => __('Content cannot begin with the character /'),
            ]);
        }

        // TODO: Should I strip the # headings?

        /** @var Message $message */
        $message = $reservation->messages()->create([
            'user_id' => $authUser->id,
            'channel' => $reservation->ulid,
            'author' => [
                'name' => $authUser->name,
                'email' => $authUser->email
            ],
            'data' => ['content' => $content],
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
            'message' => ['required', 'string'],
        ];
    }
}