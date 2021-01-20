<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title> APPSIEL ..:: Sistemas de información en línea ::..</title>

	<link rel="shortcut icon" href="{{ asset('favicon.ico') }}">

	<!-- Fonts -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css" integrity="sha384-XdYbMnZ/QjLh6iI4ogqCTaIjrFk87ip+ekIjefZch0Y+PvJ8CDYtEs1ipDmPorQ+" crossorigin="anonymous">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700">

	<!-- Styles 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">-->

	<style type="text/css">
		@font-face {
			font-family: 'Gotham-Narrow-Medium';
			src: url("fonts/Gotham-Narrow-Medium/Gotham-Narrow-Medium.woff") format('woff'),
				url("fonts/Gotham-Narrow-Medium/Gotham-Narrow-Medium.woff2") format('woff2'),
				url("fonts/Gotham-Narrow-Medium/Gotham-Narrow-Medium.eot"),
				url("fonts/Gotham-Narrow-Medium/Gotham-Narrow-Medium.eot?#iefix") format('embedded-opentype'),
				url("fonts/Gotham-Narrow-Medium/Gotham-Narrow-Medium.otf") format('truetype');

			font-weight: normal;
			font-style: normal;
			font-display: swap;
		}


		body {
			color: #fff;
			background-image: url('assets/images/fondo-inicio.jpeg');
			background-position: center center;
			background-repeat: no-repeat;
			background-attachment: fixed;
			background-size: cover;
			/*background: #d47677;*/
			font-family: "Gotham-Narrow-Medium";
		}

		.form-control {
			min-height: 41px;
			background: #fff;
			box-shadow: none !important;
			border-color: #e3e3e3;
		}

		.form-control:focus {
			border-color: #70c5c0;
		}

		.form-control,
		.btn {
			border-radius: 2px;
		}

		.login-form {
			width: 350px;
			margin: 0 auto;
			padding: 100px 0 30px;
		}

		.form-login {
			color: #000000;
			border-radius: 2px;
			margin-bottom: 15px;
			font-size: 13px;
			background: #FFFFFF;
			position: relative;
			border-radius: 15px;
			border: 1px solid #d9d7d7;
			-webkit-box-shadow: 4px 4px 5px -5px rgba(0, 0, 0, 0.75);
			-moz-box-shadow: 4px 4px 5px -5px rgba(0, 0, 0, 0.75);
			box-shadow: 4px 4px 5px -5px rgba(0, 0, 0, 0.75);
			font-size: 13px;
		}

		.login-form h2 {
			font-size: 22px;
			/*margin: 35px 0 25px;*/
		}

		.login-form .avatar {
			position: absolute;
			margin: 0 auto;
			left: 0;
			right: 0;
			top: -50px;
			width: 120px;
			height: 120px;
			border-radius: 50%;
			z-index: 9;
			background: #323232;
			/* #70c5c0; */
			padding: 15px;
			box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.1);
		}

		.login-form .avatar img {
			width: 100%;
		}

		.login-form input[type="checkbox"] {
			margin-top: 2px;
		}

		.login-form .btn {
			font-size: 16px;
			font-weight: bold;
			background: #70c5c0;
			border: none;
			margin-bottom: 20px;
		}

		.login-form .btn:hover,
		.login-form .btn:focus {
			background: #50b8b3;
			outline: none !important;
		}

		.login-form a {
			color: #fff;
			text-decoration: underline;
		}

		.login-form a:hover {
			text-decoration: none;
		}

		.login-form form a {
			color: #7a7a7a;
			text-decoration: none;
		}

		.login-form form a:hover {
			text-decoration: underline;
		}

		.footer {
			position: fixed;
			left: 0;
			bottom: 0;
			width: 100%;
			color: #000;
			font-weight: bold;
			text-align: center;
			font-family: "Gotham-Narrow-Medium";
			font-size: 14px;
		}

		.login-form form {
			padding: 30px;
		}

		.form-control2 {
			font-family: "Gotham-Narrow-Medium";
			font-size: 16px;
			color: #303F9F;
			width: 100%;
			outline: none;
			padding: 15px;
			background: none;
			border: none;
			border-bottom: 2px solid #BBDEFB;
		}

		.form-control2:focus {
			outline: none;
			border-bottom: 2px solid #303F9F;
		}

		.boton {
			background: #574696;
			border-radius: 1px;
			border: 2px solid #574696;
			color: #fff;
			cursor: pointer;
			display: inline-block;
			font-family: "Gotham-Narrow-Medium";
			text-transform: uppercase;
			font-weight: bold;
			font-size: 16px;
			padding: 5px;
			width: 100%;
			-webkit-transition: all 0.3s ease;
			-o-transition: all 0.3s ease;
			transition: all 0.3s ease;
		}

		.boton:hover {
			background-color: #42A3DC;
			border: 2px solid #42A3DC;
		}
	</style>
</head>

<body>

	@yield('content')

	<!-- JQuery -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js" integrity="sha384-I6F5OKECLVtK/BL+8iSLDEHowSAfUo76ZL9+kGAgTRdiByINKJaqTPH/QVNS1VDb" crossorigin="anonymous"></script>

	<!-- Latest compiled and minified CSS 
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
	
	 Optional theme 
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
	-->
	<link rel="stylesheet" href="{{asset('assets/bootswatch-3.3.7/paper/bootstrap.min.css')}}">

	<!-- Latest compiled and minified JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js" integrity="sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS" crossorigin="anonymous"></script>

	{{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
	@yield('scripts')
</body>

</html>