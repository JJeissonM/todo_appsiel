<?php

namespace App\Core;

use Illuminate\Database\Eloquent\Model;

use DB;

use App\Sistema\SecuenciaCodigo;

class Ciudad extends Model
{
    protected $table = 'core_ciudades'; 

    protected $fillable = [ 'core_departamento_id', 'descripcion'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Código', 'Ciudad', 'Departamento/Estado','Pais'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit"}';

    public function departamento()
    {
        return $this->belongsTo(Departamento::class,'core_departamento_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return Ciudad::leftJoin('core_departamentos','core_departamentos.id','=','core_ciudades.core_departamento_id')
                            ->leftJoin('core_paises','core_paises.id','=','core_departamentos.codigo_pais')
                            ->select(
                                        'core_ciudades.id AS campo1',
                                        'core_ciudades.descripcion AS campo2',
                                        'core_departamentos.descripcion AS campo3',
                                        'core_paises.descripcion AS campo4',
                                        'core_ciudades.id AS campo5')
                            ->where("core_ciudades.descripcion", "LIKE", "%$search%")
                            ->orWhere("core_departamentos.descripcion", "LIKE", "%$search%")
                            ->orWhere("core_paises.descripcion", "LIKE", "%$search%")
                            ->orderBy('core_ciudades.created_at','DESC')
                            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = Ciudad::leftJoin('core_departamentos','core_departamentos.id','=','core_ciudades.core_departamento_id')
                            ->leftJoin('core_paises','core_paises.id','=','core_departamentos.codigo_pais')
                            ->select(
                                        'core_ciudades.descripcion AS campo1',
                                        'core_departamentos.descripcion AS campo2',
                                        'core_paises.descripcion AS campo3',
                                        'core_ciudades.id AS campo4')
                            ->where("core_ciudades.descripcion", "LIKE", "%$search%")
                            ->orWhere("core_departamentos.descripcion", "LIKE", "%$search%")
                            ->orWhere("core_paises.descripcion", "LIKE", "%$search%")
                            ->orderBy('core_ciudades.created_at','DESC')
                            ->toSql();

        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CIUDADES";
    }

    public static function opciones_campo_select()
    {
        $opciones = Ciudad::all();

        $vec[''] = '';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion . ', ' . $opcion->departamento->descripcion;
        }

        return $vec;
    }

    public static function opciones_campo_select_2()
    {
        $opciones = Ciudad::all();

        $vec[''] = '';
        foreach ($opciones as $opcion)
        {
            $vec[ $opcion->descripcion . ', ' . $opcion->departamento->descripcion ] = $opcion->descripcion . ', ' . $opcion->departamento->descripcion;
        }

        return $vec;
    }

    public function store_adicional( $datos, $registro )
    {
        // Se obtiene el consecutivo para actualizar el logro creado
        $consecutivo = SecuenciaCodigo::where( ['modulo'=>'ciudades'] )->value('consecutivo');
        $consecutivo += 1;

        // Actualizar el consecutivo
        SecuenciaCodigo::where( ['modulo'=>'ciudades'] )->increment('consecutivo');

        $codigo = $datos['pais_id'].$this->formatear_campo( $datos['departamento_id'],'0','izquierda',2).$consecutivo;
        
        $registro->id = $codigo;
        $registro->core_departamento_id = (int)$datos['departamento_id'];
        $registro->save();
    }


    public function formatear_campo( $valor_campo, $caracter_relleno, $orientacion_relleno, $longitud_campo )
    {
        $largo_campo = strlen( $valor_campo );
        $longitud_campo -= $largo_campo;
        switch ( $orientacion_relleno)
        {
            case 'izquierda':
                for ($i=0; $i < $longitud_campo; $i++)
                {
                    $valor_campo = $caracter_relleno . $valor_campo;
                }
                break;            
            
            case 'derecha':
                for ($i=0; $i < $longitud_campo; $i++)
                {
                    $valor_campo = $valor_campo . $caracter_relleno;
                }
                break;
            
            default:
                # code...
                break;
            
        }

        return $valor_campo;
    }

}
