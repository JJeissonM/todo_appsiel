<div class="col-sm-7">

    {{ Form::open(['url'=>url('pagina_web/contactenos'),'id'=>'form_contacto']) }}
        <p></p>Todos los campos son obligatorios.</p>
      <div class="row">
        <div class="col-sm-6 form-group">
          <input class="form-control" id="nombre" name="nombre" placeholder="Nombre completo" type="text" required>
        </div>
        <div class="col-sm-6 form-group">
          <input class="form-control" id="email" name="email" placeholder="Email" type="email" required>
        </div
      <div class="row">
        <div class="col-sm-6 form-group">
          <input class="form-control" id="telefono" name="telefono" placeholder="Teléfono" type="text" required>
        </div>
        <div class="col-sm-6 form-group">
          <input class="form-control" id="ciudad" name="ciudad" placeholder="Ciudad" type="text" required>
        </div>
      </div>
      <textarea class="form-control" id="comentarios" name="comentarios" placeholder="Comentarios" rows="5" required></textarea><br>
        <div class="checkbox">
            <label><input id="acepto_terminos" type="checkbox" checked="checked" required>* Acepto terminos y condiciones</label>
        </div>
      <div class="row">
        <div class="col-sm-12 form-group">
          <div class="g-recaptcha" data-sitekey="6LdU2WkUAAAAAHTt7C-qZIABDU0pXeCZ9MeZkhsi"></div>
        </div>
      </div>
    <div class="well well-lg">* Aurotizo a la empresa APPSIEL S.A.S. al tratamiento de mis datos personales, de acuerdo a las políticas dispuestas para tal fin en su página web www.appsiel.com.co, obedeciendo a lo establecido en la Ley 1581 de 2012 y sus decretos reglamentarios.</div>
      <div class="row">
        <div class="col-sm-12 form-group">
            <button id="submit" name="submit" class="btn btn-primary pull-right">Enviar</button>
        </div>
      </div>
    {{ Form::close() }}
    
    <div id="resultado_consulta"></div>

    <div id="div_cargando">Cargando...</div> 
</div>