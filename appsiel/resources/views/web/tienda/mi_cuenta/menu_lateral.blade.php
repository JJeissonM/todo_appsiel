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
                           aria-selected="false"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Direcciones de envío</font></font></a></li>
                @else
                    <li><a id="nav-directorio-edit" data-toggle="tab" href="#nav-directorioedit" role="tab" aria-controls="nav-directorioedit" aria-selected="false"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Direcciones</font></font></a></li>
                @endif
                <li class="last"><a id="nav-ordenes-tab" data-toggle="tab" href="#nav-ordenes" role="tab" aria-controls="nav-ordenes" aria-selected="false"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Mis pedidos</font></font></a></li>
            </ul>
        </div>
    </div>
</div>