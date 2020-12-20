<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;

class NomConcepto extends Model
{
    //protected $table = 'nom_conceptos';

    /*
        naturaleza: devengo, deduccion, provision
    */
	protected $fillable = ['modo_liquidacion_id','naturaleza', 'porcentaje_sobre_basico', 'valor_fijo', 'descripcion', 'abreviatura', 'forma_parte_basico', 'nom_agrupacion_id', 'estado'];

	public $encabezado_tabla = [ 'ID', 'Modo Liquidación', 'Descripción', 'Abreviatura', '% del básico', 'Vlr. Fijo', 'Naturaleza', 'Agrupación', 'Estado', 'Acción'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public function modo_liquidacion()
    {
        return $this->belongsTo( NomModoLiquidacion::class, 'modo_liquidacion_id');
    }

    public function agrupacion()
    {
        return $this->belongsTo( AgrupacionConcepto::class, 'nom_agrupacion_id');
    }

    public function get_valor_hora_porcentaje_sobre_basico( $salario_x_hora, $cantidad_horas )
    {
        //$salario_x_hora = $sueldo / config('nomina')['horas_laborales'];

        if ( $this->porcentaje_sobre_basico < 1 )
        {
            // Fraccion del Salario
            $valor_a_liquidar = ( $salario_x_hora * $this->porcentaje_sobre_basico ) * $cantidad_horas;
        }else{
            // Valor completo Salario + Adicional
            $valor_a_liquidar = $salario_x_hora * ( 1 + $this->porcentaje_sobre_basico / 100 ) * $cantidad_horas;
        }
        
        return $valor_a_liquidar;
    }
	
    public static function consultar_registros()
	{
	    return NomConcepto::leftJoin('nom_modos_liquidacion', 'nom_modos_liquidacion.id', '=', 'nom_conceptos.modo_liquidacion_id')
                            ->leftJoin('nom_agrupaciones_conceptos', 'nom_agrupaciones_conceptos.id', '=', 'nom_conceptos.nom_agrupacion_id')
                            ->select(
                                    'nom_conceptos.id AS campo1',
                                    'nom_modos_liquidacion.descripcion AS campo2',
                                    'nom_conceptos.descripcion AS campo3',
                                    'nom_conceptos.abreviatura AS campo4',
                                    'nom_conceptos.porcentaje_sobre_basico AS campo5',
                                    'nom_conceptos.valor_fijo AS campo6',
                                    'nom_conceptos.naturaleza AS campo7',
                                    'nom_agrupaciones_conceptos.descripcion AS campo8',
                                    'nom_conceptos.estado AS campo9',
                                    'nom_conceptos.id AS campo10')
                		    ->get()
                		    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = NomConcepto::where('estado','Activo')->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function conceptos_del_documento($encabezado_doc_id)
    {
        return NomConcepto::leftJoin('nom_doc_registros','nom_doc_registros.nom_concepto_id','=','nom_conceptos.id')
                            ->where('nom_doc_registros.nom_doc_encabezado_id',$encabezado_doc_id)
                            ->select('nom_doc_registros.nom_concepto_id','nom_conceptos.descripcion','nom_conceptos.abreviatura','nom_conceptos.naturaleza')
                            ->distinct('nom_doc_registros.nom_concepto_id')
                            ->orderBy('nom_conceptos.id','ASC')
                            ->get();
    }


    public function validar_eliminacion($id)
    {
        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"nom_agrupacion_tiene_conceptos",
                                    "llave_foranea":"nom_concepto_id",
                                    "mensaje":"Está asociado a una agrupación de conceptos."
                                },
                            "1":{
                                    "tabla":"nom_cuotas",
                                    "llave_foranea":"nom_concepto_id",
                                    "mensaje":"Está asociado al registro de una Cuota de un empleado."
                                },
                            "2":{
                                    "tabla":"nom_doc_registros",
                                    "llave_foranea":"nom_concepto_id",
                                    "mensaje":"Tienes registros en documentos de nómina ."
                                },
                            "3":{
                                    "tabla":"nom_equivalencias_contables",
                                    "llave_foranea":"nom_concepto_id",
                                    "mensaje":"Está asociado a una equivalencia contable."
                                },
                            "4":{
                                    "tabla":"nom_movimientos",
                                    "llave_foranea":"nom_concepto_id",
                                    "mensaje":"Tiene registros en movimientos de nómina."
                                },
                            "5":{
                                    "tabla":"nom_novedades_tnl",
                                    "llave_foranea":"nom_concepto_id",
                                    "mensaje":"Está asociado a una Novedad de Tiempo No laborado (TNL)."
                                },
                            "6":{
                                    "tabla":"nom_prestamos",
                                    "llave_foranea":"nom_concepto_id",
                                    "mensaje":"Está asociado al registro de un Prestamo de un empleado."
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
