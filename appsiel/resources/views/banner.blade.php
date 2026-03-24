<div class="banner">
	@php
		$logoPorDefecto = asset('assets/img/company.png');
		$logoMostrar = $logoPorDefecto;

		if (!empty($logo) && $logo !== 'Sin Logo') {
			$logoMostrar = $logo;
			$rutaLogo = trim(parse_url($logo, PHP_URL_PATH) ?? $logo, '/');
			$baseUrl = trim(parse_url(config('configuracion.url_instancia_cliente'), PHP_URL_PATH) ?? '', '/');

			if (!empty($baseUrl) && strpos($rutaLogo, $baseUrl) === 0) {
				$rutaLogo = ltrim(substr($rutaLogo, strlen($baseUrl)), '/');
			}

			$rutaFisicaLogo = base_path(str_replace('/', DIRECTORY_SEPARATOR, $rutaLogo));

			if (empty($rutaLogo) || !file_exists($rutaFisicaLogo)) {
				$logoMostrar = $logoPorDefecto;
			}
		}
	@endphp

	<img src="{{ $logoMostrar }}" height="120px"/>
</div>
