<!DOCTYPE html>
<html lang="en">
	<head>
	    <meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1">

	    <title>APPSIEL ..:: Sistemas de Información en Línea ::..</title>

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

			
		    img {
		        padding-left:30px;
		    }

		    table {
		        width:100%;
		        border-collapse: collapse;
		    }

		    table.encabezado{
		        padding:5px;
		        border: 1px solid;
		    }

		    table.banner{
		        font-family: "Lucida Grande", "Lucida Sans Unicode", Verdana, Arial, Helvetica, sans-serif;
		        font-style: italic;
		        font-size: larger;
		        border: 1px solid;
		    }

		    table.contenido td {
		        border: 1px solid;
		    }

		    th {
		        background-color: #E0E0E0;
		        border: 1px solid;
		    }

		    ul{
		        padding:0px;
		        margin:0px;
		    }

		    li{
		        list-style-type: none;
		    }

		    span.etiqueta{
		        font-weight: bold;
		        display: inline-block;
		        width: 100px;
		        text-align:right;
		    }

		    .page-break {
		        page-break-after: always;
		    }

		    .table-bordered {
			    border: 1px solid #333333;
			}

			.table-bordered>tbody>tr>td, .table-bordered>tbody>tr>th, .table-bordered>tfoot>tr>td, .table-bordered>tfoot>tr>th, .table-bordered>thead>tr>td, .table-bordered>thead>tr>th {
			    border: 1px solid #333333;
			}

			.pull-right {
			    float: right!important;
			}

			div.lbl_historia_clinica h3{
				 width: 100%; 
				 text-align: center;
				 padding-bottom: -12px;
			}

			div.lbl_historia_clinica h4{
				 width: 100%;
				 padding-bottom: -15px;
			}

			div.lbl_historia_clinica h5{
				text-align: right;
				 width: 100%;
				 padding-bottom: -12px;
			}

			body footer {
				text-align: center;
		      position: fixed;
		      left: 0px;
		      bottom: -50px;
		      right: 0px;
		      height: 40px;
		      border-bottom: 2px solid #ddd;
		    }
	   </style>

	    @yield('estilos_1')
	</head>
	<body id="app-layout">

		@yield('content')	    
	    
	</body>
</html>
