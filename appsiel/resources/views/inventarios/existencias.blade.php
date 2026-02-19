@extends('layouts.reportes')

@section('sidebar')
	
	{{ Form::open(['url'=>'ajax_existencias','id'=>'form_consulta']) }}
		{{ Form::label('fecha_corte','Fecha corte') }}
		{{ Form::date('fecha_corte',date('Y-m-d'),['class'=>'form-control','id'=>'fecha_corte']) }}
<br>
		{{ Form::label('mov_bodega_id','Bodega') }}
		{{ Form::select('mov_bodega_id',$bodegas,null,['class'=>'form-control','id'=>'mov_bodega_id']) }}
<br>
		{{ Form::label('grupo_inventario_id','Grupo de inventarios') }}
		{{ Form::select('grupo_inventario_id',$grupo_inventario,null,['class'=>'form-control','id'=>'grupo_inventario_id']) }}
<br>

		@if( !empty($prefijos_referencias) )			
			{{ Form::label('prefijo_referencia_id', config('inventarios.etiqueta_prefijo_referencia') ) }}
			{{ Form::select('prefijo_referencia_id',$prefijos_referencias,null,['class'=>'form-control','id'=>'prefijo_referencia_id']) }}
			<br>			
		@endif

		@if( !empty($tipos_de_prendas) )
			
			{{ Form::label('tipo_prenda_id','Tipo de prenda') }}
			{{ Form::select('tipo_prenda_id',$tipos_de_prendas,null,['class'=>'form-control','id'=>'tipo_prenda_id']) }}
			<br>
			
		@endif

		@if( !empty($tipos_de_material) )
			
			{{ Form::label('tipo_material_id','Tipo de Material') }}
			{{ Form::select('tipo_material_id',$tipos_de_material,null,['class'=>'form-control','id'=>'tipo_material_id']) }}
			<br>
			
		@endif

		{{ Form::label('item_id','Producto') }}
		{{ Form::select('item_id',$items,null,['class'=>'combobox','id'=>'item_id']) }}
<br>
		{{ Form::label('detallar_bodega','Detallar bodega',['class'=>'form-control','style' => 'display:none']) }}
		{{ Form::select('detallar_bodega',["No","Si"],null,['class'=>'form-control','id'=>'detallar_bodega', 'style' => 'display:none']) }}
<br>
		{{ Form::label('mostrar_items_sin_movimiento','Mostrar productos sin movimientos') }}
		{{ Form::select('mostrar_items_sin_movimiento',[0=>"No",1=>"Si"],0,['class'=>'form-control','id'=>'mostrar_items_sin_movimiento']) }}

		{{ Form::label('mostrar_costo','Mostar costo') }}
		{{ Form::select('mostrar_costo',[true=>"Si",false=>"No"],null,['class'=>'form-control','id'=>'mostrar_costo']) }}

		{{ Form::label('mostrar_cantidad','Mostar cantidad') }}
		{{ Form::select('mostrar_cantidad',[1=>"Si",0=>"No"],null,['class'=>'form-control','id'=>'mostrar_cantidad']) }}

		{{ Form::label(' ','.') }}
		<a href="#" class="btn btn-primary bt-detail form-control" id="btn_generar"><i class="fa fa-play"></i> Generar</a>
	{{ Form::close() }}

@endsection


@section('contenido')
		<div class="col-md-12 marco_formulario">
			<br/>
            {{ Form::bsBtnExcel('existencias_inventario') }}
			{{ Form::bsBtnPdf('existencias_inventario') }}
			{{ Form::Spin( 42 ) }}
			<div id="resultado_consulta">

			</div>	
		</div>
@endsection

@section('scripts_reporte')
	<script type="text/javascript">
		$(document).ready(function(){
			
			$('#fecha_corte').focus();
			$('#btn_print').hide();

			$('#fecha_corte').keyup(function(event){
				var x = event.which || event.keyCode;
				if(x==13){
					$('#mov_bodega_id').focus();				
				}		
			});

			$('#mov_bodega_id').change(function(){
				if ($('#mov_bodega_id').val()!='') {
					$('#grupo_inventario_id').focus();
				}
			});

			$('#grupo_inventario_id').change(function(){
				if ($('#grupo_inventario_id').val()!='') {
					$('#btn_generar').focus();
				}
			});

			$('#btn_ir').click(function(event){
				$('#form_consulta').submit();
			});

			// Click para generar la consulta
			$('#btn_generar').click(function(event){
				if(!valida_campos()){
					alert('Debe diligencias todos los campos.');
					return false;
				}

				//$('#btn_print').hide();
				$('#resultado_consulta').html('');
				$('#div_cargando').show();
				$('#div_spin').show();

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize()  + "&accion=consultar";

				$.post(url,datos,function(respuesta){
					$('#div_spin').hide();
					$('#div_cargando').hide();
					$('#resultado_consulta').html(respuesta);

					$('#btn_excel').show(500);
					$('#btn_pdf').show(500);

					var url_pdf = $('#btn_pdf').attr('href');
					var n = url_pdf.search('a3p0');
					if ( n > 0) {
						var new_url = url_pdf.replace( 'a3p0', 'generar_pdf/inv_existencias_corte?tam_hoja=' + $("#tam_hoja").val() + '&orientacion=' + $("#orientacion").val() );
					}
					
					$('#btn_pdf').attr('href', new_url);
				});
			});

			$('#btn_print').click(function(event){

				// Preparar datos de los controles para enviar formulario
				var form_consulta = $('#form_consulta');
				var url = form_consulta.attr('action');
				var datos = form_consulta.serialize()  + "&accion=imprimir";

				$.post(url,datos,function(respuesta){
					$('#resultado_consulta').html(respuesta);
				});
			});


			function valida_campos(){
				var valida = true;
				if($('#fecha_corte').val()=='' || $('#fecha_final').val()=='' ){
					valida = false;
				}
				return valida;
			}
		});

		
	</script>
@endsection
