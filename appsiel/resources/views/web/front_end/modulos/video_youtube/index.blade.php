@php
	$height = '350';
	if ( $altura != '')
	{
		$height = $altura;
	}
@endphp
<iframe width="100%" height="{{ $height }}" src="{{ str_replace( 'watch?v=', 'embed/', explode('&', $url_video)[0] ).'?autoplay='.$autoplay.'&controls='.$controls }}" allow="autoplay; encrypted-media" allowfullscreen>
	
</iframe>