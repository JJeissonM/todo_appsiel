<?php 
    if(!isset($colegio))
    {
        $empresa = \App\Core\Empresa::find($empresa->id);
    }else{
        $empresa = $colegio->empresa;
    }
?>

<div style="position: absolute; bottom:40px; color:{{ config('calificaciones.color_fuente_boletin') }}; opacity:0.9; width: 100%; text-align:center; font-size: 10px;">
    {{ $empresa->direccion1 }} - Teléfono {{ $empresa->telefono1 }} –  {{ $empresa->email }}
    <br>
    {{ $empresa->ciudad->descripcion }},  Colombia    
</div>
