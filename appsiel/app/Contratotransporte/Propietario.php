<?php

namespace App\Contratotransporte;

use App\Core\Tercero;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\User;

class Propietario extends Model
{
    protected $table = 'cte_propietarios';

    protected $fillable = ['id', 'genera_planilla', 'estado', 'tipo', 'tercero_id', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Tipo Documento', 'Número Documento', 'Propietario', 'Tipo Propietario', 'Estado'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function opciones_campo_select()
    {
        $opciones = Propietario::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_propietarios.tercero_id')
            ->select('cte_propietarios.id', 'core_terceros.descripcion', 'core_terceros.numero_identificacion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->numero_identificacion . ' ' . $opcion->descripcion;
        }
        return $vec;
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        return Propietario::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_propietarios.tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->leftJoin('users', 'users.id', '=', 'core_terceros.user_id')
            ->select(
                'core_tipos_docs_id.abreviatura AS campo1',
                'core_terceros.numero_identificacion AS campo2',
                'core_terceros.descripcion AS campo3',
                'cte_propietarios.tipo AS campo4',
                'cte_propietarios.estado AS campo5',
                'cte_propietarios.id AS campo6'
            )->where("core_tipos_docs_id.abreviatura", "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere('core_terceros.descripcion', "LIKE", "%$search%")
            ->orWhere("cte_propietarios.tipo", "LIKE", "%$search%")
            ->orWhere("cte_propietarios.estado", "LIKE", "%$search%")
            ->orderBy('cte_propietarios.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Propietario::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_propietarios.tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->leftJoin('users', 'users.id', '=', 'core_terceros.user_id')
            ->select(
                'core_tipos_docs_id.abreviatura AS TIPO_DOCUMENTO',
                'core_terceros.numero_identificacion AS IDENTIDAD',
                'core_terceros.descripcion AS PROPIETARIO_TENEDOR',
                'cte_propietarios.tipo AS TIPO',
                'cte_propietarios.estado AS ESTADO'
            )->where("core_tipos_docs_id.abreviatura", "LIKE", "%$search%")
            ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
            ->orWhere('core_terceros.descripcion', "LIKE", "%$search%")
            ->orWhere("cte_propietarios.tipo", "LIKE", "%$search%")
            ->orWhere("cte_propietarios.estado", "LIKE", "%$search%")
            ->orderBy('cte_propietarios.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PROPIETARIOS/TENEDORES";
    }

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class);
    }

    public function tercero()
    {
        return $this->belongsTo(Tercero::class);
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $tercero = Propietario::find($registro->id)->tercero;

        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++) {
            switch ($lista_campos[$i]['name']) {
                case 'email':
                    $usuario = User::find($tercero->user_id);
                    if (!is_null($usuario)) {
                        $lista_campos[$i]['value'] = $usuario->email;
                    }

                    break;

                case 'tercero_id':
                    $lista_campos[$i]['tipo'] = 'bsText';
                    $lista_campos[$i]['value'] = $tercero->descripcion;
                    break;

                default:
                    # code...
                    break;
            }
        }

        return $lista_campos;
    }
}
