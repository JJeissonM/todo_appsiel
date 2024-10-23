@extends('layouts.reportes')

@section('sidebar')
	{{ Form::open(['url'=>'ajax_stock_minimo','id'=>'form_consulta']) }}

        <!-- 
		{ { Form::label('fecha_corte','Fecha corte') }}
		{ { Form::date('fecha_corte',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_corte','disabled'=>'disabled']) }}
        -->

		{{ Form::label('bodega_id','Bodega') }}
		{{ Form::select('bodega_id', $bodegas, null, ['class'=>'form-control', 'id'=>'bodega_id']) }}

        @if( (int)config('inventarios.items_mandatarios_por_proveedor') )
            {{ Form::label('proveedor_id', 'Proveedor') }}
            {{ Form::select('proveedor_id', $proveedores, null, ['class'=>'form-control','id'=>'proveedor_id']) }}    
            
            
            {{ Form::label('detalla_proveedor', 'Mostrar por proveedor') }}
            {{ Form::select('detalla_proveedor', [ 1 => 'Si', 0 => 'No' ], null, ['class'=>'form-control','id'=>'detalla_proveedor']) }}    
        @endif

        <br><br>

		<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar </a>

	{{ Form::close() }}
@endsection


@section('contenido')
		<div class="col-md-12 marco_formulario">
			<br/>
			{{ Form::Spin( 42 ) }}
			<div id="resultado_consulta">
                {!! $tabla !!}
			</div>	
		</div>
@endsection

@section('scripts_reporte')
	<script type="text/javascript">
		$(document).ready(function(){

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				event.preventDefault();
                
                var newURL = url_raiz + '/inv_stock_minimo?id=8&id_modelo=0&show_table=true&bodega_id=' + $('#bodega_id').val() + '&proveedor_id=' + $('#proveedor_id').val() + '&detalla_proveedor=' + $('#detalla_proveedor').val()

                window.location.href = newURL
			});

		});		
	</script>
@endsection