<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

use DB;
use Auth;

use App\Core\Colegio;

class Grado extends Model
{

    protected $table = 'sga_grados';

    protected $fillable = ['id_colegio', 'descripcion', 'codigo', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripcion', 'Código', 'Estado'];

    public function cursos()
    {
        return $this->hasMany(Curso::class, 'sga_grado_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = Grado::select(
            'sga_grados.descripcion AS campo1',
            'sga_grados.codigo AS campo2',
            'sga_grados.estado AS campo3',
            'sga_grados.id AS campo4'
        )->where("sga_grados.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_grados.codigo", "LIKE", "%$search%")
            ->orWhere("sga_grados.estado", "LIKE", "%$search%")
            ->orderBy('sga_grados.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = Grado::select(
            'sga_grados.descripcion AS DESCRIPCIÓN',
            'sga_grados.codigo AS CODIGO',
            'sga_grados.estado AS ESTADO'
        )->where("sga_grados.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_grados.codigo", "LIKE", "%$search%")
            ->orWhere("sga_grados.estado", "LIKE", "%$search%")
            ->orderBy('sga_grados.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CATALOGO GRADOS";
    }

    public static function opciones_campo_select()
    {
        $colegio = Colegio::where('empresa_id', Auth::user()->empresa_id)->get()[0];

        $opciones = Grado::where('id_colegio', $colegio->id)
            ->where('estado', 'Activo')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public function store_adicional($datos, $registro)
    {
        //return 'prueba';
    }
}
