<?php
	use App\Http\Controllers\Ventas\ReportesController;

	$facturas = ReportesController::facturas_electronicas_pendientes_por_enviar();
?>

@extends('layouts.principal')

@section('content')

    <div class="marco_formulario">
        <div class="row">
            <div class="col-md-6">
                
            </div>
            <div class="col-md-6">
                @include('ventas.incluir.lista_facturas_electronicas',['titulo'=>'Fact. Electrónicas pendientes por enviar'])
            </div>
        </div>
    </div>

@endsection