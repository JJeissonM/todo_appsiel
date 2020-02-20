@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="row">
		<div class="col-md-8 col-md-offset-2 marco_formulario">
		    <h4>{{ $datos['titulo'] }}</h4>
		    <hr>

		    <?php
		    	switch ($datos['accion']) {
		    		case 'create':
		    			echo Form::open(['url' => $datos['url'],'method' => $datos['metodo']]);
		    			break;
		    		case 'edit':
		    			echo Form::model($datos['registro'], ['url' => [$datos['url']], 'method' => $datos['metodo']]);
		    			break;
		    		case 'edit_con_files':
		    			echo Form::model($datos['registro'], ['url' => [$datos['url']], 'method' => $datos['metodo'],'files' => true]);
		    			break;
		    		default:
		    			break;
		    	}
		    ?>

				@include($datos['ruta_campos_form'])

			{{Form::close()}}
		
		</div>
	</div>
	<br/><br/>
@endsection