<div class="page-title">
    <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit; text-align: left !important;">MI TABLERO</font></font></h1>
</div>
<div class="welcome-msg">
    <h1 class="hello"><font style="vertical-align: inherit; color:black; font-weight: bold">Hola {{ $cliente->nombre_completo }}</font></h1>
    <p>Estos son los datos de contacto e información para hacer la entrega de tus productos.</p>
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
                                    Tel. {{$cliente->telefono1}}
                                </font></font><br>
                        @endif
                    </address>
                </div>
                <div class="col-2" style="max-width: 50%">
                    <h4><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Dirección de entrega por defecto</font></font></h4>
                    <?php 
                        $direcciones = $cliente->direcciones_entrega;
                        $direccion_por_defecto = null;
                    ?>    
                    @foreach( $direcciones AS $direccion )
                    @if($direccion->por_defecto == 1)
                    <?php 
                        $direccion_por_defecto = $direccion;
                    ?>        
                    @endif        
                    @endforeach 
                    <address>
                        @if($direccion_por_defecto === null)
                            <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                    No ha establecido una dirección de envío predeterminada.
                                </font></font><br>
                        @else
                            <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                
                                    {{$direccion_por_defecto->nombre_contacto}}
                                </font></font><br>
                            <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                    {{$direccion_por_defecto->direccion1}}, {{$direccion_por_defecto->barrio}}
                                </font></font><br>
                            <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                    {{$direccion_por_defecto->ciudad->descripcion}}, {{$direccion_por_defecto->ciudad->departamento->descripcion}}, {{$direccion_por_defecto->codigo_postal}}
                                </font></font><br>
                            <font style="vertical-align: inherit;"><font style="vertical-align: inherit;">
                                    Tel. {{$direccion_por_defecto->telefono1}}
                                </font></font><br>
                        @endif
                    </address>
                </div>
            </div>
        </div>
    </div>
</div>