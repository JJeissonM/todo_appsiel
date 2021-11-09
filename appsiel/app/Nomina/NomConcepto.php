<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use DB;
use App\Nomina\EquivalenciaContable;
use App\Nomina\NominaElectronica\ConceptoDian;

class NomConcepto extends Model
{
    //protected $table = 'nom_conceptos';

    /*
        naturaleza: devengo, deduccion, provision

        forma_parte_basico: Los conceptos que forman parte integral del básico son aquellas que sustituyen el sueldo y afectan la continuidad de este. Ejemplo: permisos remunerados, licencias remuneradas, vacaciones, incapacidades y otros los cuales en su pago disminuyen el valor del sueldo o jornal a pagar.

    */
    protected $fillable = ['modo_liquidacion_id', 'naturaleza', 'porcentaje_sobre_basico', 'valor_fijo', 'descripcion', 'abreviatura', 'forma_parte_basico', 'nom_agrupacion_id', 'cpto_dian_id ', 'estado'];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Modo Liquidación', 'Descripción', 'Abreviatura', '% del básico', 'Vlr. Fijo', 'Naturaleza', 'Parte del básico', 'Agrupación', 'Estado'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    // FALTA AGREGAR EL ARCHIVO CONCEPTOS.JS. VALIDAR CAMPOS REQUERIDOS

    public function modo_liquidacion()
    {
        return $this->belongsTo(NomModoLiquidacion::class, 'modo_liquidacion_id');
    }

    public function agrupacion()
    {
        return $this->belongsTo(AgrupacionConcepto::class, 'nom_agrupacion_id');
    }

    public function cpto_dian()
    {
        return $this->belongsTo(ConceptoDian::class, 'cpto_dian_id');
    }

    public function equivalencia_contable( $nom_grupo_empleado_id )
    {
        $equivalencias_del_concepto = EquivalenciaContable::where( 'nom_concepto_id', $this->id )
                                                        ->get();
        
        foreach ($equivalencias_del_concepto as $equivalecia )
        {
            if ( $equivalecia->nom_grupo_empleado_id == $nom_grupo_empleado_id )
            {
                return $equivalecia;
            }
        }

        return $equivalencias_del_concepto->first();
    }

    public function get_valor_hora_porcentaje_sobre_basico($salario_x_hora, $cantidad_horas)
    {
        //$salario_x_hora = $sueldo / config('nomina')['horas_laborales'];

        if ($this->porcentaje_sobre_basico < 1) {
            // Fraccion del Salario
            $valor_a_liquidar = ($salario_x_hora * $this->porcentaje_sobre_basico) * $cantidad_horas;
        } else {
            // Valor completo Salario + Adicional
            $valor_a_liquidar = $salario_x_hora * (1 + $this->porcentaje_sobre_basico / 100) * $cantidad_horas;
        }

        return $valor_a_liquidar;
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return NomConcepto::leftJoin('nom_modos_liquidacion', 'nom_modos_liquidacion.id', '=', 'nom_conceptos.modo_liquidacion_id')
            ->leftJoin('nom_agrupaciones_conceptos', 'nom_agrupaciones_conceptos.id', '=', 'nom_conceptos.nom_agrupacion_id')
            ->select(
                'nom_modos_liquidacion.descripcion AS campo1',
                'nom_conceptos.descripcion AS campo2',
                'nom_conceptos.abreviatura AS campo3',
                'nom_conceptos.porcentaje_sobre_basico AS campo4',
                'nom_conceptos.valor_fijo AS campo5',
                'nom_conceptos.naturaleza AS campo6',
                DB::raw('IF(nom_conceptos.forma_parte_basico=0,REPLACE(nom_conceptos.forma_parte_basico,0,"No"),REPLACE(nom_conceptos.forma_parte_basico,1,"Si")) AS campo7'),
                'nom_agrupaciones_conceptos.descripcion AS campo8',
                'nom_conceptos.estado AS campo9',
                'nom_conceptos.id AS campo10'
            )
            ->where("nom_modos_liquidacion.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.abreviatura", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.porcentaje_sobre_basico", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.valor_fijo", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.naturaleza", "LIKE", "%$search%")
            ->orWhere("nom_agrupaciones_conceptos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.estado", "LIKE", "%$search%")
            ->orderBy('nom_conceptos.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = NomConcepto::leftJoin('nom_modos_liquidacion', 'nom_modos_liquidacion.id', '=', 'nom_conceptos.modo_liquidacion_id')
            ->leftJoin('nom_agrupaciones_conceptos', 'nom_agrupaciones_conceptos.id', '=', 'nom_conceptos.nom_agrupacion_id')
            ->select(
                'nom_modos_liquidacion.descripcion AS MODO_LIQUIDACIÓN',
                'nom_conceptos.descripcion AS DESCRIPCIÓN',
                'nom_conceptos.abreviatura AS ABREVIATURA',
                'nom_conceptos.porcentaje_sobre_basico AS %_DEL_BÁSICO',
                'nom_conceptos.valor_fijo AS VLR_FIJO',
                'nom_conceptos.naturaleza AS NATURALEZA',
                'nom_agrupaciones_conceptos.descripcion AS AGRUPACIÓN',
                'nom_conceptos.forma_parte_basico AS FORMA_PARTE_BASICO',
                'nom_conceptos.estado AS ESTADO',
                'nom_conceptos.id AS ID'
            )
            ->where("nom_modos_liquidacion.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.abreviatura", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.porcentaje_sobre_basico", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.valor_fijo", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.naturaleza", "LIKE", "%$search%")
            ->orWhere("nom_agrupaciones_conceptos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.estado", "LIKE", "%$search%")
            ->orderBy('nom_conceptos.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE CONCEPTOS";
    }

    public static function opciones_campo_select()
    {
        $opciones = NomConcepto::where('estado', 'Activo')->orderBy('descripcion')->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }

    public static function conceptos_del_documento($encabezado_doc_id)
    {
        return NomConcepto::leftJoin('nom_doc_registros', 'nom_doc_registros.nom_concepto_id', '=', 'nom_conceptos.id')
            ->where('nom_doc_registros.nom_doc_encabezado_id', $encabezado_doc_id)
            ->select('nom_doc_registros.nom_concepto_id', 'nom_conceptos.descripcion', 'nom_conceptos.abreviatura', 'nom_conceptos.naturaleza')
            ->distinct('nom_doc_registros.nom_concepto_id')
            ->orderBy('nom_conceptos.id', 'ASC')
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
        $tablas = json_decode($tablas_relacionadas);
        foreach ($tablas as $una_tabla) {
            $registro = DB::table($una_tabla->tabla)->where($una_tabla->llave_foranea, $id)->get();

            if (!empty($registro)) {
                return $una_tabla->mensaje;
            }
        }

        return 'ok';
    }
}
