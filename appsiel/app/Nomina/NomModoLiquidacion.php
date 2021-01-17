<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

class NomModoLiquidacion extends Model
{
    /*
            ID  Descripción
            18  Parafiscales    Provisiones hechas por la empresa: SENA, ICBF y Caja de compesanción.     
            17  Cesantías pagadas   Las que se pagan al empleado en la terminación de contrato.
            16  Intereses de cesantías 
            15  Cesantías consignadas   Cesantías que se consignan anualmente al fondo.
            14  Prima Legal Pagada semestralmente     
            13  Pensión obligatoria AFP. Administradoras Fondos de Pensión 
            12  Salud obligatoria   Descuentos EPS. Entidades promotoras de Salud
            11  Retefuente
            10  FondoSolidaridadPensional       
            9   Prestaciones sociales
            8   Seguridad social
            7   Tiempo NO Laborado
            6   Auxilio de transporte
            5   Cruce de saldos de CxC
            4   Préstamo
            3   Cuota
            2   Manual
            1   Tiempo Laborado
    */

    protected $table = 'nom_modos_liquidacion';
	protected $fillable = ['descripcion','detalle', 'estado'];
	public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Descripción', 'Detalle', 'Estado'];
    public static function consultar_registros($nro_registros)
    {
        return NomModoLiquidacion::select(
            'nom_modos_liquidacion.descripcion AS campo1',
            'nom_modos_liquidacion.detalle AS campo2',
            'nom_modos_liquidacion.estado AS campo3',
            'nom_modos_liquidacion.id AS campo4'
        )
            ->orderBy('nom_modos_liquidacion.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function opciones_campo_select()
    {
        $opciones = NomModoLiquidacion::where('estado','Activo')->orderBy('descripcion')->get();

        $vec['']='';
        foreach ($opciones as $opcion)
        {
            $vec[$opcion->id] = $opcion->descripcion;
        }

        return $vec;
    }
}
