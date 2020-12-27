<?php

namespace App\Sistema;

use Illuminate\Database\Eloquent\Model;

class Campo extends Model
{
    protected $table = 'sys_campos';

    protected $fillable = ['tipo', 'name', 'descripcion', 'opciones', 'value', 'atributos', 'definicion', 'requerido', 'editable', 'unico'];
    
    public $encabezado_tabla = ['ID','Tipo','Name','Descripción','Opciones','Valor','Atributos','Definición','Requerido','Editable','Único','Acción'];

    public function modelos()
    {
        return $this->belongsToMany('App\Sistema\Modelo','sys_modelo_tiene_campos','core_campo_id','core_modelo_id');
    }

    public static function consultar_registros()
    {
    	$registros = Campo::select('sys_campos.id AS campo1','sys_campos.tipo AS campo2','sys_campos.name AS campo3','sys_campos.descripcion AS campo4','sys_campos.opciones AS campo5','sys_campos.value AS campo6','sys_campos.atributos AS campo7','sys_campos.definicion AS campo8','sys_campos.requerido AS campo9','sys_campos.editable AS campo10','sys_campos.unico AS campo11','sys_campos.id AS campo12')
                    ->get()
                    ->toArray();

        return $registros;
    }

    // El archivo js debe estar en la carpeta public
    public $archivo_js = 'assets/js/sistema/modelo_campos.js';

    public static function opciones_campo_select()
    {
        $opciones = Campo::select('id','descripcion')
                    ->orderBy('descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion . ' (' . $opcion->id . ')';
        }

        return $vec;
    }

    // Esta función reemplaza el valor null para el campo value en la lista de campos
    // El reemplazo lo hace con el valor del campo correspondiente en el registro almacenado en la base de datos
    public static function asignar_valores_registro( array $lista_campos, $registro )
    {
        $cantidad_campos = count($lista_campos);
        for ($i=0; $i < $cantidad_campos; $i++) 
        {
            $nombre_campo = $lista_campos[$i]['name'];

            if ( isset( $registro->$nombre_campo ) ) 
            {
                $lista_campos[$i]['value'] = $registro->$nombre_campo;
            }
        }

        return $lista_campos;
    }
}
