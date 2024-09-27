var guardando;
function ventana(id, id_textbox, curso_id) {
    document.getElementById("caja_logro").value = id_textbox;

    window.open(
        "{{ url('calificaciones_logros/consultar' )}}" + "/" + id + "/" + curso_id,
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

    var escala_min = parseFloat($("#escala_min").val(), 10);
    var escala_max = parseFloat($("#escala_max").val(), 10);

    // 9 = Tab
    // 16 = Shift
    // 8 = Backspace
    var teclas_especiales = [9, 16];

    guardando = false;

    // Guardar calificaciones cada diez (10) segundos
    /*setInterval( function(){ 
          if( !guardando )
          {
              guardar_calificaciones();
          }
      }, 10000);
      */

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
            validar_valor_ingresado($(this)); // Si el valor esta errado, borra el valor ingresado. Luego tambien hay que calcular la definitva

            if (verificar_hay_pesos()) {
                var definitiva = calcular_definitiva_una_fila_promedio_poderado(numero_fila)
            } else {
                var definitiva = calcular_definitiva_una_fila_promedio_simple(numero_fila)
            }
            
            $("#calificacion_texto" + numero_fila).val( definitiva.toFixed(2) )

            // Cuando cambie el valor de una celda, se cambian los mensajes
            $("#mensaje_guardadas").hide();
            $("#mensaje_sin_guardar").show();
            $("#bs_boton_guardar").prop("disabled", false);
        }
    });

    $("#bs_boton_volver").click(function () {
        document.location.href = "{{ url()->previous() }}";
    });

    function generar_string_celdas(obj_fila_tabla, numero_fila) {
        var celdas = [];
        var num_celda = 0;

        celdas[num_celda] =
            "<td>" + $(obj_fila_tabla).attr("data-id_calificacion") + "</td>";

        num_celda++;

        celdas[num_celda] =
            "<td>" + $(obj_fila_tabla).attr("data-id_calificacion_aux") + "</td>";

        num_celda++;

        celdas[num_celda] =
            "<td>" + $(obj_fila_tabla).attr("data-codigo_matricula") + "</td>";

        num_celda++;

        celdas[num_celda] =
            "<td>" + $(obj_fila_tabla).attr("data-matricula_id") + "</td>";

        num_celda++;

        celdas[num_celda] = "<td>" + $(obj_fila_tabla).attr("id") + "</td>";

        num_celda++;

        celdas[num_celda] =
            "<td>" + $(obj_fila_tabla).attr("data-id_estudiante") + "</td>";

        num_celda++;

        $(".valores_" + numero_fila).each(function () {
            celdas[num_celda] = "<td>" + this.value + "</td>";
            num_celda++;
        });

        celdas[num_celda] =
            "<td>" + $("#calificacion_texto" + numero_fila).val() + "</td>";

        num_celda++;

        celdas[num_celda] = "<td>" + $("#logros_" + numero_fila).val() + "</td>";

        var cantidad_celdas = celdas.length;
        var string_celdas = "";
        for (var i = 0; i < cantidad_celdas; i++) {
            string_celdas = string_celdas + celdas[i];
        }

        return string_celdas;
    }

    // Validar que sea númerico y que esté entre la escala de valoración
    function validar_valor_ingresado(obj) {
        if (obj.attr("class") == "caja_logros") {
            return true;
        }

        var valido = true;
        if (obj.val() != "" && !$.isNumeric(obj.val())) {
            Swal.fire({
                icon: "error",
                title: "Alerta!",
                text: "Debe ingresar solo números. Para decimales use punto (.). No la coma (,).",
            });

            obj.val("");
            valido = false;
        }

        if (obj.val() != "" && (obj.val() < escala_min || obj.val() > escala_max)) {
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

        return valido;
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

    function llenar_tabla_lineas_registros() {
        var numero_fila = 1;
        $("#tabla_registros > tbody > tr").each(function (i, obj_fila_tabla) {
            
            var string_fila = generar_string_celdas(obj_fila_tabla, numero_fila);

            $("#tabla_lineas_registros_calificaciones").find("tbody:last").append("<tr>" + string_fila + "</tr>");
            
            numero_fila++;
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

    window.guardar_calificaciones = function () {
        $("#div_cargando").show();
        guardando = true;
        $("#mensaje_sin_guardar").hide();

        preparacion_para_enviar_lineas_calificaciones_estudiantes();
    };

    // Inicializar array de ids para envio
    function preparacion_para_enviar_lineas_calificaciones_estudiantes()
    {
        arr_matriculas_ids_list = JSON.parse( "[" + $("#matriculas_ids_list").val() + "]");

        restantes = arr_matriculas_ids_list.length;

        $("#counter").html(restantes);

        // Primera llamada a la funcion recursiva
        enviar_una_linea_estudiante();
    }

    function get_tabla_lineas_registros()
    {
        llenar_tabla_lineas_registros();

        return $("#tabla_lineas_registros_calificaciones").tableToJSON();
    }

    // The recursive function
    function enviar_una_linea_estudiante() {

        // Si ya se enviaron todos los documentos
        if (arr_matriculas_ids_list.length === 0) {

            $("#tabla_lineas_registros_calificaciones").find("tbody").html("");

            $("#bs_boton_guardar").html('<i class="fa fa-save"></i> Guardar');

            //$("#popup_alerta_danger").hide();
            $("#div_cargando").hide();
            $("#mensaje_guardadas").show();

            guardando = false;
            
            document.getElementById("counter").innerHTML = '';

            //location.reload();

            return true;
        }

        // pop top value
        var matricula_id = arr_matriculas_ids_list[0];
        arr_matriculas_ids_list.shift();

        var table = get_tabla_lineas_registros()

        var json_fila = get_json_fila(table, matricula_id);

        json_fila.id_periodo = $("#id_periodo").val();
        json_fila.curso_id = $("#curso_id").val();
        json_fila.asignatura_id = $("#id_asignatura").val();
        json_fila.anio = $("#anio").val();

        var url =
            url_raiz +
            "/calificaciones/almacenar_linea_calificacion_estudiante/" +
            matricula_id +
            "/" +
            JSON.stringify(json_fila);

        $.ajax({
            url: url,
            type: "get",
            dataType: "json",
            cache: false,
            contentType: false,
            processData: false,
        }).done(function (response) {

            var fila = document.getElementById(response[0].numero_fila);

            fila.setAttribute( "data-id_calificacion", response[0].id_calificacion );
            fila.setAttribute( "data-calificacion", response[0].calificacion_texto );
            fila.setAttribute( "data-id_calificacion_aux", response[0].id_calificacion_aux );

            restantes--;
            document.getElementById("counter").innerHTML = restantes;
            enviar_una_linea_estudiante();
        }).fail(function ( xhr ) {

            if ( xhr.status == 401 ) { //
                Swal.fire({
                    icon: "error",
                    title: "Alerta! Datos no almaceados.",
                    text: "La sesión se cerró de manera insperada. Por favor actualice la página y vuelva a iniciar sesión.",
                });
            }

            //response = JSON.stringify(xhr)
            // error handling
            //console.log( response, status, error )
        });
    }

    function get_json_fila(table, matricula_id)
    {
        var result = table.filter(function (table) {
            return table.matricula_id == matricula_id;
        });

        return result[0];
    }
});
