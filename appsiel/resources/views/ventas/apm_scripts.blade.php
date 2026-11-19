@if( \App\Ventas\Services\ApmAvailability::enabled() )
    <script src="{{ asset('assets/js/apm/client.js?v=' . (isset($asset_v) ? $asset_v : uniqid())) }}"></script>
@endif
