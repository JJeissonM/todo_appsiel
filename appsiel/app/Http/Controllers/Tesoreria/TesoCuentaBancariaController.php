<?php

namespace App\Http\Controllers\Tesoreria;

use App\Http\Controllers\Sistema\ModeloController;
use App\Sistema\Aplicacion;
use App\Sistema\Html\Boton;
use App\Sistema\Html\MigaPan;
use App\Sistema\Modelo;
use App\Sistema\TipoTransaccion;
use App\Tesoreria\TesoChequera;
use App\Tesoreria\TesoCuentaBancaria;
use Illuminate\Support\Facades\Input;

class TesoCuentaBancariaController extends ModeloController
{
    public function show($id)
    {
        $modelo = Modelo::find(Input::get('id_modelo'));
        $aplicacion = Aplicacion::find(Input::get('id'));

        $registro = TesoCuentaBancaria::find($id);
        if (is_null($registro)) {
            return redirect('web?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo'))
                ->with('mensaje_error', 'La cuenta bancaria no existe.');
        }

        $reg_anterior = TesoCuentaBancaria::where('id', '<', $registro->id)->max('id');
        $reg_siguiente = TesoCuentaBancaria::where('id', '>', $registro->id)->min('id');

        $lista_campos1 = $modelo->campos()->orderBy('orden')->get();
        $lista_campos = $this->asignar_valores_de_campo_al_registro($modelo, $registro, $lista_campos1->toArray());

        $id_transaccion = TipoTransaccion::where('core_modelo_id', (int)Input::get('id_modelo'))->value('id');
        $variables_url = '?id=' . Input::get('id') . '&id_modelo=' . Input::get('id_modelo') . '&id_transaccion=' . $id_transaccion;
        $acciones = $this->acciones_basicas_modelo($modelo, $variables_url);

        $url_crear = $acciones->create;
        $url_edit = $acciones->edit;

        $botones = [];
        $enlaces = json_decode($acciones->otros_enlaces);
        if (!is_null($enlaces)) {
            foreach ($enlaces as $fila) {
                $botones[] = new Boton($fila);
            }
        }

        $form_create = [
            'url' => $acciones->store,
            'campos' => $lista_campos
        ];

        $miga_pan = MigaPan::get_array($aplicacion, $modelo, $registro->descripcion);

        $chequeras = TesoChequera::where('teso_cuenta_bancaria_id', $registro->id)
            ->orderBy('id', 'DESC')
            ->get();

        return view(
            'tesoreria.cuentas_bancarias.show',
            compact(
                'form_create',
                'miga_pan',
                'registro',
                'url_crear',
                'url_edit',
                'reg_anterior',
                'reg_siguiente',
                'botones',
                'chequeras'
            )
        );
    }
}
