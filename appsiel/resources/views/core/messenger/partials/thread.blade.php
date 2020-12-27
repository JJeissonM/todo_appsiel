<?php $class = $thread->isUnread(Auth::id()) ? 'alert-warning' : 'alert-success'; ?>

<div class="media alert {{ $class }}">
    <h4 class="media-heading">
        <a href="{{ route('messages.show', $thread->id . '?id=5' ) }}">{{ $thread->subject }}</a>
        ({{ $thread->userUnreadMessagesCount(Auth::id()) }} sin leer)</h4>
    <hr>
    <p style="border: 1px solid #ddd; padding: 5px; border-radius: 4px; background: #ddd;">
        {{ $thread->latestMessage->body }}
    </p>
    <p>
        <small><strong>Creado por:</strong> {{ $thread->creator()->name }}</small>
    </p>
    <p>
        <small><strong>Participantes:</strong> {{ $thread->participantsString(Auth::id()) }}</small>
    </p>
</div>