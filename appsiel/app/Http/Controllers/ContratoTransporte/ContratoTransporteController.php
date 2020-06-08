<?php

namespace App\Http\Controllers\ContratoTransporte;

use App\Contratotransporte\Conductor;
use App\Contratotransporte\Contratante;
use App\Contratotransporte\Contrato;
use App\Contratotransporte\Contratogrupou;
use App\Contratotransporte\Documentosconductor;
use App\Contratotransporte\Planillac;
use App\Contratotransporte\Planillaconductor;
use App\Contratotransporte\Plantilla;
use App\Contratotransporte\Propietario;
use App\Contratotransporte\Vehiculo;
use App\Core\Empresa;
use App\Core\Tercero;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\View\View as ViewView;
use View;

class ContratoTransporteController extends Controller
{
    public function index()
    {
        $miga_pan = [
            ['url' => 'NO', 'etiqueta' => 'Contratos Transporte']
        ];
        $hoy = getdate();
        $mes_actual = $hoy['mon'];
        $cont = Contrato::all();
        $contratos = null;
        if (count($cont) > 0) {
            foreach ($cont as $c) {
                if ((int) explode('-', $c->fecha_inicio)[1] == $mes_actual) {
                    $contratos[] = $c;
                }
            }
        }
        if ($mes_actual < 10) {
            $mes_actual = "0" . $mes_actual;
        }
        $mes_actual = $this->mes()[$mes_actual];
        //valido documentos vencidos
        $docs = Documentosconductor::all();
        $documentos = null;
        if (count($docs) > 0) {
            foreach ($docs as $d) {
                if (strtotime(date("d-m-Y H:i:00", time())) > strtotime($d->vigencia_fin)) {
                    $documentos[] = $d;
                }
            }
        }
        return view('contratos_transporte.index', compact('miga_pan', 'contratos', 'mes_actual', 'documentos'));
    }

    //crear contrato
    public function create()
    {
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $miga_pan = [
            [
                'url' => 'contratos_transporte' . '?id=' . $idapp,
                'etiqueta' => 'Contratos transporte'
            ],
            [
                'url' => 'web?id=' . $idapp . "&id_modelo=" . $modelo,
                'etiqueta' => 'Contratos'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Crear Contrato'
            ]
        ];
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        $emp = null;
        $emp = Empresa::find(1);
        $contratantes = null;
        $cont = Contratante::all();
        if (count($cont) > 0) {
            foreach ($cont as $c) {
                $contratantes[$c->id] = "<b>" . $c->tercero->descripcion . "</b> identificado con cedula <b>N° " . $c->tercero->numero_identificacion;
            }
        }
        $vehiculos = null;
        $vehi = Vehiculo::all();
        if (count($vehi) > 0) {
            foreach ($vehi as $v) {
                $vehiculos[$v->id] = "<b>PLACA " . $v->placa . ", MOVIL INTERNO " . $v->int . ", CAPACIDAD " . $v->capacidad;
            }
        }
        return view('contratos_transporte.contratos.create')
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan)
            ->with('e', $emp)
            ->with('contratantes', $contratantes)
            ->with('vehiculos', $vehiculos);
    }

    public function store(Request $request)
    {
        $hoy = getdate();
        $mes_actual = $hoy['mon'];
        $mes_fecha_fin = explode('-', $request->fecha_fin)[1];
        if ((int) $mes_fecha_fin > (int) $mes_actual) {
            return redirect("web/" . $request->variables_url)->with('mensaje_error', 'El contrato no puede tener fecha de terminación del siguiente mes');
        }
        $result = $this->storeContract($request);
        if ($result) {
            return redirect("web" . $request->variables_url)->with('flash_message', 'Almacenado con exito');
        } else {
            return redirect("web/" . $request->variables_url)->with('mensaje_error', 'No pudo ser almacenado');
        }
    }

    //almacena un contrato con su grupo de usuarios
    public function storeContract(Request $request)
    {
        $result = false;
        $c = new Contrato($request->all());
        $c->codigo = strtoupper($c->codigo);
        $c->rep_legal = strtoupper($c->rep_legal);
        $c->representacion_de = strtoupper($c->representacion_de);
        $c->origen = strtoupper($c->origen);
        $c->destino = strtoupper($c->destino);
        $c->direccion_notificacion = strtoupper($c->direccion_notificacion);
        if ($c->save()) {
            $result = true;
            if (isset($request->identificacion)) {
                if (count($request->identificacion) > 0) {
                    foreach ($request->identificacion as $key => $value) {
                        $gu = null;
                        $gu = new Contratogrupou();
                        $gu->identificacion = $value;
                        $gu->persona = strtoupper($request->persona[$key]);
                        $gu->contrato_id = $c->id;
                        $gu->save();
                    }
                }
            }
        }
        return $result;
    }

    //show
    public function show($id)
    {
        $c = Contrato::find($id);
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $miga_pan = [
            [
                'url' => 'contratos_transporte' . '?id=' . $idapp,
                'etiqueta' => 'Contratos transporte'
            ],
            [
                'url' => 'web?id=' . $idapp . "&id_modelo=" . $modelo,
                'etiqueta' => 'Contratos'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Ver Contrato'
            ]
        ];
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        $contratante = $c->contratante;
        $vehiculo = $c->vehiculo;
        $emp = null;
        $emp = Empresa::find(1);
        return view('contratos_transporte.contratos.show')
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan)
            ->with('c', $c)
            ->with('contratante', $contratante)
            ->with('vehiculo', $vehiculo)
            ->with('e', $emp);
    }

    //elimina usuario del grupo de usuarios del contrato
    public function deletegrupousuario($id)
    {
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        $g = Contratogrupou::find($id);
        if ($g->delete()) {
            return redirect("cte_contratos/" . $g->contrato_id . "/show" . $variables_url)->with('flash_message', 'Eliminado con exito');
        } else {
            return redirect("cte_contratos/" . $g->contrato_id . "/show" . $variables_url)->with('mensaje_error', 'No pudo ser eliminado');
        }
    }

    //agrega nuevos usuarios al grupo de usuario
    public function storegrupousuario(Request $request)
    {
        if (isset($request->identificacion)) {
            if (count($request->identificacion) > 0) {
                foreach ($request->identificacion as $key => $value) {
                    $gu = null;
                    $gu = new Contratogrupou();
                    $gu->identificacion = $value;
                    $gu->persona = strtoupper($request->persona[$key]);
                    $gu->contrato_id = $request->id;
                    $gu->save();
                }
            }
        }
        return redirect("cte_contratos/" . $request->id . "/show" . $request->variables_url)->with('flash_message', 'Usuarios procesados');
    }


    //imprime un contrato a partir del id
    public function imprimir($id)
    {
        $c = Contrato::find($id);
        $contratante = $c->contratante;
        $vehiculo = $c->vehiculo;
        $emp = null;
        $emp = Empresa::find(1);
        $documento_vista =  View::make('contratos_transporte.contratos.print', compact('c', 'contratante', 'vehiculo', 'emp'))->render();

        // Se prepara el PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($documento_vista); //->setPaper( $tam_hoja, $orientacion );

        //echo $documento_vista;
        return $pdf->stream('contrato.pdf');
    }


    //contratos de un propietario o un conductor
    public function miscontratos()
    {
        $u = Auth::user();
        $contratos = null;
        $terceros = Tercero::where('user_id', $u->id)->get();
        if (count($terceros) > 0) {
            foreach ($terceros as $t) {
                $p = null;
                $c = null;
                //reviso propietario
                $p = $t->propietario;
                if ($p != null) {
                    $vehis = null;
                    $vehis = $p->vehiculos;
                    if ($vehis != null) {
                        foreach ($vehis as $v) {
                            $conts = null;
                            $conts = $v->contratos;
                            if ($conts != null) {
                                foreach ($conts as $c) {
                                    $contratos[] = [
                                        'identificacion' => $t->numero_identificacion,
                                        'persona' => $t->descripcion,
                                        'tercero' => $t,
                                        'propietario' => $p,
                                        'conductor' => null,
                                        'genera' => $p->genera_planilla,
                                        'contrato' => $c,
                                        'vehiculo' => $v,
                                        'tipo' => 'PROPIETARIO'
                                    ];
                                }
                            }
                        }
                    }
                }
                //reviso conductor
                $c = $t->conductor;
                if ($c != null) {
                    $planillacond = null;
                    $planillacond = $c->planillaconductors;
                    if ($planillacond != null) {
                        foreach ($planillacond as $pc) {
                            $planilac = null;
                            $planilac = $pc->planillac;
                            if ($planilac != null) {
                                $cont = null;
                                $cont = $planilac->contrato;
                                $genera = "NO";
                                if ($c->estado == 'Activo') {
                                    $genera = "SI";
                                }
                                //si tiene licencia vencida o no tiene no puede generar
                                $docs = $c->documentosconductors;
                                if (count($docs) > 0) {
                                    foreach ($docs as $d) {
                                        if ($d->licencia == 'SI') {
                                            //tiene licencia, se revisa si esta vencida
                                            if (strtotime(date("d-m-Y H:i:00", time())) > strtotime($d->vigencia_fin)) {
                                                $genera = 'NO';
                                            } else {
                                                $genera = 'SI';
                                            }
                                        }
                                    }
                                } else {
                                    $genera = 'NO';
                                }
                                $contratos[] = [
                                    'identificacion' => $t->numero_identificacion,
                                    'persona' => $t->descripcion,
                                    'tercero' => $t,
                                    'propietario' => null,
                                    'conductor' => $c,
                                    'genera' => $genera,
                                    'contrato' => $cont,
                                    'vehiculo' => $cont->vehiculo,
                                    'tipo' => 'CONDUCTOR'
                                ];
                            }
                        }
                    }
                }
            }
        }
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $miga_pan = [
            [
                'url' => 'contratos_transporte' . '?id=' . $idapp,
                'etiqueta' => 'Contratos transporte'
            ],
            [
                'url' => 'NO',
                'etiqueta' => 'Mis Contratos'
            ]
        ];
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        return view('contratos_transporte.contratos.miscontratos')
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan)
            ->with('contratos', $contratos);
    }


    //index de planillas de contratos
    public function planillaindex($id, $source)
    {
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $miga_pan = null;
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
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
                    'etiqueta' => 'Planillas FUEC'
                ]
            ];
        } else {
            $miga_pan = [
                [
                    'url' => 'contratos_transporte' . '?id=' . $idapp,
                    'etiqueta' => 'Contratos transporte'
                ],
                [
                    'url' => 'web?id=' . $idapp . "&id_modelo=" . $modelo,
                    'etiqueta' => 'Contratos'
                ],
                [
                    'url' => 'cte_contratos/' . $id . '/show' . $variables_url,
                    'etiqueta' => 'Ver Contrato'
                ],
                [
                    'url' => 'NO',
                    'etiqueta' => 'Planillas FUEC'
                ]
            ];
        }
        $c = Contrato::find($id);
        $planillas = $c->planillacs;
        return view('contratos_transporte.contratos.planillas')
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan)
            ->with('c', $c)
            ->with('source', $source)
            ->with('planillas', $planillas);
    }


    //crear planilla
    public function planillacreate($id, $source)
    {
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $miga_pan = null;
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
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
                    'url' => 'cte_contratos/' . $id . '/planillas/' . $source . '/index' . $variables_url,
                    'etiqueta' => 'Planillas FUEC'
                ],
                [
                    'url' => 'NO',
                    'etiqueta' => 'Generar Planilla'
                ]
            ];
        } else {
            $miga_pan = [
                [
                    'url' => 'contratos_transporte' . '?id=' . $idapp,
                    'etiqueta' => 'Contratos transporte'
                ],
                [
                    'url' => 'web?id=' . $idapp . "&id_modelo=" . $modelo,
                    'etiqueta' => 'Contratos'
                ],
                [
                    'url' => 'cte_contratos/' . $id . '/show' . $variables_url,
                    'etiqueta' => 'Ver Contrato'
                ],
                [
                    'url' => 'cte_contratos/' . $id . '/planillas/' . $source . '/index' . $variables_url,
                    'etiqueta' => 'Planillas FUEC'
                ],
                [
                    'url' => 'NO',
                    'etiqueta' => 'Generar Planilla'
                ]
            ];
        }
        $co = Contrato::find($id);
        $p = Plantilla::where('estado', 'SI')->first();
        if ($p == null) {
            return redirect('cte_contratos/' . $id . '/planillas/' . $source . '/index' . $variables_url)->with('mensaje_error', 'No hay plantilla para generar planilla, contacte al administrador del sistema.');
        }
        $cond = Conductor::all();
        $conductores = null;
        if (count($cond) > 0) {
            foreach ($cond as $c) {
                $licencia = "[XX] SIN LICENCIA";
                $docs = $c->documentosconductors;
                if (count($docs) > 0) {
                    foreach ($docs as $d) {
                        if ($d->licencia == 'SI') {
                            //tiene licencia, se revisa si esta vencida
                            if (strtotime(date("d-m-Y H:i:00", time())) > strtotime($d->vigencia_fin)) {
                                $licencia = "[XX] LICENCIA VENCIDA";
                            } else {
                                $licencia = '[OK] LICENCIA VIGENTE';
                            }
                        }
                    }
                }
                $conductores[$c->id] =  $licencia . " - " . $c->tercero->descripcion;
            }
        }
        $emp = Empresa::find(1);
        $docs = $co->vehiculo->documentosvehiculos;
        $to = null;
        if (count($docs) > 0) {
            foreach ($docs as $d) {
                if ($d->tarjeta_operacion == 'SI') {
                    $to = $d;
                }
            }
        }
        $hoy = getdate();
        $consecutivo = count(Planillac::all());
        $nro_planilla = config('contrato_transporte.numero_territorial') . config('contrato_transporte.resolucion_habilitacion') . config('contrato_transporte.anio_creacion_empresa');
        $nro_planilla = $nro_planilla . $hoy['year'] . $co->numero_contrato . ($consecutivo + 1);
        $fi = explode('-', $co->fecha_inicio);
        $ff = explode('-', $co->fecha_fin);
        return view('contratos_transporte.contratos.generaplanilla')
            ->with('variables_url', $variables_url)
            ->with('miga_pan', $miga_pan)
            ->with('c', $co)
            ->with('source', $source)
            ->with('v', $p)
            ->with('conductores', $conductores)
            ->with('e', $emp)
            ->with('to', $to)
            ->with('nro', $nro_planilla)
            ->with('fi', $fi)
            ->with('ff', $ff);
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


    //guarda una planilla
    public function planillastore(Request $request)
    {
        $p = new Planillac();
        $e = Empresa::find(1);
        $p->razon_social = $e->descripcion;
        $p->nit = $e->numero_identificacion . "-" . $e->digito_verificacion;
        $p->convenio = " -- ";
        $p->contrato_id = $request->id;
        $p->plantilla_id = $request->plantilla_id;
        $p->nro = $request->nro;
        $result = false;
        if ($p->save()) {
            $result = true;
            if (isset($request->conductor_id)) {
                foreach ($request->conductor_id as $c) {
                    if ($c != '') {
                        $pc = new Planillaconductor();
                        $pc->conductor_id = $c;
                        $pc->planillac_id = $p->id;
                        $pc->save();
                    }
                }
            }
        }
        $idapp = Input::get('id');
        $modelo = Input::get('id_modelo');
        $transaccion = Input::get('id_transaccion');
        $variables_url = "?id=" . $idapp . "&id_modelo=" . $modelo . "&id_transaccion=" . $transaccion;
        if ($result) {
            return redirect('cte_contratos/' . $request->id . '/planillas/' . $request->source . '/index' . $variables_url)->with('flash_message', 'Planilla generada con éxito.');
        } else {
            return redirect('cte_contratos/' . $request->id . '/planillas/' . $request->source . '/index' . $variables_url)->with('mensaje_error', 'La planilla no pudo ser generada.');
        }
    }


    //imprime una planilla FUEC a partir del id
    public function planillaimprimir($id)
    {
        $p = Planillac::find($id);
        $c = $p->contrato;
        //$contratante = $c->contratante;
        $conductores = $p->planillaconductors;
        $v = $p->plantilla;
        $fi = explode('-', $c->fecha_inicio);
        $ff = explode('-', $c->fecha_fin);
        $docs = $c->vehiculo->documentosvehiculos;
        $to = null;
        if (count($docs) > 0) {
            foreach ($docs as $d) {
                if ($d->tarjeta_operacion == 'SI') {
                    $to = $d;
                }
            }
        }
        if (count($conductores) > 0) {
            foreach ($conductores as $cond) {
                $cond->licencia = null;
                $docs = null;
                $docs = $cond->conductor->documentosconductors;
                if (count($docs) > 0) {
                    foreach ($docs as $do) {
                        if ($do->licencia == 'SI') {
                            $cond->licencia = $do;
                        }
                    }
                }
            }
        }
        $empresa = null;
        $empresa = Empresa::find(1);
        $documento_vista =  View::make('contratos_transporte.contratos.print2', compact('p', 'conductores', 'v', 'c', 'fi', 'ff', 'to', 'empresa'))->render();

        // Se prepara el PDF
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($documento_vista); //->setPaper( $tam_hoja, $orientacion );

        //echo $documento_vista;
        return $pdf->stream('fuec.pdf');
    }
}
