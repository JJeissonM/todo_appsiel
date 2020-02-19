<div class="media">
    <div class="media-body">
        <h5 class="media-heading"> <i class="fa fa-btn fa-user"></i> {{ $message->user->name }}</h5>
        <p style="border-left: 2px solid gray; margin-left: 15px;">
            &nbsp;&nbsp;&nbsp;{{ $message->body }}
        </p>
        <div class="text-muted">
            <small> <i class="fa fa-btn fa-calendar"></i>  Creado {{ $message->created_at->diffForHumans() }}</small>
            
        </div>
    </div>
</div>