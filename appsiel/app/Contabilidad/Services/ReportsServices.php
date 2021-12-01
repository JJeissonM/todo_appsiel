<?php

namespace App\Contabilidad\Services;

use App\Contabilidad\ContabCuentaGrupo;
use DB;
use Auth;

use App\Contabilidad\ContabMovimiento;

class ReportsServices
{ 

    public function get_saldo_clase_cuenta( $fecha_inicial, $fecha_final, $clase_cuenta_id )
    {
        return ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
            ->whereBetween( 'contab_movimientos.fecha', [ $fecha_inicial, $fecha_final ] )
            ->where('contab_cuentas.contab_cuenta_clase_id', $clase_cuenta_id )
            ->get();
    }

    public function get_mov_grupos_padre_cuentas( $fecha_inicial, $fecha_final )
    {
        return ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
        ->leftJoin('contab_cuenta_grupos', 'contab_cuenta_grupos.id', '=', 'contab_cuentas.contab_cuenta_grupo_id')
        ->whereBetween( 'contab_movimientos.fecha', [ $fecha_inicial, $fecha_final ] )
            ->where('contab_cuenta_grupos.grupo_padre_id', 0 )
            ->get();
    }

    public function get_grupos_padre_de_clase_cuenta( $clase_cuenta_id )
    {
        return ContabCuentaGrupo::where( 'contab_cuenta_clase_id', $clase_cuenta_id )
                                ->where( 'grupo_padre_id', 0 )
                                ->get();
    }

    public function get_grupos_hijos( $clase_cuenta_padre_id )
    {
        return ContabCuentaGrupo::where( 'grupo_padre_id', $clase_cuenta_padre_id )
                                ->get();
    }
}