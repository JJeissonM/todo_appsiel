var guardando;
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
    //checkCookie();

    guardando = false;

    
    $(".select_escala_valoracion").on("change", function () {
        
        $("#div_cargando").show();

        var fila = $(this).closest("tr");

        console.log( parseInt($(this).val()) );

        var valoracion = parseInt($(this).val());
        if ( valoracion == NaN) {
            valoracion = 0;
        }

        var url = url_raiz + "/academico_docente/almacenar_linea_calificacion_estudiante/" + $('#periodo_id').val() + "/" + $('#curso_id').val() + "/" + $('#asignatura_id').val() + "/" + fila.attr('data-matricula_id') + "/" + fila.attr('data-logro_id') + "/" + valoracion;

        $.ajax({
            url: url,
            type: "GET"
        }).done(function (response) {

            $("#div_cargando").hide();
            
            Swal.fire({
                icon: response.icon,
                title: response.title,
                text: response.text
            });

        }).fail(function ( xhr ) {

            $("#div_cargando").hide();

            if ( xhr.status == 401 ) { //
                Swal.fire({
                    icon: "error",
                    title: "Alerta! Datos no almaceados.",
                    text: "La sesión se cerró de manera insperada. Por favor actualice la página y vuelva a iniciar sesión.",
                });
            }
        });

    });


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

    // Inicio de la ejecución recursiva
    $("#bs_boton_guardar").click(function (event) {
        event.preventDefault();

        /*
        $("#bs_boton_guardar").prop("disabled", true);
        $("#bs_boton_volver").prop("disabled", true);

        $("#bs_boton_guardar").html('<i class="fa fa-spinner fa-spin"></i> Guardando');

        guardar_calificaciones();
        */
    });

    window.guardar_calificaciones = function (from_encabezados = false) {
        $("#div_cargando").show();
        guardando = true;
        $("#mensaje_sin_guardar").hide();

        preparacion_para_enviar_lineas_calificaciones_estudiantes( from_encabezados );
    };

    // Inicializar array de ids para envio
    function preparacion_para_enviar_lineas_calificaciones_estudiantes( from_encabezados )
    {
        arr_matriculas_ids_list = JSON.parse( "[" + $("#matriculas_ids_list").val() + "]");

        restantes = arr_matriculas_ids_list.length;

        $("#counter").html(restantes);

        // Tabla auxiliar oculta
        llenar_tabla_lineas_registros();

        // Primera llamada a la funcion recursiva
        enviar_una_linea_estudiante( from_encabezados );
    }

    function get_tabla_lineas_registros()
    {
        return $("#tabla_lineas_registros_calificaciones").tableToJSON();
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
            
            document.getElementById("counter").innerHTML = '';

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

        var table = get_tabla_lineas_registros()

        var json_fila = get_json_fila(table, matricula_id);

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

            restantes--;
            document.getElementById("counter").innerHTML = restantes;
            enviar_una_linea_estudiante( from_encabezados );
        }).fail(function ( xhr ) {

            if ( xhr.status == 401 ) { //
                Swal.fire({
                    icon: "error",
                    title: "Alerta! Datos no almaceados.",
                    text: "La sesión se cerró de manera insperada. Por favor actualice la página y vuelva a iniciar sesión.",
                });
            }
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
