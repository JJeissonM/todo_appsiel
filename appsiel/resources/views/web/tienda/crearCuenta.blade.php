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
                        <!-- Category Image-->
                        <!--   -->
                        <div class="col-main">
                            <div class="account-create">
                                <div class="page-title">
                                    <h1>Completa tus datos...</h1>
                                </div>
                                    <form action="{{url('/web')}}" method="post" id="form-validate">
                                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                                        <input name="url_id" type="hidden" value="10">
                                        <input name="url_id_modelo" type="hidden" value="218">
                                        <input name="url_id_transaccion" type="hidden">
                                        <div class="row">
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">Nombre: </label>
                                                    <input type="text" name="descripcion" class="form-control" placeholder="nombres" required>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">*Tipo de Documento: </label>
                                                    <select name="tipo_doc_id"  id="" class="form-control" required>
                                                        @foreach($tipos as $tipo)
                                                            <option value="{{$tipo->id}}">{{$tipo->descripcion}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
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
                                                    <label for="">*Dirección: </label>
                                                    <input type="text" name="direccion" class="form-control" placeholder="Direccion" required>
                                                </div>
                                            </div>
                                            <div class="col-xs-12 col-md-6">
                                                <div class="form-group">
                                                    <label for="">*Teléfono: </label>
                                                    <input type="number" name="telefono" class="form-control" placeholder="Teléfono" required>
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
                                        <div class="buttons-set">
                                            <p class="required">* Campos requeridos</p>
                                            <button id="btn_crear_cuenta" type="button" title="Submit" class="button"><span><span>Registrarme</span></span></button>
                                        </div>
                                    </form>
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
