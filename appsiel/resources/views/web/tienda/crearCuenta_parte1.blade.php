@extends('web.templates.index')

@section('style')
    <link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/normalize.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/login.css')}}">
@endsection

@section('content')
    @include('web.tienda.header')
    <main>
        <div class="main-container col1-layout">
            <div class="container">
                <div class="container-inner">
                    <div class="main">
                        <div class="col-main">
                            <div class="account-create">
                                <div class="page-title">
                                    <h3 style="width:100%; text-align:center;">
                                        Completa tus datos <br>
                                        <small style="color: #ddd;">* Campos requeridos</small>
                                    </h3>
                                </div>

                                <form action="{{url('/ecommerce/public/nuevacuenta_parte2')}}" method="post" id="form-validate">
                                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                                    <input name="url_id" type="hidden" value="10">
                                    <input name="url_id_modelo" type="hidden" value="218">
                                    <input name="url_id_transaccion" type="hidden">
                                    <input name="core_tercero_id" type="hidden" value="{{config('contabilidad.contact_id_default')}}">
                                    <input name="vendedor_id" type="hidden" value="{{config('pagina_web.vendedor_id')}}">
                                    <input name="inv_bodega_id" type="hidden" value="{{config('pagina_web.inv_bodega_id')}}">                                    
                                    
                                    <div class="row">
                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">*Número de identificación: </label>
                                                <input type="number" id="numero_identificacion" name="numero_identificacion" class="form-control" placeholder="Identificación" required>
                                                <span id="error_identificacion">
                                                    <label style="color: red">Ya existe una persona con ese número de identificación.</label>
                                                    <label style="color: green">Por favor, Inicie sesión.</label>
                                                </span>
                                                
                                            </div>
                                        </div>
                                    </div>

                                    <div class="buttons-set">
                                        <button id="btn_continuar" type="button" title="Submit" class="button"><span><span>Continuar</span></span></button>
                                    </div>
                                </form>
                                <br>
                                <h5>¿Ya está registrado?</h5>
                                <div class="buttons-set">
                                    <a href="{{url('ecommerce/public/signIn')}}"  class="btn btn-primary btn-lg"><span><span>Iniciar sesión</span></span></a>
                                </div>
                                <br>
                                <br>                                    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    @include('web.tienda.footer')
@endsection

@section('script')
    <script src="{{asset('assets/tienda/js/categories.js')}}"></script>
    <script type="text/javascript">

        $(document).on('blur', '#numero_identificacion', validarnumero_identificacion);
        $(document).on('keyup', '#numero_identificacion', validarnumero_identificacion);

        $('#error_identificacion').css({'display':'none'})

        function validarnumero_identificacion () {
            var documento = $("#numero_identificacion").val();            

            url = '../../core/validar_numero_identificacion/'; // crear            

            $.get(url + documento, function (datos) {
                if (datos != '') {
                    if (datos == documento) {
                        // No hay problema
                        $('#error_identificacion').css({'display':'block'});
                        $('#btn_continuar').attr('type','button');
                        $('#btn_continuar').attr('disabled','disabled');
                        return false;                                
                    }else{
                        $('#error_identificacion').css({'display':'none'});
                        $('#btn_continuar').removeAttr('disabled');
                        $('#btn_continuar').attr('type','submit');
                        return true;
                    }
                }else{
                    $('#btn_continuar').removeAttr('disabled');
                    $('#error_identificacion').css({'display':'none'});
                    $('#btn_continuar').attr('type','submit');
                    return true;
                }
            });
            
        };

    </script>
@endsection
