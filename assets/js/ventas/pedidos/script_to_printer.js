
    $(document).ready(function() {

        $("#btn_imprimir_en_cocina").on('click',function(event){
            event.preventDefault();

            print_comanda();

            Swal.fire({
                icon: 'info',
                title: 'Muy bien!',
                text: 'Pedido enviado a la impresora de COCINA.'
            });

        });
        
	});
    //WebSocket settings
    JSPM.JSPrintManager.auto_reconnect = true;
    JSPM.JSPrintManager.start();
    JSPM.JSPrintManager.WS.onStatusChanged = function () {
        if (jspmWSStatus()) {
            //get client installed printers
            JSPM.JSPrintManager.getPrinters().then(function (myPrinters) {
                var options = '';
                for (var i = 0; i < myPrinters.length; i++) {
				    options += '<option>' + myPrinters[i] + '</option>';
				}
                $('#lista_impresoras_equipo_local').html(options);
            });
        }
    };

    //Check JSPM WebSocket status
    function jspmWSStatus() {
        if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Open)
            return true;
        else if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Closed) {
            alert('El componente JSPrintManager (JSPM) no está instalado o no se está ejecutando en su computador! Debe descargar, instalar y ejecutar JSPM Client App desde https://neodynamic.com/downloads/jspm');
            return false;
        }
        else if (JSPM.JSPrintManager.websocket_status == JSPM.WSStatus.Blocked) {
            alert('JSPM has blocked this website!');
            return false;
        }
    }

    //Do printing...
    function print_comanda(o) {
        if (jspmWSStatus()) {

            /*  IMPRIMIR CON COMANDOS ESC/POS */
            
            //Create a ClientPrintJob
            var cpj = new JSPM.ClientPrintJob();

            //Set Printer type (Refer to the help, there many of them!)
            cpj.clientPrinter = new JSPM.InstalledPrinter($('#impresora_cocina_por_defecto').val());

            //Set content to print...
            //cpj.printerCommands = generate_string_commands();
            cpj.binaryPrinterCommands = generate_string_commands();

            //Send print job to printer!
            cpj.sendToClient();
        }
    }

    function generate_string_commands()
    {
        var escpos = Neodynamic.JSESCPOSBuilder;
        var doc = new escpos.Document();
        
        var lineas_registros = get_lineas();
        
        var escposCommands = doc
                        .beep(3,2)
                        //.image(logo, escpos.BitmapDensity.D24)
                        //.setPrintWidth(720)
                        .font(escpos.FontFamily.A)
                        .align(escpos.TextAlignment.Center)
                        .style([escpos.FontStyle.Bold])
                        .size(0, 1)
                        .text('PEDIDO # ' + $('#lbl_consecutivo_doc_encabezado').val())
                        .text('F. ' + $('#lbl_fecha').val())
                        .text($('#lbl_cliente_descripcion').val())
                        .text('Atiende: ' + $('#nombre_vendedor').val())
                        .font(escpos.FontFamily.B)
                        .size(1, parseFloat($('#tamanio_letra_impresion_items_cocina').val()) )
                        .text( lineas_registros )
                        .feed(5)
                        .cut()
                        .generateUInt8Array();

        return escposCommands;
    }

    function get_lineas()
    {
        var newLine = '\n';

        var cmds = newLine;
        cmds += '  CANT.        ITEM           ';
        cmds += newLine;
        cmds += '______________________________';
        cmds += newLine;
        
        var lbl_total_factura = 0;
        var cantidad_total_productos = 0;
        $('.linea_registro').each(function( ){
            //Libro Matemáticas D     1
            var item_name = $(this).find('.lbl_producto_descripcion').text();

            let end = 20;
            cmds += $(this).find('.cantidad').text() + ' - ' + item_name.substring(0, end);

            cmds += newLine;

            let length_pendiente = item_name.length - end;
            let start = end;
             
            while (length_pendiente > 0) {
                end += 20;   

                cmds += ' ' + item_name.substring(start, end);
                cmds += newLine;

                length_pendiente = length_pendiente - start;

                start = end;
            }

            cmds += newLine;

            lbl_total_factura += parseFloat( $(this).find('.precio_total').text() );

            cantidad_total_productos++;

        });

        cmds += 'Detalle: ' + $('#lbl_descripcion_doc_encabezado').val();
        cmds += newLine;
        cmds += newLine;

        cmds += '# TOTAL ITEMS: ' + cantidad_total_productos;
        cmds += newLine;
        
        cmds += 'VENTA TOTAL: ' + $('#lbl_total_factura').val();
        cmds += newLine;
        
        //cmds += '11/03/13  19:53:17';

        return cmds;
    }

    function formatear_cadena(cadena, longitud_maxima, caracter_relleno)
    {
        var largo = cadena.length;
        if (largo <= longitud_maxima) {
            var tope = longitud_maxima - largo;
            for (let index = 0; index < tope; index++) {
                cadena += caracter_relleno;
            }
        }else{
            cadena = cadena.substring(0, longitud_maxima)
        }

        return cadena;
    }