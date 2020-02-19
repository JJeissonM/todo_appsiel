<?php

namespace App\Sistema;

use Illuminate\Database\Eloquent\Model;

class Aplicacion extends Model
{
    protected $table = 'sys_aplicaciones';

    protected $fillable = ['ambito','descripcion','app','definicion','tipo_precio','precio','orden','nombre_imagen','mostrar_en_pag_web','estado'];

    public $encabezado_tabla = [ 'ID','Ámbito','Descripción','Detalles','Tipo precio','Precio','Orden','Estado','Acción'];

    public static function consultar_registros()
    {
    	$registros = Aplicacion::select(
                                        'sys_aplicaciones.id AS campo1',
                                        'sys_aplicaciones.ambito AS campo2',
                                        'sys_aplicaciones.descripcion AS campo3',
                                        'sys_aplicaciones.definicion AS campo4',
                                        'sys_aplicaciones.tipo_precio AS campo5',
                                        'sys_aplicaciones.precio AS campo6',
                                        'sys_aplicaciones.orden AS campo7',
                                        'sys_aplicaciones.estado AS campo8',
                                        'sys_aplicaciones.id AS campo9')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public function tipos_transacciones()
    {
        return $this->hasMany('App\Sistema\TipoTransaccion','core_app_id');
    }

    public static function opciones_campo_select()
    {
        $opciones = Aplicacion::where('estado','=','Activo')->get();

        $vec['']='';
        foreach ($opciones as $opcion){
            $vec[$opcion->id] = $opcion->descripcion;
        }
        
        return $vec;
    }
}
