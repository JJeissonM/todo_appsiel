var guardando;
var filas_modificadas = [];
var modoIngresoCalificaciones = "teclado";
var selectorPopup;
var selectorValues = [];
var selectorBuilt = false;
var currentSelectorInput;
function obtenerModoIngresoCalificaciones() {
    var valor = $("#modo_ingreso_calificaciones").val() || "teclado";
    return valor.toString().toLowerCase().trim();
}
function ventana(id, id_textbox, curso_id) {
    document.getElementById("caja_logro").value = id_textbox;

    window.open(
        url_raiz + "/calificaciones_logros/consultar" + "/" + id + "/" + curso_id,
        "Consulta de logros",
        "width=800,height=600,menubar=no"
    );
}

function getChildVar(a_value) {
    var caja;
    caja = document.getElementById("caja_logro").value;
    document.getElementById("logros_" + caja).value = a_value;
    $("#mensaje_guardadas").hide();
    $("#mensaje_sin_guardar").show();
    $("#bs_boton_guardar").prop("disabled", false);
}

$(document).ready(function () {
    checkCookie();
    modoIngresoCalificaciones = obtenerModoIngresoCalificaciones();
    // Fixed invalid selector below:
    // modoIngresoCalificaciones = #modo_ingreso_calificaciones.val() || "teclado";

    var escala_min = parseFloat($("#escala_min").val(), 10);
    var escala_max = parseFloat($("#escala_max").val(), 10);

    // 9 = Tab
    // 16 = Shift
    // 8 = Backspace
    var teclas_especiales = [9, 16];

    guardando = false;

    // Vaciar los inputs que tienen cero (0)
    $("input[type=text]").each(function () {
        var val = $(this).val();
        if (val == 0) {
            $(this).val("");
        }
    });

    // Sombrear la columna al seleccionar text input
    $("input[type=text]").on("focus", function () {
        var id = $(this).attr("id");
        var vec_id = id.split("_");
        $(".celda_" + vec_id[0]).css("background-color", "#a3e7fe");
    });

    // Quitar Sombra de la columna cuando el text input pierde el foco
    $("input[type=text]").on("blur", function () {
        var id = $(this).attr("id");
        var vec_id = id.split("_");
        $(".celda_" + vec_id[0]).css("background-color", "transparent");
        $("#tabla_registros th.celda_" + vec_id[0]).css(
            "background-color",
            "#e5e4e3"
        );
    });

    // Cuando se presiona una caja de texto
    $("input[type=text]").keyup(function (e) {

        var caja_texto_id = $(this).attr("id"); // Cx_x, x=1,2,3...15

        var n = caja_texto_id.split("_");
        
        var numero_fila = parseInt(n[1]);

        // Si se presiona flecha hacia abajo
        if (e.keyCode == 40) {
            var j = numero_fila + 1;
            var sig = "#" + n[0] + "_" + j;
            $(sig).focus().select();
            return false;
        }

        // Si se presiona flecha hacia arriba
        if (e.keyCode == 38) {
            var j = numero_fila - 1;
            var sig = "#" + n[0] + "_" + j;
            $(sig).focus().select();
            return false;
        }

        // inArray devuelve la posicion del codigo de la tecla presionada (e.keyCode) dentro del array: 0,1,... y un valor negativo si no se halla el codigo.

        // Si NO se presionan teclas especiales (El codigo no esta en el Array)
        if ($.inArray(e.keyCode, teclas_especiales) < 0) {
            var valorValido = validar_valor_ingresado($(this));
            if (valorValido) {
                recalcularFilaDesdeInput($(this));

                // Cuando cambie el valor de una celda, se cambian los mensajes
                $("#mensaje_guardadas").hide();
                $("#mensaje_sin_guardar").show();
                $("#bs_boton_guardar").prop("disabled", false);
                marcarFilaDesdeInput(this);
            }
        }
    });

    $(document).on("input change", ".caja_logros", function () {
        marcarFilaDesdeInput(this);
    });

    $(document).on("focus", "input[class*='valores_']", function () {
        showSelectorForInput($(this));
    });

    $(document).on("click", "input[class*='valores_']", function () {
        showSelectorForInput($(this));
    });

    $("#bs_boton_volver").click(function () {
        document.location.href = "{{ url()->previous() }}";
    });

    // Validar que sea numérico y que esté entre la escala de valoración
        function validar_valor_ingresado(obj) {
        if (obj.attr("class") == "caja_logros") {
            return true;
        }

        var valido = true;
        var raw = obj.val();
        if (raw != "" && !$.isNumeric(raw)) {
            Swal.fire({
                icon: "error",
                title: "Alerta!",
                text: "Debe ingresar solo números. Para decimales use punto (.). No la coma (,).",
            });

            obj.val("");
            valido = false;
            return valido;
        }

        if (raw != "") {
            var valor = parseFloat(raw);
            if (isNaN(valor) || valor < escala_min || valor > escala_max) {
                Swal.fire({
                    icon: "error",
                    title: "Alerta!",
                    text:
                    "La calificación ingresada está por fuera de la escala de valoración. Ingrese un número entre " +
                        escala_min +
                        " y " +
                        escala_max,
                });
                obj.val("");
                valido = false;
            }
        }

        return valido;
    }

    function obtenerNumeroFilaDesdeInput($input) {
        var id = $input.attr("id");
        if (!id) {
            return null;
        }

        var parts = id.split("_");
        if (parts.length < 2) {
            return null;
        }

        var numero = parseInt(parts[1], 10);
        return isNaN(numero) ? null : numero;
    }

    function recalcularFilaDesdeInput($input) {
        var numero_fila = obtenerNumeroFilaDesdeInput($input);
        if (numero_fila === null) {
            return;
        }

        var definitiva;
        if (verificar_hay_pesos()) {
            definitiva = calcular_definitiva_una_fila_promedio_poderado(numero_fila);
        } else {
            definitiva = calcular_definitiva_una_fila_promedio_simple(numero_fila);
        }

        $("#calificacion_texto" + numero_fila).val(definitiva.toFixed(2));
    }

    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + exdays * 24 * 60 * 60 * 1000);
        var expires = "expires=" + d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(";");
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == " ") {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    function checkCookie() {
        var mostrar_ayuda = getCookie("mostrar_ayuda_calificaciones_form");

        if (mostrar_ayuda == "true" || mostrar_ayuda == "") {
            $("#myModal").modal({
                keyboard: "true",
            });

            $(".modal-title").html("Ayuda");
            $(".btn_edit_modal").hide();
            $(".btn_save_modal").hide();

            /* <li class="list-group-item">Las calificaciones se almacenan automáticamente cada diez (10) segundos.</li> */
            $("#contenido_modal").html(
                '<div class="well well-lg"><ul class="list-group"><li class="list-group-item">Se pueden guardar las calificaciones en cualquier momento presionando el botón guardar y seguir ingresando información.</li>  <li class="list-group-item">Verifique que antes de salir de la página se muestre el mensaje <spam id="mensaje_guardadas" style="background-color: #b1e6b2;">Calificaciones guardadas</spam></li></ul> <div class="checkbox">  <label><input type="checkbox" name="mostrar_ayuda_calificaciones_form" id="mostrar_ayuda_calificaciones_form" value="true">No volver a mostrar este mensaje.</label> </div></div>'
            );

            setCookie("mostrar_ayuda_calificaciones_form", true, 365);

            $(document).on(
                "click",
                "#mostrar_ayuda_calificaciones_form",
                function () {
                    if ($(this).val() == "true") {
                        $(this).val("false");
                        setCookie("mostrar_ayuda_calificaciones_form", "false", 365);
                    } else {
                        $(this).val("true");
                        setCookie("mostrar_ayuda_calificaciones_form", "true", 365);
                    }
                }
            );
        }
    }

    function marcarFilaDesdeInput(input) {
        var $fila = $(input).closest("tr");
        var matricula_id = $fila.attr("data-matricula_id");
        if (!matricula_id) {
            return;
        }

        if ($.inArray(matricula_id, filas_modificadas) < 0) {
            filas_modificadas.push(matricula_id);
        }

        $("#mensaje_guardadas").hide();
        $("#mensaje_sin_guardar").show();
        $("#bs_boton_guardar").prop("disabled", false);
    }

    window.marcarFilaDesdeInput = marcarFilaDesdeInput;

    function quitarFilaModificada(matricula_id) {
        filas_modificadas = filas_modificadas.filter(function (id) {
            return id != matricula_id;
        });
    }

    window.quitarFilaModificada = quitarFilaModificada;

    function actualizarContador(valor) {
        $("#counter").html(valor);
        $(".counter").html(valor);
    }

    function marcarTodasFilasDesdeEncabezados() {
        $("#tabla_registros > tbody > tr").each(function () {
            var $fila = $(this);
            var $input = $fila.find("input[class*='valores_']").first();
            if ($input.length > 0) {
                marcarFilaDesdeInput($input);
            }
        });
    }

    window.marcarTodasFilasDesdeEncabezados = marcarTodasFilasDesdeEncabezados;

    function showSelectorForInput($input) {
        modoIngresoCalificaciones = obtenerModoIngresoCalificaciones();
        if (modoIngresoCalificaciones !== "botones") {
            return;
        }
        if (!selectorPopup) {
            ensureSelectorPopup();
        }
        currentSelectorInput = $input;
        selectorPopup.show();
        positionSelectorPopup($input);
    }

    function ensureSelectorPopup() {
        selectorPopup = $("#selector_calificacion_popup");
        if (!selectorPopup.length) {
            selectorPopup = $(
                "<div id='selector_calificacion_popup' class='selector-calificacion-popup'></div>"
            );
        }

        if (selectorPopup.parent().length && selectorPopup.parent()[0] !== document.body) {
            selectorPopup.appendTo("body");
        } else if (!selectorPopup.parent().length) {
            selectorPopup.appendTo("body");
        }

        buildSelectorValues();
        renderSelectorButtons();

        if (selectorBuilt) {
            return;
        }

        selectorPopup.on("click", ".selector-value", function () {
            var value = $(this).data("value");
            var formatted =
                typeof value === "number" ? value.toFixed(1) : value;
            if (currentSelectorInput) {
                currentSelectorInput.val(formatted);
                currentSelectorInput.trigger("input").trigger("change");
                if (validar_valor_ingresado(currentSelectorInput)) {
                    recalcularFilaDesdeInput(currentSelectorInput);
                }
                currentSelectorInput.focus();
                marcarFilaDesdeInput(currentSelectorInput);
            }
            hideSelectorPopup();
        });

        selectorPopup.on("click", ".selector-clear", function () {
            if (currentSelectorInput) {
                currentSelectorInput.val("");
                currentSelectorInput.trigger("input").trigger("change");
                recalcularFilaDesdeInput(currentSelectorInput);
                marcarFilaDesdeInput(currentSelectorInput);
            }
            hideSelectorPopup();
        });

        $(document).on("mousedown", function (e) {
            if (
                !selectorPopup.is(":visible") ||
                $(e.target).closest(selectorPopup).length > 0 ||
                $(e.target).closest("input[class*='valores_']").length > 0
            ) {
                return;
            }
            hideSelectorPopup();
        });

        $(window).on("resize scroll", hideSelectorPopup);

        selectorBuilt = true;
    }

    function buildSelectorValues() {
        selectorValues = [];
        var min = parseFloat($("#escala_min").val());
        var max = parseFloat($("#escala_max").val());
        if (isNaN(min) || isNaN(max) || min > max) {
            return;
        }
        var step = 0.1;
        var steps = Math.round((max - min) / step);
        for (var i = 0; i <= steps; i++) {
            var value = parseFloat((min + step * i).toFixed(1));
            selectorValues.push(value);
        }
        if (selectorValues.length === 0 || selectorValues[selectorValues.length - 1] < max) {
            selectorValues.push(max);
        }
    }

    function renderSelectorButtons() {
        if (!selectorPopup) {
            return;
        }
        var html = '<div class="selector-grid">';
        var sortedValues = selectorValues.slice().sort(function (a, b) {
            return b - a;
        });
        sortedValues.forEach(function (value) {
            html +=
                '<button type="button" class="selector-value" data-value="' +
                value +
                '">' +
                value.toFixed(1) +
                "</button>";
        });
        html += "</div>";
        html += '<button type="button" class="selector-clear">Borrar</button>';
        selectorPopup.html(html);
    }

    function positionSelectorPopup($input) {
        if (!selectorPopup) {
            return;
        }
        var offset = $input.offset();
        var left = offset.left;
        var top = offset.top + $input.outerHeight() + 4;
        var popupWidth = selectorPopup.outerWidth();
        if (left + popupWidth > $(window).width() - 12) {
            left = $(window).width() - popupWidth - 12;
        }
        selectorPopup.css({
            top: top,
            left: left,
        });
    }

    function hideSelectorPopup() {
        if (selectorPopup) {
            selectorPopup.hide();
            currentSelectorInput = null;
        }
    }

    function capturar_fila_por_matricula(matricula_id) {
        var $fila = $("#tabla_registros > tbody > tr").filter(function () {
            return $(this).attr("data-matricula_id") == matricula_id;
        });

        if ($fila.length == 0) {
            return null;
        }

        var id_fila = $fila.attr("id");
        var numero_fila = id_fila ? parseInt(id_fila.split("_")[1], 10) : null;

        var datos = {
            id_calificacion: $fila.attr("data-id_calificacion"),
            id_calificacion_aux: $fila.attr("data-id_calificacion_aux"),
            codigo_matricula: $fila.attr("data-codigo_matricula"),
            matricula_id: matricula_id,
            fila_id: id_fila,
            id_estudiante: $fila.attr("data-id_estudiante"),
            calificacion: numero_fila ? $("#calificacion_texto" + numero_fila).val() : "",
            logros: numero_fila ? $("#logros_" + numero_fila).val() : ""
        };

        if (numero_fila !== null) {
            $fila.find(".valores_" + numero_fila).each(function () {
                var nombreCampo = $(this).attr("id").split("_")[0];
                datos[nombreCampo] = $(this).val();
            });
        }

        return datos;
    }

    function manejar_error_envio(matricula_id, mensaje) {
        arr_matriculas_ids_list.unshift(matricula_id);
        restantes = arr_matriculas_ids_list.length;
        actualizarContador(restantes);

        guardando = false;
        $("#mensaje_sin_guardar").show();
        $("#mensaje_guardadas").hide();
        $("#bs_boton_guardar").prop("disabled", false);
        $("#bs_boton_volver").prop("disabled", false);
        $("#bs_boton_guardar").html('<i class="fa fa-save"></i> Guardar');
        $("#div_cargando").hide();

        Swal.fire({
            icon: "error",
            title: "Alerta! Datos no guardados.",
            text: mensaje,
        });
    }

    // Inicio de la ejecución recursiva
    $("#bs_boton_guardar").click(function (event) {
        event.preventDefault();

        $("#bs_boton_guardar").prop("disabled", true);
        $("#bs_boton_volver").prop("disabled", true);

        $("#bs_boton_guardar").html('<i class="fa fa-spinner fa-spin"></i> Guardando');

        guardar_calificaciones();
        
    });

    window.guardar_calificaciones = function (from_encabezados = false) {
        $("#div_cargando").show();
        guardando = true;
        $("#mensaje_sin_guardar").hide();

        if (filas_modificadas.length === 0) {
            $("#div_cargando").hide();
            Swal.fire({
                icon: "info",
                title: "Sin cambios",
                text: "No hay calificaciones nuevas para guardar.",
            });
            guardando = false;
            $("#div_spin").hide();
            $(".btn_close_modal").removeAttr("disabled");
            $(".btn_save_modal").removeAttr("disabled");
            return;
        }

        preparacion_para_enviar_lineas_calificaciones_estudiantes(from_encabezados);
    };

    // Inicializar array de ids para envio
    function preparacion_para_enviar_lineas_calificaciones_estudiantes( from_encabezados )
    {
        arr_matriculas_ids_list = filas_modificadas.slice();

        restantes = arr_matriculas_ids_list.length;

        actualizarContador(restantes);

        // Primera llamada a la funcion recursiva
        enviar_una_linea_estudiante( from_encabezados );
    }

    // The recursive function
    function enviar_una_linea_estudiante( from_encabezados ) {

        // Si ya se enviaron todos los documentos
        if (arr_matriculas_ids_list.length === 0) {

            $("#tabla_lineas_registros_calificaciones").find("tbody").html("");

            $("#bs_boton_guardar").html('<i class="fa fa-save"></i> Guardar');

            $("#div_cargando").hide();
            $("#mensaje_guardadas").show();

            guardando = false;
            
            actualizarContador('');

            if ( from_encabezados ) {
                
                $("#div_spin").hide();

                $("#alert_mensaje").fadeIn();
                
                $("#myModal").modal("hide");

                Swal.fire({
                    icon: "success",
                    title: "¡Muy bien!",
                    text:
                        "El encabezado de la calificación ha sido Almacenado correctamente.",
                });
            }

            return true;
        }

        // pop top value
        var matricula_id = arr_matriculas_ids_list[0];
        arr_matriculas_ids_list.shift();

        var json_fila = capturar_fila_por_matricula(matricula_id);

        if (json_fila == null) {
            manejar_error_envio(
                matricula_id,
                "No se pudo leer el registro solicitado. Recargue la página y vuelva a intentar."
            );
            return;
        }
        json_fila.fila_timestamp = Date.now();

        json_fila.id_periodo = $("#id_periodo").val();
        json_fila.curso_id = $("#curso_id").val();
        json_fila.asignatura_id = $("#id_asignatura").val();
        json_fila.anio = $("#anio").val();

        var url = url_raiz + "/calificaciones/almacenar_linea_calificacion_estudiante";

        $.ajax({
            url: url,
            type: "POST",
            data: {"json_fila":json_fila,"_token":$("#csrf_token").val()}
        }).done(function (response) {

            var fila = document.getElementById(response[0].numero_fila);

            fila.setAttribute( "data-id_calificacion", response[0].id_calificacion );
            fila.setAttribute( "data-calificacion", response[0].calificacion_texto );
            fila.setAttribute( "data-id_calificacion_aux", response[0].id_calificacion_aux );

            quitarFilaModificada(matricula_id);
            restantes--;
            actualizarContador(restantes);
            enviar_una_linea_estudiante( from_encabezados );
        }).fail(function ( xhr ) {

            if (xhr.status == 401) {
                Swal.fire({
                    icon: "error",
                    title: "Alerta! Datos no almaceados.",
                    text: "La sesión se cerró de manera inesperada. Por favor actualice la página y vuelva a iniciar sesión.",
                });
                return;
            }

            manejar_error_envio(
                matricula_id,
                "No se pudo conectar con el servidor. Verifica tu red y vuelve a guardar la fila."
            );
        });
    }

});








