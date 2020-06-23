@extends('web.templates.index')

@section('style')
    <link rel="stylesheet" href="{{asset('assets/tienda/css/skeleton.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/normalize.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/custom.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/main.css')}}">
    <link rel="stylesheet" href="{{asset('assets/tienda/css/cuenta.css')}}">
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
                                    <div class="block block-account">
                                        <div class="block-title">
                                            <strong><span><font style="vertical-align: inherit;">MI CUENTA</font></span></strong>
                                        </div>
                                        <div class="block-content">
                                            <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                                                <ul>
                                                    <li><a id="nav-home-tab" data-toggle="tab" href="#nav-home" role="tab" aria-controls="nav-home" aria-selected="true"><strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Panel de cuenta</font></font></strong></a></li>
                                                    <li><a id="nav-infor-tab" data-toggle="tab" href="#nav-infor" role="tab" aria-controls="nav-infor" aria-selected="true"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información de la cuenta</font></font></a></li>
                                                    @if($cliente->direccion1 !== 0)
                                                        <li><a id="nav-directorio-tab" data-toggle="tab"
                                                               href="#nav-directorio" role="tab" aria-controls="nav-directorio"
                                                               aria-selected="false"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Direcciones</font></font></a></li>
                                                    @else
                                                        <li><a id="nav-directorio-edit" data-toggle="tab" href="#nav-directorioedit" role="tab" aria-controls="nav-directorioedit" aria-selected="false"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Direcciones</font></font></a></li>
                                                    @endif
                                                    <li class="last"><a id="nav-ordenes-tab" data-toggle="tab" href="#nav-ordenes" role="tab" aria-controls="nav-ordenes" aria-selected="false"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Mis ordenes</font></font></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-main col-lg-9 col-md-9 col-sm-12 col-xs-12">
                                    <div class="my-account">
                                        <div class="dashboard">
                                            <div class="tab-content py-3 px-3 px-sm-0" style="border: 0;" id="nav-tabContent">
                                                <div class="tab-pane fade show active" id="nav-home" role="tabpanel"
                                                     aria-labelledby="nav-home-tab">
                                                    <div class="page-title">
                                                        <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit; text-align: left !important;">MI TABLERO</font></font></h1>
                                                    </div>
                                                    <div class="welcome-msg">
                                                        <p class="hello"><strong><font style="vertical-align: inherit; background: yellow; color:black;"><font style="vertical-align: inherit;">Hola {{ $cliente->nombre_completo }}!</font></font></strong></p>
                                                        <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Desde el Panel de control de Mi cuenta, puede ver una instantánea de la actividad reciente de su cuenta y actualizar la información de su cuenta. </font><font style="vertical-align: inherit;">Seleccione un enlace a continuación para ver o editar información.</font></font></p>
                                                    </div>
                                                    <div class="box-account box-info">
                                                        <div class="box-head">
                                                            <h2><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información de la cuenta</font></font></h2>
                                                        </div>
                                                        <div class="col2-set">
                                                            <div class="col-1" style="max-width: 50%">
                                                                <div class="box">
                                                                    <div class="box-title">
                                                                        <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información del contacto</font></font></h3>
                                                                    </div>
                                                                    <div class="box-content">
                                                                        <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                    {{$cliente->nombre_completo}}</font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                    {{$cliente->email}} </font></font><br>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-2" style="max-width: 50%">
                                                                <div class="box">
                                                                    <div class="box-title">
                                                                        <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Boletines informativos</font></font></h3>
                                                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/newsletter/manage/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar</font></font></a>
                                                                    </div>
                                                                    <div class="box-content">
                                                                        <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Actualmente no estás suscrito a ningún boletín.</font></font></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2-set">
                                                            <div class="box">
                                                                <div class="box-title">
                                                                    <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Directorio</font></font></h3>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="col-1" style="max-width: 50%">
                                                                        <h4><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">direccion de FACTURACIÓN por defecto</font></font></h4>
                                                                        <address>
                                                                            @if($cliente->direccion1 === 0 || $cliente->direccion1 === null)
                                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                        No ha establecido una dirección de facturación predeterminada.
                                                                                    </font></font><br>
                                                                            @else
                                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                        {{$cliente->nombre_completo}}
                                                                                    </font></font><br>
                                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                        {{$cliente->direccion1}}, {{$cliente->barrio}}
                                                                                    </font></font><br>
                                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                        {{$cliente->ciudad}}, {{$cliente->departamento}}, {{$cliente->codigo_postal}}
                                                                                    </font></font><br>
                                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                        {{$cliente->pais}}
                                                                                    </font></font><br>
                                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                        Tel. {{$cliente->telefono1}}
                                                                                    </font></font><br>
                                                                            @endif
                                                                        </address>
                                                                    </div>
                                                                    <div class="col-2" style="max-width: 50%">
                                                                        <h4><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Dirección de entrega por defecto</font></font></h4>
                                                                        <address>
                                                                            @if($cliente->direccion1 === 0 || $cliente->direccion1 === null)
                                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                        No ha establecido una dirección de envío predeterminada.
                                                                                    </font></font><br>
                                                                            @else
                                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                        {{$cliente->nombre_completo}}
                                                                                    </font></font><br>
                                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                        {{$cliente->direccion1}}, {{$cliente->barrio}}
                                                                                    </font></font><br>
                                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                        {{$cliente->ciudad}}, {{$cliente->departamento}}, {{$cliente->codigo_postal}}
                                                                                    </font></font><br>
                                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                        {{$cliente->pais}}
                                                                                    </font></font><br>
                                                                                <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                        Tel. {{$cliente->telefono1}}
                                                                                    </font></font><br>
                                                                            @endif
                                                                        </address>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="nav-infor" role="tabpanel"
                                                     aria-labelledby="nav-infor-tab">
                                                    <div class="page-title">
                                                        <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">EDITAR INFORMACIÓN DE LA CUENTA</font></font></h1>
                                                    </div>
                                                    {!! Form::model($cliente,['route'=>['tienda.informacionupdate',$cliente],'method'=>'PUT','class'=>'form-horizontal','id'=>'form-validate','autocomplete'=>'off','files'=>'true'])!!}
                                                    <div class="fieldset">
                                                        <h2 class="legend"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información de la cuenta</font></font></h2>
                                                        <ul class="form-list">
                                                            <li class="fields">
                                                                <div class="customer-name">
                                                                    <div class="field name-firstname">
                                                                        <label for="firstname" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Nombre</font></font></label>
                                                                        <div class="input-box">
                                                                            <input type="text" id="firstname" name="nombre1" value="{{$cliente->nombre1}}" title="Nombre de pila" maxlength="255" class="input-text required-entry">
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
                                                                            <input type="text" id="lastname" name="apellido1" value="{{$cliente->apellido1}}" title="Primer Apellido" maxlength="255" class="input-text required-entry">
                                                                        </div>
                                                                    </div>
                                                                    <div class="field name-lastname">
                                                                        <label for="lastname" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Segundo Apellido</font></font></label>
                                                                        <div class="input-box">
                                                                            <input type="text" id="apellido2" name="apellido2" value="{{$cliente->apellido2}}" title="Apellido" maxlength="255" class="input-text required-entry">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <label for="email" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Dirección de correo electrónico</font></font></label>
                                                                <div class="input-box">
                                                                    <input type="text" name="email" id="email" value="{{$cliente->email}}" title="Dirección de correo electrónico" class="input-text required-entry validate-email">
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
                                                        <button type="submit" title="Salvar" class="button"><span><span><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Salvar</font></font></span></span></button>
                                                    </div>
                                                    {!! Form::close() !!}
                                                </div>
                                                <div class="tab-pane fade" id="nav-directorioedit" role="tabpanel" aria-labelledby="nav-directorioedit-tab">
                                                    {!! Form::model($cliente,['route'=>['tienda.informacionupdate',$cliente],'method'=>'PUT','class'=>'form-horizontal','id'=>'form-validate','autocomplete'=>'off','files'=>'true'])!!}
                                                    <div class="fieldset">
                                                        <h2 class="legend"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información del contacto</font></font></h2>
                                                        <ul class="form-list">
                                                            <li class="fields">
                                                                <div class="customer-name">
                                                                    <div class="field name-firstname">
                                                                        <label for="firstname" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Primer Nombre</font></font></label>
                                                                        <div class="input-box">
                                                                            <input type="text" id="firstname" name="nombre1" value="{{$cliente->nombre1}}" title="Nombre de pila" maxlength="255" class="input-text required-entry">
                                                                        </div>
                                                                    </div>
                                                                    <div class="field name-firstname">
                                                                        <label for="firstname"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Segundo Nombre</font></font></label>
                                                                        <div class="input-box">
                                                                            <input type="text" id="otros_nombres" name="otros_nombres" value="{{$cliente->otros_nombres}}" title="Segundo Nombre" maxlength="255" class="input-text">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="fields">
                                                                <div class="customer-name">
                                                                    <div class="field name-lastname">
                                                                        <label for="lastname" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Primer Apellido</font></font></label>
                                                                        <div class="input-box">
                                                                            <input type="text" id="lastname" name="apellido1" value="{{$cliente->apellido1}}" title="Apellido" maxlength="255" class="input-text required-entry">
                                                                        </div>
                                                                    </div>
                                                                    <div class="field name-lastname">
                                                                        <label for="lastname" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Segundo Apellido</font></font></label>
                                                                        <div class="input-box">
                                                                            <input type="text" id="apellido2" name="apellido2" value="{{$cliente->apellido2}}" title="Apellido" maxlength="255" class="input-text required-entry">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="fields">
                                                                <div class="field">
                                                                    <label for="telephone" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Teléfono</font></font></label>
                                                                    <div class="input-box">
                                                                        <input type="text" name="telefono1" value="{{$cliente->telefono1}}" title="Teléfono" class="input-text   required-entry" id="telephone">
                                                                    </div>
                                                                </div>
                                                                <div class="field">
                                                                    <label for="company"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Empresa</font></font></label>
                                                                    <div class="input-box">
                                                                        <input type="text" name="company" id="company" title="Empresa" value="" class="input-text ">
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="fieldset">
                                                        <h2 class="legend"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Habla a</font></font></h2>
                                                        <ul class="form-list">
                                                            <li class="wide">
                                                                <label for="street_1" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Dirección</font></font></label>
                                                                <div class="input-box">
                                                                    <input type="text" name="direccion1" value="{{$cliente->direccion1}}" title="Dirección" id="direccion_1" class="input-text  required-entry">
                                                                </div>
                                                            </li>
                                                            <li class="wide" style="margin-top: 20px">
                                                                <div class="input-box">
                                                                    <input type="text" name="direccion2" value="{{$cliente->direccion2}}" title="Dirección 2" id="direccion_2" class="input-text" placeholder="Dirección 2">
                                                                </div>
                                                            </li>
                                                            <li class="fields">
                                                                <div class="field">
                                                                    <label for="country" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> País</font></font></label>
                                                                    <div class="input-box">
                                                                        <select class="validate-select" title="País" id="pais" name="pais_id" onchange="getCiudades()">
                                                                            <option value="">--Selecciones una opción--</option>
                                                                            @foreach($paises as $pais)
                                                                                <option value="{{$pais->id}}"><font style="vertical-align: inherit;">{{$pais->descripcion}}</font></option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="field">
                                                                    <label for="city" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Ciudad @if($cliente->ciudad !== 0)({{$cliente->ciudad}})@endif</font></font></label>
                                                                    <div class="input-box">
                                                                        <select class="validate-select" title="Ciudad" name="codigo_ciudad" id="ciudad">

                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="fields">
                                                                <div class="field">
                                                                    <label for="zip" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Barrio</font></font></label>
                                                                    <div class="input-box">
                                                                        <input type="text" name="barrio" value="{{$cliente->barrio}}" title="Barrio" id="barrio" class="input-text validate-zip-international  required-entry">
                                                                    </div>
                                                                </div>
                                                                <div class="field">
                                                                    <label for="zip" class="required"><em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">*</font></font></em><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Código postal</font></font></label>
                                                                    <div class="input-box">
                                                                        <input type="text" name="codigo_postal" value="{{$cliente->codigo_postal}}" title="Código postal" id="codigo_postal" class="input-text validate-zip-international  required-entry">
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <input type="hidden" name="default_billing" value="1">
                                                            </li>
                                                            <li>
                                                                <input type="hidden" name="default_shipping" value="1">
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="buttons-set">
                                                        <p class="required"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">* Campos requeridos</font></font></p>
                                                        <button type="submit" title="Salvar" class="button"><span><span><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Salvar</font></font></span></span></button>
                                                    </div>
                                                    {!! Form::close() !!}
                                                </div>
                                                <div class="tab-pane fade" id="nav-directorio" role="tabpanel"
                                                     aria-labelledby="nav-directorio-tab">
                                                    <div class="page-title">
                                                        <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">AGREGAR NUEVA DIRECCIÓN</font></font></h1>
                                                    </div>
                                                    <div class="col2-set addresses-list">
                                                        <div class="col-1 addresses-primary" style="max-width: 100%">
                                                            <h2 style="text-transform: uppercase;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Direcciones predeterminadas</font></font></h2>
                                                            <ol>
                                                                <li class="item">
                                                                    <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">direccion de FACTURACION por defecto</font></font></h3>
                                                                    <address><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                {{$cliente->nombre_completo}}
                                                                            </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                {{$cliente->direccion1}}
                                                                            </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                {{$cliente->barrio}}
                                                                            </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                {{$cliente->ciudad}}, {{$cliente->departamento}}, {{$cliente->codigo_postal}}
                                                                            </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                {{$cliente->pais}}
                                                                            </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                T: {{$cliente->telefono1}}
                                                                            </font></font>
                                                                    </address>
                                                                    <p><a id="nav-directorioedit-tab" data-toggle="tab" href="#nav-directorioedit" role="tab" aria-controls="nav-directorioedit" aria-selected="false"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Cambiar dirección de facturación</font></font></a></p>
                                                                </li>
                                                                <li class="item">
                                                                    <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Dirección de entrega por defecto</font></font></h3>
                                                                    <address><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                {{$cliente->nombre_completo}} </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                {{$cliente->direccion1}} </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                {{$cliente->barrio}} </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                {{$cliente->ciudad}}, {{$cliente->departamento}}, {{$cliente->codigo_postal}} </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                {{$cliente->pais}} </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                T: {{$cliente->telefono1}}</font></font>
                                                                    </address>
                                                                    <p><a id="nav-directorioedit-tab" data-toggle="tab" href="#nav-directorioedit" role="tab" aria-controls="nav-directorioedit" aria-selected="false"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Cambiar la dirección de envío</font></font></a></p>
                                                                </li>
                                                            </ol>
                                                        </div>
                                                        <div class="col-2 addresses-additional" style="max-width: 100%">
                                                            <h2 style="text-transform: uppercase;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Entradas de direcciones adicionales</font></font></h2>
                                                            <ol>
                                                                <li class="item empty">
                                                                    <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">No tiene entradas de direcciones adicionales en su libreta de direcciones.</font></font></p>
                                                                </li>
                                                            </ol>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tab-pane fade" id="nav-ordenes" role="tabpanel"
                                                     aria-labelledby="nav-ordenes-tab">
                                                    <div class="page-title">
                                                        <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">MIS PEDIDOS</font></font></h1>
                                                    </div>
                                                    <div class="welcome-msg">
                                                        <p class="hello"><strong><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Hola Jordan Cuadro!</font></font></strong></p>
                                                        <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Desde el Panel de control de Mi cuenta, puede ver una instantánea de la actividad reciente de su cuenta y actualizar la información de su cuenta. </font><font style="vertical-align: inherit;">Seleccione un enlace a continuación para ver o editar información.</font></font></p>
                                                    </div>
                                                    <div class="box-account box-info">
                                                        <div class="box-head">
                                                            <h2><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información de la cuenta</font></font></h2>
                                                        </div>
                                                        <div class="col2-set">
                                                            <div class="col-1" style="max-width: 50%">
                                                                <div class="box">
                                                                    <div class="box-title">
                                                                        <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Información del contacto</font></font></h3>
                                                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/edit/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar</font></font></a>
                                                                    </div>
                                                                    <div class="box-content">
                                                                        <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                    Jordan Cuadro </font></font><br><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                    jordan_j9@hotmail.com </font></font><br>
                                                                            <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/account/edit/changepass/1/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Cambiar contraseña</font></font></a>
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-2" style="max-width: 50%">
                                                                <div class="box">
                                                                    <div class="box-title">
                                                                        <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Boletines informativos</font></font></h3>
                                                                        <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/newsletter/manage/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar</font></font></a>
                                                                    </div>
                                                                    <div class="box-content">
                                                                        <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                    Actualmente no estás suscrito a ningún boletín.                                    </font></font></p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col2-set">
                                                            <div class="box">
                                                                <div class="box-title">
                                                                    <h3><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Directorio</font></font></h3>
                                                                    <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/address/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Administrar direcciones</font></font></a>
                                                                </div>
                                                                <div class="box-content">
                                                                    <div class="col-1" style="max-width: 50%">
                                                                        <h4><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">direccion de FACTURACION por defecto</font></font></h4>
                                                                        <address><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                    No ha establecido una dirección de facturación predeterminada. </font></font><br>
                                                                            <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/address/edit/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar dirección</font></font></a>
                                                                        </address>
                                                                    </div>
                                                                    <div class="col-2" style="max-width: 50%">
                                                                        <h4><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Dirección de entrega por defecto</font></font></h4>
                                                                        <address><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                                                                    No ha establecido una dirección de envío predeterminada. </font></font><br>
                                                                            <a href="http://www.plazathemes.com/demo/ma_dicove/index.php/customer/address/edit/"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Editar dirección</font></font></a>
                                                                        </address>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    {!! Form::footer($footer,$redes,null,'small')  !!}
@endsection

@section('script')
<script src="{{asset('assets/tienda/js/categories.js')}}"></script>
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
    var dataForm = new VarienForm('form-validate', true);
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

