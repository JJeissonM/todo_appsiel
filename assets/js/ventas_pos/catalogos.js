var contornos_permitidos;
var productos,
  precios,
  todos_los_precios,
  descuentos,
  todos_los_descuentos,
  clientes,
  cliente_default,
  forma_pago_default,
  fecha_vencimiento_default,
  vendedores,
  dataform_modelo_cliente;

function set_catalogos(pdv_id) {
    $("#contenido_modal2").html("Cargando recursos... por favor espere.");
  
    $("#contenido_modal2").attr(
      "style",
      "text-align:center;color:#fff;font-size:22px;font-weight:700;letter-spacing:.3px;text-shadow:0 2px 14px rgba(0,0,0,.95);"
    );
    $("#myModal2 .modal-body").attr(
      "style",
      "height:100%;display:flex;flex-direction:column;justify-content:center;"
    );
    $("#myModal2 .modal-content").attr(
      "style",
      "background: rgba(0, 0, 0, 0.22);border: 1px solid rgba(255,255,255,.12);box-shadow: 0 12px 34px rgba(0,0,0,.35);height:100%;"
    );
    $("#myModal2 .modal-dialog").attr(
      "style",
      "width:90%;max-width:98vw;height:98vh;margin:1vh auto;"
    );
    $("#div_spin2 img").attr(
      "style",
      "filter: drop-shadow(0 0 16px rgba(255,255,255,.75)) drop-shadow(0 0 30px rgba(66,163,220,.7));"
    );
    $("#div_spin2").fadeIn();
    
    $("#myModal2")
      .data("catalogos_loading_lock", true)
      .off("hide.bs.modal.catalogos_loading")
      .on("hide.bs.modal.catalogos_loading", function (e) {
        if ($(this).data("catalogos_loading_lock")) {
          e.preventDefault();
          return false;
        }
      })
      .off("shown.bs.modal.catalogos_loading")
      .on("shown.bs.modal.catalogos_loading", function () {
        $(".modal-backdrop.in")
          .last()
          .css({ "background-color": "#000", opacity: "0.8" });
      });

    $("#myModal2").modal({ backdrop: "static", keyboard: false, show: true });
  
    $("#myModal2 .modal-title").text("");
  
    $("#myModal2 .btn_edit_modal").hide();
    $("#myModal2 .btn-danger").hide();
    $("#myModal2 .btn_save_modal").hide();
    $("#myModal2 .close").hide();
  
    $.get(url_raiz + "/ventas_pos_set_catalogos" + "/" + pdv_id)
    .done(function ( datos ) {
      redondear_centena = datos.redondear_centena;
  
      productos = datos.productos;
      precios = datos.precios;
      todos_los_precios = datos.todos_los_precios;
      descuentos = datos.descuentos;
      todos_los_descuentos = datos.todos_los_descuentos;
      clientes = datos.clientes;
      cliente_default = datos.cliente_default;
      forma_pago_default = datos.forma_pago_default;
      fecha_vencimiento_default = datos.fecha_vencimiento_default;
      contornos_permitidos = datos.contornos_permitidos;
      vendedores = datos.vendedores;
      
      dataform_modelo_cliente = datos.dataform_modelo_cliente;
  
      if ($("#action").val() == "edit") {
        set_lista_precios();
        set_cantidades_ingresadas();
      }
  
      finalizar_modal_carga_catalogos(true);
    })
    .fail(function( jqXHR, textStatus, errorThrown ) {


      // If the server returned HTML, you can parse it and find elements by class
      if (jqXHR.responseText) {
        try {
          // Create a temporary DOM from the response
          var responseDOM = $("<div>").html(jqXHR.responseText);

          // Find elements by class in the error HTML
          var errorMessages = responseDOM.find(".block_exception.clear_fix").map(function () {
            return $(this).text().trim();
          }).get();

          Swal.fire({
            icon: 'error',
            title: 'Error al cargar los catálogos!',
            text: errorMessages
          });

        } catch (e) {
          alert("Error parsing response:", e);
        }
      }

      finalizar_modal_carga_catalogos();

    });
  }

function finalizar_modal_carga_catalogos(enfocar_producto = false) {
  if (enfocar_producto) {
    $("#myModal2")
      .off("hidden.bs.modal.catalogos_focus")
      .one("hidden.bs.modal.catalogos_focus", function () {
        var $campo_producto = $("#inv_producto_id");
        $campo_producto.focus();
        if ($campo_producto.is("input, textarea")) {
          $campo_producto.select();
        }
      });
  }

  $("#myModal2").data("catalogos_loading_lock", false);
  $("#myModal2 .modal-content").removeAttr("style");
  $("#myModal2 .modal-dialog").removeAttr("style");
  $("#myModal2 .modal-body").removeAttr("style");
  $("#contenido_modal2").removeAttr("style");
  $("#div_spin2 img").removeAttr("style");
  $("#myModal2 .close").show();
  $("#myModal2").modal("hide");
}
