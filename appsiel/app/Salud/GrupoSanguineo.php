<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

use App\Sistema\Modelo;
use App\Core\ModeloEavValor;

use DB;

class GrupoSanguineo extends Model
{
    protected $table = 'core_eav_valores';

    protected $fillable = ['modelo_padre_id', 'registro_modelo_padre_id', 'modelo_entidad_id', 'registro_modelo_entidad_id', 'core_campo_id', 'valor'];

    protected $crud_model_id = 224; // Grupos Sanguineos. Es el mismo $modelo_padre_id, a variable no se puede usar en métodos estáticos

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web"}';

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'ID'];

    public static function consultar_registros($nro_registros, $search)
    {
        $modelo_padre_id = 224; // Grupos Sanguineos
        return GrupoSanguineo::leftJoin('sys_campos', 'sys_campos.id', '=', 'core_eav_valores.core_campo_id')
            ->where('core_eav_valores.modelo_padre_id', $modelo_padre_id)
            ->select(
                'core_eav_valores.valor AS campo1',
                'core_eav_valores.id AS campo2',
                'core_eav_valores.id AS campo3'
            )
            ->orderBy('core_eav_valores.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $modelo_padre_id = 224; // Grupos Sanguineos
        $string = GrupoSanguineo::leftJoin('sys_campos', 'sys_campos.id', '=', 'core_eav_valores.core_campo_id')
            ->where('core_eav_valores.modelo_padre_id', $modelo_padre_id)
            ->select(
                'core_eav_valores.valor AS DESCRIPCIÓN'
            )
            ->orWhere("core_eav_valores.valor", "LIKE", "%$search%")
            ->orderBy('core_eav_valores.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE GRUPOS SANGUINEOS";
    }


    public static function opciones_campo_select()
    {
        $modelo_padre_id = 224; // Grupos Sanguineos
        $opciones = GrupoSanguineo::where('core_eav_valores.modelo_padre_id', $modelo_padre_id)
            ->orderBy('valor')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->modelo_padre_id . '-' . $opcion->registro_modelo_padre_id . '-' . $opcion->modelo_entidad_id . '-' . $opcion->core_campo_id] = $opcion->valor;
        }
        return $vec;
    }

    public function get_campos_adicionales_create($lista_campos)
    {
        $modelo_padre_id = Modelo::find($this->crud_model_id)->id;

        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++) {
            if ($lista_campos[$i]['name'] == 'modelo_padre_id') {
                $lista_campos[$i]['value'] = $modelo_padre_id;
            }
        }

        return $lista_campos;
    }

    public function store_adicional($datos, $registro)
    {
        // Con ModeloController se almacena un solo registro en la tabla EAV con datos vacíos
        // Se obtiene ese registro y se actualiza
        $registro_eav = ModeloEavValor::where(
            [
                "modelo_padre_id" => $registro->modelo_padre_id,
                "registro_modelo_padre_id" => 0,
                "core_campo_id" => 0
            ]
        )
            ->get()
            ->first();

        $datos2 = array_shift($datos);
        $registro_eav->registro_modelo_padre_id = $registro->id;
        $core_campo_id = explode("-", array_keys($datos)[0])[1];
        $registro_eav->core_campo_id = $core_campo_id;
        $registro_eav->valor = array_values($datos)[0];
        $registro_eav->save();

        array_shift($datos);

        // Se va a crear un registro por cada Atributo (campo) que tenga un Valor distinto a vacío 
        foreach ($datos as $key => $value) {
            // Si el name del campo enviado tiene la palabra core_campo_id
            if (strpos($key, "core_campo_id") !== false) {
                $core_campo_id = explode("-", $key)[1]; // Atributo
                $valor = $value; // Valor

                $nuevo_registro = ModeloEavValor::create(["modelo_padre_id" => $registro->modelo_padre_id, "modelo_entidad_id" => 0, "core_campo_id" => $core_campo_id, "valor" => $valor]);
                $nuevo_registro->registro_modelo_padre_id = $nuevo_registro->id;
                $nuevo_registro->save();
            }
        }
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $modelo_padre_id = Modelo::find($this->crud_model_id)->id;

        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++) {
            // Si el name del campo enviado tiene la palabra core_campo_id
            if (strpos($lista_campos[$i]['name'], "core_campo_id") !== false) {
                $core_campo_id = $lista_campos[$i]['id']; // Atributo_ID

                $registro_eav = ModeloEavValor::where(
                    [
                        "modelo_padre_id" => $modelo_padre_id,
                        "registro_modelo_padre_id" => $registro->registro_modelo_padre_id,
                        "core_campo_id" => $core_campo_id
                    ]
                )
                    ->get()
                    ->first();

                if (!is_null($registro_eav)) {
                    $lista_campos[$i]['value'] = $registro_eav->valor;
                }
            }
        }

        return $lista_campos;
    }

    public function update_adicional($datos, $id)
    {
        $modelo_padre_id = Modelo::find($this->crud_model_id)->id;

        $this->almacenar_registros_eav($datos, $modelo_padre_id, $id);
    }


    public function almacenar_registros_eav($datos, $modelo_padre_id, $registro_modelo_padre_id)
    {
        // Se va a crear un registro por cada Atributo (campo) que tenga un Valor distinto a vacío 
        foreach ($datos as $key => $value) {
            // Si el name del campo enviado tiene la palabra core_campo_id
            if (strpos($key, "core_campo_id") !== false) {
                $core_campo_id = explode("-", $key)[1]; // Atributo
                $valor = $value; // Valor

                $registro_eav = ModeloEavValor::where(
                    [
                        "modelo_padre_id" => $modelo_padre_id,
                        "registro_modelo_padre_id" => $registro_modelo_padre_id,
                        "core_campo_id" => $core_campo_id
                    ]
                )
                    ->get()
                    ->first();

                if (is_null($registro_eav)) {
                    ModeloEavValor::create(["modelo_padre_id" => $modelo_padre_id, "registro_modelo_padre_id" => $registro_modelo_padre_id, "modelo_entidad_id" => 0, "core_campo_id" => $core_campo_id, "valor" => $valor]);
                } else {
                    $registro_eav->valor = $valor;
                    $registro_eav->save();
                }
            }
        }
    }

    public static function get_valor_campo($string_ids_campos)
    {
        $vec_ids_campos = explode('-', $string_ids_campos);

        if (!isset($vec_ids_campos[1]) || !isset($vec_ids_campos[2]) || !isset($vec_ids_campos[3])) {
            return '';
        }

        return ModeloEavValor::where(
            [
                "modelo_padre_id" => $vec_ids_campos[0],
                "registro_modelo_padre_id" => $vec_ids_campos[1],
                "core_campo_id" => $vec_ids_campos[3]
            ]
        )
            ->value('valor');
    }
}
