@extends('transaccion.formatos_impresion.estandar')

@section('documento_datos_adicionales')
    @if( $doc_encabezado->condicion_pago == 'credito' )
        <br>
        <b>Fecha vencimiento:</b> {{ $doc_encabezado->fecha_vencimiento }}
    @endif
@endsection

@section('documento_transaccion_prefijo_consecutivo')
    @if( !is_null( $resolucion ) )
        {{ $resolucion->prefijo }} {{ $doc_encabezado->documento_transaccion_consecutivo }}
    @else
        {{ $doc_encabezado->documento_transaccion_prefijo_consecutivo }}
    @endif
@endsection

@section('encabezado_2')
    @if( $etiquetas['encabezado'] != '')
        <table style="width: 100%;">
            <tr>
                <td style="border: solid 1px #ddd; text-align: center; font-family: Courier New; font-style: italic;">
                    <b> {!! $etiquetas['encabezado'] !!} </b> 
                </td>
            </tr>
        </table>
    @endif
@endsection

@section('lbl_tercero')
    Paciente:
@endsection

@section('encabezado_datos_adicionales')
    <br>
    <b>Historia clínica No.: &nbsp;&nbsp;</b> {{ App\Salud\Paciente::where( 'core_tercero_id', $doc_encabezado->core_tercero_id )->value('codigo_historia_clinica') }}
    <br/>
    <b>Detalle: &nbsp;&nbsp;</b> {{ $doc_encabezado->descripcion }}
@endsection

@section('tabla_registros_1')
    <?php 
        $total_cantidad = 0;
        $subtotal = 0;
        $total_descuentos = 0;
        $total_impuestos = 0;
        $total_factura = 0;
        $array_tasas = [];

        foreach($doc_registros as $linea )
        {

            // Si la tasa no está en el array, se agregan sus valores por primera vez
            if ( !isset( $array_tasas[$linea->tasa_impuesto] ) )
            {
                // Clasificar el impuesto
                $array_tasas[$linea->tasa_impuesto]['tipo'] = 'IVA='.$linea->tasa_impuesto.'%';
                if ( $linea->tasa_impuesto == 0)
                {
                    $array_tasas[$linea->tasa_impuesto]['tipo'] = 'EX=0%';
                }
                // Guardar la tasa en el array
                $array_tasas[$linea->tasa_impuesto]['tasa'] = $linea->tasa_impuesto;


                // Guardar el primer valor del impuesto y base en el array
                $array_tasas[$linea->tasa_impuesto]['precio_total'] = (float)$linea->precio_total;
                $array_tasas[$linea->tasa_impuesto]['base_impuesto'] = (float)$linea->base_impuesto * (float)$linea->cantidad;
                $array_tasas[$linea->tasa_impuesto]['valor_impuesto'] = (float)$linea->valor_impuesto * (float)$linea->cantidad;

            }else{
                // Si ya está la tasa creada en el array
                // Acumular los siguientes valores del valor base y valor de impuesto según el tipo
                $precio_total_antes = $array_tasas[$linea->tasa_impuesto]['precio_total'];
                $array_tasas[$linea->tasa_impuesto]['precio_total'] = $precio_total_antes + (float)$linea->precio_total;
                $array_tasas[$linea->tasa_impuesto]['base_impuesto'] += (float)$linea->base_impuesto * (float)$linea->cantidad;
                $array_tasas[$linea->tasa_impuesto]['valor_impuesto'] += (float)$linea->valor_impuesto * (float)$linea->cantidad;
            }

            $total_cantidad += $linea->cantidad;
            $subtotal += (float)$linea->base_impuesto * (float)$linea->cantidad;
            $total_impuestos += (float)$linea->valor_impuesto * (float)$linea->cantidad;
            $total_factura += $linea->precio_total;
            $total_descuentos += $linea->valor_total_descuento;
        }


        $formula_asociada_factura = App\Ventas\DocEncabezadoTieneFormulaMedica::where( 'vtas_doc_encabezado_id', $doc_encabezado->id )->get()->first();

        $formula_id = null;

        if( !is_null($formula_asociada_factura) )
        {
            $formula_id = $formula_asociada_factura->formula_medica_id;
        }

        $formula_medica = '';
        $examen = '';
        if( !is_null($formula_id) && $formula_id != 0 )
        {
            $formula_medica = App\Salud\FormulaOptica::find( $formula_id );
            $resultado = new App\Http\Controllers\Salud\ResultadoExamenMedicoController();
            $examen = $resultado->get_tabla_resultado_examen( $formula_medica->consulta_id, $formula_medica->paciente_id, $formula_medica->examenes->first()->id);
        } 

        $total_abonos = 0;
        foreach ($abonos as $linea_abono)
        {
            $total_abonos += $linea_abono->abono;
        }

    ?>

    @if( $formula_medica != '' )
        <p style="width: 100%; text-align: center; font-weight: bold; font-size: 12px; padding: -10px;"> Exámen de {{ $formula_medica->examenes->first()->descripcion }}</p>
        {!! $examen !!}
        @include( 'consultorio_medico.formula_optica_show_tabla', [ 'formula' => $formula_medica ] )
        <br>
    @endif

    @if( !is_null($formula_id) && $formula_asociada_factura->contenido_formula != '' )
        <p style="width: 100%; text-align: center; font-weight: bold; font-size: 12px; padding: -10px;">  Formula óptica </p>
        <?php 
            $datos = json_decode( $formula_asociada_factura->contenido_formula );
        ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th> Esfera </th>
                    <th> Cilindro </th>
                    <th> Eje </th>
                    <th> Adición </th>
                    <th> Agudeza Visual </th>
                    <th> Distancia Pupilar </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td> O. D. </td>
                    <td> {{ $datos->esfera_ojo_derecho }}  </td>
                    <td> {{ $datos->cilindro_ojo_derecho }}  </td>
                    <td> {{ $datos->eje_ojo_derecho }}  </td>
                    <td> {{ $datos->adicion_ojo_derecho }}  </td>
                    <td> {{ $datos->agudeza_visual_ojo_derecho }}  </td>
                    <td> {{ $datos->distancia_pupilar_ojo_derecho }}  </td>
                </tr>
                <tr>
                    <td> O. I. </td>
                    <td> {{ $datos->esfera_ojo_izquierdo }}  </td>
                    <td> {{ $datos->cilindro_ojo_izquierdo }}  </td>
                    <td> {{ $datos->eje_ojo_izquierdo }}  </td>
                    <td> {{ $datos->adicion_ojo_izquierdo }}  </td>
                    <td> {{ $datos->agudeza_visual_ojo_izquierdo }}  </td>
                    <td> {{ $datos->distancia_pupilar_ojo_izquierdo }}  </td>
                </tr>
            </tbody>
        </table>
        <br>
    @endif

    <p style="width: 100%; text-align: center; font-weight: bold; font-size: 12px; padding: -10px;"> Items facturados</p>
    @include('ventas.incluir.lineas_registros_imprimir',compact('total_cantidad','total_factura'))
    <!-- @ include('ventas.incluir.factura_detalles_impuestos',compact('array_tasas')) -->
@endsection

@section('tabla_registros_2')
    @include('ventas.incluir.factura_medica_firma_totales')
@endsection