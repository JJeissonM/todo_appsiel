<?php

namespace App\Http\Controllers\Tesoreria;

use App\Core\Empresa;
use App\Core\Tercero;
use App\Sistema\Html\Boton;
use App\Sistema\TipoTransaccion;
use App\Tesoreria\ArqueoCaja;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use Illuminate\Support\Facades\View;
use Input;

use App\Http\Controllers\Sistema\ModeloController;

class ArqueoCajaController extends ModeloController
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

    public function create()
    {
        return redirect('web/create?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&vista=tesoreria.arqueo_caja.create');
    }

    // Generar vista para SHOW  o IMPRIMIR
    public function vista_preliminar($id)
    {
        $registro = ArqueoCaja::find($id);
        $empresa = Empresa::find($registro->core_empresa_id);
        $doc_encabezado = ['documento' => 'ACTA DE ARQUEO DE CAJA', 'fecha' => $registro->fecha, 'titulo' => 'ACTA DE ARQUEO DE CAJA'];
        $user = User::where('email', $registro->creado_por)->first();
        $registro->billetes_contados = json_decode($registro->billetes_contados);
        $registro->monedas_contadas = json_decode($registro->monedas_contadas);
        $registro->detalles_mov_entradas = json_decode($registro->detalles_mov_entradas);
        $registro->detalles_mov_salidas = json_decode($registro->detalles_mov_salidas);



        if ( is_null( $registro->billetes_contados ) )
        {
            $registro->billetes_contados = [];
        }

        if ( is_null( $registro->monedas_contadas ) )
        {
            $registro->monedas_contadas = [];
        }

        if ( $registro->detalles_mov_entradas == 0 )
        {
            $registro->detalles_mov_entradas = [];
        }

        if ( $registro->detalles_mov_salidas == 0 )
        {
            $registro->detalles_mov_salidas = [];
        }

        
        // Crear vista
        $view = \View::make('tesoreria.arqueo_caja.print', compact('registro', 'empresa', 'doc_encabezado', 'user'))->render();

        return $view;
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $registro = ArqueoCaja::find($id);
        $empresa = Empresa::find($registro->core_empresa_id);
        $doc_encabezado =['documento'=>'TRASLADO DE EFECTIVO','fecha'=>$registro->fecha,'titulo'=>'TRASLADO DE EFECTIVO'];
        $user = User::where('email', $registro->creado_por)->first();
        $reg_anterior = app($this->modelo->name_space)->where('id', '<', $registro->id)->max('id');
        $reg_siguiente = app($this->modelo->name_space)->where('id', '>', $registro->id)->min('id');
        $miga_pan = $this->get_miga_pan($this->modelo, 'Ver');
        $url_crear = '';
        $url_edit = '';

        $id_transaccion = TipoTransaccion::where('core_modelo_id', (int)Input::get('id_modelo'))->value('id');

        // Se le asigna a cada variable url, su valor en el modelo correspondiente
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
        if ($this->modelo->url_crear != '') {
            $url_crear = $this->modelo->url_crear . $variables_url;
        }
        if ($this->modelo->url_edit != '') {
            $url_edit = $this->modelo->url_edit . $variables_url;
        }
        // ENLACES
        $botones = [];
        if ($this->modelo->enlaces != '') {
            $enlaces = json_decode($this->modelo->enlaces);
            $i = 0;
            foreach ($enlaces as $fila) {
                $botones[$i] = new Boton($fila);
            }
        }
        $registro->billetes_contados = json_decode($registro->billetes_contados);
        $registro->monedas_contadas = json_decode($registro->monedas_contadas);
        $registro->detalles_mov_entradas = json_decode($registro->detalles_mov_entradas);
        $registro->detalles_mov_salidas = json_decode($registro->detalles_mov_salidas);

        if ( is_null( $registro->billetes_contados ) )
        {
            $registro->billetes_contados = [];
        }

        if ( is_null( $registro->monedas_contadas ) )
        {
            $registro->monedas_contadas = [];
        }

        if ( $registro->detalles_mov_entradas == 0 )
        {
            $registro->detalles_mov_entradas = [];
        }

        if ( $registro->detalles_mov_salidas == 0 )
        {
            $registro->detalles_mov_salidas = [];
        }

        //return view( 'matriculas.show_matricula',compact('reg_anterior','reg_siguiente','miga_pan','view_pdf','id') );
        return view('tesoreria.arqueo_caja.show', compact('miga_pan', 'registro', 'url_crear', 'url_edit', 'reg_anterior', 'reg_siguiente', 'botones', 'empresa', 'doc_encabezado', 'user'));

        //  return view('tesoreria.arqueo_caja.show',compact('form_create','miga_pan','registro','archivo_js','url_action'));
    }

    public function imprimir($id)
    {
        $view = ArqueoCajaController::vista_preliminar($id);
        //dd($view);
        $orientacion = 'portrait';
        $tam_hoja = 'Letter';

        // Crear PDF
        $pdf = \App::make('dompdf.wrapper');
        $pdf->loadHTML(($view))->setPaper($tam_hoja, $orientacion);
        return $pdf->stream('arqueocaja.pdf');//stream();


        /*echo $view;*/
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $registro = ArqueoCaja::find($id);
        $miga_pan = $this->get_miga_pan($this->modelo, 'Editar');
        $lista_campos = $this->get_campos_modelo($this->modelo, $registro, 'edit');
        $form_create = [
            'url' => $this->modelo->url_form_create,
            'campos' => $lista_campos
        ];
        $archivo_js = app($this->modelo->name_space)->archivo_js;
        $url_action = 'web/' . $id;
        $registro->billetes_contados = json_decode($registro->billetes_contados);
        $registro->monedas_contadas = json_decode($registro->monedas_contadas);
        //dd($arqueocaja);
        return view('tesoreria.arqueo_caja.edit', compact('form_create', 'miga_pan', 'registro', 'archivo_js', 'url_action'));
    }

}