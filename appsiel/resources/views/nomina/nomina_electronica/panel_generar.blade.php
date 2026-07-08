<h4> Generación y envío de Documentos de soporte de Nómina Electrónica</h4>
<p>
	Por esta opción, el sistema genera los documentos de soporte de nómina electrónica.
	<br>
	Un documento por cada empleado que tenga movimiento en el periodo selecionado.
	<br>
	Puede escoger la opción <b>Previsualizar</b> para revisar los documentos antes de hacer el envío hacia el proveedor tecnológico y la DIAN.
	@if( $msj_advertencia != '' )
		<div class="alert alert-danger">
		  <strong>Advertencia!</strong> {{ $msj_advertencia }}
		</div>
	@endif
</p>

<div class="row" id="div_formulario">
	{{ Form::open(['url'=>'nom_electronica_generar_doc_soporte','id'=>'formulario_inicial','files' => true]) }}

		<div class="row" style="padding:5px;">
			<label class="control-label col-sm-4" > <b> *Opciones de generación: </b> </label>
			<div class="col-sm-8">
				{{ Form::select( 'almacenar_registros', ['Previsualizar','Almacenar registros'],null, [ 'class' => 'form-control', 'id' => 'almacenar_registros' ]) }}
			</div>
		</div>

		<div class="row" style="padding:5px;">
			<label class="control-label col-sm-4" > <b> Fecha final periodo a generar: </b> </label>

			<div class="col-sm-8">
				{{ Form::date( 'fecha_final_periodo',null, [ 'class' => 'form-control', 'id' => 'fecha_final_periodo', 'required' => 'required' ]) }}
			</div>
		</div>

		<div class="row" style="padding:5px;">
			&nbsp;				 
		</div>
			
	{{ Form::close() }}

	@if( $msj_advertencia == '' )
		<div class="row" style="padding:5px; text-align: center;">
			<div class="col-md-6">

				<button class="btn btn-primary" id="btn_previsualizar"> <i class="fa fa-check"></i> Consultar </button>
			</div>
			<div class="col-md-6">
				<button type="button" class="btn btn-info" id="btn_enviar" style="display:none;" data-ids="[]">
					<i class="fa fa-send"></i> <span id="texto_btn_enviar">Enviar</span>
				</button>
				<span class="label label-default" id="contador_envio_generados" style="display:none; margin-left: 10px;">Enviados: 0 | Pendientes: 0</span>
			</div>
		</div>
	@endif	
		
</div>

<div id="mensaje_envio_generados" style="display:none; margin-bottom: 15px;"></div>


<div class="row" id="div_resultado_panel_generar">

</div>

@section('scripts2')
	<script type="text/javascript">

		$(document).ready(function(){
			var procesando_envio = false;
			var enviados_generados = 0;
			var pendientes_generados = 0;

			function get_ids_documentos_generados()
			{
				var ids = $("#btn_enviar").attr('data-ids');
				if (ids == null || ids == '') {
					return [];
				}

				try {
					ids = JSON.parse(ids);
				} catch (e) {
					return [];
				}

				if (!$.isArray(ids)) {
					return [];
				}

				return ids;
			}

			function actualizar_contador_envio()
			{
				$("#contador_envio_generados").text('Enviados: ' + enviados_generados + ' | Pendientes: ' + pendientes_generados);
			}

			function mostrar_mensaje_envio(tipo, texto_html)
			{
				$("#mensaje_envio_generados")
					.attr('class', 'alert alert-' + tipo)
					.html(texto_html)
					.show();
			}

			function ocultar_mensaje_envio()
			{
				$("#mensaje_envio_generados").removeAttr('class').html('').hide();
			}

			function bloquear_boton_envio(estado)
			{
				procesando_envio = estado;
				$("#btn_enviar").prop('disabled', estado);

				if (estado) {
					$("#btn_enviar").children('.fa-send').attr('class','fa fa-spinner fa-spin');
					$("#texto_btn_enviar").text('Enviando...');
				}else{
					$("#btn_enviar").children('.fa-spinner').attr('class','fa fa-send');
					$("#texto_btn_enviar").text('Enviar');
				}
			}

			function enviar_documento_generado(documento_id)
			{
				return $.ajax({
					url: "{{ url('nom_electronica_enviar_documento_ajax') }}" + '/' + documento_id,
					type: "post",
					dataType: "json",
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
						'X-Requested-With': 'XMLHttpRequest'
					}
				});
			}

			function procesar_siguiente_envio(ids, indice, errores)
			{
				if (indice >= ids.length) {
					bloquear_boton_envio(false);

					if (errores.length > 0) {
						$("#btn_enviar").attr('data-ids', JSON.stringify(errores.ids));
						mostrar_mensaje_envio('warning', 'Proceso finalizado. Enviados: ' + enviados_generados + '. Pendientes: ' + pendientes_generados + '. Errores:<br>' + errores.join('<br>'));
					}else{
						$("#btn_enviar").attr('data-ids', '[]');
						mostrar_mensaje_envio('success', 'Proceso finalizado. Todos los documentos fueron enviados correctamente.');
						$("#btn_enviar").hide();
					}

					return;
				}

				var documento_id = ids[indice];
				enviar_documento_generado(documento_id)
					.done(function(){
						enviados_generados++;
						pendientes_generados = Math.max(pendientes_generados - 1, 0);
						actualizar_contador_envio();
					})
					.fail(function(xhr){
						var respuesta = xhr.responseJSON || {};
						var mensaje = respuesta.message || 'Error no controlado durante el envío.';
						errores.push('Doc. ' + documento_id + ': ' + mensaje);
						errores.ids.push(documento_id);
					})
					.always(function(){
						procesar_siguiente_envio(ids, indice + 1, errores);
					});
			}
			
			$("#almacenar_registros").on('change',function(event){				
				if ( $(this).val() == 1 ) {
		 			$("#btn_previsualizar").attr('class','btn btn-success');
					$("#btn_previsualizar").html('<i class="fa fa-check"></i> Almacenar');
				}else{
		 			$("#btn_previsualizar").attr('class','btn btn-primary');
					$("#btn_previsualizar").html('<i class="fa fa-check"></i> Consultar');
				}
			});

			$("#btn_previsualizar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}


				$(this).children('.fa-check').attr('class','fa fa-spinner fa-spin');
		        //$(this).attr( 'disabled', 'disabled' );

		 		$("#div_cargando").show();
        		$("#div_resultado_panel_generar").html( '' );
        		$("#btn_enviar").hide().attr('data-ids', '[]');
        		$("#contador_envio_generados").hide();
        		ocultar_mensaje_envio();
				
				var form = $('#formulario_inicial');
				var url = form.attr('action');
				var datos = new FormData(document.getElementById("formulario_inicial"));

				$.ajax({
				    url: url,
				    type: "post",
				    dataType: "html",
				    data: datos,
				    cache: false,
				    contentType: false,
				    processData: false
				})
			    .done(function( respuesta ){

			    	$("#btn_previsualizar").children('.fa-spinner').attr('class','fa fa-check');

			        $('#div_cargando').hide();

        			$("#div_resultado_panel_generar").html( respuesta );
        			$("#div_resultado_panel_generar").fadeIn( 1000 );

					if( document.getElementById('status') != null )
					{
						if ( document.getElementById('status').value == 'success' && $('#almacenar_registros').val() == 1) {
							var arr_ids_docs_generados = document.getElementById('arr_ids_docs_generados').value;
							$("#btn_enviar").attr( 'data-ids', arr_ids_docs_generados);
							pendientes_generados = get_ids_documentos_generados().length;

							if (pendientes_generados == 0) {
								$("#btn_enviar").hide();
								$("#contador_envio_generados").hide();
								return;
							}

							$("#btn_enviar").fadeIn( 1000 );
							enviados_generados = 0;
							actualizar_contador_envio();
							$("#contador_envio_generados").show();
							ocultar_mensaje_envio();
						}
					}
			    })
			    .fail(function( xhr ){
			    	$("#btn_previsualizar").children('.fa-spinner').attr('class','fa fa-check');

			        $('#div_cargando').hide();

			        var respuesta = xhr.responseText;
			        if ( respuesta == null || respuesta == '' ) {
			        	respuesta = '<div class="alert alert-danger"><strong>Error:</strong> No fue posible generar los documentos. Revise el log de Laravel o el log del contenedor.</div>';
			        }

        			$("#div_resultado_panel_generar").html( respuesta );
        			$("#div_resultado_panel_generar").fadeIn( 1000 );
			    });
		    });

			$("#btn_enviar").on('click',function(event){
		    	event.preventDefault();

				if (procesando_envio) {
					return false;
				}

				var ids = get_ids_documentos_generados();
				if (ids.length == 0) {
					mostrar_mensaje_envio('warning', 'No hay documentos generados para enviar.');
					return false;
				}

				enviados_generados = 0;
				pendientes_generados = ids.length;
				actualizar_contador_envio();
				$("#contador_envio_generados").show();
				ocultar_mensaje_envio();
				bloquear_boton_envio(true);
				var errores = [];
				errores.ids = [];
				procesar_siguiente_envio(ids, 0, errores);
			});	

			$("#btn_retirar").on('click',function(event){
		    	event.preventDefault();

		    	if ( !validar_requeridos() )
		    	{
		    		return false;
		    	}

		 		$("#div_spin").show();
		 		$("#div_cargando").show();
        		$("#div_resultado_panel_generar").html( '' );

				var form = $('#formulario_inicial');
				var url = "{{ url('nom_retirar_retefuente') }}" + '/' + $('#nom_doc_encabezado_id').val();

				$.ajax({
				    url: url,
				    type: "get",
				    dataType: "html",
				    cache: false,
				    contentType: false,
				    processData: false
				})
			    .done(function( respuesta ){
			        $('#div_cargando').hide();
        			$("#div_spin").hide();

        			$("#div_resultado_panel_generar").html( respuesta );
        			$("#div_resultado_panel_generar").fadeIn( 1000 );
			    });
		    });

		});
	</script>
@endsection
