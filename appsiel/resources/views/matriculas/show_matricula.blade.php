@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( 'matriculas/create?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'matriculas/imprimir/'.$id ) }}
&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEdit( 'matriculas/'.$id.'/edit?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
&nbsp;&nbsp;&nbsp;<a href="{{route('responsables.index')}}?id={{$app}}&id_modelo={{$modelo}}&estudiante_id={{$matricula->id_estudiante}}" class="btn-gmail" title="RESPONSABLES DEL ESTUDIANTE: Responsable Financiero (Acudiente), Mamá, Papá"><i class="fa fa-btn fa-users"></i> </a>
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

		<br><br>

		@include('matriculas.incluir.matriculas_anteriores')

	</div>
</div>
<br /><br />

@endsection

@section('scripts')

    <script type="text/javascript">
       

        $(document).ready(function() {

            //$('#btn_excel').show();

        });
    </script>
@endsection