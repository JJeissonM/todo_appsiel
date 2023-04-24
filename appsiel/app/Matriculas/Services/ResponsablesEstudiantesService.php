<?php 

namespace App\Matriculas\Services;

use App\Core\Tercero;
use App\Matriculas\Responsableestudiante;

use App\Ventas\Cliente;
use App\Ventas\VtasMovimiento;
use Illuminate\Support\Facades\Schema;

class ResponsablesEstudiantesService
{
    public function crear_datos_padres_y_acudiente($request,$empresa_id,$estudiante_id)
    {
        $datos_generales = [
            'codigo_ciudad' => $request->codigo_ciudad,
            'id_tipo_documento_id' => 13,
            'core_empresa_id' => $empresa_id, 
            'tipo' => 'Persona natural', 
            'estado' => 'Activo'
        ];

        if ($request->cedula_mama != '') {
            $mama = Tercero::where( 'numero_identificacion', $request->cedula_mama )->get()->first();
            if ($mama == null) {
                $mama = Tercero::create( array_merge( $datos_generales, [
                                            'email' => $request->email_mama, 
                                            'numero_identificacion' => $request->cedula_mama, 
                                            'descripcion' => $request->mama, 
                                            'nombre1' => $request->mama, 
                                            'telefono1' => $request->telefono_mama, 
                                            'direccion1' => $request->direccion_mama
                                        ] ) );
            }
            
            Responsableestudiante::create( [
                'tiporesponsable_id' => 2,
                'estudiante_id' => $estudiante_id,
                'tercero_id' => $mama->id
            ] );
        }
        
        if ($request->cedula_papa != '') {
            $papa = Tercero::where( 'numero_identificacion', $request->cedula_papa )->get()->first();
            if ($papa == null) {
                $papa = Tercero::create( array_merge( $datos_generales, [
                                            'email' => $request->email_papa, 
                                            'numero_identificacion' => $request->cedula_papa, 
                                            'descripcion' => $request->papa, 
                                            'nombre1' => $request->papa, 
                                            'telefono1' => $request->telefono_papa,
                                            'direccion1' => $request->direccion_papa
                                        ] ));
            }
                            
            Responsableestudiante::create( [
                'tiporesponsable_id' => 1,
                'estudiante_id' => $estudiante_id,
                'tercero_id' => $papa->id
            ] );
        }
        
        if ($request->cedula_acudiente != '') {
            $acudiente = Tercero::where( 'numero_identificacion', $request->cedula_acudiente )->get()->first();
            if ($acudiente == null) {
                $acudiente = Tercero::create( array_merge( $datos_generales, [
                                            'email' => $request->email_acudiente, 
                                            'numero_identificacion' => $request->cedula_acudiente, 
                                            'descripcion' => $request->acudiente, 
                                            'nombre1' => $request->acudiente, 
                                            'telefono1' => $request->telefono_acudiente,
                                            'direccion1' => $request->direccion_acudiente
                                        ] ));
            }
        }

        switch ($request->acudiente_seleccionado) {
            case 'madre':
                $acudiente_tercero_id = $mama->id;
                break;
            
            case 'padre':
                $acudiente_tercero_id = $papa->id;
                break;
            
            case 'otro':
                $acudiente_tercero_id = $acudiente->id;
                break;
            
            default:
                # code...
                break;
        }

        Responsableestudiante::create( [
            'tiporesponsable_id' => 3,
            'estudiante_id' => $estudiante_id,
            'tercero_id' => $acudiente_tercero_id
        ] );

        if ( Cliente::where( 'core_tercero_id', $acudiente_tercero_id)->get()->first() == null )
        {
            // Datos del Cliente
            $cliente = new Cliente;
            $cliente->fill( 
                            [
                                'core_tercero_id' => $acudiente_tercero_id,
                                'encabezado_dcto_pp_id' => 1,
                                'clase_cliente_id' => 1,
                                'lista_precios_id' => 1,
                                'lista_descuentos_id' => 1,
                                'vendedor_id' => 1,
                                'inv_bodega_id' => 1,
                                'zona_id' => 1,
                                'liquida_impuestos' => 1,
                                'condicion_pago_id' => 1,
                                'estado' => 'Activo' 
                            ]
                        );
            $cliente->save();
        }

    }

    public function delete_datos_padres_y_acudiente($estudiante_id)
    {
        $responsables = Responsableestudiante::where( [
            ['estudiante_id', '=', $estudiante_id]
        ] )
            ->get();
        
        foreach ($responsables as $responsable) {
            $otra_relacion_del_responsable = $responsables = Responsableestudiante::where( [
                ['estudiante_id', '<>', $estudiante_id],
                ['tercero_id', '=', $responsable->tercero_id]
            ] )
                ->get()
                ->first();

            // El responsable NO es responsable de otros estudiantes
            if ( $otra_relacion_del_responsable == null) {

                $cliente_asociado = Cliente::where([
                    ['core_tercero_id','=',$responsable->tercero_id]
                ])->get()
                ->first();

                if ($cliente_asociado != null) {
                    
                    $movimiento_cliente = null;

                    if (Schema::hasTable('vtas_movimientos'))
                    {
                        $movimiento_cliente = VtasMovimiento::where([
                            ['cliente_id','=',$cliente_asociado->id]
                        ])->get()
                        ->first();
                    }                    
                    
                    // Clietne NO tiene movimientos
                    if ($movimiento_cliente == null) {
                        $cliente_asociado->delete();
                    }
                }

                $responsable->delete();
            }
        }
    }
}