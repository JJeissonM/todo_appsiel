@if($registro->device_type == 'printer')
    @php
        $apmUser = Auth::user();
        $canTestApmDevice = !is_null($apmUser) && (
            $apmUser->hasRole('SuperAdmin') ||
            $apmUser->hasRole('Administrador') ||
            $apmUser->can('vtas_apm_device_testing') ||
            $apmUser->can('vtas_apm_devices')
        );
    @endphp

    @if($canTestApmDevice)
        <hr>
        <div class="row" id="apm-device-test-panel" style="padding:5px;">
            <div class="col-md-12">
                <b>Pruebas APM:</b>
                <div class="btn-group" style="margin-left: 10px;">
                    <button type="button" class="btn btn-default btn-sm apm-device-test-command" data-endpoint="{{ 'apm_devices/'.$registro->id.'/test_beep' }}">
                        <i class="fa fa-volume-up"></i> Beep
                    </button>
                    <button type="button" class="btn btn-default btn-sm apm-device-test-command" data-endpoint="{{ 'apm_devices/'.$registro->id.'/test_open_drawer' }}">
                        <i class="fa fa-archive"></i> OpenDrawer
                    </button>
                    <button type="button" class="btn btn-default btn-sm apm-device-test-command" data-endpoint="{{ 'apm_devices/'.$registro->id.'/test_cut' }}">
                        <i class="fa fa-scissors"></i> Cut
                    </button>
                </div>
            </div>
        </div>
    @endif
@endif
