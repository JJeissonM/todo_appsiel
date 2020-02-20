
<br>

<?php
	$formula = App\Salud\FormulaOptica::where('paciente_id', $consulta->paciente_id)->where('consulta_id', $consulta->id)->get()->first();
?>

@if( is_null($formula) )
	@can('salud_consultas_create')
		&nbsp;&nbsp;&nbsp;{{ Form::bsBtnCreate( 'consultorio_medico/formulas_opticas/create?id='.Input::get('id').'&id_modelo='.$modelo_formulas_opticas->id.'&paciente_id='.$registro->id.'&consulta_id='.$consulta->id ) }}
	@endcan
@else

	<?php 
		//$formula = $formulas[0]; 
		$examenes = DB::table('salud_formula_tiene_examenes')->leftJoin('salud_examenes','salud_examenes.id','=','salud_formula_tiene_examenes.examen_id')->where('formula_id', $formula->id)->select('salud_examenes.descripcion','salud_formula_tiene_examenes.formula_id','salud_formula_tiene_examenes.examen_id')->get();
	?>
	
	@can('salud_consultas_edit')
		&nbsp;&nbsp;&nbsp;{{ Form::bsBtnEdit( 'consultorio_medico/formulas_opticas/'.$formula->id.'/edit?id='.Input::get('id').'&id_modelo='.$modelo_formulas_opticas->id.'&paciente_id='.$registro->id.'&consulta_id='.$consulta->id ) }}
	@endcan
	
	&nbsp;&nbsp;&nbsp;{{ Form::bsBtnPrint( 'consultorio_medico/formulas_opticas/'.$formula->id.'/print?id='.Input::get('id').'&id_modelo='.$modelo_formulas_opticas->id.'&paciente_id='.$registro->id.'&consulta_id='.$consulta->id ) }}

	&nbsp;&nbsp;&nbsp;
	{{ Form::open( [ 'url' => 'consultorio_medico/eliminar_formula_optica?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo'), 'style' => 'display:inline;' ] ) }}
		{{ Form::hidden( 'formula_id', $formula->id ) }}
		{{ Form::hidden( 'ruta_redirect', 'consultorio_medico/pacientes/'.$registro->id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo') ) }}
		
		<button class="btn btn-danger btn-xs btn-detail btn_eliminar" title="Eliminar"> <i class="fa fa-trash"></i> &nbsp; </button>
	{{ Form::close() }}

	<br><br>
	
	@include('consultorio_medico.formula_optica_show_tabla' )

	<b>Exámenes asociados: </b> &nbsp;&nbsp;
	<div class="btns_examenes_asignados" style="display: inline;">
		@foreach( $examenes as $examen )
			<span class="label label-default label-md"> <span> {{ $examen->descripcion }} </span> <button class="desasociar_examen" style="background-color: transparent;" data-formula_id="{{$examen->formula_id}}" data-examen_id="{{$examen->examen_id}}">&times;</button> </span> &nbsp;&nbsp;&nbsp;
		@endforeach
	</div>
	<button class="btn btn-primary btn-xs agregar_examen"><i class="fa fa-btn fa-plus"></i></button>

@endif



@section('scripts3')
	<script type="text/javascript">
		$(document).ready(function(){
			
			var elementos_consulta_actual;

			$(".agregar_examen").click(function(event){
				event.preventDefault();

				elementos_consulta_actual = $(this).parents(".secciones_consulta");

				// El ID de la formula lo tiene una etiqueta div en el título
				var formula_id = elementos_consulta_actual.find(".formula_id").html();

				// Se reutiliza la caja modal, se oculta el botón editar y se cambia el título
				$(".btn_edit_examen").hide();
				$("#myModal").modal(
		        	{keyboard: 'true'}
		        );
		        $(".modal-title").html( "Haga click sobre los exámenes que quiera asociar a la fórmula." );
		        $("#alert_mensaje").hide();

		        // Se recorren los botones de la pestaña Exámenes, pues estos son los exámenes que realmente tiene el paciente en esta consulta
		        var botones = '';
		        elementos_consulta_actual.find(".btns_examenes button").each(function(){

		        	var texto_boton = $(this).attr('data-examen_descripcion');
		        	var examen_realizado_id = $(this).attr('data-examen_id');

		        	// Se valida que ya no esté asociado el exámen
		        	// Se recorren los botones YA asociados a la fórmula y se valida si ya está
		        	var esta = false;
		        	elementos_consulta_actual.find(".desasociar_examen").each(function(){
		        		if ( examen_realizado_id == $(this).attr('data-examen_id')) {
		        			esta = true;
		        		}
		        	});
		        	if ( !esta ) {
		        		botones = botones + '<p data-examen_descripcion="' + texto_boton + '">' + texto_boton + '  <button class="asociar_examen btn btn-xs btn-default" data-formula_id="' + formula_id + '" data-examen_id="' + examen_realizado_id + '" > <i class="fa fa-check"></i> </button></p>';
		        	}						
				});

		        // Se agrega al cuerpo de la ventana modal el listado de los exámenes que se le han practicado al paciente y que no se hayan asignado a la fórmula.
				$("#info_examen").html( botones );
			});


			// Al presionar el botón "check". Nota: este botón fue creado dinámicamente, no se puede acceder a él directamente desde su ID o CLASS, sino a través de document()
			$(document).on('click', '.asociar_examen', function() {
				$("#div_spin").show();

				console.log( elementos_consulta_actual );

				var linea = $(this).parent('p');
				var examen_descripcion = linea.attr('data-examen_descripcion');
				var formula_id = $(this).attr('data-formula_id');
				var examen_id = $(this).attr('data-examen_id');

				var url = "../../consultorio_medico/asociar_examen/formulas_opticas/" + formula_id + "/" + examen_id;

				$.get( url, function( respuesta ){
					$("#div_spin").hide();
					alert( "El exámen fue asignado correctamente!" );
					linea.remove();

					elementos_consulta_actual.find(".btns_examenes_asignados").append( '<span class="label label-default label-md"> <span> ' +  examen_descripcion + ' </span> <button class="desasociar_examen" style="background-color: transparent;" data-formula_id="' + formula_id + '" data-examen_id="' + examen_id + '">&times;</button> </span> &nbsp;&nbsp;&nbsp;' );
				});				
			});

			// Al presionar el botón X del exámen asociado.
			$(document).on('click', '.desasociar_examen', function() {
				event.preventDefault();

				elementos_consulta_actual = $(this).parents(".secciones_consulta");

				var linea = $(this).parent('span');

				if (confirm("Realmente quiere quitar el examen " + $(this).prev().html() + " de la formula?" )) {			

					$("#div_cargando").show();

					var url = "../../consultorio_medico/quitar_examen/formulas_opticas/" + $(this).attr('data-formula_id') + "/" + $(this).attr('data-examen_id');

					$.get( url, function( respuesta ){
						$("#div_cargando").hide();
						linea.remove();
						alert( "El exámen fue removido correctamente!" );
					});					  	
				}
			});
		});
	</script>
@endsection