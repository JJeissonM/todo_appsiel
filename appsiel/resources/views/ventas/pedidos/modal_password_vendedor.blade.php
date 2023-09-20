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
          <br><br><br>
          <p>
            <button class="btn btn-default btn_numero_teclado" value="1">1</button>
            <button class="btn btn-default btn_numero_teclado" value="2">2</button>
            <button class="btn btn-default btn_numero_teclado" value="3">3</button>
            <button class="btn btn-default btn_numero_teclado" value="4">4</button>
            <button class="btn btn-default btn_numero_teclado" value="5">5</button>
            <button class="btn btn-default btn_numero_teclado" value="6">6</button>
            <button class="btn btn-default btn_numero_teclado" value="7">7</button>
            <button class="btn btn-default btn_numero_teclado" value="8">8</button>
            <button class="btn btn-default btn_numero_teclado" value="9">9</button>
            <button class="btn btn-default btn_numero_teclado" value="0">0</button>
            <button class="btn btn-danger" id="btn_clear_teclado">Limpiar</button>
          </p>
        </div>
        <div class="modal-footer">
            <br>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-success" id="btn_validate_password"> <i class="fa fa-check"></i>Aceptar</button>
        </div>
      </div>
    </div>
</div>