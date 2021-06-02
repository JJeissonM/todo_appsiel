@extends('web.templates.index')

@section('style')

    
    <!--<link rel="stylesheet" href="{{asset('assets/tienda/css/normalize.css')}}">-->
    <link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/login.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
    <link href="{{asset('assets/css/toastr.min.css')}}" rel="stylesheet">
    <style>

        #img_producto {
             object-fit: cover;
             height: 350px;
        }

        .add_carrito {
            background-color:rgb(253, 153, 67);
            border-radius: 4px;
            color: white;
            font-weight: bold;
        }

        .cantidades {
            display: flex;
            align-items: center;
        }

        .cantidades input {
          height: 30px;
          border-radius: 0;
          border: none;
        }

        .label-success {
           background-color: green;
           border-radius: 0 5px 5px 0;             
        }

        .label-danger {
           background-color: red;
           border-radius: 5px 0 0 5px;
        }

        .label {
            padding: 3px;
            color: white;            
            cursor: pointer;
        }

        .description{
            font-size: 20px
        }

    </style>
@endsection

@section('content')

    @include('web.tienda.header')

    <div class="container" style="margin-top: 10px;">
        <a href="{{url('/')}}" style="color: #0b58a2; "><i class="fa fa-angle-left" aria-hidden="true"></i>  Regresar</a>
    </div>

    <main>
        <div class="main-container col2-left-layout">
            <div class="container">
                <div class="container-inner">
                    <div class="main">
                        <div class="main-inner">
                            <div class="row">
                                <?php
                                $url_imagen_producto = '#';
                                if ( $inv_producto->imagen != '' )
                                {
                                    $url_imagen_producto = asset( config('configuracion.url_instancia_cliente') . 'storage/app/inventarios/' . $inv_producto->imagen );
                                }
                                ?>
                                <div class="col-left sidebar col-lg-8 col-md-8 col-sm-12 col-xs-12" style="margin-bottom: 20px;">
                                    <img id="img_producto" src="{{$url_imagen_producto}}"  alt="" style="object-fit: contain; width: 100%; height: 350px;">
                                </div>
                                <div class="col-main col-lg-4 col-md-4 col-sm-12 col-xs-12 d-flex flex-column justify-content-between">
                                    <div class="description">
                                        <div class="page-title category-title">
                                            <h1>{{$inv_producto->descripcion}}</h1>
                                        </div>
                                        <p>{{$inv_producto->detalle}}</p>
                                        <div class="my-2">
                                            <label style="width: 110px; display: inline-block"><strong>Precio: </strong></label>$ {{ number_format( $inv_producto->precio_venta,0,',','.' ) }}
                                        </div>
                                        
                                        <div class="cantidades">
                                            <label for="" style="width: 110px;margin-right: 5px;">Cantidad: </label>
                                            <span class="label label-danger" onclick="less()"><i class="fa fa-minus" aria-hidden="true"></i></span>
                                            <input type="text" style="width: 40px; " value="1" id="cantidad">
                                            <span class="label label-success" onclick="plus()"><i class="fa fa-plus" aria-hidden="true"></i></span>
                                        </div>    
                                    </div>
                                    

                                     <div style="display: flex; flex-direction: column; justify-content: space-between; margin-top: 2rem; height: 84px;">

                                         <a href="#" onclick="comprarUno({'id':'{{$inv_producto->id}}','imagen':'{{$url_imagen_producto}}', 'titulo':'{{$inv_producto->descripcion}}','precio':'{{$inv_producto->precio_venta}}','tasa_impuesto':'{{$inv_producto->tasa_impuesto}}'})"  id="comprar" class="btn button btn-block" style="background-color: var(--color-primario,#42A3DC); border: none ;font-size: 16px; color: white">Comprar</a>

                                         <button class="btn add_carrito" style="border: none ;font-size: 16px; background-color: var(--color-primario,#42A3DC44)" onclick="add_carrito({'id':'{{$inv_producto->id}}','imagen':'{{$url_imagen_producto}}', 'titulo':'{{$inv_producto->descripcion}}','precio':'{{$inv_producto->precio_venta}}','tasa_impuesto':'{{$inv_producto->tasa_impuesto}}'})">Agregar al Carrito</button>

                                         
                                     </div>
                                </div>
                                <div class="col-main col-lg-12 col-md-12 col-sm-12 col-xs-12"  style="margin-top: 20px">
                                    <h2 class="title" style=" padding-bottom: 10px; font-size: 24px; border-bottom: 1px solid grey">Especificacíones</h2>
                                    <div style="width: 100%; display: flex; flex-direction: column; justify-content: space-between">
                                        @if($inv_producto->fichas->count() > 0)
                                            @foreach($inv_producto->fichas as $ficha)
                                                <div class="caracteristica">
                                                    <p style="margin-bottom: 5px;"><strong>{{$ficha->key}}</strong></p>
                                                    <p><?php echo $ficha->descripcion; ?></p>
                                                </div>
                                            @endforeach
                                        @else
                                            <p>SIN ESPECIFICACIÓN </p>
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

    {!! Form::footer($footer,$redes,null,'small')  !!}
@endsection

@section('script')
    <script src="{{asset('assets/js/toastr.min.js')}}"></script>
    <script type="text/javascript">

         function less() {
           let val = $('#cantidad').val();
           val--;
           if(val == -1){
               val = 0;
           }
           $('#cantidad').val(val);
         }

         function plus(){
             let val = $('#cantidad').val();
             val++;
             $('#cantidad').val(val);
         }

         function comprarUno(producto){
            window.location.href='{{route("tienda.comprar")}}';
            let val = $('#cantidad').val();
            if(val == 1){
                add_carrito(producto);
            }else{
                add_carrito(producto);
            }
         }
         
         function add_carrito(producto) {

             let cant = $('#cantidad').val();
             producto.cantidad = cant;
             producto.total =  parseFloat(producto.precio*cant);
             producto.precio_base = parseFloat(producto.precio/ ( 1 + producto.tasa_impuesto/100)).toFixed(2);

             guardarProductoLocalStorage(producto);
             toastr.success(`${producto.titulo} agregado al carrito`);

         }

         // Almacena cursos en el carrito a Local Storage
         function guardarProductoLocalStorage(producto) {

             let productos = [];
             // Toma el valor de un arreglo con datos de LS o vacio
             productos = obtenerProductosLocalStorage();
             let exist = false;

             productos.forEach(item => {
                 if(item.id == producto.id){
                     item.cantidad = producto.cantidad;
                     item.total = item.precio*item.cantidad;
                     exist = true;
                 }
             });

             // el curso seleccionado se agrega al arreglo
             if(!exist){
                 productos.push(producto);
             }

             localStorage.setItem('productos', JSON.stringify(productos));
         }

         // Comprueba que haya elementos en Local Storage
         function obtenerProductosLocalStorage() {
             let productosLS;
             // comprobamos si hay algo en localStorage
             if(localStorage.getItem('productos') === null) {
                 productosLS = [];
             } else {
                 productosLS = JSON.parse( localStorage.getItem('productos') );
             }
             return productosLS;
         }

    </script>
@endsection
