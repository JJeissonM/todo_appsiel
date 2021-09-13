<?php

namespace App\Http\Controllers\Sistema;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Input;
use DB;
use PDF;
use Auth;
use Storage;
use View;
use Yajra\Datatables\Facades\Datatables;


use App\User;

use App\Sistema\Html\Boton;
use App\Sistema\TipoTransaccion;
use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Sistema\Campo;

use App\Core\TipoDocApp;
use App\Core\Empresa;
use App\Core\Tercero;
use App\Core\ModeloEavValor;
use App\Matriculas\Matricula;
use App\Calificaciones\Asignatura;
use App\Core\ConsecutivoDocumento;

use App\Inventarios\InvMovimiento;
use App\Inventarios\InvBodega;

use App\Tesoreria\TesoMedioRecaudo;
use App\Tesoreria\TesoCaja;
use App\Tesoreria\TesoCuentaBancaria;

use App\Contabilidad\ContabCuenta;
use App\PropiedadHorizontal\Propiedad;

class CrudAjaxController extends ModeloController
{
    protected $empresa, $app, $modelo, $transaccion, $variables_url;

    public function __construct()
    {
        $this->middleware('auth');
    }


    public function update(Request $request, $id)
    {
        $datos = $request->all(); // Datos originales

        // Se crea un nuevo registro para el ID del modelo enviado en el request 
        $registro = $this->crear_nuevo_registro( $request, $request->url_id_modelo );

        $this->almacenar_imagenes( $request, $modelo->ruta_storage_imagen, $registro );

        app($this->modelo->name_space)->store_adicional($datos, $registro);

        return $registro;
    }

    // FORMULARIO de un Modelo con sus Campos
    // $accion = { create | edit }
    public function formulario_ajax_modelo( $modelo_id, $registro_id, $accion)
    {   
        $modelo = Modelo::find( $modelo_id );
        // Se obtiene el registro a modificar del modelo
        $registro = app($modelo->name_space)->find($registro_id);

        $lista_campos = $this->get_campos_modelo($modelo,$registro,$accion);

        // Asignar a cada $campo en la key value el valor que tienen en la tabla core_eav_valores, pues el Form::model(), no lo puede asignar automáticamente a través del name.
        $cant = count($lista_campos);
        for ($i=0; $i < $cant; $i++) 
        {
            if ( $lista_campos[$i]['tipo'] != 'bsCheckBox' )
            {
                $lista_campos[$i] = VistaController::mostrar_campo( $lista_campos[$i]['id'], 
                                                            app($modelo->name_space)->where( [ "modelo_padre_id" => Input::get('modelo_padre_id'), "registro_modelo_padre_id" => Input::get('registro_modelo_padre_id'), "modelo_entidad_id" => $id, "core_campo_id" => $lista_campos[$i]['id'] ] )->value('valor'),
                                                                'edit' );
            }
                
        }

        $acciones = $this->acciones_basicas_modelo( $modelo, '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . Input::get('id_transaccion') );

        $url_action = str_replace('id_fila', $id, $acciones->update);
        
        $form_create = [
                        'url' => $url_action,
                        'campos' => $lista_campos
                    ];
        
        $url_action = 'web_ajax/'.$registro_id;

        return view( 'layouts.formulario_ajax_modelo', compact( 'form_create', 'registro', 'accion' ) );        
    }

}