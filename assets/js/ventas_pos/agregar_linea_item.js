
/**
 * 
 */
function agregar_la_linea_ini() {
    // Se escogen los campos de la fila ingresada
    var fila = $("#linea_ingreso_default_aux");
  
    // agregar nueva fila a la tabla
    $("#ingreso_registros").find("tfoot:last").append(fila);
  
    $("#inv_producto_id").focus();
}

/**
 * 
 * @returns boolean
 */
function agregar_la_linea() {
    if (!validar_venta_menor_costo()) {
        return false;
    }

    $("#popup_alerta").hide();
    $("#existencia_actual").html("");
    $("#existencia_actual").hide();

    if (!$.isNumeric(parseInt($("#core_tercero_id").val()))) {
        Swal.fire({
        icon: "error",
        title: "Alerta!",
        text: "Error al seleccionar el cliente. Ingrese un cliente correcto.",
        });

        return false;
    }

    var string_fila = generar_string_celdas();

    if (string_fila == false) {
        $("#popup_alerta").show();
        $("#popup_alerta").css("background-color", "red");
        $("#popup_alerta").text("Producto no encontrado.");
        return false;
    }

    // agregar nueva fila a la tabla
    $("#ingreso_registros")
        .find("tbody:last")
        .append(
        '<tr class="linea_registro" data-numero_linea="' +
            numero_linea +
            '">' +
            string_fila +
            "</tr>"
        );

    // Se calculan los totales
    calcular_totales();

    hay_productos++;
    $("#btn_nuevo").show();
    $("#numero_lineas").text(hay_productos);
    set_cantidades_ingresadas();
    //deshabilitar_campos_encabezado();

    // Bajar el Scroll hasta el final de la página
    //$("html, body").animate({ scrollTop: $(document).height() + "px" });

    reset_linea_ingreso_default();
    reset_efectivo_recibido();

    $("#total_valor_total").actualizar_medio_recaudo();

    numero_linea++;
    $("#efectivo_recibido").removeAttr("readonly");

    //if ($("#manejar_platillos_con_contorno").val() == 1) {
    //    reset_component_items_contorno();
    //}
}

/**
 * 
 * @param {*} item_id 
 * Se llama desde el listado de productos (boton de la lupa)
 * SI agrega la linea de Registro. No cierra la Ventana modal.
 */
function mandar_codigo(item_id) {
    var producto = productos.find((item) => item.id === parseInt(item_id));

    tasa_impuesto = producto.tasa_impuesto;
    inv_producto_id = producto.id;
    unidad_medida = producto.unidad_medida1;
    costo_unitario = producto.costo_promedio;

    $("#inv_producto_id").val(producto.descripcion);

    $("#existencia_actual").html(
        "Stock: " + producto.existencia_actual.toFixed(2)
    );
    //$('#existencia_actual').show();

    $("#precio_unitario").val(get_precio(producto.id));
    $("#tasa_descuento").val(get_descuento(producto.id));

    $("#cantidad").val(1);
    cantidad = 1;

    calcular_valor_descuento();
    calcular_impuestos();
    
    if (calcular_precio_total()) {
        agregar_la_linea();
    }
    
}

/**
 * 
 * @param {*} item_id 
 * @returns booblean
 * Agrega la linea completa del item (Usanda en Tactil)
 */
function mandar_codigo2(item_id) {
    var producto = productos.find((item) => item.id === parseInt(item_id));

    tasa_impuesto = producto.tasa_impuesto;
    inv_producto_id = producto.id;
    unidad_medida = producto.unidad_medida1;
    costo_unitario = producto.costo_promedio;

    $("#inv_producto_id").val(producto.descripcion);
    $("#precio_unitario").val(get_precio(producto.id));
    $("#tasa_descuento").val(get_descuento(producto.id));

    cantidad = 1;
    $("#cantidad").val(cantidad);
    calcular_valor_descuento();
    calcular_impuestos();

    if (!calcular_precio_total()) {
        $("#popup_alerta").show();
        $("#popup_alerta").css("background-color", "red");
        $("#popup_alerta").text("Error en precio total. Por favor verifique");
        return false;
    }

    numero_linea = 1;
    agregar_la_linea();
    mostrar_mensaje_item_agregado()
}

/**
 * 
 * @param {*} item_id 
 * Se llama desde el listado de productos (boton de la lupa). 
 * NO agrega la lìnea de registro. El focus queda ubicado en Cantidad
 */
function mandar_codigo3(item_id) {
    $("#myModal").modal("hide");

    var producto = productos.find((item) => item.id === parseInt(item_id));

    tasa_impuesto = producto.tasa_impuesto;
    inv_producto_id = producto.id;
    unidad_medida = producto.unidad_medida1;
    costo_unitario = producto.costo_promedio;

    $("#inv_producto_id").val(producto.descripcion);
    $("#precio_unitario").val(get_precio(producto.id));
    $("#tasa_descuento").val(get_descuento(producto.id));

    $("#cantidad").select();
}

/**
 * 
 * @param {*} item_id 
 * @returns boolean
 * Agrega la linea completa del item (Usanda en Filtros de items)
 */
function mandar_codigo4(item_id) {
    var producto = productos.find((item) => item.id === parseInt(item_id));

    tasa_impuesto = producto.tasa_impuesto;
    inv_producto_id = producto.id;
    unidad_medida = producto.unidad_medida1;
    costo_unitario = producto.costo_promedio;

    $("#inv_producto_id").val(producto.descripcion);
    $("#precio_unitario").val(get_precio(producto.id));
    $("#tasa_descuento").val(get_descuento(producto.id));

    var quantity = $("#quantity").val();

    if ($.isNumeric(quantity)) {
        cantidad = quantity;
    } else {
        cantidad = 1;
    }

    $("#cantidad").val(cantidad);
    calcular_valor_descuento();
    calcular_impuestos();


    if (!calcular_precio_total()) {
        $("#popup_alerta").show();
        $("#popup_alerta").css("background-color", "red");
        $("#popup_alerta").text("Error en precio total. Por favor verifique");
        return false;
    }
    numero_linea = 1;
    agregar_la_linea();
    $("#quantity").val("");
    mostrar_mensaje_item_agregado()
}

/**
 * 
 */
function mostrar_mensaje_item_agregado() 
{
    $("#popup_alerta").hide(200);
    $("#popup_alerta").css("background-color", "#00b998");
    $("#popup_alerta").css("color", "black");
    $("#popup_alerta").css("opacity", "revert");
    $("#popup_alerta").text("Producto agregado.");
    $("#popup_alerta").show(200);
}