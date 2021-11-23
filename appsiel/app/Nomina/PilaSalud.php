<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class PilaSalud extends Model
{
    protected $table = 'nom_pila_liquidacion_salud';

    protected $fillable = ['planilla_generada_id', 'nom_contrato_id', 'fecha_final_mes', 'codigo_entidad_salud', 'dias_cotizados_salud', 'ibc_salud', 'tarifa_salud', 'cotizacion_salud', 'valor_upc_adicional_salud', 'total_cotizacion_salud', 'empleado_planilla_id'];
    
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Planilla generada', 'Empleado', 'Fecha PILA', 'Codigo Entidad', 'Dias cotizados', 'IBC', 'Tarifa', 'Cotizacion', 'Valor UPC Adicional', 'Total Cotizacion'];

    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","show":"web/id_fila"}';

    public function entidad()
    {
        return NomEntidad::where('codigo_nacional', $this->codigo_entidad_salud)->get()->first();
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return PilaSalud::leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_pila_liquidacion_salud.nom_contrato_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->select(
                'nom_pila_liquidacion_salud.planilla_generada_id AS campo1',
                'core_terceros.descripcion AS campo2',
                'nom_pila_liquidacion_salud.fecha_final_mes AS campo3',
                'nom_pila_liquidacion_salud.codigo_entidad_salud AS campo4',
                'nom_pila_liquidacion_salud.dias_cotizados_salud AS campo5',
                'nom_pila_liquidacion_salud.ibc_salud AS campo6',
                'nom_pila_liquidacion_salud.tarifa_salud AS campo7',
                'nom_pila_liquidacion_salud.cotizacion_salud AS campo8',
                'nom_pila_liquidacion_salud.valor_upc_adicional_salud AS campo9',
                'nom_pila_liquidacion_salud.total_cotizacion_salud AS campo10',
                'nom_pila_liquidacion_salud.id AS campo11'
            )
            ->where("nom_pila_liquidacion_salud.planilla_generada_id", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.fecha_final_mes", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.codigo_entidad_salud", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.dias_cotizados_salud", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.ibc_salud", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.tarifa_salud", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.cotizacion_salud", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.valor_upc_adicional_salud", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.total_cotizacion_salud", "LIKE", "%$search%")

            ->orderBy('nom_pila_liquidacion_salud.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = PilaSalud::leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_pila_liquidacion_salud.nom_contrato_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
            ->select(
                'nom_pila_liquidacion_salud.planilla_generada_id AS PLANILLA_GENERADA',
                'core_terceros.descripcion AS EMPLEADO',
                'nom_pila_liquidacion_salud.fecha_final_mes AS FECHA_PILA',
                'nom_pila_liquidacion_salud.codigo_entidad_salud AS CODIGO_ENTIDAD',
                'nom_pila_liquidacion_salud.dias_cotizados_salud AS DIAS_COTIZADOS',
                'nom_pila_liquidacion_salud.ibc_salud AS IBC',
                'nom_pila_liquidacion_salud.tarifa_salud AS TARIFA',
                'nom_pila_liquidacion_salud.cotizacion_salud AS COTIZACION',
                'nom_pila_liquidacion_salud.valor_upc_adicional_salud AS VALOR_UPC_ADICIONAL',
                'nom_pila_liquidacion_salud.total_cotizacion_salud AS TOTAL_COTIZACION'
            )
            ->where("nom_pila_liquidacion_salud.planilla_generada_id", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.fecha_final_mes", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.codigo_entidad_salud", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.dias_cotizados_salud", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.ibc_salud", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.tarifa_salud", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.cotizacion_salud", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.valor_upc_adicional_salud", "LIKE", "%$search%")
            ->orWhere("nom_pila_liquidacion_salud.total_cotizacion_salud", "LIKE", "%$search%")

            ->orderBy('nom_pila_liquidacion_salud.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE PILA SALUD";
    }

    public static function opciones_campo_select()
    {
        $opciones = PilaSalud::where('nom_pila_liquidacion_salud.estado', 'Activo')
            ->select('nom_pila_liquidacion_salud.id', 'nom_pila_liquidacion_salud.descripcion')
            ->get();

        $vec[''] = '';
        foreach ($opciones as $opcion) {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
