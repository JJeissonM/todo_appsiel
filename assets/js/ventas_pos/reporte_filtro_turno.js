$(document).ready(function () {
  var $turno = $('#turno_operativo_id'), $busqueda = $('#turno_operativo_busqueda');
  if (!$turno.length || !$busqueda.length) { return; }
  var $desde = $('#fecha_desde'), $hasta = $('#fecha_hasta');
  var fechasManuales = { desde: $desde.val(), hasta: $hasta.val() };
  var teniaTurno = false;
  var ayuda = 'Busque y seleccione un turno para utilizar su periodo de apertura y cierre.';

  function cancelarBusqueda() {
    clearTimeout($busqueda.data('suggestions-timeout'));
    var request = $busqueda.data('suggestions-request');
    if (request && request.readyState !== 4) { request.abort(); }
    $('#lista_sugerencias').remove();
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

  $busqueda.on('input change', function () {
    if ($busqueda.val() !== ($busqueda.attr('data-selected-label') || '') || !$busqueda.val()) {
      limpiarTurno(false);
    }
  });
  $('#limpiar_turno_resumen').on('click', function () { limpiarTurno(true); });
  $('#form_consulta').on('change comboboxselect', '[name="pdv_id"], [name="agrupar_por"], [name="estado_facturas"], [name="detalla_productos"], [name="detalla_clientes"], [name="iva_incluido"]', function () {
    limpiarTurno(true);
  });
  limpiarTurno(false);
});
