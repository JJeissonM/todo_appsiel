<?php

    $empresa = App\Core\Empresa::find(1);
/*
    if ( is_null( $cliente ) )
    {
        $cliente = (object)[
            'nombre_completo' => 'SIN REGISTRAR',
            'tipo_y_numero_documento_identidad' => 0,
            'user_id' => 0,
            'imagen' => 0,
            'nombre1' => 0,
            'otros_nombres' => 0,
            'apellido1' => 0,
            'apellido2' => 0,
            'telefono1' => 0,
            'id_tipo_documento_id' => 0,
            'numero_identificacion' => 0,
            'direccion1' => 0,
            'direccion2' => 0,
            'barrio' => 0,
            'codigo_postal' => 0,
            'ciudad' => 0,
            'departamento' => 0,
            'pais' => 0,
            'email' => 0,
            'id' => 0
        ];
    }*/
?>
<!doctype html>
<html lang="es">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
       Finalizar la compra
    </title>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/icon" href="{{asset('assets/images/favicon.ico')}}" />
    <!-- Font Awesome -->
    <link href="{{asset('assets/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <!-- Main Style -->
    <link href="{{asset('assets/style.css')}}" rel="stylesheet">
    <link href="{{asset('assets/tienda/css/compra.css')}}" rel="stylesheet">
    <link href="{{asset('assets/css/toastr.min.css')}}" rel="stylesheet">

    

    <style>
        .label-success {
           background-color: green;
           border-radius: 0 0 5px 5px ;             
        }

        .label-danger {
           background-color: red;
              border-radius: 5px 5px 0 0 ;
        }

        .label {
            padding: 3px;
            color: white;            
            cursor: pointer;
        }
        .accion{
            text-align: center;
        }
        .accion > p{
            margin: 0;
            display: block;            
        }
        @media (min-width: 1024px) {
            .accion > p{
                display: inline;
            }
            .label-success {                
                border-radius: 0 5px 5px 0;            
            }

            .label-danger {             
                border-radius: 5px 0 0 5px;
            }
            .cant{
                min-width: 80px;
            }
        }

        
        .cant{
            min-width: 00px;
        }

        table td, table th{
            border: 1px solid #e9ecef;

        }
    </style>

</head>
<body>
<header>
    <div class="checkoutHeader">
        <div class="checkoutHeader__logoHeader">
        </div>
        <div class="checkoutHeader__safePurchase">
            <p><img src="{{asset('img/carrito/ico_beneficio_seguridad.jpeg')}}" alt="Compra segura"> Tu compra es <strong>100% segura</strong></p>
        </div>
    </div>
</header>
<main>
    <div class="container-fluid" >
        <div class="row">
            <div class="col-md-9 col-sm-12" id="products" style="overflow-y: scroll; height: 70vh;">
                
                <table id="lista-productos" style="width: 100%">
                    <thead>
                        <tr>
                            <th width="15%"><center>Cant.</center></th>
                            <th><center>Descripcion</center></th>
                            <th width="15%"><center>Precio Total</center></th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                                $total_pagar = 0;
                                $total_iva = 0;
                                $subtotal = 0;
                        ?>                        
                        @foreach ($doc_registros as $producto)
                            <tr>
                                <td class="text-center">{{ $producto->cantidad.' '.$producto->unidad_medida1 }}</td>
                                <td>{{ $producto->producto_descripcion }}</td>
                                <td class="text-right">{{ $producto->precio_total }}</td>
                            </tr>
                            <?php
                                $total_pagar += $producto->precio_total;
                                $total_iva += $producto->valor_impuesto*$producto->cantidad;
                                $subtotal += $producto->base_impuesto*$producto->cantidad;
                            ?>
                            
                        @endforeach

                    </tbody>
                </table>
            </div>
            <div class="col-md-3 col-sm-12">
                <div class="contenido px-2 flex-column">
                    <?php 
                        $direcciones = $cliente->direcciones_entrega;
                        $direccion_por_defecto;
                    ?>                
                    <h4 class="text-center">Forma de Envio</h4>
                    <div class="nav nav-pills w-100 bg-light" id="myTab" role="tablist">
                        @foreach( $direcciones AS $direccion )
                        @if($direccion->por_defecto == 1)
                        <?php 
                            $direccion_por_defecto = $direccion;
                        ?>        
                        @endif        
                        @endforeach 
                        
                        <a class="nav-link active" style="flex: 1 1 auto;" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">
                                    Domicilio: {{$direccion_por_defecto->nombre_contacto}}<br>
                                    
                                </a>
                        
                        <a class="nav-link" style="flex: 1 1 auto;" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">
                                    Recoger en Tienda
                        </a>                        
                    </div>
                    <div class="tab-content w-100" id="v-pills-tabContent">
                            <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                                <div class="border border-primary rounded position-relative">
                                    <a id="cruddomicilio" class="btn pull-right bg-light rounded-circle" href="{{route('tienda.micuenta').'/nav-directorio-tab'}}">Agregar o Cambiar</a>
                                    @if ($direccion_por_defecto == null)
                                    <address>
                                        <br>
                                        <br>
                                        <br>
                                        No ha establecido una dirección de envío predeterminada.
                                    </address>
                                    @else
                                    <address>
                                        <b>Domicilio: {{$direccion_por_defecto->nombre_contacto}}</b><br>
                                        {{$direccion_por_defecto->direccion1}}, {{$direccion_por_defecto->barrio}}<br>
                                        {{$direccion_por_defecto->ciudad->descripcion }}, {{ $direccion_por_defecto->ciudad->departamento->descripcion }}, {{$direccion_por_defecto->codigo_postal}}<br>
                                        Tel.: {{$direccion_por_defecto->telefono1}}<br>
                                    </address>
                                    @endif  
                                </div>
                                <div class="contenido px-2">
                                    <p>Subtotal</p>
                                    <p id="subtotal">$ {{ number_format($subtotal,2,',','.') }}</p>
                                </div>
                                <div class="contenido px-2">
                                    <p>IVA</p>
                                    <p id="iva">$ {{ number_format($total_iva,2,',','.') }}</p>
                                </div>
                                <div class="contenido px-2">
                                    <p>Envio a Domicilio</p>
                                    <p id="iva">$ 5.000,00</p>
                                </div>
                                <div class="total_compra contenido px-2" >
                                    <p>Total: </p>
                                    <p><span style="color: green" id="total">$ {{ number_format($total_pagar+5000,2,',','.') }}</span></p>
                                </div>
                                <div class="acciones">
                                    <div class="d-flex justify-content-center">
                                        @if ($doc_encabezado->estado != 'Pendiente' || $total_pagar == 0)
                                        <a class="btn text-light btn-secondary btn-block" href="{{route('tienda.micuenta').'/nav-ordenes-tab'}}">Ver pedidos</a>
                                        @else  
                                        <form>
                                            <script
                                            src="https://checkout.wompi.co/widget.js"
                                            data-render="button"
                                            data-public-key="pub_test_enceqQgcwYlN9PQozadt9XBRa9VLCnsf"
                                            data-currency="COP"
                                            data-amount-in-cents="{{ ($total_pagar+5000).'00' }}"
                                            data-reference="{{ $doc_encabezado->id }}"
                                            data-redirect-url="{{ url('ecommerce/public/detallepedido/').'/'.$doc_encabezado->id }}"
                                            >
                                            </script>
                                        </form>                          
                                        @endif                        
                                    </div>     
                                </div>
                            </div>
                            <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                <div class="border border-primary rounded">
                                    <address>
                                        <b>Recoger en Tienda: {{ $empresa->descripcion }}</b>
                                        <br>
                                        {{ $empresa->direccion1 }}, {{ $empresa->barrio }}<br>
                                        {{ $empresa->ciudad->descripcion }}, {{ $empresa->ciudad->departamento->descripcion }}, {{ $empresa->codigo_postal }}<br>
                                        Tel.: {{$empresa->telefono1}}<br>    
                                    </address>
                                </div>
                                <div class="contenido px-2">
                                    <p>Subtotal</p>
                                    <p id="subtotal">$ {{ number_format($subtotal,2,',','.') }}</p>
                                </div>

                                <div class="contenido px-2">
                                    <p>IVA</p>
                                    <p id="iva">$ {{ number_format($total_iva,2,',','.') }}</p>
                                </div>

                                <div class="total_compra contenido px-2" >
                                    <p>Total: </p>
                                    <p><span style="color: green" id="total">$ {{ number_format($total_pagar,2,',','.') }}</span></p>
                                </div>
                                
                                <div class="acciones">
                                    <div class="d-flex align-items-center flex-column">
                                        @if ($doc_encabezado->estado != 'Pendiente' || $total_pagar == 0)
                                        <a class="btn text-light btn-secondary btn-block" href="{{route('tienda.micuenta').'/nav-ordenes-tab'}}">Ver pedidos</a>
                                        @else  
                                        <form>
                                            <script
                                            src="https://checkout.wompi.co/widget.js"
                                            data-render="button"
                                            data-public-key="pub_test_enceqQgcwYlN9PQozadt9XBRa9VLCnsf"
                                            data-currency="COP"
                                            data-amount-in-cents="{{ $total_pagar.'00' }}"
                                            data-reference="{{ $doc_encabezado->id }}"
                                            data-redirect-url="{{ url('ecommerce/public/detallepedido/').'/'.$doc_encabezado->id }}"
                                            >
                                            </script>
                                        </form>    
                                        <button type="button" style="width: 162px" class="btn btn-secondary mt-2" data-toggle="modal" data-target="#exampleModal">
                                            Pagar en Efectivo
                                        </button>

                                      <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog" role="document">
                                          <div class="modal-content">
                                            <div class="modal-header">
                                              <h5 class="modal-title" id="exampleModalLabel">Pago en Efectivo</h5>
                                              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                              </button>
                                            </div>
                                            <div class="modal-body">
                                              Se enviara a tu correo un comprobante con los detalles de tu pedido, el cual deberas llevar a la tienda para reclamarlo.
                                            </div>
                                            <div class="modal-footer">
                                              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                              <form action="{{ url('vtas_pedidos_enviar_por_email').'/'.$doc_encabezado->id }}">
                                                  <button type="submit" class="btn btn-primary">Pagar en Efectivo</button>
                                              </form>                                              
                                            </div>
                                          </div>
                                        </div>
                                      </div>                      
                                        @endif    

                                    </div> 
                                    
                                </div>

                            </div>
                          </div>                       
                </div>    
            </div>
        </div>
    </div>
</main>

<script src="{{asset('js/jquery.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous">
    </script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous">
    </script>
</body>
</html>