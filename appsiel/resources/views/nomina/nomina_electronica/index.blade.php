@extends('layouts.principal')

@section('content')
	{{ Form::bsMigaPan($miga_pan) }}
	<hr>

	@include('layouts.mensajes')

	<div class="container-fluid">
		<div class="marco_formulario">
			
			<div class="container">
			  <ul class="nav nav-tabs">
			    <li class="active"><a href="#doc_soporte">Docs. Soporte</a></li>
			    <li><a href="#doc_ajuste_e">Docs. Ajuste E.</a></li>
			    <li><a href="#doc_ajuste_r">Docs. Ajuste R.</a></li>
			    <li><a href="#generar">Generar</a></li>
			  </ul>

			  <div class="tab-content">
			    
			    <div id="doc_soporte" class="tab-pane fade in active">
			      <h4>{{ $model->modelo->descripcion }}</h4>
			      <div id="div_datos_doc_soporte">
			      	{!! $model->get_records_table() !!}
			      </div>
			    </div>
			    
			    <div id="doc_ajuste_e" class="tab-pane fade">
			      <h3>Menu 1</h3>
			      <p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
			    </div>
			    
			    <div id="doc_ajuste_r" class="tab-pane fade">
			      <h3>Menu 2</h3>
			      <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam.</p>
			    </div>
			    
			    <div id="generar" class="tab-pane fade">
			      @include('nomina.nomina_electronica.panel_generar')
			    </div>

			  </div>
			    <hr>
			    <p class="act"><b>Active Tab</b>: <span></span></p>
			    <p class="prev"><b>Previous Tab</b>: <span></span></p>
			</div>

		</div>
	</div>

	<br/>
@endsection

@section('scripts1')
<script>
	$(document).ready(function(){
	  $(".nav-tabs a").click(function(){
	    $(this).tab('show');
	  });
	  $('.nav-tabs a').on('shown.bs.tab', function(event){
	    var x = $(event.target).text();         // active tab
	    var y = $(event.relatedTarget).text();  // previous tab
	    $(".act span").text(x);
	    $(".prev span").text(y);
	  });
	});
</script>
@endsection