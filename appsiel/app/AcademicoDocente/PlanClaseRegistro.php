<?php

namespace App\AcademicoDocente;

use Illuminate\Database\Eloquent\Model;

class PlanClaseRegistro extends Model
{
    protected $table = 'sga_plan_clases_registros';
<<<<<<< HEAD

    protected $fillable = ['plan_clase_encabezado_id', 'plan_clase_estruc_elemento_id', 'contenido', 'estado'];

    public $encabezado_tabla = ['Plan de clases', 'Elemento', 'Contenido', 'Estado', 'Acción'];

    public static function consultar_registros()
    {
        return PlanClaseRegistro::select('sga_plan_clases_registros.plan_clase_encabezado_id AS campo1', 'sga_plan_clases_registros.plan_clase_estruc_elemento_id AS campo2', 'sga_plan_clases_registros.contenido AS campo3', 'sga_plan_clases_registros.estado AS campo4', 'sga_plan_clases_registros.id AS campo5')
            ->get()
            ->toArray();
    }

    public static function opciones_campo_select()
    {
        $opciones = PlanClaseRegistro::where('sga_plan_clases_registros.estado', 'Activo')
            ->select('sga_plan_clases_registros.id', 'sga_plan_clases_registros.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
=======
	
    protected $fillable = ['plan_clase_encabezado_id', 'plan_clase_estruc_elemento_id', 'contenido', 'estado'];
	
    public $encabezado_tabla = ['Plan de clases', 'Elemento', 'Contenido', 'Estado', 'Acción'];
	
	public static function consultar_registros()
	{
	    return PlanClaseRegistro::select('sga_plan_clases_registros.plan_clase_encabezado_id AS campo1', 'sga_plan_clases_registros.plan_clase_estruc_elemento_id AS campo2', 'sga_plan_clases_registros.contenido AS campo3', 'sga_plan_clases_registros.estado AS campo4', 'sga_plan_clases_registros.id AS campo5')
	    ->get()
	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = PlanClaseRegistro::where('sga_plan_clases_registros.estado','Activo')
                    ->select('sga_plan_clases_registros.id','sga_plan_clases_registros.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
>>>>>>> fb0374952b86cefd9534446459798e324d7e80e9
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

<<<<<<< HEAD
    public static function get_registros_impresion($plan_clase_encabezado_id)
    {
        return PlanClaseRegistro::leftJoin('sga_plan_clases_struc_elementos', 'sga_plan_clases_struc_elementos.id', '=', 'sga_plan_clases_registros.plan_clase_estruc_elemento_id')
            ->where('plan_clase_encabezado_id', $plan_clase_encabezado_id)
            ->where('sga_plan_clases_struc_elementos.estado', 'Activo')
            ->select(
                'sga_plan_clases_struc_elementos.descripcion AS elemento_descripcion',
                'sga_plan_clases_struc_elementos.id AS elemento_id',
                'sga_plan_clases_registros.contenido',
                'sga_plan_clases_registros.estado',
                'sga_plan_clases_registros.id')
            ->orderBy('orden')
            ->get();
    }

=======

    
    public static function get_registros_impresion( $plan_clase_encabezado_id )
    {
        return PlanClaseRegistro::leftJoin('sga_plan_clases_struc_elementos', 'sga_plan_clases_struc_elementos.id', '=', 'sga_plan_clases_registros.plan_clase_estruc_elemento_id')
                                ->where('plan_clase_encabezado_id', $plan_clase_encabezado_id)
                                ->where('sga_plan_clases_struc_elementos.estado', 'Activo')
                                ->select(
                                            'sga_plan_clases_struc_elementos.descripcion AS elemento_descripcion',
                                            'sga_plan_clases_struc_elementos.id AS elemento_id',
                                            'sga_plan_clases_registros.contenido',
                                            'sga_plan_clases_registros.estado',
                                            'sga_plan_clases_registros.id')
                                ->orderBy('orden')
                                ->get();
    }
>>>>>>> fb0374952b86cefd9534446459798e324d7e80e9
}
