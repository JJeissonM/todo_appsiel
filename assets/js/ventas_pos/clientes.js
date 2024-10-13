
/**
 * 
 * @returns boolean
 */
function set_cliente_default()
{
  if ( $("#lista_precios_id").val() != cliente_default.lista_precios_id && hay_productos > 0) {
    Swal.fire({
      icon: "error",
      title: "Alerta!",
      text: "No puede cambiar a Consumidor Final. El cliente cliente seleccionado tiene una Lista de precios DIFERENTE para los productos ingresados. Debe retirar los productos ingresados.",
    });
    return false;
  }

  $("#cliente_id").val(cliente_default.id);
  $("#cliente_input").val(cliente_default.descripcion);
  $("#cliente_input").css("background-color", "transparent");

  $("#inv_bodega_id").val(cliente_default.inv_bodega_id);
  $("#forma_pago").val(forma_pago_default);
  $("#fecha_vencimiento").val(fecha_vencimiento_default);
  $("#lista_precios_id").val(cliente_default.lista_precios_id);
  $("#lista_descuentos_id").val(cliente_default.lista_descuentos_id);
  $("#liquida_impuestos").val(cliente_default.liquida_impuestos);
  $("#core_tercero_id").val(cliente_default.core_tercero_id);
  $("#zona_id").val(cliente_default.zona_id);
  $("#clase_cliente_id").val(cliente_default.clase_cliente_id);

  $("#cliente_descripcion_aux").val(cliente_default.descripcion);
  $("#numero_identificacion").val(cliente_default.numero_identificacion);
  $("#direccion1").val(cliente_default.direccion1);
  $("#telefono1").val(cliente_default.telefono1);
  
  set_lista_precios();
}

/**
 * 
 * @param {*} item_sugerencia 
 * @returns boolean
 */
function seleccionar_cliente(item_sugerencia) {

    if ( $("#lista_precios_id").val() != item_sugerencia.attr("data-lista_precios_id") && hay_productos > 0) {
      Swal.fire({
        icon: "error",
        title: "Alerta!",
        text: "El cliente seleccionado tiene una Lista de precios DIFERENTE para los productos ingresados. Debe retirar los productos ingresados.",
      });
      return false;
    }
  
    // Asignar descripción al TextInput
    $("#cliente_input").val(item_sugerencia.html());
    $("#cliente_input").css("background-color", "transparent");
  
    // Asignar Campos ocultos
    $("#cliente_id").val(item_sugerencia.attr("data-cliente_id"));
    $("#zona_id").val(item_sugerencia.attr("data-zona_id"));
    $("#clase_cliente_id").val(item_sugerencia.attr("data-clase_cliente_id"));
    $("#liquida_impuestos").val(item_sugerencia.attr("data-liquida_impuestos"));
    $("#core_tercero_id").val(item_sugerencia.attr("data-core_tercero_id"));
    $("#lista_precios_id").val(item_sugerencia.attr("data-lista_precios_id"));
    $("#lista_descuentos_id").val(
      item_sugerencia.attr("data-lista_descuentos_id")
    );
  
    // Asignar resto de campos
    $("#vendedor_id").val(item_sugerencia.attr("data-vendedor_id"));
    $("#vendedor_id").attr(
      "data-vendedor_descripcion",
      item_sugerencia.attr("data-vendedor_descripcion")
    );
    $(".vendedor_activo").attr("class", "btn btn-default btn_vendedor");
    $(
      "button[data-vendedor_id='" +
        item_sugerencia.attr("data-vendedor_id") +
        "']"
    ).attr("class", "btn btn-default btn_vendedor vendedor_activo");
    $(document).prop(
      "title",
      item_sugerencia.attr("data-vendedor_descripcion")
    );
  
    $("#inv_bodega_id").val(item_sugerencia.attr("data-inv_bodega_id"));
  
    $("#cliente_descripcion").val(item_sugerencia.attr("data-nombre_cliente"));
    $("#cliente_descripcion_aux").val(
      item_sugerencia.attr("data-nombre_cliente")
    );
    $("#numero_identificacion").val(
      item_sugerencia.attr("data-numero_identificacion")
    );
    $("#email").val(item_sugerencia.attr("data-email"));
    $("#direccion1").val(item_sugerencia.attr("data-direccion1"));
    $("#telefono1").val(item_sugerencia.attr("data-telefono1"));
  
    var forma_pago = "contado";
    var dias_plazo = parseInt(item_sugerencia.attr("data-dias_plazo"));
    if (dias_plazo > 0) {
      forma_pago = "credito";
    }
    $("#forma_pago").val(forma_pago);
  
    // Para llenar la fecha de vencimiento
    var fecha = new Date($("#fecha").val());
    fecha.setDate(fecha.getDate() + (dias_plazo + 1));
  
    var mes = fecha.getMonth() + 1; // Se le suma 1, Los meses van de 0 a 11
    var dia = fecha.getDate(); // + 1; // Se le suma 1,
  
    if (mes < 10) {
      mes = "0" + mes;
    }
  
    if (dia < 10) {
      dia = "0" + dia;
    }
    $("#fecha_vencimiento").val(fecha.getFullYear() + "-" + mes + "-" + dia);
  
    //Hacemos desaparecer el resto de sugerencias
    $("#clientes_suggestions").html("");
    $("#clientes_suggestions").hide();
  
    //reset_tabla_ingreso_items();
    //reset_resumen_de_totales();
    //reset_linea_ingreso_default();
  
    set_lista_precios();
  
    if (!$.isNumeric(parseInt($("#core_tercero_id").val()))) {
      Swal.fire({
        icon: "error",
        title: "Alerta!",
        text: "Error al seleccionar el cliente. Ingrese un cliente correcto.",
      });
    }
  
    activar_boton_guardar_factura();
  
    // Bajar el Scroll hasta el final de la página
    //$("html, body").animate({scrollTop: $(document).height() + "px"});
  }

// AL CARGAR EL DOCUMENTO
$(document).ready(function () {

    $("#cliente_input").on("focus", function () {
      $(this).select();
    });
  
    $("#cliente_input").after('<div id="clientes_suggestions"> </div>');
  
  
    $("#cliente_input").on("blur", function (event) {
      if ($("#cliente_input").val() == "") {
        $("#cliente_input").val($("#cliente_descripcion_aux").val());
      }
    });
  
    // Al ingresar código, descripción o código de barras del producto
    $("#cliente_input").on("keyup", function (event) {
      var codigo_tecla_presionada = event.which || event.keyCode;
  
      switch (codigo_tecla_presionada) {
        case 27: // 27 = ESC
          $("#clientes_suggestions").html("");
          $("#clientes_suggestions").hide();
          break;
  
        case 40: // Flecha hacia abajo
          var item_activo = $("a.list-group-item.active");
          item_activo
            .next()
            .attr("class", "list-group-item list-group-item-cliente active");
          item_activo.attr("class", "list-group-item list-group-item-cliente");
          $("#cliente_input").val(item_activo.next().html());
          break;
  
        case 38: // Flecha hacia arriba
          $(".flecha_mover:focus").prev().focus();
          var item_activo = $("a.list-group-item.active");
          item_activo
            .prev()
            .attr("class", "list-group-item list-group-item-cliente active");
          item_activo.attr("class", "list-group-item list-group-item-cliente");
          $("#cliente_input").val(item_activo.prev().html());
          break;
  
        case 13: // Al presionar Enter
          if ($(this).val() == "") {
            return false;
          }
  
          var item = $("a.list-group-item.active");
  
          if (item.attr("data-cliente_id") === undefined) {
            alert("El cliente ingresado no existe.");
            //reset_campos_formulario();
          } else {
            seleccionar_cliente(item);
          }
          break;
  
        default:
          // Manejo código de producto o nombre
          var campo_busqueda = "descripcion";
          if ($.isNumeric($(this).val())) {
            var campo_busqueda = "numero_identificacion";
          }
  
          // Si la longitud es menor a tres, todavía no busca
          if ($(this).val().length < 2) {
            return false;
          }
  
          var url = "../vtas_consultar_clientes";
  
          if ($("#action").val() == "edit") {
            var url = "../../vtas_consultar_clientes";
          }
  
          $.get(url, {
            texto_busqueda: $(this).val(),
            campo_busqueda: campo_busqueda,
          }).done(function (data) {
            // Se llena el DIV con las sugerencias que arooja la consulta
            $("#clientes_suggestions").show().html(data);
            $("a.list-group-item.active").focus();
          });
          break;
      }
    });

    //Al hacer click en alguna de las sugerencias (escoger un cliente)
    $(document).on("click", ".list-group-item-cliente", function () {
        seleccionar_cliente($(this));

        return false;
    });

    //Al hacer click en la sugerencia Crear nuevo
    $(document).on("click", ".list-group-item-sugerencia", function () {
        $("#cliente_input").val(cliente_default.descripcion);
        $("#cliente_input").css("background-color", "transparent");

        $("#clientes_suggestions").html("");
        $("#clientes_suggestions").hide();

        if ($(this).attr("data-accion") == "crear_nuevo_registro") {
        window.open($(this).attr("href"), "_blank");
        }

        return false;
    });
  
  });
  