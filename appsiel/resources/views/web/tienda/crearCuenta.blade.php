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

                                <form action="{{url('/web')}}" method="post" id="form-validate">
                                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                                    <input name="url_id" type="hidden" value="10">
                                    <input name="url_id_modelo" type="hidden" value="218">
                                    <input name="url_id_transaccion" type="hidden">
                                    <div class="row">
                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">*Número de identificación: </label>
                                                <input type="number" name="numero_identificacion" class="form-control" placeholder="Identificación" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">*Email: </label>
                                                <input type="email" id="email" name="email" class="form-control" placeholder="Email" required>
                                                <label id="errormail" style="color: red">Ya existe una persona con ese correo</label>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">*Nombres: </label>
                                                <input type="text" name="nombre1" class="form-control" placeholder="Nombres" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">*Apellidos: </label>
                                                <input type="text" name="apellido1" class="form-control" placeholder="Apellidos" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">*Dirección: </label>
                                                <input type="text" name="direccion1" class="form-control" placeholder="Direccion" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">*Teléfono: </label>
                                                <input type="number" name="telefono1" class="form-control" placeholder="Teléfono" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">*Contraseña: </label>
                                                <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
                                            </div>
                                        </div>
                                        <div class="col-xs-12 col-md-6">
                                            <div class="form-group">
                                                <label for="">*Confirmar Contraseña: </label>
                                                <input type="password" name="password_confirmation" class="form-control" placeholder="Confirmar contraseña">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="checkbox col-xs-12 col-md-12">
                                        <input type="checkbox" name="subscribe" required> Acepto la <a href="#"> política de tratamiento de datos. </a>
                                    </div>

                                    <div class="buttons-set">
                                        <button id="btn_crear_cuenta" type="button" title="Submit" class="button"><span><span>Registrarme</span></span></button>
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

        $(document).on('blur', '#email', validarEmail);
        $(document).on('keyup', '#email', validarEmail);

        $('#errormail').css({'display':'none'})

        function validarEmail () {
            var documento = $("#email").val();

            /* Cuando el javascript está dentro de una vista blade se puede llamar la url de la siguiente forma:
            url = "{{ url('core/validar_numero_identificacion/') }}";*/
            

            url = '../../ecommerce/validar_email/'; // crear            

            $.get(url + documento, function (datos) {
                console.log(datos);
                if (datos != '') {
                    if (datos == documento) {
                        // No hay problema
                        $('#errormail').css({'display':'block'})
                        //alert("Ya existe una persona con ese número de documento de identidad. Cambié el número o no podrá guardar el registro.");  
                        $('#btn_crear_cuenta').attr('type','button')
                        console.log('false')  ;  
                        return false;   
                                
                    }else{
                        $('#errormail').css({'display':'none'})
                        $('#btn_crear_cuenta').attr('type','submit')
                        console.log('true')
                        return true;
                    }
                }else{
                    $('#errormail').css({'display':'none'})
                    $('#btn_crear_cuenta').attr('type','submit')
                    console.log('true')
                    return true;
                }
            });
            
        };

    </script>
@endsection
