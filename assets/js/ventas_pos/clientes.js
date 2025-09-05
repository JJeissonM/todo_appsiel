
var cantidad_clientes_local, numero_identificacion_existe;

/**
 *
 * @returns boolean
 */
function set_cliente_default() 
{
  if (
    $("#lista_precios_id").val() != cliente_default.lista_precios_id &&
    hay_productos > 0
  ) {
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
function seleccionar_cliente( item_sugerencia )
{
  if (
    $("#lista_precios_id").val() !=
    item_sugerencia.attr("data-lista_precios_id") &&
    hay_productos > 0
  ) {
    Swal.fire({
      icon: "error",
      title: "Alerta!",
      text: "El cliente seleccionado tiene una Lista de precios DIFERENTE para los productos ingresados. Debe retirar los productos ingresados.",
    });
    return false;
  }

  // Asignar descripción al TextInput
  $("#cliente_input").val( item_sugerencia.html());
  $("#cliente_input").css("background-color", "transparent");

  // Asignar Campos ocultos
  $("#cliente_id").val( item_sugerencia.attr("data-cliente_id"));
  $("#zona_id").val( item_sugerencia.attr("data-zona_id"));
  $("#clase_cliente_id").val( item_sugerencia.attr("data-clase_cliente_id"));
  $("#liquida_impuestos").val( item_sugerencia.attr("data-liquida_impuestos"));
  $("#core_tercero_id").val( item_sugerencia.attr("data-core_tercero_id"));
  $("#lista_precios_id").val( item_sugerencia.attr("data-lista_precios_id"));
  $("#lista_descuentos_id").val(
    item_sugerencia.attr("data-lista_descuentos_id")
  );

  // Asignar resto de campos
  $("#vendedor_id").val( item_sugerencia.attr("data-vendedor_id"));
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
  $(document).prop("title", item_sugerencia.attr("data-vendedor_descripcion"));

  $("#inv_bodega_id").val( item_sugerencia.attr("data-inv_bodega_id"));

  $("#cliente_descripcion").val( item_sugerencia.attr("data-nombre_cliente"));
  $("#cliente_descripcion_aux").val(
    item_sugerencia.attr("data-nombre_cliente")
  );
  $("#numero_identificacion").val(
    item_sugerencia.attr("data-numero_identificacion")
  );
  $("#email").val( item_sugerencia.attr("data-email"));
  $("#direccion1").val( item_sugerencia.attr("data-direccion1"));
  $("#telefono1").val( item_sugerencia.attr("data-telefono1"));

  var forma_pago = "contado";
  var dias_plazo = parseInt( item_sugerencia.attr("data-dias_plazo"));
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

function consultar_clientes(query) {
  var textoFiltro = query.toLowerCase();

  $(".filtros").html("");

  arr_texto_filtro = textoFiltro.split(" ");

  var clientes_filtered = clientes;

  for (let index = 0; index < arr_texto_filtro.length; index++) {
    const element = arr_texto_filtro[index];

    var items_to_draw = [];
    $.each(clientes_filtered, function (key, item) {
      var label = item.numero_identificacion + " " + item.descripcion;
      if (label.toLowerCase().indexOf(element) === -1) {
        // No existe
      } else if (label.toLowerCase().indexOf(element) > -1) {
        items_to_draw.push(item);
      }
    });
    clientes_filtered = items_to_draw;
  }

  data = draw_suggestion_list(clientes_filtered);
  
  $("#clientes_suggestions").show().html(data);

  $("a.list-group-item.active").focus();

  return false;
}

function draw_suggestion_list(lista_clientes) {
  var html = '<div class="list-group">';
  var es_el_primero = true;
  var ultimo_cliente = 0;
  cantidad_clientes_local = 1;
  var cantidad_datos = clientes.length;

  $.each(lista_clientes, function (key, cliente) {
    var primer_cliente = 0;
    var clase = "";
    if (es_el_primero) {
      clase = "active";
      es_el_primero = false;
      primer_cliente = 1;
    }

    if (cantidad_clientes_local == cantidad_datos) {
      ultimo_cliente = 1;
    }

    //var label = cliente.referencia + " " + cliente.descripcion + " (" + cliente.id + ")";

    html += get_linea_item_sugerencia(cliente, clase, primer_cliente, ultimo_cliente);

    cantidad_clientes_local++;
  });

  // Linea crear nuevo registro
  var modelo_id = 138; // App\Ventas\Clientes
  var app_id = 20; // App Ventas POS

  html +=
    '<button href="' +
    url_raiz +
    "/vtas_clientes/create?id=" +
    app_id +
    "&id_modelo=" +
    modelo_id +
    "&id_transaccion" +
    '" target="_blank" class="list-group-item list-group-item-sugerencia-crear-nuevo list-group-item-info" data-modelo_id="' +
    modelo_id +
    '" data-accion="crear_nuevo_registro" > + Crear nuevo </button>';

  html += "</div>";

  return html;
}

function get_linea_item_sugerencia(cliente, clase, primer_item, ultimo_item) {
  var descripcion = cliente.descripcion;
  if (cliente.razon_social != "") {
    descripcion += " (" + cliente.razon_social + ")";
  }
  
  var vendedor = vendedores.find((item) => item.id === cliente.vendedor_id);

  var html =
    '<a class="list-group-item list-group-item-cliente ' +
    clase +
    '" data-cliente_id="' +
    cliente.id +
    '" data-primer_item="' +
    primer_item +
    '" data-accion="na" ' +
    '" data-ultimo_item="' +
    ultimo_item; // Esto debe ser igual en todas las busquedas

  html +=
    '" data-nombre_cliente="' +
    descripcion +
    '" data-zona_id="' +
    cliente.zona_id +
    '" data-clase_cliente_id="' +
    cliente.clase_cliente_id +
    '" data-liquida_impuestos="' +
    cliente.liquida_impuestos +
    '" data-core_tercero_id="' +
    cliente.core_tercero_id +
    '" data-direccion1="' +
    cliente.direccion1 +
    '" data-telefono1="' +
    cliente.telefono1 +
    '" data-numero_identificacion="' +
    cliente.numero_identificacion +
    '" data-vendedor_id="' +
    vendedor.id +
    '" data-vendedor_descripcion="' +
    vendedor.descripcion +
    '" data-equipo_ventas_id="0' +
    '" data-inv_bodega_id="' +
    cliente.inv_bodega_id +
    '" data-email="' +
    cliente.email +
    '" data-dias_plazo="' +
    cliente.dias_plazo +
    '" data-lista_precios_id="' +
    cliente.lista_precios_id +
    '" data-lista_descuentos_id="' +
    cliente.lista_descuentos_id +
    '" > ' +
    descripcion +
    " (" + 
    new Intl.NumberFormat("de-DE").format( cliente.numero_identificacion ) +
    ") </a>";

  return html;
}

  /**
   * 
   */
  function validar_numero_identificacion()
  {
    var documento = parseInt( $("#numero_identificacion_aux").val() );

    var direccion = location.href;

		if (direccion.search("edit") == -1) {
			url = '../core/validar_numero_identificacion/'; // crear
		} else {
			url = '../../core/validar_numero_identificacion/'; // editar
		}

    $.ajax({
            url: url + documento,
            data: data,
            type: 'GET',
            async: false,
            success: function( datos ){
                if (datos != '') {
                  if ( parseInt(datos) == documento ) {
                    $('#btn_save_cliente').hide();
                    numero_identificacion_existe = true;
                    alert("Ya existe una persona con ese número de documento de identidad. Cambié el número o no podrá guardar el registro.");
                    
                  } else {
                    // No hay problema
                    numero_identificacion_existe = false;
                    $('#btn_save_cliente').show();
                  }

                } else {
                  // Número de identificación no existe
                  numero_identificacion_existe = false;
                  $('#btn_save_cliente').show();
                }
            },
            error: function( xhr ){
         
            }
    });
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
        $("#cliente_input").val($("#cliente_descripcion_aux").val());
        $(this).select();
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
        // Si la longitud es menor a dos, todavía no busca
        if ($(this).val().length < 2) {
          $("#clientes_suggestions").hide();
          return false;
        }

        // Manejo código de producto o nombre
        var campo_busqueda = "descripcion";
        if ($.isNumeric($(this).val())) {
          var campo_busqueda = "numero_identificacion";
        }

        consultar_clientes( $(this).val() );

        if ( cantidad_clientes_local > 1) {
          return false;
        }

        var url = "../vtas_consultar_clientes";

        if ($("#action").val() == "edit") {
          var url = "../../vtas_consultar_clientes";
        }

        $.get(url, {
          texto_busqueda: $(this).val(),
          campo_busqueda: campo_busqueda,
          url_id: $("#url_id").val(),
          enlace_tipo_boton: 'true',
        }).done(function (data) {
          // Se llena el DIV con las sugerencias que arroja la consulta
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

  
  //Al hacer click en alguna de las sugerencias (escoger un cliente)
  $(document).on("click", "#btn_create_cliente", function () {
    
    modal_create_cliente();

    return false;
  });

  /**
   * 
   */
  function modal_create_cliente()
  {
    //var url = "../vtas_consultar_clientes";

    var url =  url_raiz + "/pos_clientes/create?id=13&id_modelo=138&id_transaccion";

    $.get(url)
    .done(function (data) {
      
      $("#myModal2").modal({ backdrop: "static" });
      $("#div_spin2").hide();
    
      $("#myModal2 .modal-title").text("Creación de nuevo cliente");
    
      $("#myModal2 .btn_edit_modal").hide();
      $("#myModal2 .btn-danger").show();
      $("#myModal2 .btn_save_modal").show();
      $("#myModal2 .btn_save_modal").attr('id', 'btn_save_cliente');
      $("#myModal2 .btn_save_modal").attr('class', 'btn btn-primary');
      $("#btn_save_cliente").removeAttr('disabled');
      $("#myModal2 .close").show();

      $("#contenido_modal2").html(data);

      $('#razon_social').parent().parent().hide();
    });
  }

  /**
   * 
   */
  $(document).on("click", "#btn_save_cliente", function () {
    
      if (!validar_requeridos()) {
        return false;
      }

      //validar_numero_identificacion_aux();

      console.log(numero_identificacion_existe);

      if (numero_identificacion_existe) {
        return false;
      }

      $("#btn_save_cliente").attr('disabled', 'disabled');

      var formulario = $('#myModal2').find('form');

      var url = formulario.attr('action') + "?id=20&modelo_id=138";
      var data = formulario.serialize();

      $.post(url, data, function (respuesta) {

        /*
        */
        $("#btn_save_cliente").attr('class', 'btn btn-primary btn_save_modal');
        $("#btn_save_cliente").removeAttr('disabled');
        $("#btn_save_cliente").removeAttr('id');

        $("#contenido_modal2").html('');
        $('#myModal2').modal("hide");
        
        enfocar_tab_totales();

        clientes.push(respuesta);

        seleccionar_cliente_creado( respuesta );
        $('#cliente_input').css('background-color', '#f8ff6d');

      });
  });

  
/**
 *
 * @param {*} item_sugerencia
 * @returns boolean
 */
function seleccionar_cliente_creado( item_sugerencia ) {
  if ( $("#lista_precios_id").val() != item_sugerencia.lista_precios_id && hay_productos > 0 )
  {
    Swal.fire({
      icon: "error",
      title: "Alerta!",
      text: "El cliente Creado tiene una Lista de precios DIFERENTE para los productos ingresados. Debe retirar los productos ingresados.",
    });
    return false;
  }

  // Asignar descripción al TextInput
  $("#cliente_input").val( item_sugerencia.descripcion );
  $("#cliente_input").css("background-color", "transparent");

  // Asignar Campos ocultos
  $("#cliente_id").val( item_sugerencia.id );
  $("#zona_id").val( item_sugerencia.zona_id);
  $("#clase_cliente_id").val( item_sugerencia.clase_cliente_id);
  $("#liquida_impuestos").val( item_sugerencia.liquida_impuestos);
  $("#core_tercero_id").val( item_sugerencia.core_tercero_id);
  $("#lista_precios_id").val( item_sugerencia.lista_precios_id);
  $("#lista_descuentos_id").val( item_sugerencia.lista_descuentos_id );

  // Asignar resto de campos
  $("#vendedor_id").val( item_sugerencia.vendedor_id);
  $("#vendedor_id").attr(
    "data-vendedor_descripcion",
    item_sugerencia.vendedor_descripcion
  );
  $(".vendedor_activo").attr("class", "btn btn-default btn_vendedor");
  $(
    "button[data-vendedor_id='" +
    item_sugerencia.vendedor_id +
    "']"
  ).attr("class", "btn btn-default btn_vendedor vendedor_activo");
  $(document).prop("title", item_sugerencia.vendedor_descripcion);

  $("#inv_bodega_id").val( item_sugerencia.inv_bodega_id);

  $("#cliente_descripcion").val( item_sugerencia.nombre_cliente);
  $("#cliente_descripcion_aux").val(
    item_sugerencia.nombre_cliente
  );
  $("#numero_identificacion").val(
    item_sugerencia.numero_identificacion
  );
  $("#email").val( item_sugerencia.email);
  $("#direccion1").val( item_sugerencia.direccion1);
  $("#telefono1").val( item_sugerencia.telefono1);

  var forma_pago = "contado";
  var dias_plazo = parseInt( item_sugerencia.dias_plazo);
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

  set_lista_precios();

  if (!$.isNumeric(parseInt($("#core_tercero_id").val()))) {
    Swal.fire({
      icon: "error",
      title: "Alerta!",
      text: "Error al seleccionar el cliente. Ingrese un cliente correcto.",
    });
  }

  activar_boton_guardar_factura();

}

  
  /**
   * 
   */
	$(document).on('blur', '#numero_identificacion_aux', function () {
    validar_numero_identificacion();
	});


  //Al hacer click en la sugerencia Crear nuevo
  $(document).on(
    "click",
    ".list-group-item-sugerencia-crear-nuevo",
    function () {
      $("#cliente_input").css("background-color", "transparent");

      $("#clientes_suggestions").html("");
      $("#clientes_suggestions").hide();

      set_cliente_default();

      if ($(this).attr("data-accion") == "crear_nuevo_registro") {
        modal_create_cliente();
      }

      return false;
    }
  );
});
