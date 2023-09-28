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

    public $ids_cuentas, $clases_cuentas, $grupos_cuentas, $cuentas;

    public $totales_clases = [ 0, 0, 0, 0, 0, 0, 0 ];

    public function get_filas_eeff( $fecha_inicial, $fecha_final, $detallar_cuentas, $ids_clases_cuentas )
    {        
        $filas = [];

        $this->movimiento = ContabMovimiento::leftJoin('contab_cuentas','contab_cuentas.id','=','contab_movimientos.contab_cuenta_id')
                            ->leftJoin('contab_cuenta_clases','contab_cuenta_clases.id','=','contab_cuentas.contab_cuenta_clase_id')
                            ->whereBetween( 'contab_movimientos.fecha', [ $fecha_inicial, $fecha_final ] )
                            ->select(
                                    'contab_cuentas.contab_cuenta_clase_id',
                                    'contab_cuentas.contab_cuenta_grupo_id',
                                    'contab_movimientos.contab_cuenta_id',
                                    'contab_movimientos.valor_saldo',
                                    'contab_movimientos.fecha'
                                )
                            ->get();
        
        $this->clases_cuentas = ClaseCuenta::all();

        $this->grupos_cuentas = ContabCuentaGrupo::all();

        $this->cuentas = ContabCuenta::all();

        foreach ( $ids_clases_cuentas as $key => $clase_cuenta_id )
        {                    
            // Cada cuenta debe estar, obligatoriamente, asignada a un grupo hijo
            $grupos_invalidos = $this->validar_grupos_hijos();

            if( !empty( $grupos_invalidos ) )
            {
                dd( 'Las siguientes Cuentas no tienen correctamente asociado un Grupo de cuentas. por favor modifique la Cuenta en los Catálogos para continuar.', $grupos_invalidos );
            }

            $valor_clase = (object)[ 
                'descripcion' => strtoupper( $this->clases_cuentas->where('id',$clase_cuenta_id)->first()->descripcion ),
                'valor' => $this->movimiento->where( 'contab_cuenta_clase_id', $clase_cuenta_id )->sum('valor_saldo')
            ];
            
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
            
            $grupos_padres = $this->get_ids_grupos_padres( $clase_cuenta_id );
            
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
                
                $grupos_hijos = $this->grupos_cuentas->where( 'grupo_padre_id', $grupo_padre_id )->all();

                foreach ($grupos_hijos as $grupo_hijo )
                {
                    $valor_hijo = $this->movimiento->where( 'contab_cuenta_grupo_id', $grupo_hijo->id )->sum('valor_saldo');

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
                    
                    $cuentas_del_grupo = $this->cuentas->where( 'contab_cuenta_grupo_id', $grupo_hijo->id );

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