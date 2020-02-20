<?php

namespace App\Matriculas;

use Illuminate\Database\Eloquent\Model;

class CodigoDisciplinario extends Model
{
    protected $table = 'sga_codigos_disciplinarios';

    protected $fillable = ['colegio_id','descripcion','tipo_codigo','estado'];

    // Calificacion: { positiva | negativa }
    public $encabezado_tabla = ['Código','Descripción','Calificación','Estado','Acción'];

    public static function consultar_registros()
    {
    	$registros = CodigoDisciplinario::select('sga_codigos_disciplinarios.id AS campo1','sga_codigos_disciplinarios.descripcion AS campo2','sga_codigos_disciplinarios.tipo_codigo AS campo3','sga_codigos_disciplinarios.estado AS campo4','sga_codigos_disciplinarios.id AS campo5')
                    ->get()
                    ->toArray();

        return $registros;
    }
}
