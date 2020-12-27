<?php
    $caja = null;
    $cuenta_bancaria = null;
    if( !is_null( $registros_tesoreria ) )
    {
        if( $registros_tesoreria->teso_caja_id != 0 )
        {
            $caja = $registros_tesoreria->caja;
            $cuenta_bancaria = null;
        }

        if( $registros_tesoreria->teso_cuenta_bancaria_id != 0 )
        {
            $cuenta_bancaria = $registros_tesoreria->cuenta_bancaria;
            $caja = null;
        }
    }   
?>
@if( !is_null( $caja ) )
    <b>Caja: &nbsp;&nbsp;</b> {{ $caja->descripcion }}
    <br>
@endif
@if( !is_null( $cuenta_bancaria ) )
    <b>Cuenta bancaria: &nbsp;&nbsp;</b> Cuenta {{ $cuenta_bancaria->tipo_cuenta }} {{ $cuenta_bancaria->entidad_financiera->descripcion }} No. {{ $cuenta_bancaria->descripcion }}
    <br>
@endif