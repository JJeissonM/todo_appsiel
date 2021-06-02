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
        ul li{
            list-style: none;
        }
        .pull-right{
            position: absolute;
            right: 25px;
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
                                <input type="hidden" name="cliente_id" id="cliente_id" value="{{ $cliente->id }}">
                                
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
    <?php
            echo "$('#".$vista."').tab('show');";
    ?>
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


        // RELATIVO A MIS DIRECCIONES
        $("#btn_create_general").on('click', function(e) {
            e.preventDefault();
            
            $("#contenido_modal").html('');

            $("#myModal").modal({
                keyboard: false,
                backdrop: 'static'
            });

            $('#div_spin').fadeIn();
            $('.btn_edit_modal').hide();
            $(".btn_save_modal").show();
            $(".modal-title").html('Agrega un domicilio');

            var url = '{{url('/')}}/vtas_direcciones_entrega/create?cliente_id=' + $('#cliente_id').val() + '&id_modelo=300';

            $.get(url, function(respuesta) {
                $('#div_spin').hide();
                $("#contenido_modal").html(respuesta);
            });

        });

        $(".btn_edit_direccion").on('click', function(e) {
            e.preventDefault();
            
            $("#contenido_modal").html('');

            $("#myModal").modal({
                keyboard: false,
                backdrop: 'static'
            });

            $('#div_spin').fadeIn();
            $('.btn_edit_modal').hide();
            $(".btn_save_modal").show();
            $(".modal-title").html('Modificar domicilio');

            var url = '{{url('/')}}/vtas_direcciones_entrega/' + $(this).attr('data-direccion_cliente_id') + '/edit?id_modelo=300';

            $.get(url, function(respuesta) {
                $('#div_spin').hide();
                $("#contenido_modal").html(respuesta);
            });

        });

        $(document).on('click', '.btn_save_modal', function(e) {

            e.preventDefault();

            if ( !validar_requeridos() )
            {
                return false;
            }

            $('#form_create').submit();
        });

        $(document).on('click', '.btn_delete_direccion', function(e) {

            e.preventDefault();

            if ( confirm('¿Realmente desea eliminar este domicilio del cliente?') )
            {
                $(this.form).submit();
            }

            
        });

        var control_requeridos; // es global para que se pueda usar dentro de la función each() de abajo
        function validar_requeridos()
        {
            control_requeridos = true;
            $("#form_create :input[required]").each(function() {
                if ($(this).val() == "") {
                    $(this).focus();
                    //alert( 'Este campo es requerido: ' + $(this).attr('name') );
                    var lbl_campo = $(this).parent().prev('label').text();
                    if( lbl_campo === '' )
                    {
                        lbl_campo = $(this).prev('label').text();
                    }
                    alert( 'Este campo es requerido: ' + lbl_campo );

                    control_requeridos = false;
                    return false;
                }
            });

            return control_requeridos;
        }

    </script>

@endsection

