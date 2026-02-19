<div class="container-fluid">

    <?php
        $user = Auth::user();
        $vendedor_usuario = App\Ventas\Vendedor::where('user_id', $user->id)->get()->first();
        $mostrar_cocinas = true;

        if ( $user->hasRole('Mesero') && is_null($vendedor_usuario) ) {
            $mostrar_cocinas = false;
        }
    ?>

    @if( !$mostrar_cocinas )
        <div class="alert alert-warning">
            El usuario no esta asociado a un vendedor (mesero). Consulte con el administrador.
        </div>
    @else
        <input type="hidden" id="metodo_impresion_pedido_restaurante" value="{{ config('ventas.metodo_impresion_pedido_restaurante') }}">
        <input type="hidden" id="apm_ws_url" value="{{ config('ventas.apm_ws_url') }}">

        @if( (int)config('ventas_pos.imprimir_pedidos_en_cocina') )
            <div class="col">
                <br><br>
                <a class="btn btn-default btn-bg btn-info" href="{{ url( 'vtas_mesero_listado_pedidos_pendientes' . '?id=13' ) }}" title="Listado Pedidos Pendientes"><i class="fa fa-btn fa-list"></i> Listado de Pedidos Pendientes</a>
            </div>
        @endif	

        <h3>Toma de pedidos</h3>
        <hr>
        <?php 
            $cocinas = config('pedidos_restaurante.cocinas');
        ?>

        @foreach($cocinas as $index => $cocina)
            <div class="col-md-3 col-xs-6" style="padding: 10px;">
                <a href="{{url( 'vtas_pedidos_restaurante/create?id=13&id_modelo=320&id_transaccion=60' ) . '&grupo_inventarios_id=' . $cocina['grupo_inventarios_id'] . '&cocina_index=' . $index }}" class="btn btn-block btn-default apm_cocina_link">
                    <br>
                    <img style="width: 100px; height: 100px; border-radius:4px;" src="{{$cocina['url_imagen']}}">
                    <p style="text-align: center; white-space: nowrap; overflow: hidden; white-space: initial;">{{ $cocina['label'] }}</p>
                </a>
            </div>
        @endforeach
    @endif
</div>

<script src="{{ asset( 'assets/js/apm/client.js?aux=' . uniqid() )}}"></script>
<script type="text/javascript">
    (function () {
        var apm_modal_open = false;

        function metodo_apm_activo() {
            return ((document.getElementById('metodo_impresion_pedido_restaurante') || {}).value || 'normal') === 'apm';
        }

        function show_apm_modal() {
            if (apm_modal_open) {
                return;
            }
            apm_modal_open = true;
            Swal.fire({
                icon: 'error',
                title: 'APM no conectado',
                text: 'No se puede seleccionar cocina hasta que Appsiel Print Manager (APM) este en linea.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });
        }

        function close_apm_modal() {
            if (!apm_modal_open) {
                return;
            }
            Swal.close();
            apm_modal_open = false;
        }

        function set_apm_cocina_blocked(blocked) {
            document.querySelectorAll('.apm_cocina_link').forEach(function (el) {
                if (blocked) {
                    el.classList.add('disabled');
                    el.setAttribute('aria-disabled', 'true');
                } else {
                    el.classList.remove('disabled');
                    el.removeAttribute('aria-disabled');
                }
            });

            if (blocked) {
                show_apm_modal();
                return;
            }

            close_apm_modal();
        }

        function conectar_apm() {
            if (!window.APM_CLIENT || typeof window.APM_CLIENT.connect !== 'function') {
                return;
            }
            window.APM_CLIENT.connect();
        }

        function apm_esta_conectado() {
            if (!window.APM_CLIENT || !window.APM_CLIENT.socket) {
                return false;
            }
            return window.APM_CLIENT.socket.readyState === WebSocket.OPEN;
        }

        function validar_apm() {
            if (!metodo_apm_activo()) {
                set_apm_cocina_blocked(false);
                return;
            }

            if (!window.APM_CLIENT) {
                set_apm_cocina_blocked(true);
                return;
            }

            if (apm_esta_conectado()) {
                set_apm_cocina_blocked(false);
                return;
            }

            conectar_apm();
            set_apm_cocina_blocked(true);
        }

        document.addEventListener('click', function (event) {
            var target = event.target.closest('.apm_cocina_link');
            if (!target) {
                return;
            }

            if (target.getAttribute('aria-disabled') === 'true') {
                event.preventDefault();
            }
        });

        if (window.APM_CLIENT && typeof window.APM_CLIENT.setLogger === 'function') {
            window.APM_CLIENT.setLogger(function (message, type) {
                if (!metodo_apm_activo()) {
                    return;
                }
                if (type === 'success') {
                    set_apm_cocina_blocked(false);
                }
                if (type === 'warning' || type === 'error') {
                    set_apm_cocina_blocked(true);
                }
            });
        }

        validar_apm();
        setInterval(validar_apm, 2000);
    })();
</script>

