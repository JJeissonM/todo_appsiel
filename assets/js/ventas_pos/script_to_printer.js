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
    function print(o) {
        if (jspmWSStatus()) {

            /*  IMPRIMIR CON COMANDOS ESC/POS */
            
            //Create a ClientPrintJob
            var cpj = new JSPM.ClientPrintJob();

            //Set Printer type (Refer to the help, there many of them!)
            cpj.clientPrinter = new JSPM.InstalledPrinter($('#impresora_cocina_por_defecto').val());

            //Set content to print...
            cpj.printerCommands = generate_string_commands();

            //Send print job to printer!
            cpj.sendToClient();
        }
    }

    function generate_string_commands()
    {
        //Create ESP/POS commands for sample label
        var esc = '\x1B'; //ESC byte in hex notation
        var newLine = '\x0A'; //LF byte in hex notation
    
        var cmds = esc + "@"; //Initializes the printer (ESC @)
        cmds += esc + '!' + '\x18'; //Emphasized + Double-height + Double-width mode selected (ESC ! (8 + 16 + 32)) 56 dec => 38 hex

        cmds += $('#pdv_label').val(); //text to print
        cmds += newLine + newLine;
        
        cmds += ' Factura de ventas No. ' + $('.lbl_consecutivo_doc_encabezado').text(); //text to print
        cmds += newLine;

        cmds += esc + '!' + '\x06'; //Character font A selected (ESC ! 0)

        cmds += '             ITEM                     CANT.  '; //text to print
        cmds += newLine;
        cmds += '_____________________________________________'; //text to print
        cmds += newLine;

        var lbl_total_factura = 0;
        var cantidad_total_productos = 0;
        $('.linea_registro').each(function( ){
            //Libro Matemáticas D     1 
            cmds += formatear_cadena($(this).find('.lbl_producto_descripcion').text(),35) + '     ' + formatear_cadena($(this).find('.cantidad').text(),5);
            
            cmds += newLine;

            lbl_total_factura += parseFloat( $(this).find('.precio_total').text() );

            cantidad_total_productos++;

        });

        /*
        cmds += 'COOKIES                   5.00'; // 30 caracteres 
        cmds += newLine;
        cmds += 'MILK 65 Fl oz             3.78';
        cmds += newLine + newLine;
        cmds += 'SUBTOTAL                  8.78';
        cmds += newLine;
        cmds += 'TAX 5%                    0.44';
        cmds += newLine;
        cmds += 'TOTAL                     9.22';
        cmds += newLine;
        cmds += 'CASH TEND                10.00';
        cmds += newLine;
        cmds += 'CASH DUE                  0.78';
        */

        cmds += newLine + newLine;
        cmds += esc + '!' + '\x18'; //Emphasized + Double-height mode selected (ESC ! (16 + 8)) 24 dec => 18 hex
        cmds += '# TOTAL ITEMS: ' + cantidad_total_productos;
        cmds += newLine;
        cmds += 'VENTA TOTAL  : ' + $('.lbl_total_factura').text();
        cmds += esc + '!' + '\x00'; //Character font A selected (ESC ! 0)
        cmds += newLine + newLine;
        //cmds += '11/03/13  19:53:17';

        cmds += esc + '!' + '\x48'; // CUT PAPER

        console.log(cmds);

        return cmds;
    }

    function formatear_cadena(cadena, longitud_maxima)
    {
        var largo = cadena.length;
        if (largo <= longitud_maxima) {
            for (let index = 0; index <= largo; index++) {
                cadena += ' ';
            }
        }else{
            cadena = cadena.substring(0, longitud_maxima)
        }

        return cadena;
    }   
