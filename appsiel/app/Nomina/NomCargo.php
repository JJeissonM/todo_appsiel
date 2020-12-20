<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

class NomCargo extends Model
{
    //protected $table = 'nom_cargos';
	protected $fillable = ['descripcion', 'estado', 'cargo_padre_id', 'rango_salarial_id'];

	public $encabezado_tabla = [ 'ID', 'Descripción', 'Estado', 'Acción'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

	public static function consultar_registros()
	{
	    return NomCargo::select(
                                        'nom_cargos.id AS campo1',
                                        'nom_cargos.descripcion AS campo2',
                                        'nom_cargos.estado AS campo3',
                                        'nom_cargos.id AS campo4')
                	    ->get()
                	    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = NomCargo::where('estado','Activo')->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"nom_contratos",
                                    "llave_foranea":"cargo_id",
                                    "mensaje":"Está asociado al Contrato de un empleado."
                                },
                            "1":{
                                    "tabla":"nom_cargos",
                                    "llave_foranea":"cargo_padre_id",
                                    "mensaje":"Está asociado como Cargo padre de otro Cargo."
                                }
                        }';
        $tablas = json_decode( $tablas_relacionadas );
        foreach($tablas AS $una_tabla)
        { 
            $registro = DB::table( $una_tabla->tabla )->where( $una_tabla->llave_foranea, $id )->get();

            if ( !empty($registro) )
            {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
