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

        .addresses-list h2 {
            font-weight: normal;
            font-size: 13px;
            color: #333;
            text-transform: uppercase;
        }
        .pull-right{
            position: absolute;
            right: 25px;
        }
        
    </style>

</head>
<body>

<main>
    <div class="container-fluid mt-4" >
        <div class="row">
            <div class="col-md-9 col-sm-12" id="products" style="overflow-y: scroll; height: 70vh;">
                
                
               <table id="lista-productos" style="width: 100%">
                   <thead>
                       <tr>
                           <th><center>Producto</center></th>
                           <th><center>Descripcion</center></th>
                           <th><center>Precio</center></th>
                           <th class="cant"><center>Cant.</center></th>
                           <th><center>Total</center></th>
                           <th><center>Borrar</center></th>
                       </tr>
                   </thead>

                   <tbody>
                   </tbody>
               </table>
            </div>
            <div class="col-md-3 col-sm-12">
                
                <div class="contenido px-2">
                    <p>Subtotal</p>
                    <p id="subtotal">$ 0.000</p>
                </div>

                <div class="contenido px-2">
                    <p>IVA</p>
                    <p id="iva">$ 0.000</p>
                </div>

                <div class="total_compra px-2 contenido" >
                    <p>Total: </p>
                    <p><span style="color: green" id="total">$ 0.000</span></p>
                </div>

                <div class="terminos">
                    <input class="select" type="checkbox" id="contrato">
                    <a href="">Aceptas términos y condiciones de la marketplace y autorizo el tratamiento de mis datos personales con las siguientes condiciones.</a>
                </div>

                <div class="acciones">                    
                    <form action="{{url('/vtas_pedidos')}}" id="form" method="POST">
                        <input type="hidden" name="login" id="url_login" value="{{url('/ecommerce/public/signIn')}}"> 
                        <input type="hidden" name="detailpedido" id="url_detallepedido" value="{{url('/ecommerce/public/detallepedido')}}"> 
                        <input type="hidden" id="token" name="_toker" value="{{csrf_token()}}">
                        <center><img style="display: none" id="loading" src="{{asset('img/fidget-spinner-loading.gif')}}" alt="" width="250px" height="250px"></center>
                        <button class="btn btn-primary btn-block" id="comprar" type="submit">Comfirmar Compra</button>
                    </form>
                    <a class="btn btn-light btn-block mt-2" href="{{url('/')}}"><center>Seguir comprando</center></a>
                </div>

            </div>
        </div>
    </div>
</main>
<header>
    <div class="checkoutHeader">
        <div class="checkoutHeader__logoHeader">
        </div>
        <div class="checkoutHeader__safePurchase">
            <p><img src="{{asset('img/carrito/ico_beneficio_seguridad.jpeg')}}" alt="Compra segura"> Tu compra es <strong>100% segura</strong></p>
        </div>
    </div>
</header>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="{{asset('assets/js/toastr.min.js')}}"></script>
<script src="{{asset('assets/js/axios.min.js')}}"></script>
<script src="{{asset('assets/tienda/js/compra.js')}}"></script>

</body>
</html>