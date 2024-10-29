<?php

namespace App\Http\Controllers\Tesoreria;

use App\Contabilidad\ContabMovimiento;
use App\Core\Empresa;
use App\Core\Tercero;
use App\Http\Controllers\Core\TransaccionController;

use App\Sistema\Html\BotonesAnteriorSiguiente;

use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMovimiento;
use App\Tesoreria\TesoDocEncabezadoTraslado;
use App\User;
use Collective\Html\FormFacade as Form;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class TrasladoEfectivosController extends TransaccionController
{
    public function show($id)
    {
        $this->set_variables_globales();
        
        $botones_anterior_siguiente = new BotonesAnteriorSiguiente($this->transaccion, $id);
        
        $doc_encabezado = TesoDocEncabezado::get_registro_impresion($id);
        
        $doc_registros = TesoDocRegistro::get_registros_impresion($doc_encabezado->id);
        
        $registros_contabilidad = TransaccionController::get_registros_contabilidad($doc_encabezado);
        
        $empresa = $this->empresa;
        
        $id_transaccion = $this->transaccion->id;
        
        $documento_vista = '';
        
         $miga_pan = [
            ['url' => $this->app->app . '?id=' . Input::get('id'), 'etiqueta' => $this->app->descripcion],
            ['url' => 'web' . $this->variables_url, 'etiqueta' => $this->modelo->descripcion],
            ['url' => 'NO', 'etiqueta' => $doc_encabezado->documento_transaccion_prefijo_consecutivo]
        ];
        
        //$nombre = true;
        
        return view('tesoreria.traslados_efectivo.show', compact('empresa', 'botones_anterior_siguiente', 'doc_encabezado', 'doc_registros', 'registros_contabilidad', 'miga_pan', 'id', 'id_transaccion', 'documento_vista'));
    }

    public function vista_preliminar($id)
    {
        $registro = TesoDocEncabezado::find($id);
        $empresa = Empresa::find($registro->core_empresa_id);
        $tercero = Tercero::find($registro->core_tercero_id);
        $user = User::where('email', $registro->creado_por)->first();
        $doc_encabezado = TesoDocEncabezado::get_registro_impresion($id);
        $doc_registros = TesoDocRegistro::get_registros_impresion($doc_encabezado->id);
       // dd($doc_registros);
        $view = View::make('tesoreria.traslados_efectivo.print', compact('registro', 'empresa', 'doc_encabezado', 'user', 'doc_registros'))->render();

        return $view;
    }

    public function imprimir($id)
    {
        $view = TrasladoEfectivosController::vista_preliminar($id);
        // Se prepara el PDF
        $orientacion = 'portrait';
        $tam_hoja = 'Letter';
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja, $orientacion);
        return $pdf->download('traslado_efectivo.pdf');
    }


    // AJAX: enviar fila para el ingreso de registros al elaborar pago
    public function ajax_get_fila()
    {
        $medios_recaudo = TesoMedioRecaudo::opciones_campo_select();

        $cajas = TesoCaja::opciones_campo_select();
        $cuentas_bancarias = TesoCuentaBancaria::opciones_campo_select();
        $motivos = TesoMotivo::where('teso_tipo_motivo', 'Traslado')->get()->pluck('descripcion', 'movimiento');

        $btn_borrar = "<button class='btn btn-danger btn-xs btn_eliminar'><i class='fa fa-trash'></i></button>";
        $btn_confirmar = "<button class='btn btn-success btn-xs btn_confirmar'><i class='fa fa-check'></i></button>";

        $tr = '<tr id="linea_ingreso_default" class="linea_ingreso_default">
                    <td>
                        ' . Form::select('teso_medio_recaudo_id', $medios_recaudo, null, ['id' => 'teso_medio_recaudo_id', 'class' => 'lista_desplegable' ] ) . '
                    </td>
                    <td>
                        ' . Form::select('motivo', $motivos, null, ['id' => 'teso_motivo_id', 'class' => 'lista_desplegable']) . '
                    </td>
                    <td>
                        ' . Form::select('teso_caja_id', $cajas, null, ['id' => 'teso_caja_id', 'class' => 'lista_desplegable']) . '
                    </td>
                    <td>
                        ' . Form::select('teso_cuenta_bancaria_id', $cuentas_bancarias, null, ['id' => 'teso_cuenta_bancaria_id', 'class' => 'lista_desplegable']) . '
                    </td>
                    <td> ' . Form::text('valor', null, ['id' => 'valor_total', 'class' => 'caja_texto']) . ' </td>
                    <td> <div class="btn-group"> ' . $btn_confirmar . $btn_borrar . ' </div> </td>
                </tr>';
        return $tr;
    }

    public function anular_traslado($id)
    {
        $documento = TesoDocEncabezado::find($id);
        $modificado_por = Auth::user()->email;

        $array_wheres = ['core_empresa_id' => $documento->core_empresa_id,
            'core_tipo_transaccion_id' => $documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $documento->core_tipo_doc_app_id,
            'consecutivo' => $documento->consecutivo];


        // >>> Eliminación

        // 1ro. Borrar registros contables
        ContabMovimiento::where($array_wheres)->delete();

        // 2do. Se elimina el movimiento de tesorería
        TesoMovimiento::where($array_wheres)->delete();

        // 3ro. Se eliminan los registros del documento
        TesoDocRegistro::where('teso_encabezado_id', $documento->id)->update(['estado' => 'Anulado']);

        // 4to. Se marca commo anulado el documento
        $documento->update(['estado' => 'Anulado', 'modificado_por' => $modificado_por]);

        return redirect('tesoreria/traslado_efectivo/' . $id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('flash_message', 'Documento de traslado de efectico fue anulado correctamente.');
    }



    public function recontabilizar( $doc_encabezado_id )
    {
        $doc_encabezado = TesoDocEncabezadoTraslado::find( $doc_encabezado_id );

        $doc_encabezado->recontabilizar();

        return redirect('tesoreria/traslado_efectivo/' . $doc_encabezado_id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('flash_message', 'Documento RECONTABILIZADO Correctamente.');
    }


    public function recontabilizacion_masiva( $fecha_inicial, $fecha_final )
    {
        $docs_encabezados = TesoDocEncabezadoTraslado::where([
                                                                ['core_tipo_transaccion_id',43],
                                                                ['estado','Activo']
                                                            ])
                                                    ->whereBetween( 'fecha', [$fecha_inicial,$fecha_final] )
                                                    ->get();

        $i = 0;
        foreach ($docs_encabezados as $doc_encabezado)
        {
            $doc_encabezado->recontabilizar();
            $i++;
        }

        echo 'Se Recontabilizaron <u>' . $i . '</u> documentos.';
    }
}
