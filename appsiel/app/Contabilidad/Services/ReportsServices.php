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

    public $ids_cuentas, $grupos_cuentas, $cuentas;

    public $totales_clases = [ 0, 0, 0, 0, 0, 0, 0 ];

    public function get_filas_eeff( $fecha_inicial, $fecha_final, $detallar_cuentas, $ids_clases_cuentas )
    {        
        $filas = [];
        
        $this->set_movimiento_entre_fechas( $fecha_inicial, $fecha_final );

        $this->grupos_cuentas = ContabCuentaGrupo::all();

        $this->cuentas = ContabCuenta::all();

        foreach ( $ids_clases_cuentas as $key => $clase_cuenta_id )
        {
            $valor_clase = $this->datos_clase_cuenta( $clase_cuenta_id );
            
            if ( $valor_clase->valor == 0 )
            {
                continue;
            }

            $this->totales_clases[$clase_cuenta_id] = $valor_clase->valor;

            $filas[] = (object)[
                                'datos_clase_cuenta' => $valor_clase,
                                'datos_grupo_padre' => 0,
                                'datos_grupo_hijo' => 0,
                                'datos_cuenta' => 0
                                ];
                                
            // Cada cuenta debe estar, obligatoriamente, asignada a un grupo hijo
            $grupos_invalidos = $this->validar_grupos_hijos();

            if( !empty( $grupos_invalidos ) )
            {
                dd( 'Las siguientes Cuentas no tienen correctamente asociado un Grupo de cuentas. por favor modifique la Cuenta en los Catálogos para continuar.', $grupos_invalidos );
            }
            
            $arr_ids_grupos_hijos = $this->get_arr_ids_grupos_hijos($clase_cuenta_id);
            
            $grupos_padres = $this->get_ids_grupos_padres( $arr_ids_grupos_hijos );
            
            foreach ( $grupos_padres as $key => $grupo_padre_id )
            {
                $valor_padre = $this->datos_fila_grupo_padre( $grupo_padre_id );

                if ( $valor_padre->valor == 0 )
                {
                    continue;
                }

                $filas[] = (object)[
                                    'datos_clase_cuenta' => 0,
                                    'datos_grupo_padre' => $valor_padre,
                                    'datos_grupo_hijo' => 0,
                                    'datos_cuenta' => 0
                                    ];
                
                $grupos_hijos = $this->get_grupos_hijos( $grupo_padre_id );
                foreach ($grupos_hijos as $grupo_hijo )
                {
                    $valor_hijo = $this->get_mov_grupo_cuenta( $fecha_inicial, $fecha_final, $grupo_hijo->id )->sum('valor_saldo');

                    if ( $valor_hijo == 0 )
                    {
                        continue;
                    }

                    $filas[] = (object)[
                        'datos_clase_cuenta' => 0,
                        'datos_grupo_padre' => 0,
                        'datos_grupo_hijo' => (object)[ 
                                                        'descripcion' => $grupo_hijo->descripcion,
                                                        'valor' => $valor_hijo
                                                    ],
                        'datos_cuenta' => 0
                        ];
                    
                    $cuentas_del_grupo = $this->get_cuentas_del_grupo( $grupo_hijo->id );

                    foreach ($cuentas_del_grupo as $cuenta)
                    {
                        if( !$detallar_cuentas )
                        {
                            continue;
                        }
                        
                        $valor_cuenta = $this->movimiento->where( 'contab_cuenta_id', $cuenta->id )->sum('valor_saldo');
                        if ( $valor_cuenta == 0 )
                        {
                            continue;
                        }

                        $filas[] = (object)[
                                                'datos_clase_cuenta' => 0,
                                                'datos_grupo_padre' => 0,
                                                'datos_grupo_hijo' => 0,
                                                'datos_cuenta' => (object)[ 
                                                                            'descripcion' => $cuenta->codigo . ' ' . $cuenta->descripcion,
                                                                            'valor' => $valor_cuenta
                                                                        ]
                                                ];
                    }
                }
            }
        }

        return $filas;
    }

    public function get_arr_ids_grupos_hijos($clase_cuenta_id)
    {
        $cuentas_movimiento = array_values( $this->movimiento->pluck('contab_cuenta_id')->unique()->toArray() );

        $arr_ids_grupos_hijos = [];
        foreach ($cuentas_movimiento as $cuenta_id) {

            $cuenta = $this->cuentas->where('id',$cuenta_id)->first();

            if ($cuenta == null) {
                dd('Cuenta con ID=' . $cuenta_id . ' errada.');
            }

            if (!in_array($cuenta->contab_cuenta_grupo_id, $arr_ids_grupos_hijos) && $cuenta->contab_cuenta_clase_id == $clase_cuenta_id) {
                $arr_ids_grupos_hijos[] = $cuenta->contab_cuenta_grupo_id;
            }            
        }

        return $arr_ids_grupos_hijos;
    }

    public function get_ids_grupos_padres( array $arr_ids_grupos_hijos )
    {
        $arr_ids_grupos_padres = [];
        foreach ($this->grupos_cuentas as $grupo_cuenta) {
            if (in_array($grupo_cuenta->id,$arr_ids_grupos_hijos) && !in_array($grupo_cuenta->grupo_padre_id,$arr_ids_grupos_padres)) {
                $arr_ids_grupos_padres[] = $grupo_cuenta->grupo_padre_id;
            }
        }
        return $arr_ids_grupos_padres;
    }

    // Todo Grupo Hijo debe tener un Grupo Padre
    public function validar_grupos_hijos()
    {
        $cuentas_ids_movimiento = $this->movimiento->pluck('contab_cuenta_id')->unique()->toArray();

        $cuentas_grupos_invalidos = ContabCuenta::leftJoin('contab_cuenta_grupos', 'contab_cuenta_grupos.id', '=', 'contab_cuentas.contab_cuenta_grupo_id')
                                ->whereIn( 'contab_cuentas.id', $cuentas_ids_movimiento )
                                ->where( 'contab_cuenta_grupos.grupo_padre_id', '=', 0 )
                                ->select('contab_cuentas.codigo','contab_cuentas.descripcion')
                                ->get();

        $lista = [];
        foreach ($cuentas_grupos_invalidos as $cuenta)
        {
            $lista[] = $cuenta->codigo . ' ' . $cuenta->descripcion;
        }

        return $lista;
    }

    public function get_grupos_hijos( $grupo_padre_id )
    {
        return $this->grupos_cuentas->where( 'grupo_padre_id', $grupo_padre_id )->all();
    }

    public function get_cuentas_del_grupo( $grupo_cuenta_id )
    {
        return $this->cuentas->where( 'contab_cuenta_grupo_id', $grupo_cuenta_id );
    }

    public function set_movimiento_entre_fechas( $fecha_inicial, $fecha_final )
    {
        $this->movimiento = ContabMovimiento::whereBetween( 'fecha', [ $fecha_inicial, $fecha_final ] )
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
        $grupo_padre = $this->grupos_cuentas->where( 'id', $grupo_padre_id )->first();
        
        $arr_ids_grupos_hijos = $this->grupos_cuentas->where( 'grupo_padre_id', $grupo_padre_id  )->pluck('id')->all();
        
        // Para los grupos hijos de este papa
        $ids_cuentas = $this->cuentas->whereIn( 'contab_cuenta_grupo_id', $arr_ids_grupos_hijos )
                                   ->pluck('id')->toArray();
        
        return (object)[ 
                            'descripcion' => $grupo_padre->descripcion,
                            'valor' => $this->movimiento->whereIn( 'contab_cuenta_id', $ids_cuentas )->sum('valor_saldo')
                        ];
    }

    public function datos_clase_cuenta( $clase_cuenta_id )
    {
        $clase_cuenta = ClaseCuenta::find( $clase_cuenta_id );
        
        $ids_cuentas = $this->cuentas->where( 'contab_cuenta_clase_id', $clase_cuenta_id )
                                   ->pluck('id')->all();

        return (object)[ 
                            'descripcion' => strtoupper( $clase_cuenta->descripcion ),
                            'valor' => $this->movimiento->whereIn( 'contab_cuenta_id', $ids_cuentas )->sum('valor_saldo')
                        ];
    }
}