<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <?php

	//$aplicaciones_inactivas_demo = [9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 6];
	$aplicaciones_inactivas_demo = [];
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
    <link rel="stylesheet" href="{{ asset('assets/css/jquery.dataTables.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/css/mis_estilos.css') }}">
    <link rel="stylesheet" href="{{asset('css/sweetAlert2.min.css')}}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/bower_components/select2/dist/css/select2.min.css')}}">

    <!-- Estilos de las tablas tipo GMAIL -->
    <link rel="stylesheet" href="{{ asset('css/Styletable.css')}}">


    @if( app()->environment() == 'demo' )
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-123891072-2"></script>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-XW7BPKHWM7"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());

	gtag('config', 'G-XW7BPKHWM7');
    </script>
    @endif
    <style type="text/css">
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
            background-position: bottom;
            background-attachment: fixed;
            background-size: cover;
            background-image: url({{asset('assets/img/academico_estudiante/fondo-estudiante.png')}})
			/*width: 98%;*/
		}

        body,
        html {
            height: 100%;
        }

        /* Colorize-zoom Container */
        .img-hover-zoom--colorize img {
            transition: transform .1s;
        }

        /* The Transformation */
        .img-hover-zoom--colorize:hover img {
            transform: scale(1.4);
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

        /* remove outer padding */
        .main .row {
            padding: 0px;
            margin: 0px;
        }

        /*Remove rounded coners*/

        nav.sidebar.navbar {
            border-radius: 0px;
        }

        nav.sidebar,
        .main {
            -webkit-transition: margin 200ms ease-out;
            -moz-transition: margin 200ms ease-out;
            -o-transition: margin 200ms ease-out;
            transition: margin 200ms ease-out;
        }

        /* Add gap to nav and right windows.*/
        .main {
            padding: 10px 10px 0 10px;
        }

        /* .....NavBar: Icon only with coloring/layout.....*/

        /*small/medium side display*/
        @media (min-width: 768px) {

            /*Allow main to be next to Nav*/
            .main {
                position: absolute;
                width: calc(100% - 40px);
                /*keeps 100% minus nav size*/
                margin-left: 40px;
                float: right;
            }

            /*lets nav bar to be showed on mouseover*/
            nav.sidebar:hover+.main {
                margin-left: 200px;
            }

            /*Center Brand*/
            nav.sidebar.navbar.sidebar>.container .navbar-brand,
            .navbar>.container-fluid .navbar-brand {
                margin-left: 0px;
            }

            /*Center Brand*/
            nav.sidebar .navbar-brand,
            nav.sidebar .navbar-header {
                text-align: center;
                font-size: 18px;
                width: 100%;
                height: 50px;
                color: white !important;
            }

            /*Center Icons*/
            nav.sidebar a {
                padding-right: 13px;
            }

            /*adds border top to first nav box */
            nav.sidebar .navbar-nav>li:first-child {
                border-top: 1px #e5e5e5 solid;
            }

            /*adds border to bottom nav boxes*/
            nav.sidebar .navbar-nav>li {
                border-bottom: 1px #e5e5e5 solid;
            }

            /* Colors/style dropdown box*/
            nav.sidebar .navbar-nav .open .dropdown-menu {
                position: static;
                float: none;
                width: auto;
                margin-top: 0;
                background-color: transparent;
                border: 0;
                -webkit-box-shadow: none;
                box-shadow: none;
            }

            /*allows nav box to use 100% width*/
            nav.sidebar .navbar-collapse,
            nav.sidebar .container-fluid {
                padding: 0 0px 0 0px;
            }

            /*colors dropdown box text */
            .navbar-inverse .navbar-nav .open .dropdown-menu>li>a {
                color: #ffffff;
            }

            /*gives sidebar width/height*/
            nav.sidebar {
                width: 200px;
                height: 100%;
                margin-left: -160px;
                float: left;
                z-index: 8000;
                margin-bottom: 0px;
            }

            /*give sidebar 100% width;*/
            nav.sidebar li {
                width: 100%;
            }

            nav.sidebar li > a{
                color: white !important;
            }

            /* Move nav to full on mouse over*/
            nav.sidebar:hover {
                margin-left: 0px;
            }

            /*for hiden things when navbar hidden*/
            .forAnimate {
                opacity: 0;
            }
        }

        /* .....NavBar: Fully showing nav bar..... */

        @media (min-width: 1330px) {

            /*Allow main to be next to Nav*/
            .main {
                width: calc(100% - 200px);
                /*keeps 100% minus nav size*/
                margin-left: 200px;
            }

            /*Show all nav*/
            nav.sidebar {
                margin-left: 0px;
                float: left;
            }

            /*Show hidden items on nav*/
            nav.sidebar .forAnimate {
                opacity: 1;
            }
        }

        nav.sidebar .navbar-nav .open .dropdown-menu>li>a:hover,
        nav.sidebar .navbar-nav .open .dropdown-menu>li>a:focus {
            color: #ffffff;
            background-color: transparent;
        }

        nav:hover .forAnimate {
            opacity: 1;
        }

        section {
            padding-left: 15px;
        }
    </style>

    @yield('webstyle')
    @yield('estilos_1')
    @yield('estilos_2')
</head>

<body id="app-layout">

    
    <?php 

        if ( !isset($estudiante) ) {
            $estudiante = \App\Matriculas\Estudiante::get_por_usuario( Auth::user()->id );
        }
        
        if ( !isset($curso) ) {
            $curso = \App\Matriculas\Curso::find( $estudiante->matricula_activa()->curso_id );
        }
    
        $libreta = App\Tesoreria\TesoLibretasPago::where( 'id_estudiante', $estudiante->id )->get()->last();

        if ( is_null($libreta ) )
        {
            $libreta_id = 0;
        }else{
            $libreta_id = $libreta->id;
        }
    ?>


    <div class="container-fluid">
        
        <div id="div_cargando">Cargando...</div>

        <nav class="navbar navbar-inverse navbar-static-top" style="background: rgb(87, 70, 150) !important;">
            <div class="container-fluid">
                <div class="navbar-header">

                    <!-- Collapsed Hamburger -->
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                        <span class="sr-only">Toggle Navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
    
                    <!-- Branding Image -->
                    <a class="navbar-brand" href="{{ url('/inicio') }}" style="height: 50px; padding-top: 5px;">
                        <img src="{{ asset('assets/img/appsiel-logo2.png') }}" width="180" height="50px">
                    </a>
                </div>
    
                <div class="collapse navbar-collapse" id="app-navbar-collapse">    
                    <!-- Right Side Of Navbar -->
                    <ul class="nav navbar-nav navbar-right">
                        <!-- Authentication Links -->
                        @if (Auth::guest())
                            <li><a style="color: #FFFFFF !important;" href="{{ url('/login') }}">Ingresar</a></li>
                        @else
                            
                            @if( config('configuracion.usar_mensajes_internos') )
                                <li>
                                    <a title="Mis Mensajes" style="color: #FFFFFF !important;" href="{{url('/messages')}}"><i class="fa fa-btn fa-envelope"></i>  @include('core.messenger.unread-count')</a>
                                </li>
                            @endif
    
                            <li class="dropdown">
                                <a style="color: #FFFFFF !important;" href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                                    {{ Auth::user()->name }} <span class="caret"></span>
                                </a>
    
                                <ul class="dropdown-menu" role="menu" style="background-color: #42A3DC !important;">
                                    <!-- <li><a href="{ { url('/dashboard?id='.Input::get('id')) }}"><i class="fa fa-btn fa-dashboard"></i>DashBoard</a></li> -->
    
                                    @if( !is_null( Input::get('id') ) )
                                        <li><a style="color: #FFFFFF !important;" href="{{ url('/core/usuario/perfil/?id='.Input::get('id')) }}"><i class="fa fa-btn fa-user"></i> Perfil</a></li>
                                    @else
                                        <li><i>(Ingrese a una aplicación para ver su perfil)</i></li>
                                    @endif
    
                                    <li><a style="color: #FFFFFF !important;" href="{{ url('/logout') }}"><i class="fa fa-btn fa-sign-out"></i> Cerrar sesión</a></li>
                                </ul>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>

        <nav class="navbar navbar-inverse sidebar" role="navigation" style="background: rgb(87, 70, 150) !important;">
            <div class="container-fluid">
                <!-- Brand and toggle get grouped for better mobile display -->
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse"
                        data-target="#bs-sidebar-navbar-collapse-1">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>

                    <a class="navbar-brand" href="{{ url('/academico_estudiante?id=6') }}">
                        <span style="font-size:16px;"
                        class="pull-left hidden-xs showopacity glyphicon glyphicon-home"></span> <span class="hidden-xs" style="font-size: 0.8em; text-align:left; margin-left: -90px !important;">Inicio</span>
                    </a>
                </div>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <div class="collapse navbar-collapse" id="bs-sidebar-navbar-collapse-1">
                    <ul class="nav navbar-nav">
                        
                        @if( (int)config('calificaciones.activar_horario'))
                        <li>
                            <a href="{{url('academico_estudiante/horario?id='.Input::get('id'))}}">
                                <span style="font-size:16px;"
                                    class="pull-left hidden-xs showopacity glyphicon glyphicon-calendar"></span>
                                &nbsp; Horario
                            </a>
                        </li>
                        @endif

                        <li>
                            <a href="{{url('academico_estudiante/calificaciones?id='.Input::get('id'))}}">
                                <span style="font-size:16px;"
                                    class="pull-left hidden-xs showopacity fa fa-sort-numeric-desc"></span>
                                    &nbsp; Calificaciones
                            </a>
                        </li>

                        @if( (int)config('calificaciones.activar_aula_virtual') )
                        <li>
                            <a
                                href="{{ url( 'academico_estudiante_aula_virtual/'.$curso->id.'?id='.Input::get('id') . '&fecha=' . date('Y-m-d') ) }}">
                                <span style="font-size:16px;"
                                    class="pull-left hidden-xs showopacity glyphicon glyphicon-blackboard"></span>
                                    &nbsp; Aula Virtual
                            </a>
                        </li>
                        @endif

                        <li>
                            <a href="{{ url( 'mis_asignaturas/'.$curso->id.'?id='.Input::get('id') ) }}">
                                <span style="font-size:16px;"
                                    class="pull-left hidden-xs showopacity glyphicon glyphicon-book"></span>
                                    &nbsp; Mis Asignaturas
                            </a>
                        </li>

                        @if( (int)config('calificaciones.activar_libreta_pagos') )
                        <li>
                            <a
                                href="{{ url('academico_estudiante/mi_plan_de_pagos/'.$libreta_id.'?id='.Input::get('id'))}}">
                                <span style="font-size:16px;"
                                    class="pull-left hidden-xs showopacity fa fa-dollar"></span>
                                    &nbsp; Libreta de pagos
                            </a>
                        </li>
                        @endif

                        @if( config('calificaciones.url_correo_institucional') != '')
                        <li>
                            <a href="{{ config('calificaicones.url_correo_institucional') }}" target="_blank">
                                <span style="font-size:16px;"
                                    class="pull-left hidden-xs showopacity glyphicon glyphicon-envelope"></span>
                                    &nbsp; Correo institucional
                            </a>
                        </li>
                        @endif

                        
                        @if( (int)config('calificaciones.activar_reconocimientos') )
                        <li>
                            <a href="{{ url('academico_estudiante/reconocimientos?id=' . Input::get('id') ) }}">
                                <span style="font-size:16px;"
                                    class="pull-left hidden-xs showopacity glyphicon glyphicon-envelope"></span>
                                    &nbsp; Reconocimientos
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </nav>
        
        <div class="main">

            @yield('content')

        </div>

        <a href="{{route('ayuda.videos')}}">
            <div id="paula"
                style="background-size: 100% 100%; background-image: url('{{asset('assets/images/ayuda.png')}}'); height: 160px; width: 174px">
                <div class="paula"
                    style="font-size: 13px; color: #574696;border: 1px solid #574696; position: absolute; right: 0px; bottom: -6px; border-radius: 10px; background-color: white; padding: 2px 4px">
                    Tutoriales <i class="fa fa-arrow-right"></i>
                </div>
            </div>
        </a>

        <div id="btnPaula">
            <button onclick="paula()" style="border-radius: 50px;" class="btn btn-danger">¿Ayuda?</button>
        </div>
    </div>

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

    <script src="https://cdn.ckeditor.com/4.16.0/standard-all/ckeditor.js"></script>

    <script src="{{asset('js/sweetAlert2.min.js')}}"></script>
    <!-- Select2 -->
    <script src="{{ asset('assets/bower_components/select2/dist/js/select2.full.min.js')}}"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <!-- 
<script src="https://unpkg.com/jspdf@ latest/dist/jspdf.min.js"></script>
-->
    <script type="text/javascript">
        var url_raiz = "{{ url('/') }}";

    var control_requeridos; // es global para que se pueda usar dentro de la función each() de abajo
    function validar_requeridos() {
        control_requeridos = true;
        $("*[required]").each(function() {
            if ($(this).val() == "" || $(this).val() == null) {
                $(this).focus();
                var name_campo = $(this).attr('name');
                var lbl_campo = $(this).parent().prev('label').text();
                if( lbl_campo === '' )
                {
                    lbl_campo = $(this).prev('label').text();
                }
                alert( 'Este campo es requerido: ' + lbl_campo + ' (' + name_campo + ')' );

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
            $("#btnPaula").html("<button class='btn btn-danger' style='border-radius: 50px;' onclick='paula()'>Ocultar Paula</button>");
            $("#paula").fadeIn();
            verPaula = false;
        } else {
            //ocultar paula
            $("#btnPaula").html("<button class='btn btn-danger' style='border-radius: 50px;' onclick='paula()'>¿Ayuda?</button>");
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

    function cargarCombobox(){
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

        

        $(cargarCombobox());

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
    @yield('scripts11')
    @yield('scripts12')
    @yield('scripts13')
    @yield('scripts14')
    @yield('scripts15')
    @yield('odontograma')
    @yield('multiselect')

    <script src="{{ asset('assets/js/gstatic/loader.js') }}"></script>
    <script>
        window.google.charts.load('46', {
        packages: ['corechart'],
        language: 'es'
        
    });
    </script>

</body>

</html>