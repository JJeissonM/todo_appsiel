@extends('layouts.principal')

@section('content')
	
	<div class="container col-sm-6 col-sm-offset-3">

		<h1> La página ingresada no existe, por favor verifique la url: <small>{{ url('/').'/'.$slug }}</small></h1>
		<img src="https://upload.wikimedia.org/wikipedia/commons/a/af/Notfound.png">

		<br><br>

		<div class="row">
		  <div class="col-sm-4"> <a href="{{ url()->previous() }}" class="btn btn-lg btn-info"> <i class="fa fa-arrow-left"></i> Volver atrás </a> </div>
		  <div class="col-sm-4"> <a href="{{ url('/') }}" class="btn btn-lg btn-info"> <i class="fa fa-home"></i> Página Web </a> </div>
		  <div class="col-sm-4"> <a href="{{ url('/inicio') }}" class="btn btn-lg btn-info"> <i class="fa fa-th-large"></i> Menú de aplicaciones </a> </div>
		</div>

	</div>

	<br/><br/>
@endsection