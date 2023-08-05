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

    public $ids_cuentas;

    public function get_ids_grupos_padres( array $grupos_hijos_ids )
    {
        $grupos_hijos = ContabCuentaGrupo::whereIn( 'id', $grupos_hijos_ids )
                                        ->groupBy('grupo_padre_id')
                                        ->get();
        return $grupos_hijos->pluck('grupo_padre_id')->unique();
    }

    // Todo Grupo Hijo debe tener un Grupo Padre
    public function validar_grupos_hijos( array $cuentas_ids_movimiento )
    {
        $cuentas_grupos_invalidos = ContabCuenta::leftJoin('contab_cuenta_grupos', 'contab_cuenta_grupos.id', '=', 'contab_cuentas.contab_cuenta_grupo_id')
                                ->whereIn( 'contab_cuentas.id', $cuentas_ids_movimiento )
                                ->where( 'contab_cuenta_grupos.grupo_padre_id', '=', 0 )
                                ->select('contab_cuentas.codigo','contab_cuentas.descripcion')
                                ->get();
                                //dd( $cuentas_ids_movimiento, $cuentas_grupos_invalidos );
        $lista = [];
        foreach ($cuentas_grupos_invalidos as $cuenta)
        {
            $lista[] = $cuenta->codigo . ' ' . $cuenta->descripcion;
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

        /**/
        $this->movimiento = ContabMovimiento::leftJoin('contab_cuentas', 'contab_cuentas.id', '=', 'contab_movimientos.contab_cuenta_id')
                                   ->whereBetween( 'contab_movimientos.fecha', [ $fecha_inicial, $fecha_final ] )
                                   ->where('contab_cuentas.contab_cuenta_clase_id', $clase_cuenta_id )
                                   ->get();
        /*$this->ids_cuentas = ContabCuenta::where( 'contab_cuenta_clase_id', $clase_cuenta_id )
                                   ->get()->pluck('id')->all();
        $this->movimiento = ContabMovimiento::whereBetween( 'fecha', [ $fecha_inicial, $fecha_final ] )
                                   ->whereIn('contab_cuenta_id', $this->ids_cuentas )
                                   ->get();
                                   */
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
        $grupo_padre = ContabCuentaGrupo::find( $grupo_padre_id );
        $grupos_hijos_ids = ContabCuentaGrupo::where( 'grupo_padre_id', $grupo_padre_id  )->get()->pluck('id')->all();
        // Para los grupos hijos de este papa
        $ids_cuentas = ContabCuenta::whereIn( 'contab_cuenta_grupo_id', $grupos_hijos_ids )
                                   ->get()->pluck('id')->all();
        
        return (object)[ 
                            'descripcion' => $grupo_padre->descripcion,
                            'valor' => $this->movimiento->whereIn( 'contab_cuenta_id', $ids_cuentas )->sum('valor_saldo')
                        ];
    }

    public function datos_clase_cuenta( $clase_cuenta_id )
    {
        $clase_cuenta = ClaseCuenta::find( $clase_cuenta_id );
        
        $ids_cuentas = ContabCuenta::where( 'contab_cuenta_clase_id', $clase_cuenta_id )
                                   ->get()->pluck('id')->all();

        return (object)[ 
                            'descripcion' => strtoupper( $clase_cuenta->descripcion ),
                            'valor' => $this->movimiento->whereIn( 'contab_cuenta_id', $ids_cuentas )->sum('valor_saldo')
                        ];
    }
}