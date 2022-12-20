<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Core\Tercero;
use App\Matriculas\Estudiante;

class Inscripcion extends Model
{
    protected $table = 'sga_inscripciones';

    protected $fillable = ['codigo', 'fecha', 'sga_grado_id', 'core_tercero_id', 'genero', 'fecha_nacimiento', 'ciudad_nacimiento', 'origen', 'enterado_por', 'observacion', 'acudiente', 'colegio_anterior', 'estado', 'creado_por', 'modificado_por'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Nombres', 'Apellidos', 'Identificación', 'Cód. inscripción', 'Fecha', 'Grado', 'Origen', 'Estado'];

    public $urls_acciones = '{"create":"web/create","store":"matriculas/inscripcion","update":"matriculas/inscripcion/id_fila","edit":"web/id_fila/edit","show":"matriculas/inscripcion/id_fila","imprimir":"matriculas/inscripcion_print/id_fila","eliminar":"matriculas/inscripciones/eliminar/id_fila"}';

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/matriculas/inscripcion.js';

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public function estudiante()
    {
        return Estudiante::where('core_tercero_id',$this->core_tercero_id)->get()->first();
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return Inscripcion::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_inscripciones.core_tercero_id')
            ->leftJoin('sga_grados', 'sga_grados.id', '=', 'sga_inscripciones.sga_grado_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->select(
                DB::raw('CONCAT(core_terceros.nombre1," ",core_terceros.otros_nombres) AS campo1'),
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2) AS campo2'),
                DB::raw('CONCAT(core_tipos_docs_id.abreviatura," ", core_terceros.numero_identificacion ) AS campo3'),
                'sga_inscripciones.codigo AS campo4',
                'sga_inscripciones.fecha AS campo5',
                'sga_grados.descripcion AS campo6',
                'sga_inscripciones.origen AS campo7',
                'sga_inscripciones.estado AS campo8',
                'sga_inscripciones.id AS campo9'
            )
            ->where("sga_inscripciones.codigo", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_tipos_docs_id.abreviatura,' ', core_terceros.numero_identificacion)"), "LIKE", "%$search%")
            ->orWhere("sga_inscripciones.fecha", "LIKE", "%$search%")
            ->orWhere("sga_grados.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_inscripciones.origen", "LIKE", "%$search%")
            ->orWhere("sga_inscripciones.estado", "LIKE", "%$search%")
            ->orderBy('sga_inscripciones.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Inscripcion::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_inscripciones.core_tercero_id')
            ->leftJoin('sga_grados', 'sga_grados.id', '=', 'sga_inscripciones.sga_grado_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->select(
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS ASPIRANTE'),
                DB::raw('CONCAT(core_tipos_docs_id.abreviatura," ", core_terceros.numero_identificacion ) AS IDENTIFICACIÓN'),
                'sga_inscripciones.codigo AS CÓDIGO_INSCRIPCIÓN',
                'sga_inscripciones.fecha AS FECHA',
                'sga_grados.descripcion AS GRADO',
                'sga_inscripciones.origen AS ORIGEN',
                'sga_inscripciones.estado AS ESTADO'
            )
            ->where("sga_inscripciones.codigo", "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_terceros.apellido1,' ',core_terceros.apellido2,' ',core_terceros.nombre1,' ',core_terceros.otros_nombres)"), "LIKE", "%$search%")
            ->orWhere(DB::raw("CONCAT(core_tipos_docs_id.abreviatura,' ', core_terceros.numero_identificacion)"), "LIKE", "%$search%")
            ->orWhere("sga_inscripciones.fecha", "LIKE", "%$search%")
            ->orWhere("sga_grados.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_inscripciones.origen", "LIKE", "%$search%")
            ->orWhere("sga_inscripciones.estado", "LIKE", "%$search%")
            ->orderBy('sga_inscripciones.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE INSCRIPCIONES";
    }

    public static function get_opciones_select_inscritos()
    {
        $registros = Inscripcion::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_inscripciones.core_tercero_id')
            ->where('sga_inscripciones.estado', 'Pendiente')
            ->select(
                'sga_inscripciones.id AS id_inscripcion',
                'core_terceros.numero_identificacion',
                DB::raw('core_terceros.descripcion AS tercero')
            )
            ->distinct('core_terceros.id')
            ->get();

        $candidatos[''] = '';
        foreach ($registros as $opcion) {
            $candidatos[$opcion->id_inscripcion] = $opcion->numero_identificacion . ' ' . $opcion->tercero;
        }

        return $candidatos;
    }

    public function store_adicional($datos, $registro)
    {
        // cambiar nombre de campo email
        $registro->tercero->email = $datos['email2'];
        $registro->tercero->numero_identificacion = $datos['numero_identificacion2'];
        //dd($datos);
        $registro->tercero->save();
    }

    public function get_campos_adicionales_edit($lista_campos, $registro)
    {
        if ($registro->estado == 'Activo') {
            return [
                [
                    "id" => 999,
                    "descripcion" => "Label no se puede ingresar registros desde esta opción.",
                    "tipo" => "personalizado",
                    "name" => "lbl_planilla",
                    "opciones" => "",
                    "value" => '<div class="form-group">                    
                                                    <label class="control-label col-sm-3" style="color: red;" > <b> La incripción ya tiene matrículas asociadas y no puede ser modificada. Para modificar los datos del estudiante debe ir al Menú Catálogos. Estudiante: ' . $registro->tercero->descripcion . '</b> </label>
                                                </div>',
                    "atributos" => [],
                    "definicion" => "",
                    "requerido" => 0,
                    "editable" => 1,
                    "unico" => 0
                ]
            ];
        }

        $cantida_campos = count($lista_campos);

        //Personalización de la lista de campos
        for ($i = 0; $i < $cantida_campos; $i++) {

            switch ($lista_campos[$i]['name']) {
                case 'nombre1':
                    $lista_campos[$i]['value'] = $registro->tercero->nombre1;
                    break;
                case 'otros_nombres':
                    $lista_campos[$i]['value'] = $registro->tercero->otros_nombres;
                    break;
                case 'apellido1':
                    $lista_campos[$i]['value'] = $registro->tercero->apellido1;
                    break;
                case 'apellido2':
                    $lista_campos[$i]['value'] = $registro->tercero->apellido2;
                    break;
                case 'id_tipo_documento_id':
                    $lista_campos[$i]['value'] = $registro->tercero->id_tipo_documento_id;
                    break;
                case 'numero_identificacion':
                    $lista_campos[$i]['value'] = $registro->tercero->numero_identificacion;
                    break;
                case 'numero_identificacion2':
                    $lista_campos[$i]['value'] = $registro->tercero->numero_identificacion;
                    break;
                case 'direccion1':
                    $lista_campos[$i]['value'] = $registro->tercero->direccion1;
                    break;
                case 'telefono1':
                    $lista_campos[$i]['value'] = $registro->tercero->telefono1;
                    break;
                case 'email':
                    $lista_campos[$i]['value'] = $registro->tercero->email;
                    break;
                case 'email2':
                    $lista_campos[$i]['value'] = $registro->tercero->email;
                    break;
                case 'codigo_ciudad':
                    $lista_campos[$i]['value'] = $registro->tercero->codigo_ciudad;
                    break;

                default:
                    # code...
                    break;
            }
        }

        // Agregar NUEVO campo con el core_tercero_id
        $lista_campos[$i]['tipo'] = 'hidden';
        $lista_campos[$i]['name'] = 'core_tercero_id';
        $lista_campos[$i]['descripcion'] = '';
        $lista_campos[$i]['opciones'] = [];
        $lista_campos[$i]['value'] = $registro->tercero->id;
        $lista_campos[$i]['atributos'] = [];
        $lista_campos[$i]['requerido'] = false;

        return $lista_campos;
    }


    public static function get_registro_impresion($id)
    {
        return Inscripcion::leftJoin('core_terceros', 'core_terceros.id', '=', 'sga_inscripciones.core_tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->leftJoin('sga_grados', 'sga_grados.id', '=', 'sga_inscripciones.sga_grado_id')
            ->where('sga_inscripciones.id', $id)
            ->select(
                'sga_inscripciones.id',
                'sga_inscripciones.codigo',
                'sga_inscripciones.core_tercero_id',
                'sga_inscripciones.fecha',
                DB::raw('CONCAT(core_terceros.apellido1," ",core_terceros.apellido2," ",core_terceros.nombre1," ",core_terceros.otros_nombres) AS nombre_completo'),
                DB::raw('CONCAT(core_tipos_docs_id.abreviatura," ",core_terceros.numero_identificacion) AS tipo_y_numero_documento_identidad'),
                'sga_inscripciones.acudiente',
                'sga_grados.descripcion AS nombre_grado',
                'sga_inscripciones.enterado_por',
                'sga_inscripciones.genero',
                'sga_inscripciones.ciudad_nacimiento',
                'sga_inscripciones.fecha_nacimiento',
                'sga_inscripciones.origen',
                'sga_inscripciones.observacion',
                'sga_inscripciones.creado_por',
                'sga_inscripciones.created_at',
                'sga_inscripciones.colegio_anterior',
                'core_terceros.nombre1',
                'core_terceros.otros_nombres',
                'core_terceros.apellido1',
                'core_terceros.apellido2',
                'core_terceros.telefono1',
                'core_terceros.id_tipo_documento_id',
                'core_terceros.numero_identificacion',
                'core_terceros.direccion1',
                'core_terceros.barrio',
                'core_terceros.email'
            )
            ->get()
            ->first();
    }
}
