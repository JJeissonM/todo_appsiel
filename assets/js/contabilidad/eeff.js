
		
var pdv_id;
var continuar = true;
var arr_ids_clases_cuentas;
var arr_ids_grupos_padres;
var arr_ids_grupos_hijos;
var arr_ids_cuentas;
var form_consulta;
var datos;
var gran_total = 0;

$(document).ready(function(){

    $('#btn_generar2').click(function(event){
        
        event.preventDefault();
        
        if(!valida_campos()){
            alert('Debe diligenciar todos los campos.');
            return false;
        }

        $('#resultado_consulta').html( '' );
        
        $('#tabla_resultados').hide();
        $('#tabla_resultados').find('tbody').html( '' );

        $('#div_cargando').show();
        $('#div_spin').show();

        btn_acumular = $(this);

        $("#ids_clases_cuentas").val( $(this).attr( 'data-' + $('#reporte_id').val() ) );

        generar_eeff();
    });
    

    function valida_campos(){
        var valida = true;
        if($('#lapso1_ini').val()=='' || $('#lapso1_fin').val()=='' || $('#reporte_id').val()==''|| $('#lapso1_lbl').val()=='')
        {
            valida = false;
        }

        if($('#lapso2_lbl').val() != ''){
            if($('#lapso2_ini').val()=='' || $('#lapso2_fin').val()=='' || $('#lapso2_lbl').val()=='' || $('#reporte_id').val()==''){
                valida = false;
            }
        }

        return valida;
    }

    function generar_eeff()
    {
        arr_ids_clases_cuentas = JSON.parse($("#ids_clases_cuentas").val());

        console.log('arr_ids_clases_cuentas',arr_ids_clases_cuentas);

        form_consulta = $('#form_consulta');
        datos = form_consulta.serialize();

        // fires off the first call 
        ejecucion_recursiva_clases_cuentas();

        return continuar;
    }
    
    // The recursive function 
    function ejecucion_recursiva_clases_cuentas() { 
        
        // terminate if array exhausted 
        if (arr_ids_clases_cuentas.length === 0) 
        {
            $('#div_cargando').hide();
            $("#div_spin").hide();
            return; 
        }

        // pop top value 
        var clase_cuenta_id = arr_ids_clases_cuentas[0]; 
        arr_ids_clases_cuentas.shift(); 
        
        // ajax request 
        $.get("contab_get_totales_clase_cuenta" + "/" + clase_cuenta_id + '?'+datos, function(respuesta){ 
            if(respuesta.valor_saldo != 0){
            
            $('#tabla_resultados').show();
            }
            $('#lbl_anio').val($('#lapso1_lbl').val());

            $('#tabla_resultados').find('tbody').append( get_string_fila( 'tr_abuelo', respuesta.descripcion, respuesta.valor_saldo, respuesta.lbl_cr, true) );

            arr_ids_grupos_padres = respuesta.arr_ids_grupos_padres;

            ejecucion_recursiva_grupos_padres();
        }); 
    }
    
    // The recursive function 
    function ejecucion_recursiva_grupos_padres() { 
        
        // terminate if array exhausted 
        if (arr_ids_grupos_padres.length === 0) 
        {
            ejecucion_recursiva_clases_cuentas();
            return; 
        }

        // pop top value 
        var grupo_padre_id = arr_ids_grupos_padres[0]; 
        arr_ids_grupos_padres.shift(); 
        
        // ajax request 
        $.get("contab_get_totales_grupo_padre" + "/" + grupo_padre_id + '?'+datos, function(respuesta){ 
            
            if(respuesta.valor_saldo != 0) {
                $('#tabla_resultados').find('tbody').append( get_string_fila( 'tr_padre', respuesta.descripcion, respuesta.valor_saldo, respuesta.lbl_cr) );
            }
            arr_ids_grupos_hijos = respuesta.arr_ids_grupos_hijos;

            ejecucion_recursiva_grupos_hijos();
        }); 
    }

    function ejecucion_recursiva_grupos_hijos() { 
        
        // terminate if array exhausted 
        if (arr_ids_grupos_hijos.length === 0) 
        {
            ejecucion_recursiva_grupos_padres();
            return; 
        }

        // pop top value 
        var grupo_hijo_id = arr_ids_grupos_hijos[0]; 
        arr_ids_grupos_hijos.shift(); 
        
        // ajax request 
        $.get("contab_get_totales_grupo_hijo" + "/" + grupo_hijo_id + '?'+datos, function(respuesta){ 
            
            if(respuesta.valor_saldo != 0)
            {
                $('#tabla_resultados').find('tbody').append( get_string_fila( 'tr_hijo', respuesta.descripcion, respuesta.valor_saldo, respuesta.lbl_cr) );
            }
            arr_ids_cuentas = respuesta.arr_ids_cuentas;

            if ($('#detallar_cuentas')) {
                ejecucion_recursiva_cuentas();
            }else{
                ejecucion_recursiva_grupos_hijos();
            }
            
        }); 
    }

    function ejecucion_recursiva_cuentas() { 
        
        // terminate if array exhausted 
        if (arr_ids_cuentas.length === 0) 
        {
            if ($('#detallar_cuentas')) {
                ejecucion_recursiva_grupos_hijos();
            }
            return; 
        }

        // pop top value 
        var cuenta_id = arr_ids_cuentas[0]; 
        arr_ids_cuentas.shift(); 
        
        // ajax request 
        $.get("contab_get_totales_cuenta" + "/" + cuenta_id + '?'+datos, function(respuesta){ 
            
            if(respuesta.valor_saldo != 0)
            {
                $('#tabla_resultados').find('tbody').append( get_string_fila( 'tr_cuenta', respuesta.descripcion, respuesta.valor_saldo, respuesta.lbl_cr) );
            }
            ejecucion_recursiva_cuentas();
        }); 
    }

    function get_string_fila(tr_clase, descripcion,valor_saldo,lbl_cr,mayusculas=false)
    {
        if (mayusculas) {
            descripcion = descripcion.toUpperCase();
        }

        return '<tr class="' + tr_clase + '"> <td >' + descripcion + '</td> <td align="right"> <span class="simbolo_moneda">$</span>' + new Intl.NumberFormat("de-DE").format(valor_saldo.toFixed(2)) + '</td> <td align="center">' + lbl_cr + '</td> </tr>';
    }

});
