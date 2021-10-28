<div id="modal_examen_update_{{$consulta_id}}" class="modal fade" role="dialog">
  <div class="modal-dialog">

  <!-- Modal content-->
  <div class="modal-content">
    
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h4 class="modal-title">Exámen</h4>
    </div>
    
    <div class="modal-body">
      <div id="info_examen_{{$consulta_id}}"></div>
      <div class="alert alert-success alert-dismissible fade in" style="display: none;" id="alert_mensaje_{{$consulta_id}}">
        <strong>Registro actualizado correctamente!</strong>
      </div>
    </div>

    <div class="modal-footer">
      <button class="btn btn-danger btn-xs" data-dismiss="modal"> <i class="fa fa-close"></i> Cerrar </button>
      @can('salud_consultas_edit') <!-- -->
        <button class="btn btn-warning btn-xs btn_edit_examen" data-paciente_id="0" data-consulta_id="0" data-examen_id="0"> <i class="fa fa-edit"></i> Editar </button>
        <button class="btn btn-primary btn-xs btn_save_examen" data-paciente_id="0" data-consulta_id="0" data-examen_id="0" style="display: none;"> <i class="fa fa-save"></i> Guardar </button>
        <br><br>
      @endcan <!-- -->
    </div>
  </div>

  </div>
</div>