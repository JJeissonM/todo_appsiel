@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-lg-10 col-lg-offset-1 marco_formulario">
            <h4 style="color: gray;"> <i class="fa fa-btn fa-bullhorn"></i> Conversación: {{ $thread->subject }}</h4>
            <hr>

	        @each('core.messenger.partials.messages', $thread->messages, 'message')

	        @include('core.messenger.partials.form-message')
		</div>
	</div>
@stop