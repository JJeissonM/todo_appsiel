$(document).ready(function () {
  var $turno = $('#turno_operativo_id'), $busqueda = $('#turno_operativo_busqueda');
  if (!$turno.length || !$busqueda.length) { return; }
  var $desde = $('#fecha_desde'), $hasta = $('#fecha_hasta');
  var fechasManuales = { desde: $desde.val(), hasta: $hasta.val() };
  var teniaTurno = false;
  var ayuda = 'Busque y seleccione un turno para utilizar su periodo de apertura y cierre.';

  var timerBusqueda, solicitudActiva = false, versionBusqueda = 0, busquedaPendiente = false;
  function cargandoBusqueda(estado) {
    $busqueda.attr('aria-busy', estado ? 'true' : 'false');
    $('#turno_busqueda_spinner').toggle(estado);
  }
  function cancelarBusqueda() {
    clearTimeout(timerBusqueda);
    versionBusqueda++;
    busquedaPendiente = false;
    cargandoBusqueda(false);
    $('#lista_sugerencias').remove();
  }
  function consultarTurnos() {
    if (solicitudActiva) { busquedaPendiente = true; return; }
    var texto = $.trim($busqueda.val()), version = versionBusqueda;
    if (!texto || $turno.val()) { return; }
    solicitudActiva = true;
    busquedaPendiente = false;
    $.ajax({
      url: $busqueda.attr('data-url_busqueda'),
      data: {texto_busqueda: texto, pdv_id: $('[name="pdv_id"]').val() || 0},
      timeout: 15000
    }).done(function (html) {
      if (version !== versionBusqueda) { return; }
      $('#lista_sugerencias').remove();
      $busqueda.after('<div id="lista_sugerencias" class="turno_operativo_busqueda" style="position:absolute;z-index:99999;"></div>');
      $('#lista_sugerencias').html(html);
    }).fail(function () {
      if (version !== versionBusqueda) { return; }
      $('#periodo_turno_resumen').text('No se pudieron cargar los turnos. Vuelva a escribir para reintentar.');
    }).always(function () {
      solicitudActiva = false;
      if (busquedaPendiente) { consultarTurnos(); }
      else if (version === versionBusqueda) { cargandoBusqueda(false); }
    });
  }

  function limpiarTurno(borrarTexto) {
    cancelarBusqueda();
    $turno.val('');
    $busqueda.removeAttr('data-registro_id data-turno-state').attr('data-selected-label', '');
    if (borrarTexto) { $busqueda.val(''); }
    if (teniaTurno) {
      $desde.val(fechasManuales.desde);
      $hasta.val(fechasManuales.hasta);
    }
    teniaTurno = false;
    $desde.add($hasta).prop('disabled', false).prop('required', true);
    $('#periodo_turno_resumen').text(ayuda);
  }

  var accionesAnteriores = window.ejecutar_acciones_con_item_sugerencia;
  window.ejecutar_acciones_con_item_sugerencia = function (item, input) {
    if (input.attr('id') !== 'turno_operativo_busqueda') {
      if (typeof accionesAnteriores === 'function') { accionesAnteriores(item, input); }
      return;
    }
    var apertura = item.attr('data-turno-opening-at') || '';
    var cierre = item.attr('data-turno-closing-at') || '';
    if (!apertura || !cierre) { limpiarTurno(true); return; }
    cancelarBusqueda();
    if (!teniaTurno) { fechasManuales = { desde: $desde.val(), hasta: $hasta.val() }; }
    teniaTurno = true;
    $desde.val(apertura.slice(0, 10));
    $hasta.val(cierre.slice(0, 10));
    $desde.add($hasta).prop('required', false).prop('disabled', true);
    $('#periodo_turno_resumen').text(apertura + ' — ' + cierre);
  };

  // El change al perder foco ocurre antes del clic sobre una sugerencia.
  $busqueda.on('input change', function (event) {
    if (event && event.type === 'change' && !$turno.val()) { return; }
    if ($busqueda.val() !== ($busqueda.attr('data-selected-label') || '') || !$busqueda.val()) {
      limpiarTurno(false);
      if ($.trim($busqueda.val())) {
        cargandoBusqueda(true);
        timerBusqueda = setTimeout(consultarTurnos, 400);
      }
    }
  });
  $('#limpiar_turno_resumen').on('click', function () { limpiarTurno(true); });
  limpiarTurno(false);
});
