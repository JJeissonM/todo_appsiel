{{ Form::open(['url'=>url('pagina_web/contactenos'),'id'=>'form_contacto']) }} 
  <p>Todos los campos son obligatorios.</p>
  <div class="row">
    <div class="col-md-6 form-group">
      <input class="form-control" id="nombre" name="nombre" placeholder="Nombre completo" type="text" required>
    </div>
    <div class="col-md-6 form-group">
      <input class="form-control" id="email" name="email" placeholder="Email" type="email" required>
    </div>
  </div>
  <div class="row">
    <div class="col-md-6 form-group">
      <input class="form-control" id="telefono" name="telefono" placeholder="Teléfono" type="text" required>
    </div>
    <div class="col-md-6 form-group">
      <input class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad" type="text" required>
    </div>
  </div>
  <textarea class="form-control" id="comentarios" name="comentarios" placeholder="Comentarios" rows="5" required></textarea>
  <div class="checkbox">
      <label><input id="acepto_terminos" type="checkbox"> Acepto <a href="#">terminos y condiciones </a></label>
  </div>
  <div class="row">
    <div class="col-md-12 form-group">
        <button id="submit" name="submit" class="btn btn-danger pull-right">Enviar</button>
    </div>
  </div>
{{ Form::close() }}