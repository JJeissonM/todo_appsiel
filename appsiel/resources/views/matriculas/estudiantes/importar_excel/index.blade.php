<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>APPSIEL ..:: Software de Gestión Académica ::..</title>

	<!-- Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}
	
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
	
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">

	<script src="{{ asset('js/editablegrid/editablegrid.js') }}"></script>
	<!-- [DO NOT DEPLOY] --> <script src="{{ asset('js/editablegrid/editablegrid_renderers.js') }}" ></script>
	<!-- [DO NOT DEPLOY] --> <script src="{{ asset('js/editablegrid/editablegrid_editors.js') }}" ></script>
	<!-- [DO NOT DEPLOY] --> <script src="{{ asset('js/editablegrid/editablegrid_validators.js') }}" ></script>
	<!-- [DO NOT DEPLOY] --> <script src="{{ asset('js/editablegrid/editablegrid_utils.js') }}" ></script>
	<!-- [DO NOT DEPLOY] --> <script src="{{ asset('js/editablegrid/editablegrid_charts.js') }}" ></script>
	<link rel="stylesheet" href="{{ asset('css/editablegrid/editablegrid.css') }}" type="text/css" media="screen">
	
	<style>

		body {
            font-family: 'Lato';
        }

        .fa-btn {
            margin-right: 6px;
        }
		
		li.submenu {
			margin-left: 20px;
			border-right: 2px solid gray;
			border-bottom: 2px solid gray;
		}
		
		li.botonConfig {
			border-top: 1px solid gray;
			border-left: 1px solid gray;
			border-right: 2px solid gray;
			border-bottom: 2px solid gray;
			margin-left: 50px;
			width: 186px;
			height: 70px;
			text-align: center;  
			-moz-text-align-last: center; /* Code for Firefox */
			text-align-last: center;
		}

		/* Main content */
		.main {
		    margin-left: 200px; /* Same as the width of the sidenav */
		    font-size: 15px;  /*Increased text to enable scrolling */
		    padding: 0px 10px;
		}

	</style>
</head>
<body id="app-layout">
	@include('layouts.menu_principal')
	
	<div class="main">
		<h2>
			<i class="fa fa-child"></i> Gestor de Estudiantes
		</h2>
		<hr>
		
		<div class="col-sm-offset-2 col-sm-8">
			{{ Form::bsBtnVolver('/matriculas/estudiantes') }}
			<div class="panel panel-success">
				<div class="panel-heading" align="center">
					<h4>Cargue masivo de datos</h4>
				</div>
				
				<div class="panel-body">
					{{ Form::open (['url' => '/matriculas/estudiantes/importar_excel/import-excel', 'method' => 'POST', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal','id'=>'form_importar']) }}

					<div class="form-group">
						{{ Form::label('fichero', 'Seleccionar un archivo:', ['class' => 'col-sm-4 control-label']) }}
						<span class="btn btn-default btn-file">
							{{ Form::file('excel', ['class' => 'form-control','id' => 'file']) }}
						</span>
					</div>
					<div class="form-group">
						{{ Form::label('fichero', ' ', ['class' => 'col-sm-4 control-label']) }}
						<span class="btn-file">
							{{ Form::submit('Subir archivo', ['class' => 'btn btn-primary ', 'id' => 'request', 'onclick' => 'comprueba_extension(this.form, this.form.excel.value)'])}}
						</span>
						
					</div>
					{{ Form::close() }}

					@include('matriculas.estudiantes.importar_excel.partials.info')
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript" src="{{ asset('js/tableEdit.js') }}"></script>	<!-- tabla editable-->
	<script type="text/javascript" src="{{ asset('js/jquery.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/function.js') }}"></script> 	<!-- mi ajax -->
</body>
</html>
