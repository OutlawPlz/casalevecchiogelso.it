<div id="messages" class="grow overflow-y-auto max-w-3xl w-full mx-auto px-4 md:px-6">
    @foreach($chat as $date => $messages)
        <div class="text-center text-sm py-4">{{ $date }}</div>

        @foreach($messages as $message)
            @php $isOwner = $message->user()->is($authUser); @endphp
            <div
                id="{{ "message-$message->id" }}"
                @class([
                    'flex items-start gap-2.5 mt-2',
                    'flex-row-reverse' => $isOwner,
                    'justify-start' => ! $isOwner
                ])
            >
                <div
                    @class([
                        'shadow flex flex-col max-w-[95%] leading-1.5 p-4 border-gray-200 rounded-lg',
                        'bg-gray-200' => $isOwner,
                        'bg-white' => ! $isOwner
                    ])
                >
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-semibold text-gray-900">{{ $message->author['name'] }}</span>
                        <span
                            title="{{ $message->created_at->format('Y-m-d H:m') }}"
                            class="text-sm font-normal text-gray-500"
                        >{{ $message->created_at->format('H:m') }}</span>
                    </div>
                    <div class="prose">
                        @php $locale = $isOwner ? '' : $locale; @endphp
                        {!! $message->renderContent(['reservation' => $reservation], $locale) !!}
                    </div>

                    <div class="flex flex-wrap space-x-2">
                        @foreach($message->media as $media)
                            <a href="{{ "/storage/$media" }}" target="_blank">
                                <img class="mt-1 rounded-lg w-24 h-24 object-cover" src="{{ "/storage/$media" }}" alt="Media">
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    @endforeach
