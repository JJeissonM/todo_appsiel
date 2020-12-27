@section('estilos_1')
	<link rel="stylesheet" type="text/css" href="{{asset('assets/css/estilos_formatos.css')}}" media="screen" />
@endsection 

<?php
	use App\Http\Controllers\Core\DisFormatosController;


	$secciones=DB::table('difo_secciones_formatos')->where('id_formato',$formato->id)->orderBy('orden','ASC')->get();

	$empresa = App\Core\Empresa::find(Auth::user()->empresa_id);

?>

@include('banner_colegio')

<div style="font-size: {{$request->tam_letra}}mm; line-height: 1.5em;">
	@foreach($secciones as $una_seccion)
		<?php 
			$contenido = "";
			$seccion = App\Core\DifoSeccion::find($una_seccion->id_seccion, null);

			// Se genera el contenido de la seccion de acuerdo a las palabras claves (campos) que contenga
			$contenido.=DisFormatosController::formatear_contenido($request, $seccion, null);

			$espacios_antes = str_repeat("<br/>",$seccion->cantidad_espacios_antes);
			$espacios_despues = str_repeat("<br/>",$seccion->cantidad_espacios_despues);

			$estilos='text-align:'.$seccion->alineacion.';font-weight:'.$seccion->estilo_letra.';';
		?>

		@include('core.dis_formatos.seccion',['presentacion'=>$seccion->presentacion,'contenido'=>$contenido,'espacios_antes'=>$espacios_antes,'estilos'=>$estilos,'espacios_despues'=>$espacios_despues])
	@endforeach
</div>

<footer style="font-size: 4mm;
	position: fixed; 
    bottom: 0px; 
    left: 0px; 
    right: 0px;
    width: 100%;
    /*color: blue;
    text-decoration: underline;*/
    text-align: center;">
    <hr>
	{{ $empresa->pagina_web }}
</footer>