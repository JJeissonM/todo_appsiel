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
                        //.image(logo, escpos.BitmapDensity.D24)
                        //.setPrintWidth(720)
                        .font(escpos.FontFamily.A)
                        .align(escpos.TextAlignment.Center)
                        .style([escpos.FontStyle.Bold])
                        .size(0, 1)
                        .text($('#pdv_label').val())
                        .text('Fact. Vtas. #' + $('.lbl_consecutivo_doc_encabezado').text() + ' / F. ' + $('#lbl_fecha').text())
                        .text($('.lbl_cliente_descripcion').text())
                        .font(escpos.FontFamily.B)
                        .size(1, 0)
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
        cmds += '        ITEM             CANT.';
        cmds += newLine;
        cmds += '______________________________';
        cmds += newLine;
        
        var lbl_total_factura = 0;
        var cantidad_total_productos = 0;
        $('.linea_registro').each(function( ){
            //Libro Matemáticas D     1 
            cmds += formatear_cadena($(this).find('.lbl_producto_descripcion').text(),22,'.') + '......' + formatear_cadena($(this).find('.cantidad').text(),5,' ');
            
            cmds += newLine;

            lbl_total_factura += parseFloat( $(this).find('.precio_total').text() );

            cantidad_total_productos++;

        });

        cmds += '# TOTAL ITEMS: ' + cantidad_total_productos;
        cmds += newLine;
        
        cmds += 'VENTA TOTAL: ' + $('.lbl_total_factura').first().text();
        cmds += newLine;
        
        cmds += 'Detalle: ' + $('.lbl_descripcion_doc_encabezado').text();

        
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
    
    var arr;
    function testing_print_jspm()
    {
        if (jspmWSStatus()) {

            /*  IMPRIMIR CON COMANDOS ESC/POS */
            
            //Create a ClientPrintJob
            var cpj = new JSPM.ClientPrintJob();

            var printer = 'USB POS PRINTER';

            //Set Printer type (Refer to the help, there many of them!)
            cpj.clientPrinter = new JSPM.InstalledPrinter( printer );

            var escpos = Neodynamic.JSESCPOSBuilder;
            var doc = new escpos.Document();

            var newLine = '\n';

            var cmds;

            //codigo_barras_item = '123';
            //cmds += '\x1b@\x1bE\x01ESC POS Printed from JSPrintManager\x1bd\x01\x1dk\x04' + codigo_barras_item + '\x00\x1bd\x01' + codigo_barras_item + '\x1dV\x41\x03';
            arr = [1,1,1,7,7,7,6,6,6,5,5,5,4,4,4];

            var ean13 = '\x02';

            var limite_para_ajuste = 5;

            for (let index = 0; index < 6; index++) {
                var nombre_item = 'JEANS CABALLERO COMPANY - ' + (28 + index * 2) + ' (UND)';
                
                var codigo_barras_item = '7000000' + (19 + index) + '000' + arr[index];

                cmds += '\x1b@\x1bE\x01' + nombre_item +'\x1bd\x01\x1dk' + ean13 + codigo_barras_item + codigo_barras_item + '\x1dV\x41\x03';
                
                cmds += newLine;
                cmds += newLine;

                //cmds += '\x1b32'; // Set Line Spacing to Default

                if ( (index + 1) % limite_para_ajuste == 0) {
                  //cmds += newLine;
                  cmds += '\x1b33\x10'; // Set Line Spacing to Default

                }
            }            
            
            var escposCommands = doc
                            //.image(logo, escpos.BitmapDensity.D24)
                            //.setPrintWidth(720)
                            //.font(escpos.FontFamily.A)
                            .align(escpos.TextAlignment.LeftJustification)
                            //.style([escpos.FontStyle.Bold])
                            //.size(0, 1)
                            //.text('Prueba')
                            //.font(escpos.FontFamily.B)
                            .size(1, 0)
                            //.align(escpos.TextAlignment.Center)
                            .text( cmds )
                            //.feed(5)
                            .cut()
                            .generateUInt8Array();
                            
            //Set content to print...
            cpj.binaryPrinterCommands = escposCommands;

            //Send print job to printer!
            cpj.sendToClient();
        }
    }

    function get_escposCommands_2( doc )
    {
        var escposCommands = [];

        for (let index = 0; index < 3; index++) {
            
            var codigo_barras_item = '7000000' + (19 + index) + '000' + arr[index];

            escposCommands.push( doc
                                .align(escpos.TextAlignment.LeftJustification)
                                .text( 'JEANS CABALLERO COMPANY - ' + (28 + index * 2) )
                                .linearBarcode(codigo_barras_item,2,{width:2})
                                .text(codigo_barras_item + '\n')
                                .generateUInt8Array()
                            );
                        
            console.log(escposCommands)
        }

        return escposCommands;
    }
