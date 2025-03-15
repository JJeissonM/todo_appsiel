<?php

namespace App\Contratotransporte\Services;

use App\Contratotransporte\Contrato;
use App\Contratotransporte\FuecAdicional;
use App\Contratotransporte\Vehiculo;
use App\Core\Empresa;
use App\Sistema\SecuenciaCodigo;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;

class FuecServices
{
	public $company;

	//calcula el numero del FUEC
    function nroFUEC()
    {
        $nro = SecuenciaCodigo::get_codigo('cte_fuec');
        // Se incrementa el consecutivo
        SecuenciaCodigo::incrementar_consecutivo('cte_fuec');
        
        if (strlen($nro) == 0) {
            return "0001";
        }
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

    //almacena un contrato con su grupo de usuarios
    public function storeFuecAdicional(Request $request)
    {
        $datos = $request->all();        

        $datos['conductor2_id'] = $request->conductor2_id;
        if ($request->conductor2_id == '') {
            $datos['conductor2_id'] = null;
        }

        $datos['conductor3_id'] = $request->conductor3_id;
        if ($request->conductor3_id == '') {
            $datos['conductor3_id'] = null;
        }

        $result = 0;
        $c = new FuecAdicional( $datos );
        $c->valor_empresa = 0;
        $c->valor_fuec = 0;
        $c->valor_propietario = 0;
        $c->direccion_notificacion = "--";
        $c->telefono_notificacion = "--";
        $c->estado = "ACTIVO";
        $c->codigo = null;
        $c->version = null;
        $c->fecha = null;
        $c->numero_fuec = (new FuecServices())->nroFUEC();
        $c->pie_dos = "--";
        $c->pie_tres = "--";
        $c->pie_cuatro = "--";
        $c->origen = strtoupper($c->origen);
        $c->destino = strtoupper($c->destino);

        if ($c->save()) {

             // Verifico si el vehiculo ya hizo 4 contratos este mes, si los hizo se bloquea... debe pagar para hacerlo la proxima
            $contratosMes = Contrato::where('vehiculo_id', $request->vehiculo_id)->get();
            if (count($contratosMes) > 0) {
                $total = 0;
                $hoy = getdate();
                $mes_actual = $hoy['mon'];
                foreach ($contratosMes as $cm) {
                    $mes_fecha = explode('-', $cm->fecha_inicio)[1];
                    if ($mes_actual == $mes_fecha) {
                        $total = $total + 1;
                    }
                }
                $limite = config('contratos_transporte.bloqueado_x_contratos');
                if ($total >= $limite) {
                    $lista_vehiculos = Vehiculo::find($request->vehiculo_id);
                    $lista_vehiculos->bloqueado_cuatro_contratos = 'SI';
                    $lista_vehiculos->save();
                }
            }
            
            $result = $c->id;
        }

        return $result;
    }
}