<?php

namespace App\Nomina;

use Illuminate\Database\Eloquent\Model;

use Auth;
use DB;

class NomDocRegistro extends Model
{
    //protected $table = 'nom_doc_registros';
	protected $fillable = [ 'nom_doc_encabezado_id', 'core_tercero_id', 'nom_contrato_id', 'fecha', 'core_empresa_id', 'porcentaje', 'detalle', 'nom_concepto_id', 'nom_cuota_id', 'nom_prestamo_id', 'novedad_tnl_id', 'cantidad_horas', 'valor_devengo', 'valor_deduccion', 'estado', 'creado_por', 'modificado_por'];

	public $encabezado_tabla = ['Documento', 'Empleado', 'Fecha', 'Detalle', 'Concepto', 'Cant. horas', 'Devengo', 'Deducción', 'Estado', 'ID', 'Acción'];

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
						
	public static function consultar_registros()
	{
	    return NomDocRegistro::leftJoin('nom_doc_encabezados', 'nom_doc_encabezados.id', '=', 'nom_doc_registros.nom_doc_encabezado_id')
						            ->leftJoin('core_terceros', 'core_terceros.id', '=', 'nom_doc_registros.core_tercero_id')
						            ->leftJoin('nom_conceptos', 'nom_conceptos.id', '=', 'nom_doc_registros.nom_concepto_id')
						            ->select(
						            			'nom_doc_encabezados.descripcion AS campo1',
                                                DB::raw('CONCAT(core_terceros.numero_identificacion, " - ", core_terceros.descripcion) AS campo2'),
						            			'nom_doc_registros.fecha AS campo3',
						            			'nom_doc_registros.detalle AS campo4',
						            			DB::raw('CONCAT(nom_conceptos.id, " - ", nom_conceptos.descripcion) AS campo5'),
                                                'nom_doc_registros.cantidad_horas AS campo6',
                                                'nom_doc_registros.valor_devengo AS campo7',
						            			'nom_doc_registros.valor_deduccion AS campo8',
						            			'nom_doc_registros.estado AS campo9',
						            			'nom_doc_registros.id AS campo10',
						            			'nom_doc_registros.id AS campo11')
								    ->get()
								    ->toArray();
	}


    public static function listado_acumulados( $fecha_desde, $fecha_hasta, $nom_agrupacion_id)
    {
        if ( $nom_agrupacion_id == '' )
        {
            return NomDocRegistro::where('nom_doc_registros.core_empresa_id', Auth::user()->empresa_id)
                                ->whereBetween('nom_doc_registros.fecha', [$fecha_desde, $fecha_hasta])
                                ->get();
        }

        return NomDocRegistro::leftJoin('nom_agrupacion_tiene_conceptos','nom_agrupacion_tiene_conceptos.nom_concepto_id','=','nom_doc_registros.nom_concepto_id')
                            ->where('nom_doc_registros.core_empresa_id', Auth::user()->empresa_id)
                            ->whereBetween('nom_doc_registros.fecha', [$fecha_desde, $fecha_hasta])
                            ->where('nom_agrupacion_tiene_conceptos.nom_agrupacion_id', $nom_agrupacion_id)
                            ->get();
                            
    }

    public static function movimientos_entidades_salud( $fecha_desde, $fecha_hasta, array $entidades)
    {
        return NomDocRegistro::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_doc_registros.nom_concepto_id')
                                                ->leftJoin('nom_contratos','nom_contratos.id','=','nom_doc_registros.nom_contrato_id')
                                                ->where('nom_conceptos.modo_liquidacion_id',12)
                                                ->whereIn('nom_contratos.entidad_salud_id',$entidades)
                                                ->whereBetween('nom_doc_registros.fecha', [$fecha_desde, $fecha_hasta])
                                                ->orderBy('nom_contratos.id')
                                                ->get();                            
    }

    public static function movimientos_entidades_afp( $fecha_desde, $fecha_hasta, array $entidades)
    {
        return NomDocRegistro::leftJoin('nom_conceptos','nom_conceptos.id','=','nom_doc_registros.nom_concepto_id')
                                                ->leftJoin('nom_contratos','nom_contratos.id','=','nom_doc_registros.nom_contrato_id')
                                                ->where('nom_conceptos.modo_liquidacion_id',13)
                                                ->whereIn('nom_contratos.entidad_pension_id',$entidades)
                                                ->whereBetween('nom_doc_registros.fecha', [$fecha_desde, $fecha_hasta])
                                                ->orderBy('nom_contratos.id')
                                                ->get();                            
    }


    public function get_campos_adicionales_edit( $lista_campos, $registro )
    {

    	/*
			3: Cuota
			4: Prestamo
			7: Tiempo NO Laborado
    	*/
        if( in_array( $registro->concepto->modo_liquidacion_id, [3,4,7]) ) 
        {
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

        if( $registro->encabezado_documento->estado != 'Activo' ) 
        {
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

        $empleado = NomContrato::where('core_tercero_id',$registro->core_tercero_id)
        							->where('estado','Activo')
        							->get()
        							->first();
        if (!is_null($empleado) ) 
        {
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
                                        ] );
		}

        
        // Encabezado del documento

        if ( $registro->encabezado_documento->estado == 'Cerrado' )
        {
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
                                    ] );

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
                                    ] );
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
                                    ] );				
        	
        
        return $lista_campos;
    }

    public function update_adicional( $datos, $id )
    {
    	if ( ( $datos['valor_devengo'] + $datos['valor_deduccion'] + $datos['cantidad_horas'] ) == 0 ) 
    	{
    		NomDocRegistro::find( $id )->delete();
    	}
    }
}