
@inject('inv_services', 'App\Ventas\Services\InventoriesServices')

<div class="container-fluid">
    <div class="marco_formulario">
        {{ Form::open(['url' => 'vtas_crear_remision_y_factura_desde_doc_venta?id=13&id_modelo=164&id_transaccion=24&remision_y_factura_desde_pedido=yes&doc_ventas_id=' . $doc_encabezado->id,'id'=>'form_create','files' => true]) }}
            <input type="hidden" name="url" value="vtas_pedidos/{{$doc_encabezado->id.$variables_url}}" />
            <input type="hidden" name="doc_encabezado_id" value="{{$doc_encabezado->id}}" />
            <input type="hidden" name="doc_encabezado_cotizacion_id" id="doc_encabezado_cotizacion_id" value="{{$doc_encabezado->ventas_doc_relacionado_id}}" />
            <input type="hidden" name="source" value="PEDIDO" />
            {{ csrf_field() }}
            

            <h5 class="control-label">Formulario para Generar Factura <i class="fa fa-arrow-down" aria-hidden="true"></i></h5>

            <!-- 
                <div class="row">
                    <div class="col-md-6 col-lg-6 col-xl-2">
                        @ if( $doc_encabezado->enlaces_remisiones_hijas() == '' )
                            { { Form::select( 'generar', [  'remision_y_factura_desde_pedido' => 'Remisión y Factura', 'remision_desde_pedido' => 'Remisión' ], null, ['class'=>'form-control select2','required'=>'required', 'id' =>'generar']) }}
                        @ else
                            { { Form::select( 'generar', [ 'remision_desde_pedido' => 'Remisión' ], null, ['class'=>'form-control select2','required'=>'required', 'id' =>'generar']) }}
                        @ endif
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-2">
                    </div>
                </div>
            -->
            <br>

            <div class="row">
                
                <div class="col-md-6 col-lg-6 col-xl-2">
                    {{ Form::bsRadioBtn('tipo_factura','estandar','Tipo de factura','{"estandar":"Estándar","electronica":"Electrónica"}',['required'=>'required']) }}
                </div>
                
                <div class="col-md-6 col-lg-6 col-xl-2">
                    &nbsp;
                </div>
            </div>

            <div class="row">
                
                <input type="hidden" name="generar" id="generar" value="remision_y_factura_desde_pedido">

                <div class="col-md-6 col-lg-6 col-xl-2">
                    {{ Form::bsFecha('fecha',date('Y-m-d'),'Fecha', null,[]) }}
                </div>
                
                <div class="col-md-6 col-lg-6 col-xl-2">
                    {{ Form::bsSelect('bodega_id', $inv_services->get_bodega_id($doc_encabezado->cliente_id), 'Bodega', $inv_services->get_bodegas()->pluck('descripcion','id')->toArray(), ['required'=>'required']) }}
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-lg-6 col-xl-2">
                    {{ Form::bsSelect('forma_pago', null, 'Forma de pago', [ 'credito'=>'Crédito', 'contado' => 'Contado'], ['required'=>'required']) }}
                </div>
                <div class="col-md-6 col-lg-6 col-xl-2">
                    {{ Form::bsText('vlr_total_pedido', '$ ' . number_format($total_factura,0,',','.'), 'Vlr. total pedido', ['class'=>'form-control', 'readonly' => 'readonly']) }}
                </div>
            </div>

            <div class="row">
                
                <div class="col-md-6 col-lg-6 col-xl-2">
                    {{ Form::bsText('abono', 0, 'Abono', ['class'=>'form-control', 'readonly' => 'readonly']) }}
                </div>
                
                <div class="col-md-6 col-lg-6 col-xl-2">
                    &nbsp;
                </div>
            </div>

            <input type="hidden" name="lineas_registros_medios_recaudo" id="lineas_registros_medios_recaudo" value="0">

        </form>

        <br><br>
        
        <div>
            @include('tesoreria.incluir.medios_recaudos')
        </div>

        <div class="row">
            <br>
            <div class="col-md-12 col-lg-12" style="text-align: center;">
                <button class="btn btn-primary btn-bg" id="btn_generar">GENERAR</button>
            </div>
            <br>
        </div>

        <div id="div_advertencia_factura" style="display: none; color: red;" class="container-fluid">
            Nota: La condición de pago (Crédito o Contado) de la factura será tomada de los datos del cliente.
        </div>

        <br>
    </div>
</div>