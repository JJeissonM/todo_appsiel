<div class="page-title">
    <h1><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">AGREGAR NUEVA DIRECCIÓN</font></font></h1>
</div>
<div class="col2-set addresses-list">
    
    <div class="col-1 addresses-primary" style="max-width: 100%">
        <h2 style="text-transform: uppercase;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Dirección por defecto</font></font></h2>
        <ol>
            
            <li class="item">
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
                            Tel.: {{$cliente->telefono1}}
                        </font></font>
                </address>
            </li>
        </ol>
    </div>

    <div class="col-2 addresses-additional" style="max-width: 100%">
        <h2 style="text-transform: uppercase;"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Direcciones adicionales</font></font></h2>
        <ol>
            <li class="item empty">
                <p><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">No tiene entradas de direcciones adicionales en su libreta de direcciones.</font></font></p>
            </li>
        </ol>
        <div class="row">
            <div class="col-md-12 botones-gmail">
                {{ Form::bsBtnCreate( url( '/' )  ) }}
            </div>
        </div>
    </div>
</div>