<?php

namespace App\Http\Controllers\ContratoTransporte;

use App\Contratotransporte\Conductor;
use App\Sistema\SecuenciaCodigo;

use App\Contratotransporte\Contrato;

use App\Contratotransporte\FuecAdicional;
use App\Contratotransporte\Planillac;

use App\Contratotransporte\Plantilla;
use App\Contratotransporte\Services\FuecServices;
use App\Contratotransporte\Vehiculo;
use App\Core\Empresa;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class FuecAdicionalController extends Controller
{
    //crear contrato
    public function create()
    {
        if (Input::get('contrato_id') == null) {            
            return redirect( 'web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion') )->with('mensaje_error', 'NO fue enviado el parametro contrato_id');
        }

        $contrato = Contrato::find((int)Input::get('contrato_id'));

        $source = 'CONTRATOS';
        $sourceTemp = Input::get('source');
        if ($sourceTemp != null) {
            $source = $sourceTemp;
        }

        $miga_pan = $this->get_miga_pan($source);

        $emp = null;
        $emp = Empresa::find(1);
        
        $vehiculos_permitidos = null;
        $lista_vehiculos = null;
        if ($source == 'MISCONTRATOS') {
            $u = Auth::user();
            $lista_vehiculos = Vehiculo::where('placa', $u->email)->get();
        } else {
            $lista_vehiculos = Vehiculo::where('estado','Activo')->get();
        }
        
        $hoy = strtotime( date( "d-m-Y" ) );

        if (count($lista_vehiculos) > 0) {
            foreach ($lista_vehiculos as $un_vehiculo) {
                //verificar documentos vencidos
                $docs = $un_vehiculo->documentosvehiculos;
                $vencido = false;
                if (count($docs) > 0) {
                    foreach ($docs as $d) {
                        if ($d->vigencia_fin != '0000-00-00') {
                            if (strtotime($d->vigencia_fin) < $hoy ) {
                                $vencido = true;
                            }
                        }
                    }
                    if (!$vencido) {
                        if ($un_vehiculo->bloqueado_cuatro_contratos == 'NO') {
                            $vehiculos_permitidos[$un_vehiculo->id] = "PLACA " . $un_vehiculo->placa . ", MOVIL INTERNO " . $un_vehiculo->int . ", CAPACIDAD " . $un_vehiculo->capacidad;
                        }
                    }
                }
            }
        }
        $p = Plantilla::where('estado', 'SI')->first();

        $ciudades = \App\Core\Ciudad::opciones_campo_select_2();

        $permitir_ingreso_contrato_en_mes_distinto_al_actual = config('contratos_transporte.permitir_ingreso_contrato_en_mes_distinto_al_actual');

        return view('contratos_transporte.contratos.fuec_adicional.create')
            ->with('variables_url', '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion'))
            ->with('miga_pan', $miga_pan)
            ->with('e', $emp)
            ->with('vehiculos', $vehiculos_permitidos)
            ->with('contrato', $contrato)
            ->with('source', $source)
            ->with('ciudades', $ciudades)
            ->with('permitir_ingreso_contrato_en_mes_distinto_al_actual', $permitir_ingreso_contrato_en_mes_distinto_al_actual)
            ->with('v', $p);
    }

    public function get_miga_pan($source)
    {
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $miga_pan = null;
        
        $variables_url = '?id=' . $idapp . '&id_modelo=' . $modelo . '&id_transaccion=' . $transaccion;

        if ($source == 'MISCONTRATOS') {
            $miga_pan = [
                [
                    'url' => 'contratos_transporte' . '?id=' . $idapp,
                    'etiqueta' => 'Contratos transporte'
                ],
                [
                    'url' => 'cte_contratos_propietarios' . $variables_url,
                    'etiqueta' => 'Mis Contratos'
                ],
                [
                    'url' => 'NO',
                    'etiqueta' => 'Crear Contrato'
                ]
            ];
        } else {
            $miga_pan = [
                [
                    'url' => 'contratos_transporte' . '?id=' . $idapp,
                    'etiqueta' => 'Contratos transporte'
                ],
                [
                    'url' => 'web?id=' . $idapp . '&id_modelo=' . $modelo,
                    'etiqueta' => 'Contratos'
                ],
                [
                    'url' => 'NO',
                    'etiqueta' => 'Crear Contrato'
                ]
            ];
        }

        return $miga_pan;
    }

    public function store(Request $request)
    {
        $ruta_show = 'cte_contratos/' . $request->contrato_id . '/planillas/' . $request->source . '/index';
        
        $fuec_adicional_id = (new FuecServices())->storeFuecAdicional($request);

        if ($fuec_adicional_id > 0) {            
            $messageType = 'flash_message';
            $message = 'FUEC Adicional Almacenado con exito';
        } else {
            $messageType = 'mensaje_error';
            $message = 'FUEC Adicional No pudo ser almacenado';
        }

        return redirect( $ruta_show . $request->variables_url )->with($messageType, $message);

    }

    //calcula el numero del contrato
    function nroContrato()
    {
        $nro = SecuenciaCodigo::get_codigo('cte_contratos');

        // Se incrementa el consecutivo
        SecuenciaCodigo::incrementar_consecutivo('cte_contratos');
        
        if (strlen($nro) == 1) {
            return "000" . $nro;
        }
        if (strlen($nro) == 2) {
            return "00" . $nro;
        }
        if (strlen($nro) == 3) {
            return "0" . $nro;
        }
        if (strlen($nro) == 4) {
            return  $nro;
        }
        if (strlen($nro) > 4) {
            return substr($nro, -4);
        }
    }

    //imprime un Fuec Adicional a partir del id
    public function imprimir($id)
    {
        $fuec_adicional = FuecAdicional::find($id);
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        if ($fuec_adicional->estado == 'ANULADO') {
            return redirect("web?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion)->with('mensaje_error', 'El contrato se encuentra ANULADO, no puede proceder.');
        }

        $vehiculo = $fuec_adicional->vehiculo;
        $p = Planillac::where('contrato_id', $fuec_adicional->contrato_id)->first();

        $p->nro = $this->get_numero_planilla($p, $fuec_adicional);

        $url = route('cte_contratos_fuec_adicional.planillaverificar', $fuec_adicional->id);

        //$empresa = null;
        $empresa = Empresa::find(1);
        $contratante = null;
        if ($fuec_adicional->contrato->contratante_id != null) {
            $contratante = $fuec_adicional->contrato->contratante;
        }
        $v = $p->plantilla;
        $fi = explode('-', $fuec_adicional->fecha_inicio);
        $ff = explode('-', $fuec_adicional->fecha_fin);
        $to = null;
        $docs = $fuec_adicional->vehiculo->documentosvehiculos;
        if (count($docs) > 0) {
            foreach ($docs as $d) {
                if ($d->tarjeta_operacion == 'SI') {
                    $to = $d;
                }
            }
        }

        $conductores = [];
        
        $cond = $fuec_adicional->conductor1;
        $cond->licencia = $this->get_numero_licencia_coductor($cond);
        $conductores[] = $cond;
        
        $cond = $fuec_adicional->conductor2;
        if ( $cond != null ) {
            $cond->licencia = $this->get_numero_licencia_coductor($cond);
            $conductores[] = $cond;
        }
        
        $cond = $fuec_adicional->conductor3;
        if ( $cond != null ) {
            $cond->licencia = $this->get_numero_licencia_coductor($cond);
            $conductores[] = $cond;
        }

        $representante_legal_contratante = '';
        if ( !is_null($contratante) )
        {
            $representante_legal_contratante = $contratante->tercero->representante_legal();
            if ( is_null($representante_legal_contratante) )
            {
                $representante_legal_contratante = $contratante->tercero;
            }
        }
        
        $documento_vista =  View::make('contratos_transporte.contratos.fuec_adicional.print2', compact('fuec_adicional', 'conductores', 'to', 'p', 'v', 'fi', 'ff', 'contratante', 'url', 'contratante', 'vehiculo', 'empresa', 'representante_legal_contratante'))->render();
        
        // Se prepara el PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($documento_vista); //->setPaper( $tam_hoja, $orientacion );

        //echo $documento_vista;
        return $pdf->stream('fuec.pdf');

    }

    public function get_numero_licencia_coductor( Conductor $conductor)
    {
        $licencia = null;
        $docs = null;
        $docs = $conductor->documentosconductors;
        if (count($docs) > 0) {
            foreach ($docs as $do) {
                if ($do->licencia == 'SI') {
                    $licencia = $do;
                }
            }
        }

        return $licencia;
    }

    public function get_numero_planilla($planilla_contrato, $fuec_adicional)
    {
        return substr($planilla_contrato->nro,0,17) . $fuec_adicional->numero_fuec;
    }

    public static function mes()
    {
        return [
            '01' => 'ENERO',
            '02' => 'FEBRERO',
            '03' => 'MARZO',
            '04' => 'ABRIL',
            '05' => 'MAYO',
            '06' => 'JUNIO',
            '07' => 'JULIO',
            '08' => 'AGOSTO',
            '09' => 'SEPTIEMBRE',
            '10' => 'OCTUBRE',
            '11' => 'NOVIEMBRE',
            '12' => 'DICIEMBRE'
        ];
    }

    //permite anular un Fuec Adicional por su id
    public function anular($id)
    {
        $contrato = FuecAdicional::find($id);
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        if ($contrato->estado == 'ANULADO') {
            return redirect("web?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion)->with('mensaje_error', 'El contrato se encuentra ANULADO, no puede proceder.');
        }
        $contrato->estado = "ANULADO";
        if ($contrato->save()) {
            return redirect("web?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion)->with('flash_message', 'Contrato ANULADO con éxito.');
        } else {
            return redirect("web?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion)->with('mensaje_error', 'El contrato no pudo ser ANULADO.');
        }
    }

    //verificar planilla pública (Con el QR)
    public function verificarPlanilla($id)
    {
        //return $this->planillaimprimir($id);
        return redirect('cte_contratos_fuec_adicional_imprimir/' . $id);
    }
}