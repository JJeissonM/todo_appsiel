<?php

namespace App\Core;

use App\Contratotransporte\Conductor;
use App\Contratotransporte\Contratante;
use App\Contratotransporte\Propietario;
use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;
use Storage;

use App\Matriculas\Estudiante;
use App\Sistema\Modelo;
use App\Core\Departamento;
use App\Ventas\Cliente;

class TerceroCreadoVehiculo extends Tercero
{
    protected $table = 'core_terceros';

    protected $fillable = ['core_empresa_id', 'imagen', 'tipo', 'razon_social', 'nombre1', 'otros_nombres', 'apellido1', 'apellido2', 'descripcion', 'id_tipo_documento_id', 'numero_identificacion', 'digito_verificacion', 'ciudad_expedicion', 'direccion1', 'direccion2', 'barrio', 'codigo_ciudad', 'codigo_postal', 'telefono1', 'telefono2', 'email', 'pagina_web', 'estado', 'user_id', 'contab_anticipo_cta_id', 'contab_cartera_cta_id', 'contab_cxp_cta_id', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombre/Razón Social', 'Identificación', 'Establecimiento', 'Dirección', 'Teléfono'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = Tercero::where('core_terceros.core_empresa_id', Auth::user()->empresa_id)
                    ->where('core_terceros.creado_por',Auth::user()->email)
                    ->select(
                        DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS campo1'),
                        'core_terceros.numero_identificacion AS campo2',
                        'core_terceros.descripcion AS campo3',
                        'core_terceros.direccion1 AS campo4',
                        'core_terceros.telefono1 AS campo5',
                        'core_terceros.id AS campo6'
                    )
                    ->orderBy('core_terceros.created_at', 'DESC')
                    ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = Tercero::where('core_terceros.core_empresa_id', Auth::user()->empresa_id)
                    ->where('core_terceros.creado_por',Auth::user()->email)
            ->select(
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres," ",core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.razon_social) AS TERCERO'),
                'core_terceros.numero_identificacion AS IDENTIFICACIÓN',
                'core_terceros.descripcion AS DESCRIPCIÓN',
                'core_terceros.direccion1 AS DIRECCIÓN',
                'core_terceros.telefono1 AS TELÉFONO'
            )
            ->orderBy('core_terceros.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE TERCEROS";
    }
}
