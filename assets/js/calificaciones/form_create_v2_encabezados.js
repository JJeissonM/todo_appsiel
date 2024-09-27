
function get_pesos_totales() {
    var totalPesos = 0;
    $(".encabezado_calificacion").each(function () {
        totalPesos += parseFloat($(this).attr("data-peso"));
    });

    return totalPesos;
}

function verificar_hay_pesos() {

    if (get_pesos_totales() > 0) {
        return true;
    }

    return false;
}

function calcular_definitivas_promedio_poderado() {
    var numero_fila = 1;
    // Por cada fila de la tabla
    $("#tabla_registros > tbody > tr").each(function (i, item) {
        $("#calificacion_texto" + numero_fila).val(
            calcular_definitiva_una_fila_promedio_poderado(numero_fila).toFixed(2)
        );

        numero_fila++;
    });
}

function calcular_definitiva_una_fila_promedio_poderado(numero_fila) {
    // Por cada caja de texto de la fila
    var total_def = 0;
    $(".valores_" + numero_fila).each(function () {

        var peso_columna = get_valor_peso_columna($(this));

        total_def += this.value * (peso_columna / 100);
    });

    return total_def;
}

function calcular_definitivas_promedio_simple() {
    var total_def = 0;
    var numero_fila = 1;
    // Por cada fila de la tabla
    $("#tabla_registros > tbody > tr").each(function (i, item) {
        $("#calificacion_texto" + numero_fila).val(
            calcular_definitiva_una_fila_promedio_simple(numero_fila).toFixed(2)
        );

        numero_fila++;
    });
}

function calcular_definitiva_una_fila_promedio_simple(numero_fila) {
    var total_def = 0;

    // Por cada caja de texto de la fila
    var sumatoria_calificaciones = 0;
    var n = 0;
    $(".valores_" + numero_fila).each(function () {
        if ($.isNumeric(parseFloat(this.value)) && parseFloat(this.value) != 0) {
            sumatoria_calificaciones += parseFloat(this.value);
            n++;
        }
    });

    if (n != 0) {
        total_def = sumatoria_calificaciones / n;
    }

    return total_def;
}

// Se debe ejecutar cuando la ventana modal este abierta.
function cambiar_datos_boton(peso) {
    var columna_calificacion = $("#columna_calificacion").val();
    var btn_columna = $("#btn_" + columna_calificacion);
    btn_columna.attr("data-peso", peso);
    btn_columna.attr("title", "Peso= " + peso + "%");

    if (peso == 0) {
        var color_btn = "white";
    } else {
        var color_btn = "#50B794";
    }

    btn_columna.attr("style", "background-color: " + color_btn);
}

function recalcular_definitivas() {
    if (verificar_hay_pesos()) {
        calcular_definitivas_promedio_poderado();
    } else {
        calcular_definitivas_promedio_simple();
    }
}

function get_valor_peso_columna(obj_input_text) {
    var btn_columna, peso_columna;
    btn_columna = get_obj_boton_columna(obj_input_text);
    peso_columna = parseFloat(btn_columna.attr("data-peso"));
    return peso_columna;
}

function get_obj_boton_columna(obj_input_text) {
    var id_input_text, vec_aux_id, celda_encabezado_columna;

    id_input_text = obj_input_text.attr("id");
    vec_aux_id = id_input_text.split("_");
    celda_encabezado_columna = $(".celda_" + vec_aux_id[0]);
    return celda_encabezado_columna.find("button");
}

$(document).ready(function () {

    $(".encabezado_calificacion").on("click", function (e) {
        e.preventDefault();

        if( guardando )
        {
            console.log('Aun guardando');
            return false;
        }

        $("#alert_mensaje").hide();

        $("#contenido_modal").html("");

        $("#myModal").modal({
            keyboard: "true",
        });

        $("#div_spin").fadeIn();
        $(".btn_edit_modal").hide();
        $(".btn_save_modal").show();
        $(".modal-title").html(
            "Ingreso/Actualización encabezados de calificaciones"
        );

        var url =
            url_raiz +
            "/calificaciones_encabezados/create?columna_calificacion=" +
            $(this).val() +
            "&periodo_id=" +
            $("#id_periodo").val() +
            "&curso_id=" +
            $("#curso_id").val() +
            "&asignatura_id=" +
            $("#id_asignatura").val() +
            "&anio=" +
            $("#anio").val();

        $.get(url, function (respuesta) {
            $("#div_spin").hide();
            $("#contenido_modal").html(respuesta);
            $("#descripcion").focus();
        });
    });

    $(document).on("keyup", "#peso", function () {
        if (!validar_input_numerico($(this))) {
            $(".btn_save_modal").hide();
        } else {
            $(".btn_save_modal").show();
        }
    });

    $(document).on("click", ".btn_save_modal", function (e) {
        e.preventDefault();

        $("#alert_mensaje").hide();

        // Para un nuevo registro de encabezado que se le quiera almacenar un peso sin descripcion
        if (
            $("#id_encabezado_calificacion").val() == 0 &&
            $("#descripcion").val() == ""
        ) {
            $("#descripcion").focus();

            Swal.fire({
                icon: "error",
                title: "Alerta!",
                text: "Debe ingresar una descripción para la actividad.",
            });

            return false;
        }

        $("#div_spin").fadeIn();

        // Este formulario se genera al hacer click en el botón Cn (n=1,2,3,4,5,6,...,15) del encabezado
        var url = $("#formulario_modal").attr("action");
        var data = $("#formulario_modal").serialize();

        $.post(url, data,)
            .done( function (respuesta) {

                if (respuesta == "pesos") {
                    $("#div_spin").hide();

                    Swal.fire({
                        icon: "error",
                        title: "Alerta!",
                        text: "El peso total sobrepasa 100%, debe indicar un peso menor.",
                    });
                } else {
                    $("#div_spin").hide();

                    $("#alert_mensaje").fadeIn();

                    // Si la Descripcion de la actividad esta vacia entonces, fue eliminada.
                    if ($("#descripcion").val() == "") {
                        cambiar_datos_boton(0);
                    } else {
                        cambiar_datos_boton($("#peso").val());
                    }

                    if (verificar_hay_pesos()) {
                        $("#nota_hay_pesos").show();
                        $("#lbl_suma_pesos").html( get_pesos_totales() + '%' );
                        $("#warning_pesos").html( '' );
                        
                    } else {
                        $("#nota_hay_pesos").hide();
                    }

                    recalcular_definitivas();

                    guardar_calificaciones();

                    $("#myModal").modal("hide");

                    Swal.fire({
                        icon: "success",
                        title: "¡Muy bien!",
                        text:
                            "El encabezado de la calificación ha sido " +
                            respuesta +
                            " correctamente.",
                    });
                }
            })
            .fail(function(xhr, status, error) {
                
                if ( xhr.status == 401 ) { //
                    Swal.fire({
                        icon: "error",
                        title: "Alerta! Datos no almaceados.",
                        text: "La sesión se cerró de manera insperada. Por favor actualice la página y vuelva a iniciar sesión.",
                    });
                }
                
            });
    });
});
