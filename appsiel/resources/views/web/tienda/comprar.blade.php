<!doctype html>
<html lang="en">
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
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <!-- Main Style -->
    <link href="{{asset('assets/style.css')}}" rel="stylesheet">
    <link href="{{asset('assets/tienda/css/compra.css')}}" rel="stylesheet">
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
            <div class="col-md-8 col-sm-12" id="products" style="overflow-y: scroll; height: 70vh;">
               <table id="lista-productos">
                   <thead>
                       <tr>
                           <th><center>Producto</center></th>
                           <th><center>Descripcion</center></th>
                           <th  width="150px"><center>Precio</center></th>
                           <th  width="150px"><center>Cantidad</center></th>
                           <th  width="150px"><center>Total</center></th>
                       </tr>
                   </thead>

                   <tbody>
                   </tbody>
               </table>
            </div>
            <div class="col-md-4 col-sm-12">
                <div class="contenido">
                    <p>Subtotal</p>
                    <p id="subtotal">$ 19.000</p>
                </div>

                <div class="total_compra">
                    <p>Total: </p>
                    <p><span style="color: red" id="total">$ 19.000</span></p>
                </div>

                <div class="terminos">
                    <input class="select" type="checkbox">
                    <a href="">Acepto términos y condiciones, términos y condiciones marketplace y autorizo el tratamiento de mis datos personales con las siguientes condiciones.</a>
                </div>

                <div class="acciones">
                    <button class="btn-block" id="comprar">finalizar compra</button>
                    <a href=""><center>Seguir comprando</center></a>
                </div>

            </div>
        </div>
    </div>
</main>
<script src="{{asset('assets/tienda/js/compra.js')}}"></script>
</body>
</html>