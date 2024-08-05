<div id="messages" class="grow overflow-y-auto max-w-3xl w-full mx-auto px-4 md:px-6">
    @foreach($chat as $date => $messages)
        <div class="text-center text-sm py-4">{{ $date }}</div>

        @foreach($messages as $message)
            @include('messages.show', ['message' => $message])
        @endforeach
    @endforeach
</div>
