@extends('layouts.principal')

@section('estilos_1')
<style type="text/css">
	/*body {
			background-color: #FAFAFA !important;
        }*/

	body {
		background-position: bottom;
		background-attachment: fixed;
		background-size: cover;
		background-image: url({{asset('assets/img/fondo-colegio.jpeg')}});
	}

	.img-responsive:hover {
		transform: scale(1.2) rotate(-15deg);
		cursor: pointer;
	}

	.banner {
		display: flex;
		justify-content: center;
		align-items: center;
	}

	.nombre-empresa {
		display: flex;
		justify-content: center;
		font-size: calc(1em + 3vw);
		width: 550px;
	}
	.nav-tabs > li > a{
		color: white
	}
	.nav-tabs > li.active > a{
		color: white;
	}
	.nav-tabs > li.active{
		background: #2196f3;
	}
	.nav-tabs > li.active > a:focus{
		color: white;
	}

	video::-internal-media-controls-download-button {display:none}
	video::-webkit-media-controls-enclosure {overflow:hidden}
	video::-webkit-media-controls-panel {width: calc(100% + 18px); /* Ajustar los pixeles segun se necesite */}
</style>
@endsection


@section('content')
<div id="div_contenido">

	<div class="container-fluid">
		
		@include('layouts.mensajes')
		<div id="myDIV">
			<div class="row">
				<div class="panel panel-primary">
					<div class="panel-heading" align="center" style="background: #574696;">
						<h3 class="panel-title" style="font-size: 22px; padding: 10px;"> SECCIÓN DE AYUDA</h3>
						<ul class="nav nav-tabs">
							<li class="active"><a data-toggle="tab" href="#tvideos">TUTORIALES EN VIDEO</a></li>
							<li><a data-toggle="tab" href="#tpdf">TUTORIALES EN PDF</a></li>
						  </ul>
					</div>
					<div class="tab-content">
						<div id="tvideos" class="tab-pane fade in active">
							<div class="panel-body">
								<div class="row">
									<div class="col-md-9" id="video" style="height: 660px !important;">
										<video width="100%" height="660" controls>
											Su navegador no soporta este formato de video.
										</video>
									</div>
									<div class="col-md-3" style="height: 660px !important; overflow-y: scroll;">
										<div class="accordion" id="accordionExample">
											<?php $i = 0; ?>
											@foreach( $videos as $key=>$value)
											<div class="card">
												<div class="card-header" id="heading{{$i}}">
													<h2 class="mb-0" style="margin-top: 10px !important; margin-bottom: 5px !important;">
														<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{$i}}" aria-expanded="true" aria-controls="collapse{{$i}}">
															{{$key." (".$value['total'].")"}}
														</button>
													</h2>
												</div>
												<div id="collapse{{$i}}" class="collapse" aria-labelledby="heading{{$i}}" data-parent="#accordionExample">
													<div class="card-body">
														@if($value['total']>0)
														@if($value['urls']!=null)
														@foreach($value['urls'] as $url)
														<div class="col-md-12 col-xs-6" style="padding: 10px; cursor: pointer; margin-bottom: 5px; border-bottom: 1px #d8d7d7 solid;" id="{{$url['url']}}" onclick="verVideo(this.id)">
															<div class="row">
																<div class="col-xs-4" style="padding-right: 0px; padding-left: 0px;">
																	<img width="100%" src="{{$url['preview']}}">
																</div>
																<div class="col-xs-8" style="padding-right: 0px; font-size: 12px !important;">
																	<a style="font-size: 14px !important;">{{$url['label']}}</a>
																	<p>Duración {{$url['duracion']}} <br>Publicación: {{$url['publicacion']}}</p>
																</div>	
															</div>
															
														</div>
														@endforeach
														@endif
														@else
														<h5 style="color: #4caf50; font-size: 16px;">¡Oh, no! Parece que no hay nada aquí</h5>
														@endif
													</div>
												</div>
											</div>
											<?php $i = $i + 1; ?>
											@endforeach
										</div>
									</div>
								</div>
							</div>		
						</div>
						<div id="tpdf" class="tab-pane fade">
						  	<div class="panel-body">
								<div class="col-md-9"  id="pdf" style="height: 660px !important;">
									<div style="width: 100%; height: 660px; background-color: grey"></div>
								</div>
								<div class="col-md-3" style="height: 660px !important; overflow-y: scroll;">
									<div class="accordion" id="accordionExample1">
										<?php $i = 0; ?>
										@foreach( $pdfs as $key=>$value)
										<div class="card">
											<div class="card-header" id="heading{{$i}}">
												<h2 class="mb-0" style="margin-top: 10px !important; margin-bottom: 5px !important;">
													<button class="btn btn-primary btn-block text-left" type="button" data-toggle="collapse" data-target="#collapsePdf{{$i}}" aria-expanded="true" aria-controls="collapse{{$i}}">
														{{$key." (".$value['total'].")"}}
													</button>
												</h2>
											</div>
											<div id="collapsePdf{{$i}}" class="collapse" aria-labelledby="heading{{$i}}" data-parent="#accordionExample1">
												<div class="card-body">
													@if($value['total']>0)
														@if($value['urls']!=null)
															@foreach($value['urls'] as $url)
															<div class="col-md-12" style="padding: 10px; cursor: pointer; margin-bottom: 5px; border-bottom: 1px #d8d7d7 solid;" id="{{$url['url']}}" onclick="verPdf(this.id)">
																<div style="padding-right: 0px; font-size: 12px !important;">
																	<a style="font-size: 14px !important;">{{$url['label']}}</a>
																	<p>Paginas {{$url['paginas']}} <br>Publicación: {{$url['publicacion']}}</p>
																</div>
															</div>
															@endforeach
														@endif
													@else
													<h5 style="color: #4caf50; font-size: 16px;">¡Oh, no! Parece que no hay nada aquí</h5>
													@endif
												</div>
											</div>
										</div>
										<?php $i = $i + 1; ?>
										@endforeach
									</div>
								</div>
							</div>
						  </div>
						</div>
					</div>					
				</div>
				<div class="row">
					<a href="{{ url('/inicio') }}" class="btn btn-primary" style="float: left">Volver a al inicio</a>
				</div>	
			</div>
		</div>
		
	</div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {

	});

	function verPdf(url) {
		$("#pdf").html("");
		$("#pdf").html(`<embed oncontextmenu='return false;' src="${url}#view=fitH&toolbar=0&navpanes=0" type="application/pdf" allowfullscreen width="100%" height="660" />`);
		$("#pdf").fadeIn();
	}

	function verVideo(url) {
		$("#video").html("");
		$("#video").html(`<video oncontextmenu='return false;' controlslist='nodownload' autoplay width='100%' height='660' controls><source src='${url}' type='video/mp4'>Su navegador no soporta este formato de video.</video>`);
		$("#video").fadeIn();
	}
</script>
@endsection