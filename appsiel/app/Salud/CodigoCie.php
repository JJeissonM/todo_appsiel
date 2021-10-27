<?php

namespace App\Salud;

use Illuminate\Database\Eloquent\Model;

class CodigoCie extends Model
{
    protected $table = 'salud_cie10'; // model_App\Salud\CodigoCie
	
	protected $fillable = ['codigo', 'descripcion', 'estado'];

	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Descripción', 'Estado'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = CodigoCie::select(
        			'salud_cie10.codigo AS campo1',
        			'salud_cie10.descripcion AS campo2',
        			'salud_cie10.estado AS campo3',
        			'salud_cie10.id AS campo4'
		            )->where("salud_cie10.descripcion", "LIKE", "%$search%")
		            ->orWhere("salud_cie10.estado", "LIKE", "%$search%")
		            ->orWhere("salud_cie10.codigo", "LIKE", "%$search%")
		            ->orderBy('salud_cie10.created_at', 'DESC')
		            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = CodigoCie::select(
        			'salud_cie10.codigo AS campo1',
        			'salud_cie10.descripcion AS campo2',
        			'salud_cie10.estado AS campo3',
        			'salud_cie10.id AS campo4'
		            )->where("salud_cie10.descripcion", "LIKE", "%$search%")
		            ->orWhere("salud_cie10.estado", "LIKE", "%$search%")
		            ->orWhere("salud_cie10.codigo", "LIKE", "%$search%")
		            ->orderBy('salud_cie10.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE Códigos CIE10";
    }

    public static function opciones_campo_select()
    {
        $opciones = CodigoCie::where('estado','=','Activo')
                        ->orderBy('descripcion')
                        ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->codigo . ' ' . $opcion->descripcion;
        }
        
        return $vec;
    }

    public function get_label_to_show()
    {
        return $this->codigo . ' ' . $this->descripcion;
    }
}
