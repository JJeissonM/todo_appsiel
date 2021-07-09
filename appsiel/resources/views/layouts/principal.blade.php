<!DOCTYPE html>
<html lang="es">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<?php

	//$aplicaciones_inactivas_demo = [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 6];
	$aplicaciones_inactivas_demo = [17];
	$app = App\Sistema\Aplicacion::find(Input::get('id'));
	$modelo = App\Sistema\Modelo::find(Input::get('id_modelo'));

	$titulo = '';

	if (!is_null($modelo)) {
		$titulo = $modelo->descripcion . ' - ';
	}

	if (!is_null($app)) {
		$titulo .= $app->descripcion;
	} else {
		$titulo = 'Inicio';
	}

	$titulo .= ' - APPSIEL';

	?>

	<title>
		{{ $titulo }}
	</title>

	<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

	<!-- Fonts -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css"
		integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">

	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css">

	<!-- Styles -->
	<!-- Latest compiled and minified CSS -->
	<!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">-->
	<link rel="stylesheet" href="{{asset('assets/bootswatch-3.3.7/paper/bootstrap.min.css')}}">
	<!-- Glyphicons -->
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">


	<!-- Optional theme
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous"> -->
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">

	<link rel="stylesheet" href="{{ asset('assets/css/mis_estilos.css') }}">
	<link rel="stylesheet" href="{{asset('css/sweetAlert2.min.css')}}">
	<!-- Select2 -->
	<link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css')}}">

	<!-- Estilos de las tablas tipo GMAIL -->
	<link rel="stylesheet" href="{{ asset('css/Styletable.css')}}">


	@if( app()->environment() == 'demo' )
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-123891072-2"></script>
	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());
		gtag('config', 'UA-123891072-2');
	</script>
	@endif

	<style>
		@font-face {
			font-family: 'Gotham-Narrow-Medium';
			src: url("{{url('')}}/fonts/Gotham-Narrow-Medium/Gotham-Narrow-Medium.woff") format('woff'),
			url("{{url('')}}/fonts/Gotham-Narrow-Medium/Gotham-Narrow-Medium.woff2") format('woff2'),
			url("{{url('')}}/fonts/Gotham-Narrow-Medium/Gotham-Narrow-Medium.eot"),
			url("{{url('')}}/fonts/Gotham-Narrow-Medium/Gotham-Narrow-Medium.eot?#iefix") format('embedded-opentype'),
			url("{{url('')}}/fonts/Gotham-Narrow-Medium/Gotham-Narrow-Medium.otf") format('truetype');

			font-weight: normal;
			font-style: normal;
			font-display: swap;
		}

		body {
			font-family: 'Gotham-Narrow-Medium';
			background-color: #FAFAFA !important;
			/*width: 98%;*/
		}

		#suggestions {
			position: absolute;
			z-index: 9999;
		}
		
		#clientes_suggestions {
		    position: absolute;
		    z-index: 9999;
		}

		#proveedores_suggestions {
			position: absolute;
			z-index: 9999;
		}

		a.list-group-item-sugerencia {
			cursor: pointer;
		}

		/*
		#existencia_actual, #tasa_impuesto{
			width: 35px;
		}
		*/

		.custom-combobox {
			position: relative;
			display: inline-block;
		}

		.custom-combobox-toggle {
			position: absolute;
			top: 0;
			bottom: 0;
			margin-left: -1px;
			padding: 0;
		}

		.custom-combobox-input {
			margin: 0;
			padding: 5px 10px;
		}

		#div_cargando {
			display: none;
			/**/
			color: #FFFFFF;
			background: #3394FF;
			position: fixed;
			/*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			/*right:0px; A la izquierda deje un espacio de 0px*/
			bottom: 0px;
			/*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div*/
			z-index: 999;
			width: 100%;
			text-align: center;
		}

		#popup_alerta_danger {
			display: none;
			/**/
			color: #FFFFFF;
			background: red;
			border-radius: 5px;
			position: fixed;
			/*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			right: 10px;
			/*A la izquierda deje un espacio de 0px*/
			bottom: 10px;
			/*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div */
			width: 20%;
			z-index: 999999;
			float: right;
			text-align: center;
			padding: 5px;
			opacity: 0.7;
		}

		#popup_alerta_success {
			display: none;
			/**/
			color: #FFFFFF;
			background: #55b196;
			border-radius: 5px;
			position: fixed;
			/*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			right: 10px;
			/*A la izquierda deje un espacio de 0px*/
			bottom: 10px;
			/*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div */
			width: 20%;
			z-index: 999999;
			float: right;
			text-align: center;
			padding: 5px;
			opacity: 0.7;
		}

		#myTable th {
			padding: 0;
			font-size: 1.3rem;
		}

		#myTable td {
			padding: 5px !important;
			font-size: 1.3rem;
		}

		#paula {
			right: 10px;
			bottom: 70px;
			position: fixed;
			display: none;
			width: 300px;
			height: auto;
			text-align: center;
		}

		#btnPaula {
			right: 20px;
			bottom: 15px;
			position: fixed;
			z-index: 1000;
		}
	</style>

	@yield('webstyle')
	@yield('estilos_1')
	@yield('estilos_2')
</head>

<body id="app-layout">

	<div id="div_cargando">Cargando...</div>

	<div id="popup_alerta_danger"> </div>

	<div id="popup_alerta_success"> </div>

	@include('layouts.menu_principal')

	<div class="container-fluid">

		@if( app()->environment() != 'demo' || !in_array( Input::get('id'), $aplicaciones_inactivas_demo ) )

		@yield('content')

		@else
		@include('layouts.demo_pagina_bloqueo_aplicaciones')
		@endif
	</div>

	<div id="paula" style="background-size: 100%; background-position: 100% 100%; background-image: url('{{asset('assets/images/ayuda.png')}}'); height: 305px; width: 332px">
		<div class="paula" style="position: absolute; right: 25px; bottom: -10px;">
			<a href="{{route('ayuda.videos')}}" class="btn btn-block btn-primary my-2">Tutoriales <i
					class="fa fa-arrow-right"></i></a>
		</div>
			
		
			
	</div>
	<div id="btnPaula">
		<button onclick="paula()" style="border-radius: 50%;" class="btn btn-danger">¿Ayuda?</button>
	</div>
	<!--<div id="paula">
			<img width="230px" height="350px" src="{{asset('assets/images/ayuda.png')}}" />
		</div>-->



	<!-- JQuery -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js"
		integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb" crossorigin="anonymous">
	</script>

	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
		integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous">
	</script>

	<!-- Convertir tabla a JSON -->
	<script src="https://cdn.jsdelivr.net/npm/table-to-json@0.13.0/lib/jquery.tabletojson.min.js"
		integrity="sha256-AqDz23QC5g2yyhRaZcEGhMMZwQnp8fC6sCZpf+e7pnw=" crossorigin="anonymous"></script>

	<!-- DataTable -->
	<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.2/js/dataTables.buttons.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.flash.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/pdfmake.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.36/vfs_fonts.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.html5.min.js"></script>
	<script src="https://cdn.datatables.net/buttons/1.5.2/js/buttons.print.min.js"></script>

	<!-- Convertir HTML a PDF -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/0.9.0rc1/jspdf.min.js"></script>
	<script src="{{asset('assets/js/todas_las_funciones.js')}}"></script>

	<!-- Table Export -->
	<script src="{{asset('assets/js/tableExport/xlsx.full.min.js')}}"></script>
	<script src="{{asset('assets/js/tableExport/FileSaver.min.js')}}"></script>
	<script src="{{asset('assets/js/tableExport/tableexport.min.js')}}"></script>


	<!-- <script src="{ {asset('js/ckeditor/ckeditor.js')}}"></script> -->

	<script src="https://cdn.ckeditor.com/4.16.0/standard-all/ckeditor.js"></script>

	<script src="{{asset('js/sweetAlert2.min.js')}}"></script>
	<!-- Select2 -->
	<script src="{{ asset('assets/bower_components/select2/dist/js/select2.full.min.js')}}"></script>

	<!-- 
	<script src="https://unpkg.com/jspdf@ latest/dist/jspdf.min.js"></script>
	-->
	<script type="text/javascript">

		var url_raiz = "{{ url('/') }}";

		var control_requeridos; // es global para que se pueda usar dentro de la función each() de abajo
		function validar_requeridos() {
			control_requeridos = true;
			$("*[required]").each(function() {
				if ($(this).val() == "") {
					$(this).focus();
					//alert( 'Este campo es requerido: ' + $(this).attr('name') );
					var lbl_campo = $(this).parent().prev('label').text();
					if( lbl_campo === '' )
					{
						lbl_campo = $(this).prev('label').text();
					}
					alert( 'Este campo es requerido: ' + lbl_campo );

					control_requeridos = false;
					return false;
				}
			});

			return control_requeridos;
		}

		var verPaula = true;

		function paula() {
			if (verPaula) {
				//ver paula
				$("#btnPaula").html("<button class='btn btn-danger' style='border-radius: 50%;' onclick='paula()'>Ocultar Paula</button>");
				$("#paula").fadeIn();
				verPaula = false;
			} else {
				//ocultar paula
				$("#btnPaula").html("<button class='btn btn-danger' style='border-radius: 50%;' onclick='paula()'>¿Ayuda?</button>");
				$("#paula").fadeOut();
				verPaula = true;
			}
		}


		function validar_input_numerico(obj) {
			var control = true;
			var valor = obj.val();

			if (valor != '') {
				obj.attr('style', 'background-color:transparent;');
				if (!$.isNumeric(valor)) {
					obj.attr('style', 'background-color:#FF8C8C;'); // Color rojo
					obj.focus();
					control = false;
				}
			}

			return control;
		}

		function get_fecha_hoy() {
			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth() + 1; //January is 0!
			var yyyy = today.getFullYear();

			if (dd < 10) {
				dd = '0' + dd;
			}

			if (mm < 10) {
				mm = '0' + mm;
			}

			return yyyy + '-' + mm + '-' + dd;
		}

		function get_hora_actual() {
			var today = new Date();
			var hora = today.getHours();
			if (hora < 10) {
				hora = '0' + hora;
			}

			var minutos = today.getMinutes();
			if (minutos < 10) {
				minutos = '0' + minutos;
			}

			var segundos = today.getSeconds();
			if (segundos < 10) {
				segundos = '0' + segundos;
			}

			return hora + ':' + minutos + ':' + segundos;
		}

		function getParameterByName(name) {
			name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
				results = regex.exec(location.search);
			return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
		}


		function ocultar_campo_formulario(obj_input, valor_requerido) {
			obj_input.prop('required', false);
			obj_input.prop('disabled', true);
			obj_input.hide();
			obj_input.parent().prev('label').text('');
		}

		function mostrar_campo_formulario(obj_input, texto_lbl, valor_requerido) {
			obj_input.prop('required', true);
			obj_input.prop('disabled', false);
			obj_input.show();
			obj_input.parent().prev('label').text(texto_lbl);
		}


		var email_inicial = $("#email").val();

		$(document).ready(function() {

			// Para Autocompletar
			var campo_busqueda_texto;
			var campo_busqueda_numerico;
			var url_consulta;

			$('#myTable').DataTable({
				dom: 'Bfrtip',
				"paging": false,
				buttons: [
					'excel', 'pdf'
				],
				order: [
					[0, 'desc']
				],
				"language": {
					            "search": "Buscar",
					            "zeroRecords": "Ningún registro encontrado.",
					            "info": "Mostrando página _PAGE_ de _PAGES_",
					            "infoEmpty": "Tabla vacía.",
					            "infoFiltered": "(filtrado de _MAX_ registros totales)"
					        }
			});


			// !!!! Solo valida en la tabla core_terceros
			$('#email').keyup(function() {

				var email = $("#email").val();

				url_2 = "{{ url('/core/validar_email/') }}" + "/" + email;

				console.log(url_2);

				$.get(url_2, function(datos) {
					if (datos != '') {
						if (datos == email_inicial) {
							// No hay problema
							$('#bs_boton_guardar').show();
						} else {
							alert("Ya existe una persona con ese EMAIL. Cambié el EMAIL o no podrá guardar el registro.");
							$('#bs_boton_guardar').hide();
						}

					} else {
						// Número de identificación
						$('#bs_boton_guardar').show();
					}

				});
			});

			$(function() {
				$.widget("custom.combobox", {
					_create: function() {
						this.wrapper = $("<span>")
							.addClass("custom-combobox")
							.insertAfter(this.element);

						this.element.hide();
						this._createAutocomplete();
						this._createShowAllButton();
					},

					_createAutocomplete: function() {
						var selected = this.element.children(":selected"),
							value = selected.val() ? selected.text() : "";

						this.input = $("<input>")
							.appendTo(this.wrapper)
							.val(value)
							.attr("title", "")
							.addClass("custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left")
							.autocomplete({
								delay: 0,
								minLength: 0,
								source: $.proxy(this, "_source")
							})
							.tooltip({
								classes: {
									"ui-tooltip": "ui-state-highlight"
								}
							});

						this._on(this.input, {
							autocompleteselect: function(event, ui) {
								ui.item.option.selected = true;
								this._trigger("select", event, {
									item: ui.item.option
								});
							},

							autocompletechange: "_removeIfInvalid"
						});
					},

					_createShowAllButton: function() {
						var input = this.input,
							wasOpen = false;

						$("<a>")
							.attr("tabIndex", -1)
							.attr("title", "Mostras todos los elementos")
							.tooltip()
							.appendTo(this.wrapper)
							.button({
								icons: {
									primary: "ui-icon-triangle-1-s"
								},
								text: false
							})
							.removeClass("ui-corner-all")
							.addClass("custom-combobox-toggle ui-corner-right")
							.on("mousedown", function() {
								wasOpen = input.autocomplete("widget").is(":visible");
							})
							.on("click", function() {
								input.trigger("focus");

								// Close if already visible
								if (wasOpen) {
									return;
								}

								// Pass empty string as value to search for, displaying all results
								input.autocomplete("search", "");
							});
					},

					_source: function(request, response) {
						var matcher = new RegExp($.ui.autocomplete.escapeRegex(request.term), "i");
						response(this.element.children("option").map(function() {
							var text = $(this).text();
							if (this.value && (!request.term || matcher.test(text)))
								return {
									label: text,
									value: text,
									option: this
								};
						}));
					},

					_removeIfInvalid: function(event, ui) {

						// Selected an item, nothing to do
						if (ui.item) {
							return;
						}

						// Search for a match (case-insensitive)
						var value = this.input.val(),
							valueLowerCase = value.toLowerCase(),
							valid = false;
						this.element.children("option").each(function() {
							if ($(this).text().toLowerCase() === valueLowerCase) {
								this.selected = valid = true;
								return false;
							}
						});

						// Found a match, nothing to do
						if (valid) {
							return;
						}

						// Remove invalid value
						this.input
							.val("")
							.attr("title", value + " Ningún item coincide.")
							.tooltip("open");
						this.element.val("");
						this._delay(function() {
							this.input.tooltip("close").attr("title", "");
						}, 2500);
						this.input.autocomplete("instance").term = "";
					},

					_destroy: function() {
						this.wrapper.remove();
						this.element.show();
					}
				});

				$(".combobox").combobox();
				/*$( "#toggle" ).on( "click", function() {
				  $( ".combobox" ).toggle();
				});
				*/

			});

			$('.enlace_dropdown').on('click', function() {
				$('#div_cargando').show();
			});

			@yield('j_query')

		});
	</script>

	<script src="{{ asset('assets/js/input_lista_sugerencias.js') }}"></script> <!-- -->

	@yield('scripts')
	@yield('scripts1')
	@yield('scripts2')
	@yield('scripts3')
	@yield('scripts4')
	@yield('scripts5')
	@yield('scripts6')
	@yield('scripts7')
	@yield('scripts8')
	@yield('scripts9')
	@yield('scripts10')

	<script src="https://www.gstatic.com/charts/loader.js"></script>
	<script>
		window.google.charts.load('46', {
			packages: ['corechart'],
			language: 'es'
			
		});
	</script>

</body>

</html>