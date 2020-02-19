@extends('layouts.principal')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-md-10 col-md-offset-1" align="center" style="color:green;">
			<h1 style="letter-spacing: 5px;"> APPSIEL </h1>
			<h2>..:: Sistemas de información en línea ::..</h2>
		</div>
	</div>
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">Bienvenido!!!</div>

                <div class="panel-body">
                    Esta es su aplicación APPSIEL. Con ella podrá gestionar los estudiantes, asignaturas, logros y la creación de boletines de su institución educativa.
					<br/><br/>
					@if (Auth::guest())
                        <a href="login" class="btn btn-success">Ingresar</a>
                    @endif
					
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <img src="img/pie_de_pagina.png" class="img-responsive" height="200px">
        </div>
    </div>
</div>
@endsection
