<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

class Colegio extends Model
{
	protected $table = 'sga_colegios';
	
    protected $fillable = ['empresa_id','descripcion', 'slogan', 'resolucion', 'direccion', 'telefonos','ciudad', 'piefirma1','piefirma2', 'maneja_puesto'];


    public $encabezado_tabla = ['ID','Descripcion','Slogan','Resolución','Dirección','Teléfono(s)','Acción'];

    public static function consultar_registros()
    {
    	$registros = Colegio::select('sga_colegios.id AS campo1','sga_colegios.descripcion AS campo2','sga_colegios.slogan AS campo3','sga_colegios.resolucion AS campo4','sga_colegios.direccion AS campo5','sga_colegios.telefonos AS campo6','sga_colegios.id AS campo7')
                    ->get()
                    ->toArray();

        return $registros;
    }

    public static function get_colegio_user()
    {
        return Colegio::where( 'empresa_id', Auth::user()->empresa_id )->get()->first();
    }



    public function store_adicional($datos, $registro)
    {
        DB::insert('INSERT INTO sys_secuencias_codigos (id_colegio,modulo,consecutivo,anio,estructura_secuencia,estado,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)', [$registro->id,'inscripciones', 0, date('Y'),'anio-consecutivo','Activo', date('Y-m-d H:i:s'),date('Y-m-d H:i:s')]);

        DB::insert('INSERT INTO sys_secuencias_codigos (id_colegio,modulo,consecutivo,anio,estructura_secuencia,estado,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)', [$registro->id,'matriculas',0, date('Y'),'anio-consecutivo-grado','Activo',date('Y-m-d H:i:s'),date('Y-m-d H:i:s')]);

        DB::insert('INSERT INTO sys_secuencias_codigos (id_colegio,modulo,consecutivo,anio,estructura_secuencia,estado,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)', [$registro->id,'logros',0, date('Y'),'consecutivo','Activo',date('Y-m-d H:i:s'),date('Y-m-d H:i:s')]);

        return redirect( 'web/'.$registro->id.'?id='.$datos['url_id'].'&id_modelo='.$datos['url_id_modelo'] );
    }
}
