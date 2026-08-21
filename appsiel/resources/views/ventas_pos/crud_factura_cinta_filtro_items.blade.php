F2: Buscar Ítems
<div class="col-md-12 well">
    <div class="container-fluid">

        <div class="col-md-6">
            {{ Form::bsText( 'textinput_filter_item', null, 'Ítem', ['id'=>'textinput_filter_item', 'class'=>'form-control'] ) }}
        </div>
        <div class="col-md-6">
            {{ Form::bsText( 'quantity', null, 'Cantidad', ['id'=>'quantity', 'class'=>'form-control'] ) }}
        </div>

        <div class="col-md-12 pos-item-search-status" id="pos_item_search_status" aria-live="polite" style="display:none;">
            <i class="fa fa-spinner fa-spin" id="pos_item_search_spinner" aria-hidden="true"></i>
            <span id="pos_item_search_message">Buscando productos...</span>
        </div>

        <div class="col-md-12 filtros" id="pos_item_search_results"></div>
	</div>
</div>
