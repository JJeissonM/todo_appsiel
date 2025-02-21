@extends('layouts.academico_estudiante')

@section('content')

	<div class="row" style="background: #ddd !important;">
		<div class="col-md-12">
			<div class="box box-primary">
				<div class="box-header with-border">

					<br><br><br>
					@include('matriculas.estudiantes.datos_basicos', [
								'curso_label' => $estudiante->matricula_activa()->curso->descripcion,
								'estudiante' => $estudiante->get_datos_basicos($estudiante->id)
							]
						)
				</div> <!-- /.box-header -->
			</div> <!-- /.box -->
		</div> <!-- /.col -->	
	</div> <!-- /.row -->
			
@endsection