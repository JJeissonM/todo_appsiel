<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>
        Web - APPSIEL
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
            <a href="/">
                <img src="{{asset('img/carrito/cart.png')}}" alt="logo Avipoulet">
            </a>
        </div>
        <div class="checkoutHeader__safePurchase">
            <p><img src="{{asset('img/carrito/ico_beneficio_seguridad.jpeg')}}" alt="Compra segura"> Tu compra es <strong>100% segura</strong></p>
        </div>
    </div>
</header>
<main>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 col-sm-12">
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
            </div>
        </div>
    </div>
</main>
<script src="{{asset('assets/tienda/js/compra.js')}}"></script>
</body>
</html>