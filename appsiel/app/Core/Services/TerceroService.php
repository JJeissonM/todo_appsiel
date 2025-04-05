<?php

namespace App\Core\Services;

use App\Compras\Proveedor;
use App\Core\Empresa;
use App\Core\Tercero;
use App\Ventas\Cliente;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TerceroService
{
    public function create_tercero($request)
    {        
        $tercero_enviado = Tercero::find((int)$request->core_tercero_id);

        if ($tercero_enviado == null) {
            $datos = $request->all();

            $datos['codigo_ciudad'] = '16920001'; // Valledupar
            $datos['tipo'] = 'Persona natural';
            $datos['id_tipo_documento_id'] = 13;  // CC
            $datos['descripcion'] = $request->name;
            $datos['nombre1'] = $request->name;

            $tercero_enviado = Tercero::create( $datos );
        }

        return $tercero_enviado;
    }

    public function create_or_update_tercero($request)
    {        
        $tercero_enviado = Tercero::find((int)$request->core_tercero_id);

        if ($tercero_enviado != null) {
            $datos = $request->all();

            $datos['descripcion'] = $request->name;
            $datos['nombre1'] = $request->name;

            $tercero_enviado->update( $datos );
        }else{
            $tercero_enviado = $this->create_tercero($request);
        }

        return $tercero_enviado;
    }

    public function actualizar_representante_legal($datos, $tercero_id)
    {
        $representante_legal_actual = DB::table('core_tercero_tiene_representante_legal')->where('tercero_id', '=', $tercero_id)->get();

        // Ya tiene rep. legal asociado
        if( !empty( $representante_legal_actual ) )
        {
            $representante_legal_actual_id = $representante_legal_actual[0]->representante_legal_id;

            // Cambió el rep. legal
            if ( $representante_legal_actual_id != (int)$datos['representante_legal_id'] )
            {
                // Cambió por uno vacío
                if ( (int)$datos['representante_legal_id'] == 0 )
                {
                    // Se elimina
                    DB::table( 'core_tercero_tiene_representante_legal' )->where( 'tercero_id', '=', $tercero_id)->delete();
                }else{
                    // Se actualiza
                    DB::table( 'core_tercero_tiene_representante_legal' )->where( 'tercero_id', '=', $tercero_id)
                                                                    ->update( [ 
                                                                                'representante_legal_id' => (int)$datos['representante_legal_id'] 
                                                                            ] );
                }
                    
            }
        }else{
            // Si no tiene rep. legal
            $this->asignar_representante_legal($datos, $tercero_id);
        }
    }

    public function asignar_representante_legal($datos, $tercero_id)
    {
        if (isset($datos['representante_legal_id'])) {
            if( (int)$datos['representante_legal_id'] != 0 )
            {
                DB::table('core_tercero_tiene_representante_legal')->insert([
                                                'tercero_id' => $tercero_id,
                                                'representante_legal_id' => (int)$datos['representante_legal_id']
                                            ]);
            }
        }
    }

    public function crear_tercero_como_cliente_y_proveedor($tercero_id)
    {
        $cliente = Cliente::where('core_tercero_id', $tercero_id)->get()->first();

        if ( $cliente == null ) {
            $data = [
                'core_tercero_id' => $tercero_id,
                'encabezado_dcto_pp_id' => 0,
                'clase_cliente_id' => config('ventas.clase_cliente_id'),
                'lista_precios_id' => config('ventas.lista_precios_id'),
                'lista_descuentos_id' => config('ventas.lista_descuentos_id'),
                'vendedor_id' => config('ventas.vendedor_id'),
                'inv_bodega_id' => config('ventas.inv_bodega_id'),
                'zona_id' => config('ventas.zona_id'),
                'liquida_impuestos' => 1,
                'condicion_pago_id' => 1,
                'cupo_credito' => 0,
                'bloquea_por_cupo' => 0,
                'bloquea_por_mora' => 0,
                'estado' => 'Activo'
            ];

            Cliente::create( $data );
        }
        
        $proveedor = Proveedor::where('core_tercero_id', $tercero_id)->get()->first();

        if ( $proveedor == null ) {
            $data = [
                'core_tercero_id' => $tercero_id,
                'clase_proveedor_id' => 1,
                'inv_bodega_id' => 1,
                'liquida_impuestos' => 1,
                'condicion_pago_id' => 1,
                'estado' => 'Activo'
            ];
    
            Proveedor::create( $data );
        }
        
    }
}