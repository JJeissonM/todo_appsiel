<!-- Modal -->
<div class="modal fade" id="modal_usuario_supervisor" role="dialog">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><div id="lbl_modal_usuario_supervisor"></div></h4>
        </div>
        <div class="modal-body">
          <p>
            {{ Form::bsText( 'email_supervisor', null, 'Usuario', ['id'=>'email_supervisor','autocomplete'=>'off'] ) }}
            <br><br><br>
            {{Form::bsPassword('password_supervisor', null, 'Contraseña',['id'=>'password_supervisor','autocomplete'=>'off'])}}
            <br>
            <div id="lbl_error_password_supervisor" style="color: red; display:none;">Datos incorrectos.</div>
          </p>
          <br><br><br>
          <form>
            <label class="radio-inline">
              <input type="radio" name="modal_text_input" value="email" checked>Usuario
            </label>
            <label class="radio-inline">
              <input type="radio" name="modal_text_input" value="password">Contraseña
            </label>
          </form>
          <p>
            <button class="btn btn-default btn_alfanumerico_teclado" value="q">q</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="w">w</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="e">e</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="r">r</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="t">t</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="y">y</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="u">u</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="i">i</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="o">o</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="p">p</button>
          </p>
          <p>
            <button class="btn btn-default btn_alfanumerico_teclado" value="a">a</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="s">s</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="d">d</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="f">f</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="g">g</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="h">h</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="j">j</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="k">k</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="l">l</button>
          </p>
          <p>
            <button class="btn btn-default btn_alfanumerico_teclado" value="z">z</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="x">x</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="c">c</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="v">v</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="b">b</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="n">n</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="m">m</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value=".">.</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="*">*</button>
          </p>
          <p>
            <button class="btn btn-default btn_alfanumerico_teclado" value="1">1</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="2">2</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="3">3</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="4">4</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="5">5</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="6">6</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="7">7</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="8">8</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="9">9</button>
            <button class="btn btn-default btn_alfanumerico_teclado" value="0">0</button>
            <button class="btn btn-danger" id="btn_clear_teclado_alfanumerico">Limpiar</button>
          </p>
        </div>
        <div class="modal-footer">
            <br>
            <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            <button type="button" class="btn btn-success" id="btn_validate_password_supervisor"> <i class="fa fa-check"></i>Anular</button>
        </div>
      </div>
    </div>
</div>