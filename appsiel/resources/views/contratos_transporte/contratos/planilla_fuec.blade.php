<?php

use App\Http\Controllers\ContratoTransporte\ContratoTransporteController;
?>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border" style="width: 100%; text-align: center; font-weight: bold;">{{$v->titulo}} <br> N° {{$p->nro}}</td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border" style="width: 20%; font-weight: bold;">RAZÓN SOCIAL</td>
            <td class="border_center" style="width: 50%; font-size: 12px;">{{$p->razon_social}}</td>
            <td class="border" style="width: 10%; font-weight: bold;">{{ config("configuracion.tipo_identificador") }} </td>
            <td class="border_center" style="width: 20%; font-size: 12px;">{{$p->nit}}</td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border" style="width: 20%; font-weight: bold;">CONTRATO No.</td>
            <td class="border_center" style="width: 80%; font-size: 12px;">{{$c->numero_contrato}}</td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border" style="width: 20%; font-weight: bold;">CONTRATANTE</td>
            <td class="border_center" style="width: 50%; font-size: 12px;">
                @if($c->contratante_id==null || $c->contratante_id=='null') 
                    {{$c->contratanteText}} 
                @else 
                    {{$c->contratante->tercero->descripcion." ".$c->contratante->tercero->razon_social}} 
                @endif
            </td>
            <td class="border" style="width: 10%; font-weight: bold;">{{ config("configuracion.tipo_identificador") }} /CC</td>
            <td class="border_center" style="width: 20%; font-size: 12px;">
                @if($c->contratante_id==null || $c->contratante_id=='null') 
                    {{$c->contratanteIdentificacion}}
                @else 
                    {{$c->contratante->tercero->numero_identificacion}} @if($c->contratante->tercero->tipo!='Persona natural') {{"-".$c->contratante->tercero->digito_verificacion}} @endif 
                @endif
            </td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border" style="width: 20%; font-weight: bold; border-right: none;">OBJETO CONTRATO:</td>
            <td class="border" style="width: 80%; font-size: 12px; border-left: none;">{{strtoupper($c->objeto)}}</td>
        </tr>
        <tr>
            <td class="border" style="width: 20%; font-weight: bold;">ORIGEN - DESTINO</td>
            <td class="border_center" style="width: 80%; font-size: 12px;">{{$c->origen." - ".$c->destino}} / {{$c->tipo_servicio}}</td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border" style="width: 100%;"><span style=" font-weight: bold;"> CONSORCIO UNION TEMPORAL CON: </span><span style="font-size: 12px;">{{$p->convenio}}</span></td>
        </tr>
        <tr>
            <td class="border" style="width: 100%; font-weight: bold; text-align: center;">VIGENCIA DEL CONTRATO</td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border" style="width: 30%; border-bottom: none;"></td>
            <td class="border_center" style="width: 20%; font-weight: bold;">DÍA</td>
            <td class="border_center" style="width: 20%; font-weight: bold;">MES</td>
            <td class="border_center" style="width: 20%; font-weight: bold;">AÑO</td>
        </tr>
        <tr>
            <td class="border" style="width: 30%; font-weight: bold; border-top: none;">FECHA INICIAL</td>
            <td class="border_center" style="width: 20%;font-size: 12px;">{{$fi[2]}}</td>
            <td class="border_center" style="width: 20%;font-size: 12px;">{{ContratoTransporteController::mes()[$fi[1]]}}</td>
            <td class="border_center" style="width: 20%;font-size: 12px;">{{$fi[0]}}</td>
        </tr>
        <tr>
            <td class="border" style="width: 30%; font-weight: bold;">FECHA FINAL</td>
            <td class="border_center" style="width: 20%;font-size: 12px;">{{$ff[2]}}</td>
            <td class="border_center" style="width: 20%;font-size: 12px;">{{ContratoTransporteController::mes()[$ff[1]]}}</td>
            <td class="border_center" style="width: 20%;font-size: 12px;">{{$ff[0]}}</td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border" style="width: 100%; font-weight: bold; text-align: center;">CARACTERÍSTICAS DEL VEHÍCULO</td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border_center" style="width: 15%; font-weight: bold;">PLACA</td>
            <td class="border_center" style="width: 25%; font-weight: bold;">MODELO</td>
            <td class="border_center" style="width: 20%; font-weight: bold;">MARCA</td>
            <td class="border_center" style="width: 40%; font-weight: bold;">CLASE</td>
        </tr>
        <tr>
            <td class="border_center" style="width: 15%;font-size: 12px;">{{$c->vehiculo->placa}}</td>
            <td class="border_center" style="width: 25%;font-size: 12px;">{{$c->vehiculo->modelo}}</td>
            <td class="border_center" style="width: 20%;font-size: 12px;">{{$c->vehiculo->marca}}</td>
            <td class="border_center" style="width: 40%;font-size: 12px;">{{$c->vehiculo->clase}}</td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border_center" style="width: 40%; font-weight: bold;">NÚMERO INTERNO</td>
            <td class="border_center" style="width: 60%; font-weight: bold;">NÚMERO TARJETA DE OPERACIÓN</td>
        </tr>
        <tr>
            <td class="border_center" style="width: 40%;font-size: 12px;">{{$c->vehiculo->int}}</td>
            <td class="border_center" style="width: 60%;font-size: 12px;">@if($to!=null) {{$to->nro_documento}} @else --- @endif</td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border" style="width: 15%; font-weight: bold; font-size: 12px; border-bottom: none;"></td>
            <td class="border_center" style="width: 32%; font-weight: bold; font-size: 12px;">NOMBRES Y APELLIDOS</td>
            <td class="border_center" style="width: 13%; font-weight: bold; font-size: 12px;">No CÉDULA</td>
            <td class="border_center" style="width: 19%; font-weight: bold; font-size: 12px;">No LICENCIA CONDUCCIÓN</td>
            <td class="border_center" style="width: 10%; font-weight: bold; font-size: 12px;">VIGENCIA</td>
        </tr>
        <tr>
            <td class="border" style="width: 15%; font-weight: bold; font-size: 12px; text-align: center; border-top: none;">DATOS DEL CONDUCTOR 1</td>
            <td class="border_center" style="width: 32%; font-size: 12px;">@if(isset($conductores[0])){{$conductores[0]->conductor->tercero->descripcion}}@endif</td>
            <td class="border_center" style="width: 13%; font-size: 12px;">@if(isset($conductores[0])){{$conductores[0]->conductor->tercero->numero_identificacion}}@endif</td>
            <td class="border_center" style="width: 19%; font-size: 12px;">@if(isset($conductores[0])) @if($conductores[0]->licencia!=null) {{$conductores[0]->licencia->nro_documento}} @endif @endif</td>
            <td class="border_center" style="width: 10%; font-size: 12px;">@if(isset($conductores[0])) @if($conductores[0]->licencia!=null) {{$conductores[0]->licencia->vigencia_fin}} @endif @endif</td>
        </tr>
        <tr>
            <td class="border" style="width: 15%; font-weight: bold; font-size: 12px; text-align: center;">DATOS DEL CONDUCTOR 2</td>
            <td class="border_center" style="width: 32%; font-size: 12px;">@if(isset($conductores[1])){{$conductores[1]->conductor->tercero->descripcion}}@endif</td>
            <td class="border_center" style="width: 13%; font-size: 12px;">@if(isset($conductores[1])){{$conductores[1]->conductor->tercero->numero_identificacion}}@endif</td>
            <td class="border_center" style="width: 19%; font-size: 12px;">@if(isset($conductores[1])) @if($conductores[1]->licencia!=null) {{$conductores[1]->licencia->nro_documento}} @endif @endif</td>
            <td class="border_center" style="width: 10%; font-size: 12px;">@if(isset($conductores[1])) @if($conductores[1]->licencia!=null) {{$conductores[1]->licencia->vigencia_fin}} @endif @endif</td>
        </tr>
        <tr>
            <td class="border" style="width: 15%; font-weight: bold; font-size: 12px; text-align: center;">DATOS DEL CONDUCTOR 3</td>
            <td class="border_center" style="width: 32%; font-size: 12px;">@if(isset($conductores[2])){{$conductores[2]->conductor->tercero->descripcion}}@endif</td>
            <td class="border_center" style="width: 13%; font-size: 12px;">@if(isset($conductores[2])){{$conductores[2]->conductor->tercero->numero_identificacion}}@endif</td>
            <td class="border_center" style="width: 19%; font-size: 12px;">@if(isset($conductores[2])) @if($conductores[2]->licencia!=null) {{$conductores[2]->licencia->nro_documento}} @endif @endif</td>
            <td class="border_center" style="width: 10%; font-size: 12px;">@if(isset($conductores[2])) @if($conductores[2]->licencia!=null) {{$conductores[2]->licencia->vigencia_fin}} @endif @endif</td>
        </tr>
        <tr>
            <td class="border" style="width: 15%; font-weight: bold; font-size: 12px; border-bottom: none;"></td>
            <td class="border_center" style="width: 32%; font-weight: bold; font-size: 12px;">NOMBRES Y APELLIDOS</td>
            <td class="border_center" style="width: 13%; font-weight: bold; font-size: 12px;">No CÉDULA</td>
            <td class="border_center" style="width: 19%; font-weight: bold; font-size: 12px;">DIRECCIÓN</td>
            <td class="border_center" style="width: 10%; font-weight: bold; font-size: 12px;">TELÉFONO</td>
        </tr>
        <tr>
            <td class="border" style="width: 15%; font-weight: bold; font-size: 12px; text-align: center; border-top: none;">RESPONSABLE DEL CONTRATANTE</td>
            <td class="border_center" style="width: 32%; font-size: 12px;">
                @if($c->contratante_id==null || $c->contratante_id=='null') 
                    {{$c->contratanteText}} 
                @else 
                    {{$c->contratante->tercero->descripcion." ".$c->contratante->tercero->razon_social}} 
                @endif
            </td>
            <td class="border_center" style="width: 13%; font-size: 12px;">
                @if($c->contratante_id==null || $c->contratante_id=='null') 
                    {{$c->contratanteIdentificacion}} 
                @else 
                    {{$c->contratante->tercero->numero_identificacion}} @if($c->contratante->tercero->tipo!='Persona natural') {{"-".$c->contratante->tercero->digito_verificacion}} @endif 
                @endif
            </td>
            <td class="border_center" style="width: 19%; font-size: 12px;">
                @if($c->contratante_id==null || $c->contratante_id=='null') 
                    {{$c->contratanteDireccion}}
                @else 
                    {{$c->contratante->tercero->direccion1}} 
                @endif
            </td>
            <td class="border_center" style="width: 10%; font-size: 12px;">
                @if($c->contratante_id==null || $c->contratante_id=='null') 
                    {{$c->contratanteTelefono}}
                @else {
                    {$c->contratante->tercero->telefono1}} 
                @endif
            </td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border" style="width: 40%; text-align: center; font-weight: bold; margin-top: 10px !important; font-size: 12px;">@if($empresa!=null) {{$empresa->direccion1." - "}} @endif {{$v->direccion}}<br> @if($empresa!=null) {{$empresa->telefono1." - "}} @endif {{$v->telefono}}<br><a> @if($empresa!=null) {{$empresa->email." - "}} @endif {{$v->correo}}</a></td>
            <td class="border" style="width: 20%; text-align: center; font-weight: bold; margin-top: 10px !important;">
                <img src="{{config('contrato_transporte.url_imagen_sello_empresa')}}" style="max-width: 120px;">
                <br>Sello
            </td>
            <td class="border" style="width: 40%; text-align: center; font-weight: bold; margin-top: 10px !important; font-size: 14px;">
                <img src="{{config('contrato_transporte.url_imagen_firma_rep_legal')}}"  style="max-height: 70px;">
                <br>FIRMA
                <br>
                <i style="font-size: 9px; text-decoration: none;" valign="bottom">{{$v->firma}}</i>
            </td>
        </tr>
    </tbody>
</table>
<table style="width: 100%;">
    <tbody>
        <tr>
            <td class="border" style="width: 100%; text-align: justify; font-size: 10px;">{!!$v->pie_pagina1!!}</a></td>
        </tr>
    </tbody>
</table>