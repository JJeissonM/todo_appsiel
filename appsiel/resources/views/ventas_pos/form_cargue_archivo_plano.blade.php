<h4>Cargar archivo plano</h4>
<hr>
{{ Form::open([ 'url' => 'ventas_pos_cargue_archivo_plano','id'=>'form_archivo_plano','files' => true]) }}
	<div class="container-fluid">

		{{ Form::Spin('128') }}

		<div class="row" style="padding:5px;">					
				<label class="control-label col-sm-4" > <b> *Archivo plano: </b> </label>

				<div class="col-sm-8">
					{{ Form::file('archivo_plano', [ 'class' => 'form-control', 'id' => 'archivo_plano', 'accept' => 'text/plain', 'required' => 'required' ]) }}
				</div>					 
			</div>

		<div class="col-md-12" style="text-align: center;">
				<button class="btn btn-success" id="btn_cargar_plano"> <i class="fa fa-arrow-up"></i> Cargar registros </button>
			</div>
	</div>

{{ Form::close() }}