@extends('layouts.principal')

@section('content')
{{ Form::bsMigaPan($miga_pan) }}
<hr>

@include('layouts.mensajes')

<div class="container-fluid">
	<div class="marco_formulario">
		<div class="row">
			<div class="col-md-12" style="padding-top: 30px;">
				<div class="col-md-12">
					<div class="col-md-3"></div>
					<div class="col-md-3" style="text-align: center;">
						<a href="{{route('citas_medicas.show','HOY'.$variables_url)}}" style="cursor: pointer;"><i style="font-size: 100px;" class="fa fa-calendar"></i><br>Programar Agenda<br>(Horarios de Atención)</a>
					</div>
					<div class="col-md-3" style="text-align: center;">
						<a href="{{route('citas_medicas.citas').$variables_url.'&fecha=HOY'}}" style="cursor: pointer;"><i style="font-size: 100px;" class="fa fa-check"></i><br>Gestionar Citas</a>
					</div>
					<div class="col-md-3"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<br />
@endsection