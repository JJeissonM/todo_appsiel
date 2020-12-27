@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

			&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( url( 'messages/create?id=5' ) ) }}
			
	<hr>

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-lg-10 col-lg-offset-1 marco_formulario">
            <h4 style="color: gray;"> <i class="fa fa-btn fa-bullhorn"></i> Conversaciones</h4>
            <hr>

		    @include('core.messenger.partials.flash')

		    @each('core.messenger.partials.thread', $threads, 'thread', 'core.messenger.partials.no-threads')
		</div>
	</div>
@stop