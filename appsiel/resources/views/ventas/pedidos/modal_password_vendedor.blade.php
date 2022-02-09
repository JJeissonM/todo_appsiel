<!-- Modal -->
<div class="modal fade" id="modal_password" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><div id="lbl_vendedor_modal"></div></h4>
        </div>
        <div class="modal-body">
          <p>
            {{Form::bsPassword('seller_password', null, 'Ingrese su contraseña', ['id'=>'seller_password'])}}
            <div id="lbl_error_password" style="color: red; display:none;">Contraseña incorrecta.</div>
        </p>
        </div>
        <div class="modal-footer">
            <br>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-success" id="btn_validate_password">Aceptar</button>
        </div>
      </div>
    </div>
</div>