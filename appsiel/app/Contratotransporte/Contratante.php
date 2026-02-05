<?php

namespace App\Contratotransporte;

use App\Core\Tercero;
use App\Sistema\Services\CrudService;
use App\Traits\FiltraRegistrosPorUsuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Contratante extends Model
{
    use FiltraRegistrosPorUsuario;
    protected $table = 'cte_contratantes';
    protected $fillable = ['id', 'tercero_id', 'estado', 'created_at', 'updated_at'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Tipo Documento', 'Número Documento', 'Contratante', 'Estado'];

    public $vistas = '{"index":"layouts.index3"}';

    public static function opciones_campo_select()
    {
        $opciones = Contratante::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_contratantes.tercero_id')
            ->select('cte_contratantes.id', 'core_terceros.descripcion', 'core_terceros.numero_identificacion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->numero_identificacion . ' ' . $opcion->descripcion;
        }

        return $vec;
    }

    public static function consultar_registros2($nro_registros, $search)
    {
        $query = Contratante::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_contratantes.tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->select(
                'core_tipos_docs_id.abreviatura AS campo1',
                'core_terceros.numero_identificacion AS campo2',
                DB::raw('core_terceros.descripcion AS campo3'),
                'cte_contratantes.estado AS campo4',
                'cte_contratantes.id AS campo5'
            );

        $query = $query->where(function ($q) use ($search) {
            $q->where("core_tipos_docs_id.abreviatura", "LIKE", "%$search%")
                ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
                ->orWhere(DB::raw('core_terceros.descripcion'), "LIKE", "%$search%")
                ->orWhere("cte_contratantes.estado", "LIKE", "%$search%");
        });

        $query = self::aplicarFiltroCreadoPor($query, 'core_terceros.creado_por');

        return $query->orderBy('cte_contratantes.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $query = Contratante::leftJoin('core_terceros', 'core_terceros.id', '=', 'cte_contratantes.tercero_id')
            ->leftJoin('core_tipos_docs_id', 'core_tipos_docs_id.id', '=', 'core_terceros.id_tipo_documento_id')
            ->select(
                'core_tipos_docs_id.abreviatura AS TIPO_DOCUMENTO',
                'core_terceros.numero_identificacion AS NÚMERO_DOCUMENTO',
                'core_terceros.descripcion AS CONTRATANTE',
                'cte_contratantes.estado AS ESTADO'
            );

        $query = $query->where(function ($q) use ($search) {
            $q->where("core_tipos_docs_id.abreviatura", "LIKE", "%$search%")
                ->orWhere("core_terceros.numero_identificacion", "LIKE", "%$search%")
                ->orWhere('core_terceros.descripcion', "LIKE", "%$search%")
                ->orWhere("cte_contratantes.estado", "LIKE", "%$search%");
        });

        $query = self::aplicarFiltroCreadoPor($query, 'core_terceros.creado_por');

        $string = $query->orderBy('cte_contratantes.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CONTRATANTES";
    }

    public function tercero()
    {
        return $this->belongsTo(Tercero::class);
    }

    public function contratos()
    {
        return $this->hasMany(Contrato::class);
    }

    public function scopeFiltrarPorUsuario($query)
    {
        return $query->whereIn('cte_contratantes.tercero_id', function ($sub) {
            $sub->from('core_terceros')
                ->select('core_terceros.id');

            self::aplicarFiltroCreadoPor($sub, 'core_terceros.creado_por');
        });
    }

    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"cte_contratos",
                                    "llave_foranea":"contratante_id",
                                    "mensaje":"Está relacionado en Contratos de Transporte."
                                }
                        }';

        return (new CrudService())->validar_eliminacion_un_registro( $id, $tablas_relacionadas);
    }
}
