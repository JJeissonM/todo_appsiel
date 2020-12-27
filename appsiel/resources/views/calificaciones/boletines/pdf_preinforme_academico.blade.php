<style>
	
	.table {
	    border: 1px solid #ddd;
	}

	.table-bordered {
	    border: 1px solid #ddd;
	}

	.table-striped>tbody>tr:nth-of-type(odd) {
	    background-color: #f9f9f9;
	}

	.page-break {
		page-break-after: always;
	}
</style>

<?php
    $colegio = App\Core\Colegio::where('empresa_id','=', Auth::user()->empresa_id )
                    ->get()[0];

    $url = asset( config('configuracion.url_instancia_cliente') ).'/storage/app/escudos/'.$colegio->imagen;


		$columna = 1;
		$item = 0;
?>

@foreach($estudiantes as $estudiante)

	@include('calificaciones.boletines.pdf_preinforme_academico_un_boletin')


	<div class="page-break"></div>


@endforeach {{-- Estudiante --}}