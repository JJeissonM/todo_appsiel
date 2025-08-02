<?php 

namespace App\Ventas\Services;

use App\Core\Empresa;
use App\Core\TransaccionOtrosCampos;
use App\CxC\CxcAbono;
use App\Sistema\Aplicacion;
use App\Sistema\Modelo;
use App\Sistema\TipoTransaccion;
use App\Tesoreria\TesoMovimiento;
use App\Ventas\ResolucionFacturacion;
use App\Ventas\VtasDocEncabezado;
use App\VentasPos\Services\DatafonoService;
use App\VentasPos\Services\TipService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\View;

class PrintServices
{
    protected $doc_encabezado;
    protected $empresa, $app, $modelo, $transaccion, $variables_url;

    protected $datos;    

    public function set_variables_globales()
    {
        $this->empresa = Empresa::find( Auth::user()->empresa_id );
        $this->app = Aplicacion::find( Input::get('id') );
        $this->modelo = Modelo::find( Input::get('id_modelo') );
        $this->transaccion = TipoTransaccion::find( Input::get('id_transaccion') );

        $this->variables_url = '?id='.Input::get('id').'&id_modelo='.Input::get('id_modelo').'&id_transaccion='.Input::get('id_transaccion');
    }
    
    public function generar_documento_vista( $encabezado_doc_id, $ruta_vista )
    {
        $this->set_variables_globales();
        
        $this->doc_encabezado = app( $this->transaccion->modelo_encabezados_documentos )->get_registro_impresion( $encabezado_doc_id );
        
        $doc_registros = app( $this->transaccion->modelo_registros_documentos )->get_registros_impresion( $this->doc_encabezado->id );

        $doc_encabezado = $this->doc_encabezado;

        $doc_encabezado->documento_transaccion_prefijo_consecutivo = $this->get_documento_transaccion_prefijo_consecutivo( $doc_encabezado );
        $empresa = $this->empresa;
        $plantilla_factura_pos_default = config('ventas_pos.plantilla_factura_pos_default');
        if ( $doc_encabezado->pdv != null )
        {
            if ( $doc_encabezado->pdv->direccion != '' )
            {
                $empresa->direccion1 = $doc_encabezado->pdv->direccion;
                $empresa->telefono1 = $doc_encabezado->pdv->telefono;
                $empresa->email = $doc_encabezado->pdv->email;
            }
            if ($doc_encabezado->pdv->plantilla_factura_pos_default != null && $doc_encabezado->pdv->plantilla_factura_pos_default != '') {
                $plantilla_factura_pos_default = $doc_encabezado->pdv->plantilla_factura_pos_default;
            }
        }

        $resolucion = ResolucionFacturacion::where('tipo_doc_app_id',$doc_encabezado->core_tipo_doc_app_id)->where('estado','Activo')->get()->last();

        $etiquetas = $this->get_etiquetas();

        $abonos = CxcAbono::get_abonos_documento( $doc_encabezado );

        $docs_relacionados = VtasDocEncabezado::get_documentos_relacionados( $doc_encabezado );

        $otroscampos = TransaccionOtrosCampos::where('core_tipo_transaccion_id',$this->doc_encabezado->core_tipo_transaccion_id)->get()->first();

        $datos_factura = '';
        $cliente = '';
        $tipo_doc_app = '';
        $pdv_descripcion = '';
        if ( $ruta_vista == 'ventas.formatos_impresion.pos') {

            $ruta_vista = 'ventas_pos.formatos_impresion.' . $plantilla_factura_pos_default;
            
            $valor_propina = ( new TipService() )->get_tip_amount($doc_encabezado);
            $total_factura_mas_propina = $doc_encabezado->valor_total;
            if ( $valor_propina != 0) {
                $total_factura_mas_propina = $doc_encabezado->valor_total + $valor_propina;
            }

            $valor_datafono = ( new DatafonoService() )->get_datafono_amount($doc_encabezado);
            $total_factura_mas_datafono = $doc_encabezado->valor_total;
            if ( $valor_datafono != 0) {
                $total_factura_mas_datafono = $doc_encabezado->valor_total + $valor_datafono;
            }

            $lbl_ajuste_al_peso = round($doc_encabezado->valor_ajuste_al_peso,0);

            $lbl_total_factura = $doc_encabezado->valor_total + $lbl_ajuste_al_peso + $doc_encabezado->valor_total_bolsas;
            
            $datos_factura = (object)[
                'core_tipo_transaccion_id' => $doc_encabezado->core_tipo_transaccion_id,
                'lbl_consecutivo_doc_encabezado' => $doc_encabezado->consecutivo,
                'lbl_fecha' => $doc_encabezado->fecha,
                'lbl_hora' => explode(" ", $doc_encabezado->created_at)[1],
                'lbl_condicion_pago' => $doc_encabezado->condicion_pago,
                'lbl_fecha_vencimiento' => $doc_encabezado->fecha_vencimiento,
                'lbl_descripcion_doc_encabezado' => $doc_encabezado->descripcion,
                'lbl_total_factura' => '$' . number_format($lbl_total_factura, 0,',','.'),
                'lbl_total_propina' => '$' . number_format( $valor_propina, 0, ',' , '.'),
                'total_factura_mas_propina' => '$' . number_format( $total_factura_mas_propina, 0, ',' , '.'),
                'lbl_total_datafono' => '$' . number_format( $valor_datafono, 0, ',' , '.'),
                'total_factura_mas_datafono' => '$' . number_format( $valor_datafono, 0, ',' , '.'),
                'lbl_ajuste_al_peso' => $lbl_ajuste_al_peso,
                'lbl_total_recibido' => $doc_encabezado->total_efectivo_recibido,
                'lbl_total_cambio' => $doc_encabezado->valor_total_cambio,
                'lbl_valor_total_bolsas' => $doc_encabezado->valor_total_bolsas,
                'lbl_creado_por_fecha_y_hora' => $doc_encabezado->created_at,
                'lineas_registros' => View::make( 'ventas.formatos_impresion.cuerpo_tabla_lineas_registros', compact('doc_registros') )->render(),
                'lineas_impuesto' => View::make( 'ventas.formatos_impresion.tabla_lineas_impuestos', compact('doc_registros') )->render()
            ];
    
            $cliente = $doc_encabezado->cliente;
            $tipo_doc_app = $doc_encabezado->tipo_documento_app;
        }

        $medios_pago = '';
        if($doc_encabezado->condicion_pago == 'contado')
        {
            if ($doc_encabezado->core_tipo_transaccion_id == 47 ) { // POS
                
                $registros_tesoreria = json_decode(str_replace("$", "", $doc_encabezado->lineas_registros_medios_recaudos));

                $medios_pago = View::make('ventas_pos.incluir.show_medios_pago', compact('registros_tesoreria'))->render();
            }else{
                $registros_tesoreria = TesoMovimiento::get_registros_un_documento( $doc_encabezado->core_tipo_transaccion_id, $doc_encabezado->core_tipo_doc_app_id, $doc_encabezado->consecutivo );
                $medios_pago = View::make('tesoreria.incluir.show_medios_pago', compact('registros_tesoreria'))->render();
            }  
        }
        
        return View::make( $ruta_vista, compact('doc_encabezado', 'doc_registros', 'empresa', 'resolucion', 'etiquetas', 'abonos', 'docs_relacionados', 'otroscampos', 'datos_factura', 'cliente', 'tipo_doc_app', 'pdv_descripcion', 'medios_pago' ) )->render();
    }

    public function get_documento_transaccion_prefijo_consecutivo( $doc_encabezado )
    {
        if( (int)config('ventas.longitud_consecutivo_factura') == 0 )
        {
            return $doc_encabezado->documento_transaccion_prefijo_consecutivo;
        }

        $consecutivo = $doc_encabezado->consecutivo;
        $largo = (int)config('ventas.longitud_consecutivo_factura') - strlen($doc_encabezado->consecutivo);
        for ($i=0; $i < $largo; $i++)
        { 
            $consecutivo = '0' . $consecutivo;
        }

        return $doc_encabezado->tipo_documento_app->prefijo . ' ' . $consecutivo;
    }

    public function get_etiquetas()
    {
        $parametros = config('ventas');

        $encabezado = '';

        if ($parametros['encabezado_linea_1'] != '')
        {
            $encabezado .= $parametros['encabezado_linea_1'];
        }

        if ($parametros['encabezado_linea_2'] != '')
        {
            $encabezado .= '<br>'.$parametros['encabezado_linea_2'];
        }

        if ($parametros['encabezado_linea_3'] != '')
        {
            $encabezado .= '<br>'.$parametros['encabezado_linea_3'];
        }

        $pie_pagina = '';

        if ($parametros['pie_pagina_linea_1'] != '')
        {
            $pie_pagina .= $parametros['pie_pagina_linea_1'];
        }

        if ($parametros['pie_pagina_linea_2'] != '')
        {
            $pie_pagina .= '<br><br>'.$parametros['pie_pagina_linea_2'];
        }

        if ($parametros['pie_pagina_linea_3'] != '')
        {
            $pie_pagina .= '<br><br>'.$parametros['pie_pagina_linea_3'];
        }

        return [ 'encabezado' => $encabezado, 'pie_pagina' => $pie_pagina ];
    }
        
}