
function pc_print(data){
    var socket = new WebSocket("ws://127.0.0.1:40213/");

    socket.bufferType = "arraybuffer";
    
    socket.onerror = function(error) {
        alert("Error: " + JSON.stringify(error) );
    };			
    socket.onopen = function() {
        console.log('On Open Sockect - Send Data');
        socket.send(data);
        socket.close(1000, "Work complete");
    };
}

function android_print(data){
    window.location.href = data;  
}

function ajax_print(url, btn) {

    alert('Go server Printer');

    var opt = {

        // función a llamar cuando reciba la respuesta
        
        onSuccess: function(t) {
        
        dato = eval(t.responseText);
        
        alert(dato);
        
        }
        
        }
        
        new Ajax.Request(url, opt);

    /*$.get(url, function (data) {
        var ua = navigator.userAgent.toLowerCase();
        var isAndroid = ua.indexOf("android") > -1; 
        if(isAndroid) {
            android_print(data);
        }else{
            pc_print(data);
        }
    });
    */
}


var http_request = false;

    function makeRequest(url) {

        http_request = false;

        if (window.XMLHttpRequest) { // Mozilla, Safari,...
            http_request = new XMLHttpRequest();
            if (http_request.overrideMimeType) {
                http_request.overrideMimeType('text/xml');
                // Ver nota sobre esta linea al final
            }
        } else if (window.ActiveXObject) { // IE
            try {
                http_request = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                try {
                    http_request = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {}
            }
        }

        if (!http_request) {
            alert('Falla :( No es posible crear una instancia XMLHTTP');
            return false;
        }

        http_request.onreadystatechange = alertContents;
        http_request.open('GET', url, true);
        http_request.send();

    }

    function alertContents() {

        if (http_request.readyState == 4) {
            if (http_request.status == 200) {
                var ua = navigator.userAgent.toLowerCase();
                var isAndroid = ua.indexOf("android") > -1; 
                if(isAndroid) {
                    android_print(http_request.responseText);
                }else{
                    pc_print(http_request.responseText);
                }

                //alert(http_request.responseText);
            } else {
                alert('Hubo problemas con la petición.');
            }
        }

    }

$(document).ready(function () {

    
    $('#btn_print_barcodes').click(function(event){

        event.preventDefault();

        ajax_print( url_raiz + '/sys_test_print_example_rawbt' )

    });

});