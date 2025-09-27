

function set_cantidades_ingresadas()
{
    var cantidades_ingresadas = 0;

    $(".linea_registro").each(function () {
      cantidades_ingresadas += parseFloat( $(this).find(".cantidad").text() );
    });

    $('#cantidades_ingresadas').text(cantidades_ingresadas.toFixed(2));
}
