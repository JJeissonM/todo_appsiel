<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

class DifoSeccion extends Model
{
    protected $table = 'difo_secciones';

    protected $fillable = ['descripcion','presentacion','alineacion','cantidad_filas','cantidad_columnas','cantidad_espacios_despues','cantidad_espacios_antes','contenido','estilo_letra'];

    public $encabezado_tabla = ['Nombre','Contenido','Presentación','Alineación','Acción'];

    public static function consultar_registros()
    {    	
    	$registros = DifoSeccion::select('difo_secciones.descripcion AS campo1',
                            'difo_secciones.contenido AS campo2',
                            'difo_secciones.presentacion AS campo3',
                            'difo_secciones.alineacion AS campo4',
                            'difo_secciones.id AS campo5')
                    ->get()
                    ->toArray();

        return $registros;
    }
}