$(document).ready(function () {
  $(document).on("click", "#btn_revisar_anticipos", function (event) {
    event.preventDefault();

    $("#contenido_modal2").html("");
    $("#div_spin2").fadeIn();

    $("#myModal2").modal({ backdrop: "static" });

    $("#myModal2 .modal-title").text(
      "Anticipos del cliente " + $('#cliente_input').val()
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
      $("#core_tercero_id").val();

    $.get(url, function (respuesta) {
      $("#div_spin2").hide();
      $("#contenido_modal2").html(respuesta);
    });
  });
});
