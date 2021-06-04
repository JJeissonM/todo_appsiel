<?php 
  $clase_modal = 'modal-dialog';
  if ( isset($clase_tamanio) )
  {
    $clase_modal = 'modal-dialog ' . $clase_tamanio;
  }
?>

<div id="myModal2" class="modal fade" role="dialog">
  <div class="{{$clase_modal}}">

    <!-- Modal content-->
    <div class="modal-content">
      
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">{{$titulo2}}</h4>
      </div>

      <div class="modal-body">
      	{{ Form::Spin2(64) }}
        <div class="alert alert-success alert-dismissible fade in" style="display: none;" id="alert_mensaje2">
          <strong>{{$texto_mensaje2}}</strong>
        </div>
      	<div id="contenido_modal2">
          @if( isset($contenido_modal2) )
            {!! $contenido_modal2 !!}
          @endif
        </div>
      </div>

      <div class="modal-footer">
      	<button class="btn btn-danger" data-dismiss="modal"> <i class="fa fa-close"></i> Cerrar </button>
    		
        <button class="btn btn-warning btn_edit_modal"> <i class="fa fa-edit"></i> Editar </button>
        
        <button class="btn btn-primary btn_save_modal"> <i class="fa fa-save"></i> Guardar </button>

    		<br><br>
      </div>
    </div>
  </div>
</div>