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
  
    $("#contenido_modal2").attr("style", "text-align: center;color: #42A3DC;");
    $("#myModal2 .modal-content").attr(
      "style",
      "background: transparent;border: 0px solid;box-shadow: none;"
    );
    $("#div_spin2").fadeIn();
  
    $("#myModal2").modal({ backdrop: "static" });
  
    $("#myModal2 .modal-title").text("");
  
    $("#myModal2 .btn_edit_modal").hide();
    $("#myModal2 .btn-danger").hide();
    $("#myModal2 .btn_save_modal").hide();
    $("#myModal2 .close").hide();
  
    $.get(url_raiz + "/ventas_pos_set_catalogos" + "/" + pdv_id).done(function (
      datos
    ) {
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
  
      $("#myModal2 .modal-content").removeAttr("style");
      $("#contenido_modal2").removeAttr("style");
      $("#myModal2 .close").show();
      $("#myModal2").modal("hide");
    });
  }