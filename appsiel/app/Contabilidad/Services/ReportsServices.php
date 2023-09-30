<?php

namespace App\Contabilidad\Services;

use App\Contabilidad\ClaseCuenta;
use App\Contabilidad\ContabCuentaGrupo;
use App\Contabilidad\ContabCuenta;

use DB;
use Auth;

use App\Contabilidad\ContabMovimiento;

class ReportsServices
{ 
    public $movimiento;

    public $ids_cuentas, $clases_cuentas, $grupos_cuentas, $cuentas, $totales_clases;

    public function get_ids_grupos_padres( $clase_cuenta_id )
    {
        $arr_ids_grupos_asociados_a_las_cuentas = array_values( $this->movimiento->where('contab_cuenta_clase_id', $clase_cuenta_id)->pluck('contab_cuenta_grupo_id')->unique()->toArray() );

        $arr_ids_grupos_padres = [];
        $grupos_cuentas = $this->grupos_cuentas;
        foreach ( $grupos_cuentas as $grupo_cuenta) {
            if (in_array($grupo_cuenta->id,$arr_ids_grupos_asociados_a_las_cuentas) && !in_array($grupo_cuenta->grupo_padre_id,$arr_ids_grupos_padres)) {
                $arr_ids_grupos_padres[] = $grupo_cuenta->grupo_padre_id;
            }
        }
        return $arr_ids_grupos_padres;
    }

    // Todo Grupo Hijo debe tener un Grupo Padre
    public function validar_grupos_hijos()
    {
        $ids_grupos_hijos = $this->movimiento->pluck('contab_cuenta_grupo_id')->unique()->toArray();

        $grupos_sin_padres = $this->grupos_cuentas->whereIn( 'id', $ids_grupos_hijos )->where('grupo_padre_id', '=', 0)->pluck('id')->toArray();

        $lista = [];
        foreach ($this->cuentas as $cuenta)
        {
            if (in_array($cuenta->contab_cuenta_grupo_id, $grupos_sin_padres)) {
                $lista[] = $cuenta->codigo . ' ' . $cuenta->descripcion;
            }
        }

        return $lista;
    }

    public function get_grupos_hijos( $grupo_padre_id )
    {
        return ContabCuentaGrupo::where( 'grupo_padre_id', $grupo_padre_id )
                                ->get();
    }

    public function get_cuentas_del_grupo( $grupo_cuenta_id )
    {
        return ContabCuenta::where( 'contab_cuenta_grupo_id', $grupo_cuenta_id )
                                ->get();
    }

    public function set_mov_clase_cuenta( $fecha_inicial, $fecha_final, $clase_cuenta_id )
    {
        $this->movimiento = ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                                   ->whereBetween( 'contab_movimientos.fecha', [ $fecha_inicial, $fecha_final ] )
                                   ->where('contab_cuentas.contab_cuenta_clase_id', $clase_cuenta_id )
                                   ->get();
    }

    public function get_mov_grupo_cuenta( $fecha_inicial, $fecha_final, $grupo_cuenta_id )
    {
        return ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                            ->leftJoin('contab_cuenta_grupos', 'contab_cuenta_grupos.id', '=', 'contab_cuentas.contab_cuenta_grupo_id')
                            ->whereBetween( 'contab_movimientos.fecha', [ $fecha_inicial, $fecha_final ] )
                            ->where('contab_cuenta_grupos.id', $grupo_cuenta_id )
                            ->get();
    }

    public function datos_fila_grupo_padre( $grupo_padre_id )
    {
        // Los grupos hijos son los que estan en el movimiento (los asociados a las cuentas)
        $arr_ids_grupos_hijos = $this->grupos_cuentas->where( 'grupo_padre_id', $grupo_padre_id  )->pluck('id')->all();

        $grupo_padre = $this->grupos_cuentas->where( 'id', $grupo_padre_id )->first();
        
        return (object)[ 
                            'descripcion' => $grupo_padre->descripcion,
                            'valor' => $this->movimiento->whereIn( 'contab_cuenta_grupo_id', $arr_ids_grupos_hijos )->sum('valor_saldo')
                        ];
    }
}