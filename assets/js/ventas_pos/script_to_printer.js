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

            var ancho_formato_impresion = $('#ancho_formato_impresion').val() * 800;
            //, width: ancho_formato_impresion
            //generate an image of HTML content through html2canvas utility
            html2canvas(document.getElementById('div_formato_impresion_cocina'), { scale:1 }).then(function (canvas) {

            //Create a ClientPrintJob
            var cpj = new JSPM.ClientPrintJob();
            //Set Printer type (Refer to the help, there many of them!)
            /*
            if ($('#useDefaultPrinter').prop('checked')) {
                cpj.clientPrinter = new JSPM.DefaultPrinter();
            } else {
                cpj.clientPrinter = new JSPM.InstalledPrinter($('#installedPrinterName').val());
            }
            */
           
            cpj.clientPrinter = new JSPM.InstalledPrinter($('#impresora_cocina_por_defecto').val());
            
            //Set content to print... 
            var b64Prefix = "data:image/png;base64,";
            var imgBase64DataUri = canvas.toDataURL("image/png");
            var imgBase64Content = imgBase64DataUri.substring(b64Prefix.length, imgBase64DataUri.length);

            var myImageFile = new JSPM.PrintFile(imgBase64Content, JSPM.FileSourceType.Base64, 'comanda.png', 1);
            
            //add file to print job
            cpj.files.push(myImageFile);

            //Send print job to printer!
            cpj.sendToClient();


        });

            /*
            //Create a ClientPrintJob
            var obj_trabajo_impresion_1 = new JSPM.ClientPrintJob();
            //Set Printer type (Refer to the help, there many of them!)
            if ($('#useDefaultPrinter').prop('checked')) {
                obj_trabajo_impresion_1.clientPrinter = new JSPM.DefaultPrinter();
            } else {
                obj_trabajo_impresion_1.clientPrinter = new JSPM.InstalledPrinter($('#installedPrinterName').val());
            }

            console.log($('#installedPrinterName').val());

            //Set content to print... in this sample, a pdf file
            var myPdfFile = new JSPM.PrintFilePDF('https://neodynamic.com/temp/LoremIpsum.pdf', JSPM.FileSourceType.URL, 'myFileToPrint.pdf', 1);
            //add file to print job
            obj_trabajo_impresion_1.files.push(myPdfFile);
            

            var obj_trabajo_impresion_2 = new JSPM.ClientPrintJob();
            obj_trabajo_impresion_2.clientPrinter = new JSPM.InstalledPrinter($('#installedPrinterName').val());//new JSPM.DefaultPrinter();
            obj_trabajo_impresion_2.printerCommands = 'RAW PRINTER COMMANDS HERE';
            obj_trabajo_impresion_2.sendToClient();
            */
           // var obj_trabajo_impresion_grupo = new JSPM.ClientPrintJobGroup();

            //obj_trabajo_impresion_grupo.jobs.push(obj_trabajo_impresion_1);

            //obj_trabajo_impresion_2.jobs.push(obj_trabajo_impresion_2);

            //Send print job to printer!
            //obj_trabajo_impresion_grupo.sendToClient();
        }
    }
