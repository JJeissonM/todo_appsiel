@extends('layouts.principal')

@section('estilos_1')
<style type="text/css">
	#btnPaula {
		display: none !important;
	}

	body {
		background:
			linear-gradient(135deg, rgba(8, 24, 46, 0.92), rgba(18, 63, 110, 0.88)),
			url({{asset('assets/img/fondo-colegio.jpeg')}});
		background-attachment: fixed;
		background-position: center;
		background-size: cover;
	}

	.help-shell {
		background: #f4f7fb;
		border-radius: 24px;
		box-shadow: 0 24px 70px rgba(7, 28, 52, 0.22);
		margin-bottom: 30px;
		overflow: hidden;
	}

	.help-hero {
		background: linear-gradient(135deg, #103f77 0%, #1b4f8a 50%, #2f80ed 100%);
		color: #fff;
		padding: 28px 32px;
	}

	.help-hero h2 {
		font-size: 30px;
		font-weight: 700;
		margin: 0 0 8px;
		color: #fff;
	}

	.help-hero p {
		color: rgba(255,255,255,0.82);
		font-size: 15px;
		margin: 0;
		max-width: 760px;
	}

	.help-body {
		padding: 28px;
	}

	.player-panel {
		background: #061523;
		border-radius: 22px;
		box-shadow: 0 20px 45px rgba(5, 18, 35, 0.2);
		color: #fff;
		margin-bottom: 28px;
		overflow: hidden;
	}

	.player-stage {
		align-items: center;
		background: #000;
		display: flex;
		justify-content: center;
		min-height: 460px;
		position: relative;
	}

	.player-stage iframe,
	.player-stage video,
	.player-stage embed {
		border: none;
		display: block;
		width: 100%;
	}

	.player-empty {
		color: rgba(255,255,255,0.85);
		padding: 40px;
		text-align: center;
	}

	.player-empty h3 {
		font-size: 28px;
		font-weight: 700;
		margin: 0 0 12px;
	}

	.player-empty p {
		font-size: 15px;
		margin: 0;
	}

	.player-meta {
		background: #fff;
		color: #172b43;
		padding: 20px 24px;
	}

	.player-meta-header {
		align-items: center;
		display: flex;
		gap: 14px;
		justify-content: space-between;
	}

	.player-meta h3 {
		font-size: 24px;
		font-weight: 700;
		margin: 0 0 8px;
	}

	.player-meta p {
		color: #5f6f82;
		font-size: 14px;
		margin: 0;
	}

	.player-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
	}

	.share-button {
		background: linear-gradient(135deg, #2f80ed, #1b4f8a);
		border: none;
		border-radius: 999px;
		color: #fff;
		cursor: pointer;
		font-size: 13px;
		font-weight: 700;
		padding: 10px 16px;
		transition: transform 0.18s ease, box-shadow 0.18s ease;
	}

	.share-button:hover {
		box-shadow: 0 12px 24px rgba(47, 128, 237, 0.2);
		transform: translateY(-1px);
	}

	.share-button[disabled] {
		cursor: not-allowed;
		opacity: 0.55;
		transform: none;
	}

	.app-tabs {
		border-bottom: none;
		display: flex;
		flex-wrap: wrap;
		gap: 10px;
		margin-bottom: 24px;
	}

	.app-tabs > li {
		float: none;
		margin: 0;
	}

	.app-tabs > li > a {
		background: #e6edf7;
		border: none;
		border-radius: 999px;
		color: #35506b;
		font-size: 14px;
		font-weight: 600;
		margin: 0;
		padding: 10px 18px;
	}

	.app-tabs > li.active > a,
	.app-tabs > li.active > a:hover,
	.app-tabs > li.active > a:focus {
		background: linear-gradient(135deg, #2f80ed, #1b4f8a);
		border: none;
		color: #fff;
	}

	.toolbar-row {
		align-items: center;
		display: flex;
		flex-wrap: wrap;
		gap: 14px;
		justify-content: space-between;
		margin-bottom: 22px;
	}

	.search-box {
		flex: 1 1 280px;
		position: relative;
	}

	.search-box input {
		background: #fff;
		border: 1px solid #d6e2f0;
		border-radius: 999px;
		box-shadow: inset 0 1px 2px rgba(16, 36, 61, 0.04);
		color: #18324f;
		font-size: 14px;
		padding: 12px 18px;
		width: 100%;
	}

	.search-box input:focus {
		border-color: #2f80ed;
		box-shadow: 0 0 0 3px rgba(47, 128, 237, 0.12);
		outline: none;
	}

	.filter-chips {
		display: flex;
		flex: 1 1 360px;
		flex-wrap: wrap;
		gap: 10px;
		justify-content: flex-end;
	}

	.filter-chip {
		background: #eaf1f9;
		border: 1px solid transparent;
		border-radius: 999px;
		color: #35506b;
		cursor: pointer;
		font-size: 13px;
		font-weight: 700;
		padding: 9px 14px;
		transition: all 0.18s ease;
	}

	.filter-chip:hover {
		border-color: #bfd4ee;
		transform: translateY(-1px);
	}

	.filter-chip.is-active {
		background: linear-gradient(135deg, #2f80ed, #98c8ff);
		color: #fff;
	}

	.app-pane-header {
		align-items: center;
		display: flex;
		justify-content: space-between;
		margin-bottom: 18px;
	}

	.app-pane-header h4 {
		color: #16314f;
		font-size: 22px;
		font-weight: 700;
		margin: 0;
	}

	.app-pane-header span {
		background: #dbe8f7;
		border-radius: 999px;
		color: #1b4f8a;
		font-size: 12px;
		font-weight: 700;
		padding: 6px 12px;
		text-transform: uppercase;
	}

	.video-grid {
		display: flex;
		flex-wrap: wrap;
		margin-left: -10px;
		margin-right: -10px;
	}

	.video-card-wrap {
		display: flex;
		margin-bottom: 20px;
		padding-left: 10px;
		padding-right: 10px;
		width: 100%;
	}

	.video-card {
		background: #fff;
		border: 1px solid #e3ebf5;
		border-radius: 18px;
		box-shadow: 0 14px 28px rgba(18, 42, 66, 0.08);
		cursor: pointer;
		display: flex;
		flex: 1;
		flex-direction: column;
		overflow: hidden;
		transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
	}

	.video-card:hover {
		border-color: #9bbbe4;
		box-shadow: 0 20px 36px rgba(18, 42, 66, 0.14);
		transform: translateY(-4px);
	}

	.video-card.is-active {
		border-color: #2f80ed;
		box-shadow: 0 22px 40px rgba(47, 128, 237, 0.18);
	}

	.video-thumb {
		background: linear-gradient(135deg, #dfe9f5, #f6f9fc);
		overflow: hidden;
		position: relative;
	}

	.video-badge {
		background: rgba(255, 255, 255, 0.92);
		border-radius: 999px;
		color: #17324e;
		font-size: 11px;
		font-weight: 800;
		left: 12px;
		letter-spacing: 0.04em;
		padding: 5px 9px;
		position: absolute;
		text-transform: uppercase;
		top: 12px;
	}

	.video-badge.youtube {
		background: rgba(255, 71, 87, 0.94);
		color: #fff;
	}

	.video-badge.video {
		background: rgba(17, 153, 142, 0.92);
		color: #fff;
	}

	.video-thumb img {
		display: block;
		height: 190px;
		object-fit: cover;
		width: 100%;
	}

	.video-duration {
		background: rgba(7, 13, 23, 0.88);
		border-radius: 999px;
		bottom: 12px;
		color: #fff;
		font-size: 12px;
		font-weight: 700;
		padding: 5px 10px;
		position: absolute;
		right: 12px;
	}

	.video-body {
		display: flex;
		flex: 1;
		flex-direction: column;
		padding: 16px;
	}

	.video-title {
		color: #18324f;
		font-size: 17px;
		font-weight: 700;
		line-height: 1.35;
		margin-bottom: 10px;
	}

	.video-info {
		color: #67809a;
		font-size: 13px;
		line-height: 1.5;
		margin-top: auto;
	}

	.empty-state {
		background: #fff;
		border: 1px dashed #bfd0e6;
		border-radius: 18px;
		color: #53708f;
		padding: 28px;
		text-align: center;
	}

	.empty-state.hidden {
		display: none;
	}

	.pdf-section {
		margin-top: 30px;
	}

	.pdf-section h4 {
		color: #16314f;
		font-size: 20px;
		font-weight: 700;
		margin: 0 0 18px;
	}

	.pdf-grid {
		display: flex;
		flex-wrap: wrap;
		margin-left: -10px;
		margin-right: -10px;
	}

	.pdf-card-wrap {
		margin-bottom: 20px;
		padding-left: 10px;
		padding-right: 10px;
		width: 100%;
	}

	.pdf-card {
		background: #fff;
		border: 1px solid #e3ebf5;
		border-radius: 18px;
		box-shadow: 0 12px 24px rgba(18, 42, 66, 0.06);
		cursor: pointer;
		padding: 18px 20px;
		transition: transform 0.18s ease, box-shadow 0.18s ease;
	}

	.pdf-card:hover {
		box-shadow: 0 18px 32px rgba(18, 42, 66, 0.12);
		transform: translateY(-3px);
	}

	.pdf-card h5 {
		color: #18324f;
		font-size: 17px;
		font-weight: 700;
		margin: 0 0 10px;
	}

	.pdf-card p {
		color: #67809a;
		font-size: 13px;
		margin: 0;
	}

	@media (min-width: 768px) {
		.video-card-wrap,
		.pdf-card-wrap {
			width: 50%;
		}
	}

	@media (min-width: 1200px) {
		.video-card-wrap {
			width: 33.33333333%;
		}

		.pdf-card-wrap {
			width: 33.33333333%;
		}
	}

	@media (max-width: 767px) {
		.help-hero,
		.help-body {
			padding: 20px;
		}

		.player-meta-header {
			align-items: flex-start;
			flex-direction: column;
		}

		.player-stage {
			min-height: 260px;
		}

		.player-empty h3 {
			font-size: 22px;
		}

		.video-thumb img {
			height: 170px;
		}
	}
</style>
@endsection

@section('content')

<div id="div_contenido">
	<div class="container-fluid">
		@include('layouts.mensajes')

		<div class="help-shell">
			<div class="help-hero">
				<h2>Centro de ayuda multimedia</h2>
				<p>Explora los tutoriales por aplicación, reproduce videos directamente desde esta pantalla y abre los documentos de apoyo cuando los necesites.</p>
			</div>

			<div class="help-body">
				<div class="player-panel">
					<div class="player-stage" id="ayudaframe">
						<div class="player-empty">
							<h3>Tu canal de ayuda</h3>
							<p>Selecciona un video para empezar a verlo aquí mismo.</p>
						</div>
					</div>
					<div class="player-meta" id="videoMeta">
						<div class="player-meta-header">
							<div>
								<h3>Sin reproducción activa</h3>
								<p>Elige cualquier tutorial de la grilla para cargarlo en el reproductor.</p>
							</div>
							<div class="player-actions">
								<button type="button" class="share-button" id="copyVideoLinkButton" onclick="copiarEnlaceVideoActual()" disabled>Copiar enlace</button>
							</div>
						</div>
					</div>
				</div>

				<div class="toolbar-row">
					<div class="search-box">
						<input type="text" id="videoSearch" placeholder="Buscar videos por nombre, fecha o aplicación...">
					</div>
					<div class="filter-chips" id="videoTypeFilters">
						<button type="button" class="filter-chip is-active" data-filter="all">Todos</button>
						<button type="button" class="filter-chip" data-filter="youtube">YouTube</button>
						<button type="button" class="filter-chip" data-filter="video">Videos propios</button>
					</div>
				</div>

				<ul class="nav nav-tabs app-tabs" role="tablist">
					<?php $tabIndex = 0; ?>
					@foreach($videos as $appName => $group)
						<li role="presentation" class="{{ $tabIndex === 0 ? 'active' : '' }}">
							<a href="#appTab{{$tabIndex}}" aria-controls="appTab{{$tabIndex}}" role="tab" data-toggle="tab">{{$appName}}</a>
						</li>
						<?php $tabIndex++; ?>
					@endforeach
				</ul>

				<div class="tab-content">
					<?php $paneIndex = 0; ?>
					@foreach($videos as $appName => $group)
						<div role="tabpanel" class="tab-pane fade {{ $paneIndex === 0 ? 'in active' : '' }}" id="appTab{{$paneIndex}}">
							<div class="app-pane-header">
								<h4>{{$appName}}</h4>
								<span>{{$group['total']}} videos</span>
							</div>

							@if($group['total'] > 0 && $group['urls'] != null)
								<div class="video-grid">
									@foreach($group['urls'] as $url)
										<div class="video-card-wrap">
												<div class="video-card"
													data-video-url="{{$url['player_url']}}"
													data-video-original-url="{{$url['url']}}"
													data-video-type="{{$url['player_type']}}"
													data-video-title="{{$url['label']}}"
													data-app-name="{{$appName}}"
												data-search-text="{{ strtolower($appName . ' ' . $url['label'] . ' ' . $url['publicacion'] . ' ' . $url['duracion']) }}"
												data-video-meta="Duración {{$url['duracion']}} | Publicación: {{$url['publicacion']}}"
												onclick="verVideo(this)">
												<div class="video-thumb">
													<img src="{{$url['preview']}}" alt="{{$url['label']}}">
													<span class="video-badge {{$url['player_type']}}">{{ $url['player_type'] === 'youtube' ? 'YouTube' : 'Video' }}</span>
													<span class="video-duration">{{$url['duracion']}}</span>
												</div>
												<div class="video-body">
													<div class="video-title">{{$url['label']}}</div>
													<div class="video-info">Publicación: {{$url['publicacion']}}</div>
												</div>
											</div>
										</div>
									@endforeach
								</div>
								@else
									<div class="empty-state app-empty-state">
										No hay videos disponibles en esta aplicación por ahora.
									</div>
								@endif
								<div class="empty-state hidden app-filter-empty-state">
									No hay resultados para los filtros actuales en esta aplicación.
								</div>
							</div>
						<?php $paneIndex++; ?>
					@endforeach
				</div>

				<div class="pdf-section">
				<!-- 
					<h4>Documentos PDF de apoyo</h4>
					<div class="pdf-grid">
						@ foreach($pdfs as $key => $value)
							@ if($value['total'] > 0 && $value['urls'] != null)
								@ foreach($value['urls'] as $url)
									<div class="pdf-card-wrap">
										<div class="pdf-card" onclick="verPdf('{ {$url['url']}}', '{ {$url['label']}}', 'Páginas { {$url['paginas']}} | Publicación: { {$url['publicacion']}}')">
											<h5>{ {$url['label']}}</h5>
											<p>{ {$key}} | Páginas { {$url['paginas']}} | Publicación: { {$url['publicacion']}}</p>
										</div>
									</div>
								@ endforeach
							@ endif
						@ endforeach
					</div>
					-->
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
		var currentSharedVideoUrl = '';

	$(document).ready(function() {
		var sharedVideoUrl = getParametroUrl('video');
		var $selectedVideo = sharedVideoUrl ? $('.video-card[data-video-original-url="' + sharedVideoUrl + '"]').first() : $();
		var $firstVideo = $selectedVideo.length ? $selectedVideo : $('.video-card').first();

		if ($firstVideo.length) {
			activarPestanaVideo($firstVideo);
			verVideo($firstVideo[0]);
		}

		$('#videoSearch').on('keyup', aplicarFiltrosVideos);

		$('#videoTypeFilters').on('click', '.filter-chip', function() {
			$('.filter-chip').removeClass('is-active');
			$(this).addClass('is-active');
			aplicarFiltrosVideos();
		});
	});

	function setPlayerMeta(title, meta) {
		$("#videoMeta").html(
			`<div class="player-meta-header"><div><h3>${title}</h3><p>${meta}</p></div><div class="player-actions"><button type="button" class="share-button" id="copyVideoLinkButton" onclick="copiarEnlaceVideoActual()" ${currentSharedVideoUrl ? '' : 'disabled'}>Copiar enlace</button></div></div>`
		);
	}

	function verPdf(url, title, meta) {
		$(".video-card").removeClass("is-active");
		currentSharedVideoUrl = '';
		$("#ayudaframe").html(`<embed oncontextmenu='return false;' src="${url}#view=fitH&toolbar=0&navpanes=0" type="application/pdf" allowfullscreen height="620" />`);
		setPlayerMeta(title, meta);
		$("#ayudaframe").fadeIn();
	}

	function getParametroUrl(name) {
		return new URLSearchParams(window.location.search).get(name);
	}

	function activarPestanaVideo($card) {
		var $tabPane = $card.closest('.tab-pane');

		if (!$tabPane.length) {
			return;
		}

		var tabId = $tabPane.attr('id');
		$('.app-tabs a[href="#' + tabId + '"]').tab('show');
	}

	function construirEnlaceCompartir() {
		if (!currentSharedVideoUrl) {
			return '';
		}

		var url = new URL(window.location.href);
		url.searchParams.set('video', currentSharedVideoUrl);

		return url.toString();
	}

	function copiarTextoPortapapeles(text) {
		if (navigator.clipboard && navigator.clipboard.writeText) {
			return navigator.clipboard.writeText(text);
		}

		return new Promise(function(resolve, reject) {
			var $temp = $('<input>');
			$('body').append($temp);
			$temp.val(text).select();

			try {
				document.execCommand('copy');
				$temp.remove();
				resolve();
			} catch (error) {
				$temp.remove();
				reject(error);
			}
		});
	}

	function copiarEnlaceVideoActual() {
		var shareUrl = construirEnlaceCompartir();

		if (!shareUrl) {
			return;
		}

		copiarTextoPortapapeles(shareUrl).then(function() {
			var $button = $('#copyVideoLinkButton');
			var originalText = $button.text();

			$button.text('Enlace copiado');

			setTimeout(function() {
				$('#copyVideoLinkButton').text(originalText);
			}, 1800);
		});
	}

	function aplicarFiltrosVideos() {
		var searchValue = ($('#videoSearch').val() || '').toLowerCase();
		var selectedFilter = $('#videoTypeFilters .filter-chip.is-active').data('filter') || 'all';

		$('.tab-pane').each(function() {
			var $pane = $(this);
			var visibleCards = 0;

			$pane.find('.video-card').each(function() {
				var $card = $(this);
				var matchesSearch = ($card.data('search-text') || '').indexOf(searchValue) !== -1;
				var matchesType = selectedFilter === 'all' || $card.data('video-type') === selectedFilter;
				var shouldShow = matchesSearch && matchesType;

				$card.closest('.video-card-wrap').toggle(shouldShow);

				if (shouldShow) {
					visibleCards++;
				}
			});

			$pane.find('.app-filter-empty-state').toggleClass('hidden', visibleCards > 0);
		});
	}

	function verVideo(element) {
		var $element = $(element);
		var videoUrl = $element.data('video-url');
		var videoOriginalUrl = $element.data('video-original-url');
		var videoType = $element.data('video-type');
		var videoTitle = $element.data('video-title');
		var videoMeta = $element.data('video-meta');

		$(".video-card").removeClass("is-active");
		$element.addClass("is-active");
		currentSharedVideoUrl = videoOriginalUrl;
		$("#ayudaframe").html("");

		if (videoType === 'youtube') {
			$("#ayudaframe").html(`<iframe height="620" src="${videoUrl}" title="${videoTitle}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>`);
			setPlayerMeta(videoTitle, videoMeta);
			$("#ayudaframe").fadeIn();
			return;
		}

		$("#ayudaframe").html(`<video oncontextmenu='return false;' controlslist='nodownload' autoplay height='620' controls><source src='${videoUrl}' type='video/mp4'>Su navegador no soporta este formato de video.</video>`);
		setPlayerMeta(videoTitle, videoMeta);
		$("#ayudaframe").fadeIn();
	}
</script>
@endsection
