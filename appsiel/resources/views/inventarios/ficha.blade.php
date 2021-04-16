@extends('layouts.principal')
@section('style')
@endsection
<?php
	use App\Http\Controllers\Sistema\VistaController;
?>

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
		    <h4>Ficha Técnica</h4>
		</div>
		<div class="panel">
			<span class="label label-success pull-right" style="padding: 10px;"><a href="" data-toggle="modal" data-target="#myModal" style="color: white; text-decoration: none;">Nueva Característica</a></span>
		</div>
		<h1>Detalles del Producto</h1>
		<div class="panel panel-info">
			<!-- Default panel contents -->
			<div class="panel-body" >
				<h1>{{$inv_producto->descripcion}}</h1>
			</div>
			<!-- Table -->
			<table class="table">
				<thead>
					 <th>KEY</th>
					 <th>VALUE</th>
					 <th>ACCÍONES</th>
				</thead>
				<tbody id="ficha">
				 @foreach($inv_producto->fichas  as $ficha)
					 <tr>
						 <td>{{$ficha->key}}</td>
						 <td>{{$ficha->descripcion}}</td>
						 <td>
							 <a class="btn btn-danger btn-xs btn-detail" href="{{route('ficha.delete',$ficha->id)}}" title="Eliminar"><i class="fa fa-trash"></i>&nbsp;</a>
						 </td>
					 </tr>
				 @endforeach
				</tbody>
			</table>
		</div>
	</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Característica</h4>
			</div>
			<div class="modal-body">
				<form action="{{route('ficha.store')}}" method="POST">
					<input type="hidden" name="_token"  value="{{@csrf_token()}}">
					<input type="hidden" name="producto_id"  value="{{$inv_producto->id}}">
					<div class="form-group">
						<label for="exampleInputEmail1">KEY</label>
						<input type="text" name="key" class="form-control" required placeholder="CARACTERÍSTICA">
					</div>
					<div class="form-group">
						<label for="exampleInputEmail1">VALUE</label>
						<textarea class="form-control" name="descripcion" placeholder="DETALLE" required id="detalle" cols="30" rows="10"></textarea>
					</div>
					<div class="form-group">
						<button type="button" class="btn btn-default" data-dismiss="modal">CERRAR</button>
						<button type="submit" class="btn btn-primary">GUARDAR</button>
					</div>
				</form>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->

@endsection

@section('scripts')
<script>
	// Replace the <textarea id="editor1"> with a CKEditor 4
	// instance, using default configuration.
	CKEDITOR.replace( 'detalle' );
</script>
@endsection