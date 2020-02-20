<div class="col-sm-{{(12/$cant_cols)}}" style="height: 350px;">
	<button type="button" class="ver_un_album_galeria" style="background-color: transparent; border: none; " data-carousel_id="{{$id}}" title="Ampliar">

		<div style="border: solid 1px gray; padding: 10px; border-radius: 10px; height: 350px;">
			<h4 style="width: 100%;text-align: center;">{{ $descripcion }}</h4>

		 
			<img src="{{ $url_img }}" style="height: 250px;" width="100%"> 
		</div>
	</button>
</div>