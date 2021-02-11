<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class ParametrosRetefuenteEmpleado extends Model
{
    protected $table = 'nom_parametros_retefuente_empleados';
    
    protected $fillable = ['nom_contrato_id', 'fecha_final_promedios', 'procedimiento', 'valor_base_depurada', 'renta_trabajo_exenta', 'sub_total', 'base_retencion_pesos', 'base_retencion_uvts', 'rango_tabla', 'valor_retencion_uvts', 'porcentaje_fijo', 'deduccion_pago_terceros_alimentacion', 'deduccion_viaticos_ocacionales', 'deduccion_medios_transporte', 'deduccion_aportes_pension_voluntaria', 'deduccion_ahorros_cuentas_afc', 'deduccion_rentas_trabajo_exentas', 'deduccion_intereses_vivienda', 'deduccion_salud_prepagada', 'deduccion_por_dependientes', 'estado'];
    
    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>','Empleado', 'Fecha actualización', 'No. Proc.', 'Vlr. base depurada', 'Renta trabajo exenta (25%)', 'Subtotal', 'Base retención ($)', 'Base retención (UVT)', 'Rango tabla retenciones (Art. 383)', 'Vlr. retención UVT', 'Porcentaje fijo', 'Estado'];
    
    public $urls_acciones = '{"create":"web/create","edit":"web/id_fila/edit","eliminar":"web_eliminar/id_fila"}';

    public static function consultar_registros($nro_registros, $search)
    {
        $collection =  ParametrosRetefuenteEmpleado::leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_parametros_retefuente_empleados.nom_contrato_id')
			->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_contratos.core_tercero_id')
        								->select(
        										'core_terceros.descripcion AS campo1',
        										'nom_parametros_retefuente_empleados.fecha_final_promedios AS campo2',
        										'nom_parametros_retefuente_empleados.procedimiento AS campo3',
        										'nom_parametros_retefuente_empleados.valor_base_depurada AS campo4',
        										'nom_parametros_retefuente_empleados.renta_trabajo_exenta AS campo5',
        										'nom_parametros_retefuente_empleados.sub_total AS campo6',
        										'nom_parametros_retefuente_empleados.base_retencion_pesos AS campo7',
        										'nom_parametros_retefuente_empleados.base_retencion_uvts AS campo8',
        										'nom_parametros_retefuente_empleados.rango_tabla AS campo9',
        										'nom_parametros_retefuente_empleados.valor_retencion_uvts AS campo10',
        										'nom_parametros_retefuente_empleados.porcentaje_fijo AS campo11',
        										'nom_parametros_retefuente_empleados.estado AS campo12',
        										'nom_parametros_retefuente_empleados.id AS campo13')
        								->paginate($nro_registros);
    	if (count($collection) > 0)
        {
            foreach ($collection as $c)
            {
                $c->campo4 = '$' . number_format( $c->campo4, 0, ',', '.' );
                $c->campo5 = '$' . number_format( $c->campo5, 0, ',', '.' );
                $c->campo6 = '$' . number_format( $c->campo6, 0, ',', '.' );
                $c->campo7 = '$' . number_format( $c->campo7, 0, ',', '.' );
                $c->campo8 = number_format( $c->campo8, 0, ',', '.' );
                $c->campo10 = number_format( $c->campo10, 2, ',', '.' );
                $c->campo11 = number_format( $c->campo11, 2, ',', '.' ) . '%';
            }
        }
        
        return $collection;
    }

    public static function sqlString($search)
    {
        $string = ParametrosRetefuenteEmpleado::select('nom_parametros_retefuente_empleados.nom_contrato_id AS campo1', 'nom_parametros_retefuente_empleados.fecha_final_promedios AS campo2', 'nom_parametros_retefuente_empleados.procedimiento AS campo3', 'nom_parametros_retefuente_empleados.valor_base_depurada AS campo4', 'nom_parametros_retefuente_empleados.renta_trabajo_exenta AS campo5', 'nom_parametros_retefuente_empleados.sub_total AS campo6', 'nom_parametros_retefuente_empleados.base_retencion_pesos AS campo7', 'nom_parametros_retefuente_empleados.base_retencion_uvts AS campo8', 'nom_parametros_retefuente_empleados.rango_tabla AS campo9', 'nom_parametros_retefuente_empleados.valor_retencion_uvts AS campo10', 'nom_parametros_retefuente_empleados.porcentaje_fijo AS campo11', 'nom_parametros_retefuente_empleados.estado AS campo12', 'nom_parametros_retefuente_empleados.id AS campo13')
        ->toSql();
        return str_replace('?', '""%' . $search . '%""', $string);
    }

    public static function tituloExport()
    {
        return "LISTADO DE PARAMETROS RETEFUENTE";
    }

    public static function opciones_campo_select()
    {
        $opciones = ParametrosRetefuenteEmpleado::where('nom_parametros_retefuente_empleados.estado','Activo')
                    ->select('nom_parametros_retefuente_empleados.id','nom_parametros_retefuente_empleados.descripcion')
                    ->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
