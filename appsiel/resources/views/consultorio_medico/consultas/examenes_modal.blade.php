<div class="modal fade" id="modal_examen_{{$consulta_id}}" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Registro de Exámen</h4>
      </div>
      <div class="modal-body">
        <div id="contenido_modal_examen_{{$consulta_id}}">
          &nbsp;
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-danger btn_close_modal" data-dismiss="modal"> <i class="fa fa-close"></i> Cerrar </button>        
        <button class="btn btn-primary btn_save_modal_examen">
          <i class="fa fa-save"></i> Guardar
          <span data-consulta_id="{{ $consulta_id }}"></span>
        </button>
      </div>
    </div>
  </div>
</div>