@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
<style>
table th {
    padding: 15px;
    text-align: center;
	border-bottom:solid 2px;
	background-color: #E5E4E3;
}
table td {
    padding: 2px;
}
</style>

<hr>

@include('layouts.mensajes')
	
<div class="container-fluid">
	<div class="marco_formulario">
	    <h4>Ingreso de registros</h4>
	    <hr>
		{{Form::open(array('route'=>array('nomina.store'),'method'=>'POST','class'=>'form-horizontal','id'=>'formulario'))}}
			<div class="row">
				<div class="col-sm-12">
					<b>Documento de nómina:</b><code>{{ $documento->descripcion }}</code>
					<b>Concepto:</b>	<code>{{ $concepto->descripcion }}</code>
					
					{{ Form::hidden('nom_doc_encabezado_id', $documento->id, ['id' =>'nom_doc_encabezado_id']) }}
					
					{{ Form::hidden('nom_concepto_id', $concepto->id, ['id' =>'nom_concepto_id']) }}


					{{ Form::hidden('cantidad_personas', $cantidad_personas, ['id' =>'cantidad_personas']) }}

				</div>							
			</div>
			<div class="row">
				<div class="col-sm-12">

				<table class="table table-responsive" id="tabla">
				<thead>
					<tr>
						<th>Empleado</th>
						<th>Valor concepto</th>
					</tr>
				</thead>
				<tbody>
					@foreach($personas as $fila)
						<tr> 
							<td style="font-size:12px">
								<b>{{ $fila->empleado }}</b>
								
								{{ Form::hidden('core_tercero_id[]', $fila->core_tercero_id, []) }}

							</td>

							<td>
								<input type="text" name="valor[]" class="form-control">

							</td>
                        </tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>	

			<div style="text-align: center; width: 100%;">
				{{ Form::bsButtonsForm( url()->previous() ) }}
			</div>

			{{ Form::hidden('app_id',Input::get('id')) }}
			{{ Form::hidden('modelo_id',Input::get('id_modelo')) }}

		{{Form::close()}}					
	</div>
</div> 

@endsection