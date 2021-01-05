<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

/*
	tipo_novedad_tnl: { incapacidad | permiso_remunerado | permiso_no_remunerado | suspencion | vacaciones }
	origen_incapacidad: { comun | laboral }
	clase_incapacidad: { enfermedad_general | licencia_maternidad | licencia_paternidad | accidente_trabajo | enfermedad_profesional}
*/

use DB;

class NovedadTnl extends Model
{
	protected $table = 'nom_novedades_tnl';
	
	protected $fillable = ['nom_concepto_id', 'nom_contrato_id', 'fecha_inicial_tnl', 'fecha_final_tnl', 'cantidad_dias_tnl', 'cantidad_horas_tnl', 'tipo_novedad_tnl', 'codigo_diagnostico_incapacidad', 'numero_incapacidad', 'fecha_expedicion_incapacidad', 'origen_incapacidad', 'clase_incapacidad', 'fecha_incapacidad', 'valor_a_pagar_eps', 'valor_a_pagar_arl', 'valor_a_pagar_afp', 'valor_a_pagar_empresa', 'observaciones', 'estado', 'cantidad_dias_amortizados', 'cantidad_dias_pendientes_amortizar', 'es_prorroga', 'novedad_tnl_anterior_id'];
	
	public $encabezado_tabla = ['Concepto', 'Empleado', 'Tipo novedad', 'Origen', 'Inicio TNL',  'Fin TNL', 'Cant. días TNL', 'Cant. días amortizados', 'Cant. días pend.', 'Observaciones', 'Estado', 'ID', 'Acción'];

	public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

	public $archivo_js = 'assets/js/nomina/novedades_tnl.js';

	public function concepto()
	{
		return $this->belongsTo(NomConcepto::class, 'nom_concepto_id');
	}

	public function contrato()
	{
		return $this->belongsTo(NomContrato::class, 'nom_contrato_id');
	}

	public static function consultar_registros()
	{
	    return NovedadTnl::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_novedades_tnl.nom_concepto_id')
	    				->leftJoin('nom_contratos','nom_contratos.id','=','nom_novedades_tnl.nom_contrato_id')
	    				->leftJoin('core_terceros','core_terceros.id','=','nom_contratos.core_tercero_id')
                        ->where([['nom_novedades_tnl.tipo_novedad_tnl','<>','vacaciones']])
	    				->select(
	    						'nom_conceptos.descripcion AS campo1',
	    						'core_terceros.descripcion AS campo2',
                                'nom_novedades_tnl.tipo_novedad_tnl AS campo3',
                                'nom_novedades_tnl.origen_incapacidad AS campo4',
                                'nom_novedades_tnl.fecha_inicial_tnl AS campo5',
                                'nom_novedades_tnl.fecha_final_tnl AS campo6',
	    						'nom_novedades_tnl.cantidad_dias_tnl AS campo7',
                                'nom_novedades_tnl.cantidad_dias_amortizados AS campo8',
                                'nom_novedades_tnl.cantidad_dias_pendientes_amortizar AS campo9',
	    						'nom_novedades_tnl.observaciones AS campo10',
	    						'nom_novedades_tnl.estado AS campo11',
                                'nom_novedades_tnl.id AS campo12',
                                'nom_novedades_tnl.id AS campo13')
					    ->get()
					    ->toArray();
	}

    public static function opciones_campo_select()
    {
        $opciones = NovedadTnl::where('nom_novedades_tnl.estado','Activo')
                    ->select('nom_novedades_tnl.id','nom_novedades_tnl.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }


    public function validar_eliminacion($id)
    {
        if( NovedadTnl::find($id)->cantidad_dias_amortizados != 0 )
        {
            return 'Ya tiene días amortizados.';
        }

        $tablas_relacionadas = '{
                            "0":{
                                    "tabla":"nom_doc_registros",
                                    "llave_foranea":"novedad_tnl_id",
                                    "mensaje":"Ya tiene movimientos amortizados en registros de documentos de nómina."
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
