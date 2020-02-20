
<link rel="stylesheet" type="text/css" href="{{asset('assets/css/estilos_formatos.css')}}" media="screen" />

 <?php

	 // Modelos
	use App\Core\Colegio;
	use App\Matriculas\Matricula;
	use App\Matriculas\Estudiante;
	use App\Matriculas\Curso;
	use App\Core\FirmaAutorizada;
	use App\Calificaciones\Periodo;

	$colegio = Colegio::where('empresa_id',Auth::user()->empresa_id)->get();
    $colegio = $colegio[0];

	//$colegio = Colegio::find(Auth::user()->id_colegio);
	//$anio = $request->anio;
	//$id_periodo = $request->id_periodo;
	$vec_estudiante = explode("-",$request->id_estudiante); // id_estudiamte-codigo_matricula
	$id_firma_autorizada = $request->id_firma_autorizada;
	//$curso_id = $request->curso_id;

	$tam_hoja = $request->tam_hoja;
	$tam_letra = $request->tam_letra;


	$nombre_estudiante = Estudiante::get_nombre_completo($vec_estudiante[0],1); //1=Apellidos-Nombre 2=Nombre-Apellidos
	$nombre_grado = Matricula::get_nombre_curso($vec_estudiante[1]);
	$datos_firma_autorizada = FirmaAutorizada::get_datos($id_firma_autorizada);

	$nombre_firma_autorizada = $datos_firma_autorizada['nombre'];
	$nume_doc_firma_autorizada = number_format($datos_firma_autorizada['numero_identificacion'], 0, ',', '.');
	$titulo_persona_firma_autorizada = $datos_firma_autorizada['titulo_tercero'];

 ?>

<div id="watermark-{{$tam_hoja}}">
    <img src="{{ asset(config('configuracion.url_instancia_cliente').'/storage/app/escudos/escudo_'.$colegio->id.'.jpg?'.rand(1,1000)) }}"/>
</div>

<div style="font-size: {{$tam_letra}}mm; line-height: 1.5em;">
	<br/><br/>
	<div align="center">
		LA SUSCRITA COORDINADORA ACADÉMICA Y DE CONVIVENCIA DEL {{ $colegio->descripcion }}
	</div>
	<br/><br/>
	<div align="center">
		HACE CONSTAR QUE:
	</div>
	<br/><br/>
	<div style="text-align: justify;">
		{{ $nombre_estudiante }}, estudiante que cursó el grado {{ $nombre_grado }} no presenta anotaciones en el observador del estudiante por situaciones de convivencia.
	</div>
	<br/>
	<div style="text-align: justify;">
		El estudiante durante todo año demostró un comportamiento acorde con los lineamientos institucionales respetando las normas contenidas en el Manual de Convivencia.
	</div>
	<br/><br/><br/><br/>
	<div>
		{{ $nombre_firma_autorizada}} <br/>
		CC {{ $nume_doc_firma_autorizada }} <br/>
		{{ $titulo_persona_firma_autorizada }}
	</div>
</div>

<footer style="font-size: 4mm; line-height: 1.1em">
	<b>{{ $colegio->descripcion }} - {{ $colegio->slogan }}</b>. Resolución No. {{ $colegio->resolucion }}<br/>
	{{ $colegio->direccion }}, Teléfonos: {{ $colegio->telefonos }}<br/>
</footer>