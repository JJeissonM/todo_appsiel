<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

class NomDocRegistro extends Model
{
    //protected $table = 'nom_doc_registros';
    protected $fillable = [ 'nom_doc_encabezado_id', 'core_tercero_id', 'nom_contrato_id', 'fecha', 'core_empresa_id', 'porcentaje', 'detalle', 'nom_concepto_id', 'nom_cuota_id', 'nom_prestamo_id', 'novedad_tnl_id', 'orden_trabajo_id', 'cantidad_horas', 'valor_devengo', 'valor_deduccion', 'estado', 'creado_por', 'modificado_por' ];

    public $encabezado_tabla = ['<i style="font-size: 20px;" class="fa fa-check-square-o"></i>', 'Documento', 'Empleado', 'Fecha', 'Detalle', 'Concepto', 'Horas', 'Devengo', 'Deducción', 'Estado', 'ID'];

    public $rutas = [
        'create' => 'web',
        'edit' => 'web/id_fila/edit'
    ];

    public $urls_acciones = '{"edit":"web/id_fila/edit"}';

    public function encabezado_documento()
    {
        return $this->belongsTo(NomDocEncabezado::class, 'nom_doc_encabezado_id');
    }

    public function contrato()
    {
        return $this->belongsTo(NomContrato::class, 'nom_contrato_id');
    }

    public function tercero()
    {
        return $this->belongsTo('App\Core\Tercero', 'core_tercero_id');
    }

    public function concepto()
    {
        return $this->belongsTo(NomConcepto::class, 'nom_concepto_id');
    }

    public function cuota()
    {
        return $this->belongsTo(NomCuota::class, 'nom_cuota_id');
    }

    public function prestamo()
    {
        return $this->belongsTo(NomPrestamo::class, 'nom_prestamo_id');
    }

    public function novedad_tnl()
    {
        return $this->belongsTo(NovedadTnl::class, 'novedad_tnl_id');
    }

    public static function consultar_registros($nro_registros, $search)
    {
        return NomDocRegistro::leftJoin('nom_doc_encabezados', 'nom_doc_encabezados.id', '=', 'nom_doc_registros.nom_doc_encabezado_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_doc_registros.core_tercero_id')
            ->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_doc_registros.nom_concepto_id')
            ->select(
                'nom_doc_encabezados.descripcion AS campo1',
                'core_terceros.descripcion AS campo2',
                'nom_doc_registros.fecha AS campo3',
                'nom_doc_registros.detalle AS campo4',
                'nom_conceptos.descripcion AS campo5',
                'nom_doc_registros.cantidad_horas AS campo6',
                'nom_doc_registros.valor_devengo AS campo7',
                'nom_doc_registros.valor_deduccion AS campo8',
                'nom_doc_registros.estado AS campo9',
                'nom_doc_registros.id AS campo10',
                'nom_doc_registros.id AS campo11'
            )
            ->where("nom_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_doc_registros.fecha", "LIKE", "%$search%")
            ->orWhere("nom_doc_registros.detalle", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_doc_registros.valor_devengo", "LIKE", "%$search%")
            ->orWhere("nom_doc_registros.valor_deduccion", "LIKE", "%$search%")
            ->orWhere("nom_doc_registros.estado", "LIKE", "%$search%")
            ->orWhere("nom_doc_registros.id", "LIKE", "%$search%")
            ->orderBy('nom_doc_registros.created_at', 'DESC')
            ->paginate($nro_registros);
    }

    public static function sqlString($search)
    {
        $string = NomDocRegistro::leftJoin('nom_doc_encabezados', 'nom_doc_encabezados.id', '=', 'nom_doc_registros.nom_doc_encabezado_id')
            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_doc_registros.core_tercero_id')
            ->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_doc_registros.nom_concepto_id')
            ->select(
                'nom_doc_encabezados.descripcion AS DOCUMENTO',
                'core_terceros.descripcion AS EMPLEADO',
                'nom_doc_registros.fecha AS FECHA',
                'nom_doc_registros.detalle AS DETALLE',
                'nom_conceptos.descripcion AS CONCEPTO',
                'nom_doc_registros.cantidad_horas AS HORAS',
                'nom_doc_registros.valor_devengo AS DEVENGO',
                'nom_doc_registros.valor_deduccion AS DEDUCCIÓN',
                'nom_doc_registros.estado AS ESTADO',
                'nom_doc_registros.id AS ID'
            )
            ->where("nom_doc_encabezados.descripcion", "LIKE", "%$search%")
            ->orWhere("core_terceros.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_doc_registros.fecha", "LIKE", "%$search%")
            ->orWhere("nom_doc_registros.detalle", "LIKE", "%$search%")
            ->orWhere("nom_conceptos.descripcion", "LIKE", "%$search%")
            ->orWhere("nom_doc_registros.valor_devengo", "LIKE", "%$search%")
            ->orWhere("nom_doc_registros.valor_deduccion", "LIKE", "%$search%")
            ->orWhere("nom_doc_registros.estado", "LIKE", "%$search%")
            ->orWhere("nom_doc_registros.id", "LIKE", "%$search%")
            ->orderBy('nom_doc_registros.created_at', 'DESC')
            ->toSql();
        return str_replace('?', '"%' . $search . '%"', $string);
    }

    //Titulo para la exportación en PDF y EXCEL
    public static function tituloExport()
    {
        return "LISTADO DE REGISTROS DOCUMENTOS NOMINA";
    }


    /*
        No se usan filtros por Agrupación y Contaro al mismom tiempo 
    */
    public static function listado_acumulados($fecha_desde, $fecha_hasta, $nom_agrupacion_id, $nom_contrato_id, $nom_concepto_id)
    {
        $array_wheres = [ [ 'nom_doc_registros.core_empresa_id', '=', Auth::user()->empresa_id ] ];
        
        if ( $nom_agrupacion_id != 0 )
        {
            $array_wheres = array_merge( $array_wheres, [[ 'nom_agrupacion_tiene_conceptos.nom_agrupacion_id', '=', $nom_agrupacion_id ]] );
        }
        
        if ( $nom_contrato_id != 0 )
        {
            $array_wheres = array_merge( $array_wheres, [[ 'nom_doc_registros.nom_contrato_id', '=', $nom_contrato_id ]] );
        }
        
        if ( $nom_concepto_id != 0 )
        {
            $array_wheres = array_merge( $array_wheres, [
                                                            [ 'nom_doc_registros.nom_concepto_id', '=', $nom_concepto_id ]
                                                        ] );
        }
        
        return NomDocRegistro::leftJoin('nom_agrupacion_tiene_conceptos', 'nom_agrupacion_tiene_conceptos.nom_concepto_id', '=', 'nom_doc_registros.nom_concepto_id')
                            ->where( $array_wheres )
                            ->whereBetween('nom_doc_registros.fecha', [ $fecha_desde, $fecha_hasta ] )
                            ->select('nom_doc_registros.id', 'nom_doc_registros.nom_doc_encabezado_id', 'nom_doc_registros.core_tercero_id', 'nom_doc_registros.nom_contrato_id', 'nom_doc_registros.fecha', 'nom_doc_registros.core_empresa_id', 'nom_doc_registros.porcentaje', 'nom_doc_registros.detalle', 'nom_doc_registros.nom_concepto_id', 'nom_doc_registros.nom_cuota_id', 'nom_doc_registros.nom_prestamo_id', 'nom_doc_registros.novedad_tnl_id', 'nom_doc_registros.cantidad_horas', 'nom_doc_registros.valor_devengo', 'nom_doc_registros.valor_deduccion', 'nom_doc_registros.estado', 'nom_doc_registros.creado_por', 'nom_doc_registros.modificado_por')
                            ->distinct('nom_doc_registros.id')
                            ->get();
    }


    public static function listado_acumulados_documento( $nom_doc_encabezado_id, $nom_agrupacion_id, $nom_contrato_id, $nom_concepto_id)
    {
        $array_wheres = [ [ 'nom_doc_registros.core_empresa_id', '=', Auth::user()->empresa_id ] ];

        $array_wheres = array_merge( $array_wheres, [[ 'nom_doc_registros.nom_doc_encabezado_id', '=', $nom_doc_encabezado_id ]] );
        
        if ( $nom_agrupacion_id != 0 )
        {
            $array_wheres = array_merge( $array_wheres, [[ 'nom_agrupacion_tiene_conceptos.nom_agrupacion_id', '=', $nom_agrupacion_id ]] );
        }
        
        if ( $nom_contrato_id != 0 )
        {
            $array_wheres = array_merge( $array_wheres, [[ 'nom_doc_registros.nom_contrato_id', '=', $nom_contrato_id ]] );
        }
        
        if ( $nom_concepto_id != 0 )
        {
            $array_wheres = array_merge( $array_wheres, [
                                                            [ 'nom_doc_registros.nom_concepto_id', '=', $nom_concepto_id ]
                                                        ] );
        }
        
        return NomDocRegistro::leftJoin('nom_agrupacion_tiene_conceptos', 'nom_agrupacion_tiene_conceptos.nom_concepto_id', '=', 'nom_doc_registros.nom_concepto_id')
                            ->where( $array_wheres )
                            ->select('nom_doc_registros.id', 'nom_doc_registros.nom_doc_encabezado_id', 'nom_doc_registros.core_tercero_id', 'nom_doc_registros.nom_contrato_id', 'nom_doc_registros.fecha', 'nom_doc_registros.core_empresa_id', 'nom_doc_registros.porcentaje', 'nom_doc_registros.detalle', 'nom_doc_registros.nom_concepto_id', 'nom_doc_registros.nom_cuota_id', 'nom_doc_registros.nom_prestamo_id', 'nom_doc_registros.novedad_tnl_id', 'nom_doc_registros.cantidad_horas', 'nom_doc_registros.valor_devengo', 'nom_doc_registros.valor_deduccion', 'nom_doc_registros.estado', 'nom_doc_registros.creado_por', 'nom_doc_registros.modificado_por')
                            ->distinct('nom_doc_registros.id')
                            ->get();
    }

    public static function movimientos_entidades_salud($fecha_desde, $fecha_hasta, array $entidades)
    {
        return NomDocRegistro::leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_doc_registros.nom_concepto_id')
            ->leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_doc_registros.nom_contrato_id')
            ->whereIn('nom_conceptos.modo_liquidacion_id', [12]) // Modo salud
            ->whereIn('nom_contratos.entidad_salud_id', $entidades)
            ->whereBetween('nom_doc_registros.fecha', [$fecha_desde, $fecha_hasta])
            ->orderBy('nom_contratos.id')
            ->get();
    }

    public static function movimientos_entidades_afp($fecha_desde, $fecha_hasta, array $entidades)
    {
        return NomDocRegistro::leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_doc_registros.nom_concepto_id')
            ->leftJoin('nom_contratos', 'nom_contratos.id', '=', 'nom_doc_registros.nom_contrato_id')
            ->whereIn('nom_conceptos.modo_liquidacion_id', [13,10])// Modo pensión y Fondo solidaridad
            ->whereIn('nom_contratos.entidad_pension_id', $entidades)
            ->whereBetween('nom_doc_registros.fecha', [$fecha_desde, $fecha_hasta])
            ->orderBy('nom_contratos.id')
            ->get();
    }

    // PARA UN SOLO REGISTRO
    public function get_campos_adicionales_edit($lista_campos, $registro)
    {

        /*
			3: Cuota
			4: Prestamo
			7: Tiempo NO Laborado
    	*/
        if (in_array($registro->concepto->modo_liquidacion_id, [3, 4, 7])) {
            return [[
                "id" => 999,
                "descripcion" => "",
                "tipo" => "personalizado",
                "name" => "name_1",
                "opciones" => "",
                "value" => '<p>Concepto: <b>' . $registro->concepto->descripcion . '</b> </p> <div class="form-group">                    
                                                    <div class="alert alert-danger">
                                                      <strong>¡Advertencia!</strong>
                                                      <br>
                                                      Por esta opción No puede modificar conceptos con modo de liquidación tipo <b>' . $registro->concepto->modo_liquidacion->descripcion . '</b>. <br> Debe reliquidar las transacciones automáticas.
                                                    </div>
                                                </div>',
                "atributos" => [],
                "definicion" => "",
                "requerido" => 0,
                "editable" => 1,
                "unico" => 0
            ]];
        }

        if ($registro->encabezado_documento->estado != 'Activo') {
            return [[
                "id" => 999,
                "descripcion" => "",
                "tipo" => "personalizado",
                "name" => "name_1",
                "opciones" => "",
                "value" => '<div class="container-fluid"> <p>Documento de nómina: <b>' . $registro->encabezado_documento->descripcion . '</b> </p> <div class="form-group">                    
                                                    <div class="alert alert-danger">
                                                      <strong>¡Advertencia!</strong>
                                                      <br>
                                                      El documento de nómina no esta Activo. Sus registros no pueden ser modificados.
                                                    </div>
                                                </div> </div>',
                "atributos" => [],
                "definicion" => "",
                "requerido" => 0,
                "editable" => 1,
                "unico" => 0
            ]];
        }

        $empleado = NomContrato::where('core_tercero_id', $registro->core_tercero_id)
            ->where('estado', 'Activo')
            ->get()
            ->first();
        if (!is_null($empleado)) {
            array_unshift($lista_campos, [
                "id" => 999,
                "descripcion" => "nombre_empleado",
                "tipo" => "personalizado",
                "name" => "name_2",
                "opciones" => "",
                "value" => '<div class="container-fluid"><h4> Empleado: <small>' . $empleado->tercero->descripcion . '</small></h4><hr></div>',
                "atributos" => [],
                "definicion" => "",
                "requerido" => 0,
                "editable" => 1,
                "unico" => 0
            ]);
        }


        // Encabezado del documento

        if ($registro->encabezado_documento->estado == 'Cerrado') {
            return [[
                "id" => 999,
                "descripcion" => "",
                "tipo" => "personalizado",
                "name" => "name_1",
                "opciones" => "",
                "value" => '<p>Documento: <b>' . $registro->encabezado_documento->descripcion . '</b> </p> <div class="form-group">                    
         	                                        <div class="alert alert-danger">
         											  <strong>¡Advertencia!</strong>
         											  <br>
         											  Documento de nómina está <b>Cerrado</b>. No se pueden modificar sus registros.
         											</div>
         	                                    </div>',
                "atributos" => [],
                "definicion" => "",
                "requerido" => 0,
                "editable" => 1,
                "unico" => 0
            ]];
        }

        array_unshift($lista_campos, [
            "id" => 999,
            "descripcion" => "descripcion_documento",
            "tipo" => "personalizado",
            "name" => "name_3",
            "opciones" => "",
            "value" => '<div class="container-fluid"><h4> Documento nómina: <small>' . $registro->encabezado_documento->descripcion . '</small></h4><hr></div>',
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);

        // Nota
        array_unshift($lista_campos, [
            "id" => 999,
            "descripcion" => "separador",
            "tipo" => "personalizado",
            "name" => "name_4",
            "opciones" => "",
            "value" => '&nbsp;',
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);
        array_unshift($lista_campos, [
            "id" => 999,
            "descripcion" => "nota",
            "tipo" => "personalizado",
            "name" => "name_5",
            "opciones" => "",
            "value" => '<div class="container-fluid"> <span style="color:red;"> (Puede ingresar cero en todos los campos para eliminar el registro) </span> </div>',
            "atributos" => [],
            "definicion" => "",
            "requerido" => 0,
            "editable" => 1,
            "unico" => 0
        ]);


        return $lista_campos;
    }

    public function update_adicional($datos, $id)
    {
        $registro = NomDocRegistro::find($id);
        if ( ($datos['valor_devengo'] + $datos['valor_deduccion'] + $datos['cantidad_horas'] ) == 0)
        {
            $documento = $registro->encabezado_documento;
            $documento->total_devengos = NomDocRegistro::where('nom_doc_encabezado_id',$documento->id)->sum('valor_devengo');
            $documento->total_deducciones = NomDocRegistro::where('nom_doc_encabezado_id',$documento->id)->sum('valor_deduccion');
            $documento->save();

            $registro->delete();
        }
    }
}
