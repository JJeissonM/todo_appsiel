<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php

    	$aplicaciones_inactivas_demo = [ 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 6];
    	$app = App\Sistema\Aplicacion::find( Input::get('id') );
    ?>

    <title>
    	@if(!is_null($app)) {{ $app->descripcion }} - APPSIEL @else Inicio - APPSIEL @endif
    </title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">

    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- Styles -->
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
	<!-- Glyphicons -->
	<link href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-glyphicons.css" rel="stylesheet">


	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
	
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">

	<link rel="stylesheet" href="{{ asset('assets/css/mis_estilos.css') }}">

	

    <style>
        body {
            font-family: 'Lato';
            background-color: #FAFAFA !important;
            /*width: 98%;*/
        }

        #suggestions {
		    position: absolute;
		    z-index: 9999;
		}
		#proveedores_suggestions {
		    position: absolute;
		    z-index: 9999;
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

	  #div_cargando{
			display: none;/**/
			color: #FFFFFF;
			background: #3394FF;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			/*right:0px; A la izquierda deje un espacio de 0px*/
			bottom:0px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div*/
			z-index:999;
			width: 100%;
    		text-align: center;
		}

		#popup_alerta_danger{
			display: none;/**/
			color: #FFFFFF;
			background: red;
			border-radius: 5px;
			position: fixed; /*El div será ubicado con relación a la pantalla*/
			/*left:0px; A la derecha deje un espacio de 0px*/
			right:10px; /*A la izquierda deje un espacio de 0px*/
			bottom:10px; /*Abajo deje un espacio de 0px*/
			/*height:50px; alto del div */
			width: 20%;
			z-index:999999;
			float: right;
    		text-align: center;
    		padding: 5px;
    		opacity: 0.7;
		}
		
	  </style>

    @yield('estilos_1')
    @yield('estilos_2')
</head>
<body id="app-layout">
	
	<div id="div_cargando">Cargando...</div>

	<div id="popup_alerta_danger"> </div>
	
	@include('layouts.menu_principal')

	<div class="container-fluid">

		@if( app()->environment() != 'demo' || !in_array( Input::get('id'), $aplicaciones_inactivas_demo ) )

			@yield('content')

		@else
			@include('layouts.demo_pagina_bloqueo_aplicaciones')
        @endif
	</div>



    <!-- JQuery -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js" integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb" crossorigin="anonymous"></script>

	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	
	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

	<!-- Convertir tabla a JSON -->
	<script src="https://cdn.jsdelivr.net/npm/table-to-json@0.13.0/lib/jquery.tabletojson.min.js" integrity="sha256-AqDz23QC5g2yyhRaZcEGhMMZwQnp8fC6sCZpf+e7pnw=" crossorigin="anonymous"></script>

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

	<script src="https://cdn.ckeditor.com/4.11.4/standard-all/ckeditor.js"></script>

	<script>

			

			var control_requeridos; // es global para que se pueda usar dentro de la función each() de abajo
			function validar_requeridos()
			{
				control_requeridos = true;
				$( "*[required]" ).each(function() {
					if ( $(this).val() == "" )
					{
					  $(this).focus();
					  alert( 'Este campo es requerido: ' + $(this).attr('name') );
					  control_requeridos = false;
					  return false;
					}
				});

				return control_requeridos;
			}

			function validar_input_numerico( obj )
			{
				var control = true;
				var valor = obj.val();
				
				if( valor != '')
				{
					obj.attr('style','background-color:white;');
					if( !$.isNumeric(valor) )
					{
						obj.attr('style','background-color:#FF8C8C;'); // Color rojo
						obj.focus();
						control = false;
					}
				}

				return control;
			}

			function get_fecha_hoy()
			{
				var today = new Date();
				var dd = today.getDate();
				var mm = today.getMonth()+1; //January is 0!
				var yyyy = today.getFullYear();

				if(dd<10) {
				    dd = '0'+dd;
				} 

				if(mm<10) {
				    mm = '0'+mm;
				} 

				return yyyy + '-' + mm + '-' + dd;
			}

			function get_hora_actual()
			{
				var today = new Date();
				var hora = today.getHours();
				if(hora<10) {
				    hora = '0'+hora;
				}

				var minutos = today.getMinutes();
				if(minutos<10) {
				    minutos = '0'+minutos;
				}

				var segundos = today.getSeconds();
				if(segundos<10) {
				    segundos = '0'+segundos;
				}

				return hora + ':' + minutos + ':' + segundos;
			}

			function getParameterByName(name) {
			    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
			    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
			    results = regex.exec(location.search);
			    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
			}
			
		$(document).ready( function () {
			
			// Para Autocompletar
			var campo_busqueda_texto;
			var campo_busqueda_numerico;
			var url_consulta;

			$('#myTable').DataTable( {
		        dom: 'Bfrtip',
		        buttons: [
		            'excel', 'pdf'
		        ]
		    } );
		    

		    


			$( function() {
			    $.widget( "custom.combobox", {
			      _create: function() {
			        this.wrapper = $( "<span>" )
			          .addClass( "custom-combobox" )
			          .insertAfter( this.element );
			 
			        this.element.hide();
			        this._createAutocomplete();
			        this._createShowAllButton();
			      },
			 
			      _createAutocomplete: function() {
			        var selected = this.element.children( ":selected" ),
			          value = selected.val() ? selected.text() : "";
			 
			        this.input = $( "<input>" )
			          .appendTo( this.wrapper )
			          .val( value )
			          .attr( "title", "" )
			          .addClass( "custom-combobox-input ui-widget ui-widget-content ui-state-default ui-corner-left" )
			          .autocomplete({
			            delay: 0,
			            minLength: 0,
			            source: $.proxy( this, "_source" )
			          })
			          .tooltip({
			            classes: {
			              "ui-tooltip": "ui-state-highlight"
			            }
			          });
			 
			        this._on( this.input, {
			          autocompleteselect: function( event, ui ) {
			            ui.item.option.selected = true;
			            this._trigger( "select", event, {
			              item: ui.item.option
			            });
			          },
			 
			          autocompletechange: "_removeIfInvalid"
			        });
			      },
			 
			      _createShowAllButton: function() {
			        var input = this.input,
			          wasOpen = false;
			 
			        $( "<a>" )
			          .attr( "tabIndex", -1 )
			          .attr( "title", "Mostras todos los elementos" )
			          .tooltip()
			          .appendTo( this.wrapper )
			          .button({
			            icons: {
			              primary: "ui-icon-triangle-1-s"
			            },
			            text: false
			          })
			          .removeClass( "ui-corner-all" )
			          .addClass( "custom-combobox-toggle ui-corner-right" )
			          .on( "mousedown", function() {
			            wasOpen = input.autocomplete( "widget" ).is( ":visible" );
			          })
			          .on( "click", function() {
			            input.trigger( "focus" );
			 
			            // Close if already visible
			            if ( wasOpen ) {
			              return;
			            }
			 
			            // Pass empty string as value to search for, displaying all results
			            input.autocomplete( "search", "" );
			          });
			      },
			 
			      _source: function( request, response ) {
			        var matcher = new RegExp( $.ui.autocomplete.escapeRegex(request.term), "i" );
			        response( this.element.children( "option" ).map(function() {
			          var text = $( this ).text();
			          if ( this.value && ( !request.term || matcher.test(text) ) )
			            return {
			              label: text,
			              value: text,
			              option: this
			            };
			        }) );
			      },
			 
			      _removeIfInvalid: function( event, ui ) {
			 
			        // Selected an item, nothing to do
			        if ( ui.item ) {
			          return;
			        }
			 
			        // Search for a match (case-insensitive)
			        var value = this.input.val(),
			          valueLowerCase = value.toLowerCase(),
			          valid = false;
			        this.element.children( "option" ).each(function() {
			          if ( $( this ).text().toLowerCase() === valueLowerCase ) {
			            this.selected = valid = true;
			            return false;
			          }
			        });
			 
			        // Found a match, nothing to do
			        if ( valid ) {
			          return;
			        }
			 
			        // Remove invalid value
			        this.input
			          .val( "" )
			          .attr( "title", value + " Ningún item coincide." )
			          .tooltip( "open" );
			        this.element.val( "" );
			        this._delay(function() {
			          this.input.tooltip( "close" ).attr( "title", "" );
			        }, 2500 );
			        this.input.autocomplete( "instance" ).term = "";
			      },
			 
			      _destroy: function() {
			        this.wrapper.remove();
			        this.element.show();
			      }
			    });
			 
			    $( ".combobox" ).combobox();
			    /*$( "#toggle" ).on( "click", function() {
			      $( ".combobox" ).toggle();
			    });
			    */

			  } );

	
			@yield('j_query')

		} );
	</script>
	
	@yield('scripts')
	@yield('scripts2')
	@yield('scripts3')
</body>
</html>