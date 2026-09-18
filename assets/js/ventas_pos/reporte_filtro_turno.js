$(document).ready(function () {
  var $turno = $('#turno_operativo_id');
  if (!$turno.length) { return; }
  var $desde = $('#fecha_desde'), $hasta = $('#fecha_hasta');
  var fechasManuales = { desde: $desde.val(), hasta: $hasta.val() };
  var teniaTurno = false;
  function actualizarPeriodoTurno() {
    var seleccionado = !!$turno.val();
    if (seleccionado) {
      if (!teniaTurno) {
        fechasManuales = { desde: $desde.val(), hasta: $hasta.val() };
      }
      var $opcion = $turno.find('option:selected');
      $desde.val($opcion.attr('data-desde'));
      $hasta.val($opcion.attr('data-hasta'));
      $('#periodo_turno_resumen').text($opcion.attr('data-intervalo'));
    } else if (teniaTurno) {
      $desde.val(fechasManuales.desde);
      $hasta.val(fechasManuales.hasta);
      $('#periodo_turno_resumen').text('Al seleccionar un turno se utiliza su periodo de apertura y cierre.');
    }
    $desde.add($hasta).prop('required', !seleccionado).prop('disabled', seleccionado);
    teniaTurno = seleccionado;
  }
  $turno.on('change comboboxselect', actualizarPeriodoTurno);
  $turno.next('.custom-combobox').find('input').on('autocompletechange', actualizarPeriodoTurno);
  actualizarPeriodoTurno();
});
