<?php
	use App\Http\Controllers\Ventas\ReportesController;

	$facturas = ReportesController::facturas_electronicas_pendientes_por_enviar();
?>

@extends('layouts.principal')

@section('content')

    <div class="marco_formulario">
        <div class="row">
            <div class="col-md-6">
                
            </div>
            <div class="col-md-6">
                @include('ventas.incluir.lista_facturas_electronicas',['titulo'=>'Fact. Electrónicas pendientes por enviar'])
            </div>
        </div>
    </div>

@endsection

@section('scripts')
	
<script type="text/javascript">
		
	var pdv_id;
	var es_el_primero = true;
	var arr_ids_facturas = '';
	var restantes;

	$(document).ready(function(){

		$("#btn_envio_masivo").click(function(event){
            event.preventDefault();
            
			$(this).children('.fa-cogs').attr('class','fa fa-spinner fa-spin');

			$('#tabla_documentos_pendientes > tbody > tr').each(function( ){
                if ( es_el_primero ) {
                    arr_ids_facturas = $(this).attr('data-vtas_doc_encaezado_id');
                    es_el_primero = false;
                }else{
                    arr_ids_facturas += ',' +  $(this).attr('data-vtas_doc_encaezado_id');
                }
            });

            $('#vtas_doc_encabezados_ids_list').val(arr_ids_facturas);

			$(this).children('.fa-spinner').attr('class','fa fa-cogs');

            $('.alert.alert-info').show(1000);
		});

        // Inicio de la ejecución recursiva
        $('#btn_enviar_documentos').click(function(event){
        
            event.preventDefault();
            	
            if ( !$("#opcion1").is(":checked") && !$("#opcion2").is(":checked") )
            {
                alert('Debe escoger una opción.');
                $("#opcion1").focus();
                return false;
            }

            $('#btn_envio_masivo').hide();
            $(this).children('.fa-send').attr('class','fa fa-spinner fa-spin');
            $('#message_counting').show();		

            $("#vtas_doc_encabezados_ids_list").val( '[' + $("#vtas_doc_encabezados_ids_list").val() + ']' );

            preparacion_para_enviar_doucumentos();
        });

        // Inicializar array de ids para envio 
        function preparacion_para_enviar_doucumentos()
        {
            arr_vtas_doc_encabezados_ids_list = JSON.parse($("#vtas_doc_encabezados_ids_list").val());

            restantes = arr_vtas_doc_encabezados_ids_list.length;

            $('#counter').html( restantes );
            
            // Primera llamada a la funcion recursiva
            enviar_un_documento();
        }

        // The recursive function
        function enviar_un_documento() { 
            
            // Si ya se enviaron todos los documentos
            if (arr_vtas_doc_encabezados_ids_list.length === 0) 
            {
                $('#btn_enviar_documentos').children('.fa-spinner').attr('class','fa fa-send');
                $('#message_counting').hide();
                
                location.reload();

                return true;
            }

            // pop top value 
            var vtas_doc_encabezados_id = arr_vtas_doc_encabezados_ids_list[0];
            arr_vtas_doc_encabezados_ids_list.shift();
            var url = 'fe_envio_masivo/' + vtas_doc_encabezados_id + '/' + $('input[name="cambiar_fecha"]:checked').val();

            $.ajax({
                url: url,
                type: "get",
                dataType: "html",
                cache: false,
                contentType: false,
                processData: false
            })
            .done(function(res){
                restantes--;
                document.getElementById('counter').innerHTML = restantes;
                enviar_un_documento();
            });
        }

	});
</script>
@endsection