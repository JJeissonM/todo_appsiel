$(document).ready(function () {
  $(document).on("click", "#btn_revisar_anticipos", function (event) {
    event.preventDefault();

    if ($("#object_anticipos").val() != "null") {
      Swal.fire({
                    icon: 'error',
                    title: 'Alerta!',
                    text: 'Ya hay Anticipos/Saldos a favor agregados como Medio de pago. Debe retirar los anticipos/Saldos a favor antes de agregar más.',
                });
      return false;
    }
  

    $("#contenido_modal2").html("");
    $("#div_spin2").fadeIn();

    $("#myModal2").modal({ backdrop: "static" });

    $("#myModal2 .modal-title").text(
      "Anticipos/Saldos a favor del cliente " + $("#cliente_input").val()
    );

    $("#myModal2 .btn_edit_modal").hide();
    $("#myModal2 .btn-danger").show();
    $("#myModal2 .btn_save_modal").hide();

    $("#myModal2 .btn_save_modal").removeAttr("disabled");

    var url =
      url_raiz +
      "/" +
      "cxc/revisar_anticipos" +
      "/" +
      $("#core_tercero_id").val() +
      "?origen=ventas_pos";

    $.get(url, function (respuesta) {
      $("#div_spin2").hide();
      $("#contenido_modal2").html(respuesta);
    });
  });

  $(document).on("change", "#checkbox_head", function () {

    $("#valor_anticipo_aplicar").val( 0 );
    $("#label_anticipo_aplicar").text( '$ ' );

    // Recorremos todos los checkboxes de las filas
    $(".checkbox_fila").each(function () {

      // Si el checkbox del encabezado está marcado, marcar todas las filas
      if ($("#checkbox_head").is(":checked")) {
        $(this).prop("checked", true);

        var nuevo_valor_anticipo_aplicar = parseFloat( $("#valor_anticipo_aplicar").val() ) + parseFloat( $(this).attr('data-valor_aplicar') );

        $("#valor_anticipo_aplicar").val( nuevo_valor_anticipo_aplicar );

        $("#label_anticipo_aplicar").text( '$ ' + new Intl.NumberFormat("de-DE").format(
          nuevo_valor_anticipo_aplicar.toFixed(0) ) );
        
        $(this).next(".checkbox_aux").text(1);       

      } else {
        $(this).prop("checked", false);
        
        var nuevo_valor_anticipo_aplicar = 0;

        $("#valor_anticipo_aplicar").val( nuevo_valor_anticipo_aplicar );

        $("#label_anticipo_aplicar").text( '$ ' + new Intl.NumberFormat("de-DE").format(
          nuevo_valor_anticipo_aplicar.toFixed(0) ) );

        $(this).next(".checkbox_aux").text(0);
      }

    });

  });

  $(document).on("change", ".checkbox_fila", function () {
    if ($(this).is(":checked")) {
      
      var nuevo_valor_anticipo_aplicar = parseFloat( $("#valor_anticipo_aplicar").val() ) + parseFloat( $(this).attr('data-valor_aplicar') );

        $("#valor_anticipo_aplicar").val( nuevo_valor_anticipo_aplicar );

        $("#label_anticipo_aplicar").text( '$ ' + new Intl.NumberFormat("de-DE").format(
          nuevo_valor_anticipo_aplicar.toFixed(0) ) );

      $(this).next(".checkbox_aux").text(1);
    } else {
      
      var nuevo_valor_anticipo_aplicar = parseFloat( $("#valor_anticipo_aplicar").val() ) - parseFloat( $(this).attr('data-valor_aplicar') );

        $("#valor_anticipo_aplicar").val( nuevo_valor_anticipo_aplicar );

        $("#label_anticipo_aplicar").text( '$ ' + new Intl.NumberFormat("de-DE").format(
          nuevo_valor_anticipo_aplicar.toFixed(0) ) );

      $(this).next(".checkbox_aux").text(0);
    }
  });

  $(document).on("click", "#btn_aplicar_anticipo", function (event) {
    event.preventDefault();
    
      var btn_borrar = "<button type='button' class='btn btn-danger btn-xs btn_eliminar_linea_medio_recaudo'><i class='fa fa-btn fa-trash'></i></button>";

      celda_valor_total = '<td class="valor_total">$' + $("#valor_anticipo_aplicar").val() + '</td>';

      $('#ingreso_registros_medios_recaudo').find('tbody:last').append('<tr>'+
            '<td><span style="color:white;">0-</span><span>Anticipo/Saldo a favor</span></td>'+
            '<td><span style="color:white;">' + $("#teso_motivo_default_id").val() + '-</span><span>Ventas de contado</span></td>'+ '<td><span style="color:white;">0-</span><span></span></td>' + '<td><span style="color:white;">0-</span><span></span></td>' + celda_valor_total + '<td>' + btn_borrar + '</td>' + '</tr>');

        // Se calculan los totales para la última fila
        calcular_totales_medio_recaudos();
        reset_form_registro();

        $("#object_anticipos").val( generar_string_cxc_anticipos() );

        $("#myModal2").modal("hide");

        // deshabilitar_campos_form_create();
        $('#btn_guardar').show();
    
  });

  function generar_string_cxc_anticipos() {
    var string_cxc_anticipos = '';
    $(".checkbox_fila").each(function () {
      if ($(this).is(":checked")) {
        string_cxc_anticipos += '{"cxc_movimiento_id":"' + $(this).attr("data-cxc_movimiento_id") + '","Documento":"--","Fecha":"--","saldo_pendiente":"000","valor_aplicar":"-' + $(this).attr("data-valor_aplicar") + '"}' + ",";
      }
    });

    return string_cxc_anticipos.slice(0, -1); // Eliminar la última coma
  }

  

});
