@extends('layouts.principal')

<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}

	<div class="row">
		<div class="col-md-10 col-md-offset-1">

			
			{{ Form::open(['url'=>'contab_ajax_balance_comprobacion','id'=>'form_consulta']) }}
				<div class="row">
					<div class="col-sm-3">
						{{ Form::label('fecha_desde','Fecha desde') }}
						{{ Form::date('fecha_desde',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_desde']) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label('fecha_hasta','Fecha hasta') }}
						{{ Form::date('fecha_hasta',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_hasta']) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label('contab_cuenta_grupo_id','Grupo de cuentas') }}
						{{ Form::select('contab_cuenta_grupo_id',$grupos,'todos',[ 'class' => 'form-control','id'=>'contab_cuenta_grupo_id']) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label('detalla_terceros','Detalla terceros') }}
						{{ Form::select('detalla_terceros',['No'=>'No', 'Si'=>'Si'],null,[ 'class' => 'form-control','id'=>'detalla_terceros']) }}
					</div>
				</div>
				<div class="row" style="margin-top:15px;">
					<div class="col-sm-3">
						{{ Form::label('agrupar_por_grupo','Agrupar por grupo con subtotales') }}
						{{ Form::select('agrupar_por_grupo',[0=>'No', 1=>'Sí'],0,['class'=>'form-control','id'=>'agrupar_por_grupo']) }}
					</div>
					<div class="col-sm-3">
						{{ Form::label(' ','.') }}
						<button type="submit" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</button>
					</div>
				</div>
			{{ Form::close() }}
			
		</div>
	</div>

	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
			{{ Form::bsBtnExcel('balance_de_comrpobacion') }}
			
			<div id="resultado_consulta">

			</div>	
		</div>
	</div>
	<br/><br/>	

@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $('#fecha_desde').focus();
            var generando = false;
            $('#form_consulta').on('submit', function(event){
                event.preventDefault();
                if (generando) { return; }
                var desde = $('#fecha_desde').val();
                var hasta = $('#fecha_hasta').val();
                if (!desde || !hasta || desde > hasta) {
                    alert('Ingrese las fechas. La fecha desde debe ser menor o igual a la fecha hasta.');
                    return;
                }
                generando = true;
                $('#btn_generar').prop('disabled', true);
                $('#div_cargando').show();
                var formulario = $(this);
                $.post(formulario.attr('action'), formulario.serialize())
                    .done(function(respuesta){
                        $('#resultado_consulta').html(respuesta);
                        $('#btn_excel').show(500);
                    })
                    .fail(function(){
                        $('#resultado_consulta').text('No fue posible generar el balance. Intente nuevamente.');
                    })
                    .always(function(){
                        generando = false;
                        $('#btn_generar').prop('disabled', false);
                        $('#div_cargando').hide();
                    });
            });
        });
    </script>
@endsection
