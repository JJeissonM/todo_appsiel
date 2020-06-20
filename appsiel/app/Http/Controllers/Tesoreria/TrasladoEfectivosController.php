<?php

namespace App\Http\Controllers\Tesoreria;

use App\Contabilidad\ContabMovimiento;
use App\Core\Empresa;
use App\Core\Tercero;
use App\Http\Controllers\Core\TransaccionController;
use App\Http\Controllers\Sistema\ModeloController;
use App\Http\Controllers\TrasladoEfectivoController;
use App\Sistema\Html\Boton;
use App\Sistema\Html\BotonesAnteriorSiguiente;
use App\Sistema\TipoTransaccion;
use App\Tesoreria\ArqueoCaja;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;
use App\Tesoreria\TesoDocEncabezado;
use App\Tesoreria\TesoDocRegistro;
use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoMotivo;
use App\Tesoreria\TesoMovimiento;
use App\User;
use Input;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Form;

class TrasladoEfectivosController extends TransaccionController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
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
        $nombre = true;
        return view('tesoreria.recaudos.show', compact('empresa', 'botones_anterior_siguiente', 'nombre', 'doc_encabezado', 'doc_registros', 'registros_contabilidad', 'miga_pan', 'id', 'id_transaccion', 'documento_vista'));
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
        $view = \View::make('tesoreria.traslados_efectivo.print', compact('registro', 'empresa', 'doc_encabezado', 'user', 'doc_registros'))->render();

        return $view;
    }

    public function imprimir($id)
    {
        $view = TrasladoEfectivosController::vista_preliminar($id);
        // Se prepara el PDF
        $orientacion = 'portrait';
        $tam_hoja = 'Letter';
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja, $orientacion);
        return $pdf->download('traslado_efectivo.pdf');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    // AJAX: enviar fila para el ingreso de registros al elaborar pago
    public function ajax_get_fila()
    {
        $medios_recaudo = $this->get_medios_recaudo();
        $cajas = $this->get_cajas();
        $cuentas_bancarias = $this->get_cuentas_bancarias();
        $motivos = TesoMotivo::where('teso_tipo_motivo', 'Traslado')->get()->pluck('descripcion', 'movimiento');

        $btn_borrar = "<a type='button' class='btn btn-danger btn-xs btn_eliminar'><i class='glyphicon glyphicon-trash'></i></a>";
        $btn_confirmar = "<a type='button' class='btn btn-success btn-xs btn_confirmar'><i class='glyphicon glyphicon-ok'></i></a>";

        $tr = '<tr id="linea_ingreso_default" class="linea_ingreso_default">
                    <td>
                        ' . Form::select('teso_medio_recaudo_id', $medios_recaudo, null, ['id' => 'teso_medio_recaudo_id', 'class' => 'lista_desplegable', 'onchange' => 'cambio(event)']) . '
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
                    <td>' . $btn_confirmar . $btn_borrar . '</td>
                </tr>';
        return $tr;
    }

    public static function get_medios_recaudo()
    {

        $registros = TesoMedioRecaudo::all();
        $vec_m[''] = '';
        foreach ($registros as $fila) {
            $vec_m[$fila->id . '-' . $fila->comportamiento] = $fila->descripcion;
        }

        return $vec_m;
    }

    public static function get_cajas()
    {
        $vec_m = [];
        $registros = TesoCaja::where('core_empresa_id', Auth::user()->empresa_id)->get();
        foreach ($registros as $fila) {
            $vec_m[$fila->id] = $fila->descripcion;
        }

        return $vec_m;
    }

    public static function get_cuentas_bancarias()
    {
        $vec_m = [];
        $registros = TesoCuentaBancaria::leftJoin('teso_entidades_financieras', 'teso_entidades_financieras.id', '=', 'teso_cuentas_bancarias.entidad_financiera_id')
            ->where('core_empresa_id', Auth::user()->empresa_id)
            ->select('teso_cuentas_bancarias.id', 'teso_cuentas_bancarias.descripcion AS cta_bancaria', 'teso_entidades_financieras.descripcion AS entidad_financiera')
            ->get();
        foreach ($registros as $fila) {
            $vec_m[$fila->id] = $fila->entidad_financiera . ': ' . $fila->cta_bancaria;
        }

        return $vec_m;
    }

    public function anular_traslado($id)
    {
        $documento = TesoDocEncabezado::find($id);
        $modificado_por = Auth::user()->email;

        $array_wheres = ['core_empresa_id' => $documento->core_empresa_id,
            'core_tipo_transaccion_id' => $documento->core_tipo_transaccion_id,
            'core_tipo_doc_app_id' => $documento->core_tipo_doc_app_id,
            'consecutivo' => $documento->consecutivo];

        // >>> Validaciones inciales

        // Está en un documento cruce de cartera?
//        $cantidad = CxcAbono::where($array_wheres)
//            ->where('doc_cruce_transacc_id','<>',0)
//            ->count();
//
//        if($cantidad != 0)
//        {
//            return redirect( 'tesoreria/recaudos/'.$id.'?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion') )->with('mensaje_error','Recaudo NO puede ser anulado. Está en documento cruce de cartera.');
//        }

        // >>> Eliminación

        // 1ro. Borrar registros contables
        ContabMovimiento::where($array_wheres)->delete();

//        // 2do. Si es un anticipo, se elimina el movimimeto de cartera
//        if ( $documento->teso_tipo_motivo == 'Anticipo' )
//        {
//            $cxc_movimiento = CxcMovimiento::where($array_wheres)->delete();
//        }

        // FALTA CUANDO SE TRATA DE UNA CANCELACION DE CARTERA

        // 3ro. Se elimina el movimiento de tesorería
        TesoMovimiento::where($array_wheres)->delete();

        // 5to. Se eliminan los registros del documento
        TesoDocRegistro::where('teso_encabezado_id', $documento->id)->update(['estado' => 'Anulado']);

        // 4to. Se elimina el documento de cruce
        $documento->update(['estado' => 'Anulado', 'modificado_por' => $modificado_por]);

        return redirect('tesoreria/traslado_efectivo/' . $id . '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))->with('flash_message', 'Documento de traslado de efectico fue anulado correctamente.');
    }
}
