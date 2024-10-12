<div class="modal fade" id="modal_item_relacionado" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <?php 

          if( !isset( $title ) )
          {
            $title = 'Registro de tallas';
          }

          if( !isset( $class_btn_save ) )
          {
            $class_btn_save = 'btn_save_modal_item_relacionado';
          }

        ?>
        <h4 class="modal-title">{{ $title }}</h4>
      </div>
      <div class="modal-body">
        <div id="contenido_modal_item_relacionado">
          &nbsp;
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger btn_close_modal" data-dismiss="modal"> <i class="fa fa-close"></i> Cerrar </button>        
        <button class="btn btn-primary {{$class_btn_save}}">
          <i class="fa fa-save"></i> Guardar
          <span data-mandatario_id="{{ $mandatario_id }}"></span>
        </button>
      </div>
    </div>
  </div>
</div>