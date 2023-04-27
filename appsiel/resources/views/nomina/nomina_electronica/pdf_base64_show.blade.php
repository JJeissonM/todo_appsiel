<!DOCTYPE html>
<html>
    <head>
        <title> {{ $file_name }} </title>
    </head>
    <body>
        <embed src="data:application/pdf;base64,{{$documento_electronico}}" type="application/pdf" width="100%" height="100%"/>
        
    </body>
</html>