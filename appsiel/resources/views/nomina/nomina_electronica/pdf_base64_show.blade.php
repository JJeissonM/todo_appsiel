<!DOCTYPE html>
<html>
    <head>
        <title> {{ 'Doc. Soporte Nomina Electronia ' . $encabezado_doc->tipo_documento_app->prefijo . $encabezado_doc->consecutivo }} </title>
    </head>
    <body>
        <embed src="data:application/pdf;base64,{{$documento_electronico}}" type="application/pdf" width="100%" height="100%"/>
        
    </body>
</html>