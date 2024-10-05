F2: Buscar Ítems
<div class="col-md-12 well">
    <div class="container-fluid">

        <div class="col-md-6">
            {{ Form::bsText( 'textinput_filter_item', null, 'Ítem', ['id'=>'textinput_filter_item', 'class'=>'form-control'] ) }}
        </div>
        <div class="col-md-6">
            {{ Form::bsText( 'quantity', null, 'Cantidad', ['id'=>'quantity', 'class'=>'form-control'] ) }}
        </div>

        <div class="filtros">
        </div>
	</div>
</div>