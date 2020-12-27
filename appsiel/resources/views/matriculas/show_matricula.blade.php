@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( 'matriculas/create?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'matriculas/imprimir/'.$id ) }}
&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEdit( 'matriculas/'.$id.'/edit?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
&nbsp;&nbsp;&nbsp;<a href="{{route('responsables.index')}}?id={{$app}}&id_modelo={{$modelo}}&estudiante_id={{$matricula->id_estudiante}}" class="btn btn-danger btn-xs" title="Acudiente, Papá, Mamá, Responsable Financiero, etc">RESPONSABLES DEL ESTUDIANTE</a>
<div class="pull-right">
	@if($reg_anterior!='')
	{{ Form::bsBtnPrev( 'matriculas/show/'.$reg_anterior.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
	@endif

	@if($reg_siguiente!='')
	{{ Form::bsBtnNext( 'matriculas/show/'.$reg_siguiente.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
	@endif
</div>
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">

		<?php
			echo $view_pdf;
		?>

	</div>
</div>
<br /><br />

@endsection