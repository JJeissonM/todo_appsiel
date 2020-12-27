$(document).ready(function(){
	
	$('#myTable').remove();

	$('#myTable').DataTable( {
		"processing": true,
        "serverSide": true,
        "ajax": {
            "url": "ajax_datatable",
            "data": function ( d ) {
                d.id_modelo = getParameterByName('id_modelo');
                // d.custom = $('#myInput').val();
                // etc
            }
        },
        "columns": [
        	{data:"campo1"},
        	{data:"campo2"},
        	{data:"campo3"},
        	{data:"campo4"},
        	{data:"campo5"},
        	{data:"campo6"},
        	{data:"campo7"},
        	{data:"campo8"},
        	{data:"campo9"},
        ]
    } );

    function getParameterByName(name) {
	    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
	    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
	    results = regex.exec(location.search);
	    return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
	}

});