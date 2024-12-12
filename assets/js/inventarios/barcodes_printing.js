
var array_barcodes;
var items_per_page = 30;
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

function get_data(item_init, item_end)
{
    return array_barcodes.slice( item_init, parseInt(item_end) + 1 );
}

function create_btns_for_print_barcodes()
{
    $('#div_btns_barcodes').html('');
    var data = JSON.parse( $('#data_for_print').text() );

    var quantity = data.stickers_quantity;
    var pages = Math.trunc( quantity / items_per_page );
    if (quantity % items_per_page != 0) {
        pages++;
    }

    var barcodes = data.barcodes;

    array_barcodes = [];
    for(var key in barcodes){
        if(!barcodes.hasOwnProperty(key)){
            continue;
        }
        array_barcodes.push(barcodes[key])
    } 
    
    var item_init = 0;
    var item_end;
    for (let index = 0; index < pages; index++) {
        
        item_end = item_init + items_per_page - 1;
        if ( index + 1 == pages ) {
            item_end = array_barcodes.length; 
        }

        $('#div_btns_barcodes').append('<button class="btn btn-info btn-sm btn_print_barcodes" style="display: inline-block;" href="#" title="Etiquetas de códigos de barra" data-item_init="' + item_init + '" data-item_end="' + item_end + '" onclick="ajax_print( \'' + url_raiz + '/sys_test_print_example_rawbt' + '\', this)"><i class="fa fa-print"></i> Pag. ' + (index + 1) + '</button> &nbsp;&nbsp;&nbsp; ')
        
        item_init = items_per_page * (index + 1);
    }
}

function ajax_print(url, obj_btn) {

    var print_connector_type = $('#connector_type').val();
    var printer_ip = $('#ip_printer').val();

    var items_to_print = get_data( obj_btn.getAttribute('data-item_init'), obj_btn.getAttribute('data-item_end') );
    
    $.get( 
        url, 
        { 
            data: JSON.stringify( items_to_print ),
            print_connector_type: print_connector_type,
            printer_ip: printer_ip
        }
    )
    .done(function( data ) {

        var ua = navigator.userAgent.toLowerCase();
        var isAndroid = ua.indexOf("android") > -1; 
        if(isAndroid) {
            android_print(data);
        }else{
            pc_print(data);
        }

    });
}


$(document).ready(function () {

    $('.btn_print_barcodes').click(function(event){

        event.preventDefault();

        ajax_print( url_raiz + '/sys_test_print_example_rawbt', $(this) );

    });

});