<?php

namespace App\Calificaciones;

use Illuminate\Database\Eloquent\Model;

use App\Core\ModeloEavValor;
use App\Sistema\Modelo;

use DB;

class Area extends Model
{
    protected $table = 'sga_areas';

    protected $fillable = ['colegio_id', 'orden_listados', 'descripcion', 'abreviatura', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Orden listados', 'Descripción', 'Abreviatura', 'Estado'];

    protected $crud_model_id = 122; // Areas

    public static function consultar_registros($nro_registros)
    {
        $registros = Area::select('sga_areas.orden_listados AS campo1', 'sga_areas.descripcion AS campo2', 'sga_areas.abreviatura AS campo3', 'sga_areas.estado AS campo4', 'sga_areas.id AS campo5')
            ->orderBy('sga_areas.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public function store_adicional($datos, $registro)
    {
        $modelo_padre_id = Modelo::where('modelo', 'Area')->value('id');

        $this->almacenar_registros_eav($datos, $modelo_padre_id, $registro->id);
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        $modelo_padre_id = Modelo::where('modelo', 'Area')->value('id');

        // Personalizar campos
        $cantida_campos = count($lista_campos);
        for ($i = 0; $i <  $cantida_campos; $i++) {
            // Si el name del campo enviado tiene la palabra core_campo_id
            if (strpos($lista_campos[$i]['name'], "core_campo_id") !== false) {
                $core_campo_id = $lista_campos[$i]['id']; // Atributo_ID

                $registro_eav = ModeloEavValor::where(
                    [
                        "modelo_padre_id" => $modelo_padre_id,
                        "registro_modelo_padre_id" => $registro->id,
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
        $modelo_padre_id = Modelo::where('modelo', 'Area')->value('id');

        $this->almacenar_registros_eav($datos, $modelo_padre_id, $id);
    }

    // $datos = $request->all()
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

    public function get_valor_eav($modelo_padre_id, $registro_modelo_padre_id, $core_campo_id)
    {
        return ModeloEavValor::where([
            "modelo_padre_id" => $modelo_padre_id,
            "registro_modelo_padre_id" => $registro_modelo_padre_id,
            "core_campo_id" => $core_campo_id
        ])
            ->value('valor');
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"sga_asignaturas",
                                    "llave_foranea":"area_id",
                                    "mensaje":"Tiene asignaturas relacionadas."
                                }
                        }';

        $tablas = json_decode($tablas_relacionadas);
        //$cantidad = count($tablas);
        foreach ($tablas as $una_tabla) {
            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro)) {
                //dd([ $una_tabla->tabla, $una_tabla->llave_foranea, $id, $registro, $una_tabla->mensaje ] );
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
