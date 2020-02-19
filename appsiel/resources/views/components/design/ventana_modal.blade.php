<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{{$titulo}}</h4>
      </div>

      <div class="modal-body">
      	{{ Form::Spin(64) }}
        <div class="alert alert-success alert-dismissible fade in" style="display: none;" id="alert_mensaje">
          <strong>{{$texto_mensaje}}</strong>
        </div>
      	<div id="contenido_modal">
          @if( isset($contenido_modal) )
            {!! $contenido_modal !!}
          @endif
        </div>
      </div>

      <div class="modal-footer">
      	<button class="btn btn-danger btn-xs" data-dismiss="modal"> <i class="fa fa-close"></i> Cerrar </button>
    		
        <button class="btn btn-warning btn-xs btn_edit_modal"> <i class="fa fa-edit"></i> Editar </button>
        
        <button class="btn btn-primary btn-xs btn_save_modal"> <i class="fa fa-save"></i> Guardar </button>

    		<br><br>
      </div>
    </div>
  </div>
</div>