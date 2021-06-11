<div class="page-title">
    <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Actualiza tus Datos</font></font></h1>
    @if(Session::has('flash_message'))
    <div class="container-fluid">
        <div class="alert alert-success">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <em> {!! session('flash_message') !!}</em>
        </div>
    </div>
    @endif
    @if(Session::has('mensaje_error'))
    <div class="container-fluid">
        <div class="alert alert-danger">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <em> {!! session('mensaje_error') !!}</em>
        </div>
    </div>
    @endif
</div>
{!! Form::model($cliente,['route'=>['tienda.informacionupdate',$cliente],'method'=>'PUT','class'=>'form-horizontal','id'=>'form-validate','autocomplete'=>'off','files'=>'true'])!!}
    <div class="fieldset">
        <h2 class="legend"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información de la cuenta</font></font> </h2>
        <ul class="form-list">
            <li class="fields">
                <div class="customer-name">
                    <div class="field name-firstname">
                        <label for="firstname" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Nombre</font></font></label>
                        <div class="input-box">
                            <input type="text" id="firstname" name="nombre1" value="{{$cliente->nombre1}}" title="Nombre de pila" maxlength="255" class="input-text" required>
                        </div>
                    </div>
                    <div class="field name-firstname">
                        <label for="firstname" class="required"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Segundo Nombre</font></font></label>
                        <div class="input-box">
                            <input type="text" id="otros_nombres" name="otros_nombres" value="{{$cliente->otros_nombres}}" title="Segundo nombre" maxlength="255" class="input-text">
                        </div>
                    </div>
                </div>
            </li>
            <li class="fields">
                <div class="customer-name">
                    <div class="field name-lastname">
                        <label for="lastname" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Primer Apellido</font></font></label>
                        <div class="input-box">
                            <input type="text" id="lastname" name="apellido1" value="{{$cliente->apellido1}}" title="Primer Apellido" maxlength="255" class="input-text required-entry" required>
                        </div>
                    </div>
                    <div class="field name-lastname">
                        <label for="lastname" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Segundo Apellido</font></font></label>
                        <div class="input-box">
                            <input type="text" id="apellido2" name="apellido2" value="{{$cliente->apellido2}}" title="Apellido" maxlength="255" class="input-text required-entry" required>
                        </div>
                    </div>
                </div>
            </li>
            <li>
                <label for="email" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Dirección de correo electrónico</font></font></label>
                <div class="input-box">
                    <input type="email" name="email" id="email" value="{{$cliente->email}}" title="Dirección de correo electrónico" class="input-text required-entry validate-email" required>
                </div>
            </li>
            <li class="control">
                <br><input type="checkbox" name="change_password" id="change_password" value="1" onclick="setPasswordForm(this.checked)" title="Cambia la contraseña" class="checkbox"><label for="change_password"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Cambia la contraseña</font></font></label>
            </li>
        </ul>
    </div>
    <div class="fieldset" style="display:none;" id="form-password">
        <h2 class="legend">Cambia la Contraseña</h2>
        <ul class="form-list">
            <li>
                <label for="current_password" class="required"><em>*</em>Contraseña Actual</label>
                <div class="input-box">
                    <!-- This is a dummy hidden field to trick firefox from auto filling the password -->
                    <input type="text" class="input-text" style="display: none" name="dummy" id="dummy">
                    <input type="password" title="Contraseña Actual" class="input-text" name="current_password" id="current_password">
                </div>
            </li>
            <li class="fields">
                <div class="field">
                    <label for="password" class="required"><em>*</em>Nueva Contraseña</label>
                    <div class="input-box">
                        <input type="password" title="Nueva Contraseña" class="input-text validate-password" name="password" id="password">
                    </div>
                </div>
                <div class="field">
                    <label for="confirmation" class="required"><em>*</em>Confirmar Nueva Contraseña</label>
                    <div class="input-box">
                        <input type="password" title="Confirmar Nueva Contraseña" class="input-text validate-cpassword" name="confirmation" id="confirmation">
                    </div>
                </div>
            </li>
        </ul>
    </div>
    <div class="buttons-set">
        <p class="required"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">* Campos requeridos</font></font></p>
        <button type="submit" title="Guardar" class="button"><span><span><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Guardar</font></font></span></span></button>
    </div>
{!! Form::close() !!}

@section('script1')
<script type="text/javascript">    
    //<![CDATA[
    //var dataForm = new VarienForm('form-validate', true);

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
    }
    //]]>
</script>
@endsection