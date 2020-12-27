@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-lg-10 col-lg-offset-1 marco_formulario">
            <h4 style="color: gray;"> <i class="fa fa-btn fa-bullhorn"></i> Conversación: {{ $thread->subject }}</h4>
            <hr>

            <ul class="chat-list" style="font-size: 16px !important;">
				<div class="col-md-12">
            		@each('core.messenger.partials.messages', $thread->messages, 'message')	
            	</div>
            </ul>        

	        @include('core.messenger.partials.form-message')
		</div>
	</div>
@endsection

@section('scripts')

	<script type="text/javascript">
		$(document).ready(function(){

			// Bajar el Scroll hasta el final de la página
			$("html, body").animate( { scrollTop: $(document).height()+"px"} );

		});
	</script>
@endsection