<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>APPSIEL ..:: Software de Gestión Académica ::..</title>

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

    <!-- Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">

    <!-- Styles -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}
	
	<link rel="stylesheet" href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">
	
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">
    <style>
        body {
            font-family: 'Lato';
            background-image: url("fondo.jpg");
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


	<link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.min.css') }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
	<script src="{{ asset('js/editablegrid/editablegrid.js') }}"></script>
	<!-- [DO NOT DEPLOY] --> <script src="{{ asset('js/editablegrid/editablegrid_renderers.js') }}" ></script>
	<!-- [DO NOT DEPLOY] --> <script src="{{ asset('js/editablegrid/editablegrid_editors.js') }}" ></script>
	<!-- [DO NOT DEPLOY] --> <script src="{{ asset('js/editablegrid/editablegrid_validators.js') }}" ></script>
	<!-- [DO NOT DEPLOY] --> <script src="{{ asset('js/editablegrid/editablegrid_utils.js') }}" ></script>
	<!-- [DO NOT DEPLOY] --> <script src="{{ asset('js/editablegrid/editablegrid_charts.js') }}" ></script>
	<link rel="stylesheet" href="{{ asset('css/editablegrid/editablegrid.css') }}" type="text/css" media="screen">
	
	<style>
		body { font-family:'lucida grande', tahoma, verdana, arial, sans-serif; font-size:11px; }
		h1 { font-size: 15px; }
		a { color: #548dc4; text-decoration: none; }
		a:hover { text-decoration: underline; }
		table.testgrid { border-collapse: collapse; border: 1px solid #CCB; width: 800px; }
		table.testgrid td, table.testgrid th { padding: 5px; border: 1px solid #E0E0E0; }
		table.testgrid th { background: #E5E5E5; text-align: left; }
		input.invalid { background: red; color: #FDFDFD; }
	</style>

    @yield('estilos_1')
    @yield('estilos_2')
</head>
<body id="app-layout">
	
	@include('layouts.menu_principal')
	
	<div class="main">
		@if(Session::has('flash_message'))
			<div class="container">      
				<div class="alert alert-success alert-dismissible">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<em> {!! session('flash_message') !!}</em>
				</div>
			</div>
		@endif 
		
		@if(Session::has('mensaje_error'))
			<div class="container">      
				<div class="alert alert-danger alert-dismissible">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<em> {!! session('mensaje_error') !!}</em>
				</div>
			</div>
		@endif 
		
		@yield('content')
	</div>
    
    <!-- JQuery -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js" integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb" crossorigin="anonymous"></script>
    
    <!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">

	<!-- Optional theme -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>
	
	{{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
	<script src="{{asset('js/ajaxscript.js')}}"></script>
	<script src="{{asset('js/boletines.js')}}"></script>

	<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
	
	<script>
		$(document).ready( function () {
			$('#myTable').DataTable();
			@yield('j_query')
		} );
	</script>

	<script type="text/javascript" src="{{ asset('js/tableEdit.js') }}"></script>	<!-- tabla editable-->
	<script type="text/javascript" src="{{ asset('js/jquery.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/bootstrap.min.js') }}"></script>
	<script type="text/javascript" src="{{ asset('js/function.js') }}"></script> 	<!-- mi ajax -->
	
	@yield('scripts')
	@yield('scripts2')
</body>
</html>
