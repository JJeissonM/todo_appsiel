@extends('web.templates.index')

@section('style')
    <link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/normalize.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/cuenta.css')}}">

    <style>
        table {
            width: 100%;
        }
        th, td {
            padding: 15px;
            text-align: center;
        }
        th {
             background-color: red;
             color: white;
        }
        ul li{
            list-style: none;
        }
    </style>

@endsection

@section('content')
<?php
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
    }
?>

    @include('web.tienda.header')

    <main>
        <div class="main-container col2-left-layout">
            <div class="container">
                <div class="container-inner">
                    <div class="main">
                        <!-- Category Image-->
                        <div class="main-inner">
                            <div class="row">
                                
                                <div class="col-left sidebar col-lg-3 col-md-3 col-sm-12 col-xs-12">
                                    @include('web.tienda.mi_cuenta.menu_lateral')
                                </div>

                                <div class="col-main col-lg-9 col-md-9 col-sm-12 col-xs-12">
                                    <div class="my-account">
                                        <div class="dashboard">
                                            <div class="tab-content py-3 px-3 px-sm-0" style="border: 0;" id="nav-tabContent">
                                                
                                                <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                                                     aria-labelledby="nav-home-tab">

                                                    @include('web.tienda.mi_cuenta.panel_principal')
                                                    
                                                </div>

                                                <div class="tab-pane fade" id="nav-infor" role="tabpanel"
                                                     aria-labelledby="nav-infor-tab">

                                                    @include('web.tienda.mi_cuenta.datos_basicos')

                                                </div>
                                                
                                                <div class="tab-pane fade" id="nav-directorio" role="tabpanel"
                                                     aria-labelledby="nav-directorio-tab">

                                                    @include('web.tienda.mi_cuenta.mis_direcciones')

                                                </div>

                                                <div class="tab-pane fade" id="nav-ordenes" role="tabpanel"
                                                     aria-labelledby="nav-ordenes-tab">
                                                    
                                                    @include('web.tienda.mi_cuenta.mis_pedidos')

                                                </div>

                                            </div>
                                        </div>
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
    <script type="text/javascript">
        function getCiudades() {
            var pais = $("#pais").val();
            if (pais == null) {
                alert('Debe indicar todos loa parametros para contuniuar');
            }
            $.ajax({
                type: 'GET',
                url: '{{url('')}}/' + 'tienda/' + pais + '/getciudades',
                data: {},
            }).done(function (msg) {
                $("#ciudad option").each(function () {
                    $(this).remove();
                });
                if (msg != "null") {
                    var m = JSON.parse(msg);
                    $.each(m, function (index, item) {
                        $("#ciudad").append("<option value='" + item.id + "'>" + item.value + "</option>");
                    });
                } else {
                    alert('El pais seleccionado no tiene ciudades registradas');
                }
            });
        }
        //<![CDATA[
        /*var dataForm = new VarienForm('form-validate', true);

        function setPasswordForm(arg){
            if(arg){
                $('#form-password').removeAttr('style','display');
                $('#current_password').attr('class','input-text required-entry');
                // $('current_password').addClassName('required-entry');
                // $('password').addClassName('required-entry');
                $("#password").attr('class','input-text required-entry');
                $("#confirmation").attr('class','input-text required-entry');
                // $('confirmation').addClassName('required-entry');

            }else{
                $('#form-password').attr('style','display:none');
                // $('current_password').removeClassName('required-entry');
                $('#current_password').removeClass('required-entry');
                $('#password').removeClass('required-entry');
                // $('password').removeClassName('required-entry');
                $('#confirmation').removeClass('required-entry');
                // $('confirmation').removeClassName('required-entry');
            }
        }*/
        //]]>
    </script>

@endsection

