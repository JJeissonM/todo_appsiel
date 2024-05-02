<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Colegio extends Model
{
    protected $table = 'sga_colegios';

    protected $fillable = ['empresa_id', 'descripcion', 'slogan', 'resolucion', 'direccion', 'telefonos', 'ciudad', 'piefirma1', 'piefirma2', 'maneja_puesto'];

    public function empresa()
    {
        return $this->belongsTo('App\Core\Empresa');
    }

    public function tercero_empresa()
    {
        return $this->empresa->tercero();
    }

    public function representante_legal()
    {
        return $this->empresa->tercero_representante_legal();
    }

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripcion', 'Slogan', 'Resolución', 'Dirección', 'Teléfono(s)'];

    public static function consultar_registros($nro_registros, $search)
    {
        $registros = Colegio::select(
            'sga_colegios.descripcion AS campo1',
            'sga_colegios.slogan AS campo2',
            'sga_colegios.resolucion AS campo3',
            'sga_colegios.direccion AS campo4',
            'sga_colegios.telefonos AS campo5',
            'sga_colegios.id AS campo6'
        )
            ->where("sga_colegios.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_colegios.slogan", "LIKE", "%$search%")
            ->orWhere("sga_colegios.resolucion", "LIKE", "%$search%")
            ->orWhere("sga_colegios.direccion", "LIKE", "%$search%")
            ->orWhere("sga_colegios.telefonos", "LIKE", "%$search%")
            ->orderBy('sga_colegios.created_at', 'DESC')
            ->paginate($nro_registros);

        return $registros;
    }

    public static function sqlString($search)
    {
        $string = Colegio::select(
            'sga_colegios.descripcion AS DESCRIPCION',
            'sga_colegios.slogan AS SLOGAN',
            'sga_colegios.resolucion AS RESOLUCIÓN',
            'sga_colegios.direccion AS DIRECCIÓN',
            'sga_colegios.telefonos AS TELÉFONO(S)'
        )
            ->where("sga_colegios.descripcion", "LIKE", "%$search%")
            ->orWhere("sga_colegios.slogan", "LIKE", "%$search%")
            ->orWhere("sga_colegios.resolucion", "LIKE", "%$search%")
            ->orWhere("sga_colegios.direccion", "LIKE", "%$search%")
            ->orWhere("sga_colegios.telefonos", "LIKE", "%$search%")
            ->orderBy('sga_colegios.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE COLEGIOS";
    }

    public static function get_colegio_user()
    {
        return Colegio::where('empresa_id', Auth::user()->empresa_id)->get()->first();
    }



    public function store_adicional($datos, $registro)
    {
        DB::insert('INSERT INTO sys_secuencias_codigos (id_colegio,modulo,consecutivo,anio,estructura_secuencia,estado,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)', [$registro->id, 'inscripciones', 0, date('Y'), 'anio-consecutivo', 'Activo', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);

        DB::insert('INSERT INTO sys_secuencias_codigos (id_colegio,modulo,consecutivo,anio,estructura_secuencia,estado,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)', [$registro->id, 'matriculas', 0, date('Y'), 'anio-consecutivo-grado', 'Activo', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);

        DB::insert('INSERT INTO sys_secuencias_codigos (id_colegio,modulo,consecutivo,anio,estructura_secuencia,estado,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?)', [$registro->id, 'logros', 0, date('Y'), 'consecutivo', 'Activo', date('Y-m-d H:i:s'), date('Y-m-d H:i:s')]);
    }
}
