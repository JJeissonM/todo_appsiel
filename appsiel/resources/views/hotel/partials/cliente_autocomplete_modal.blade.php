<?php
    use App\Http\Controllers\Sistema\ModeloController;
    use App\Sistema\Modelo;
    use App\Sistema\Services\ModeloService;
    use App\Http\Controllers\Sistema\VistaController;
    use App\Hotel\Support\HotelBreadcrumb;

    $clienteAutocompleteTieneCampo = false;
    if (isset($form_create) && isset($form_create['campos'])) {
        foreach ($form_create['campos'] as $campoHotelCliente) {
            if (isset($campoHotelCliente['tipo']) && $campoHotelCliente['tipo'] == 'cliente_autocomplete') {
                $clienteAutocompleteTieneCampo = true;
                break;
            }
        }
    }

    $clienteAutocompleteCampos = array();
    $clienteAutocompleteUrl = 'vtas_clientes';
    $clienteAutocompleteModeloId = HotelBreadcrumb::modelId('App\\Hotel\\HotelGuest');
    if ($clienteAutocompleteModeloId == 0) {
        $clienteAutocompleteModeloId = 138;
    }
    $clienteAutocompleteModelo = Modelo::find($clienteAutocompleteModeloId);

    if ($clienteAutocompleteTieneCampo && !is_null($clienteAutocompleteModelo)) {
        $clienteAutocompleteCampos = ModeloController::get_campos_modelo($clienteAutocompleteModelo, '', 'create');
        if (method_exists(app($clienteAutocompleteModelo->name_space), 'get_campos_adicionales_create')) {
            $clienteAutocompleteCampos = app($clienteAutocompleteModelo->name_space)->get_campos_adicionales_create($clienteAutocompleteCampos);
        }
        $clienteAutocompleteAcciones = (new ModeloService())->acciones_basicas_modelo($clienteAutocompleteModelo, '');
        $clienteAutocompleteUrl = $clienteAutocompleteAcciones->store;
    }
?>

@if($clienteAutocompleteTieneCampo)
    <div class="modal fade" id="hotelClienteAutocompleteModal" tabindex="-1" role="dialog" aria-labelledby="hotelClienteAutocompleteModalLabel">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                {{ Form::open(array('url' => $clienteAutocompleteUrl, 'id' => 'hotel_cliente_autocomplete_form', 'files' => true)) }}
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="hotelClienteAutocompleteModalLabel">Nuevo huésped</h4>
                    </div>
                    <div class="modal-body">
                        @if(count($clienteAutocompleteCampos) > 0)
                            <?php VistaController::campos_dos_colummnas($clienteAutocompleteCampos); ?>
                        @else
                            <div class="alert alert-warning">No se encontraron campos configurados para el modelo Huesped.</div>
                        @endif

                        {{ Form::hidden('url_id', Input::get('id')) }}
                        {{ Form::hidden('url_id_modelo', $clienteAutocompleteModeloId) }}
                        {{ Form::hidden('url_id_transaccion', Input::get('id_transaccion')) }}
                        {{ Form::hidden('hotel_autocomplete_modal', 1) }}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="hotel_cliente_autocomplete_save">
                            <i class="fa fa-save"></i> Guardar huésped
                        </button>
                    </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endif
