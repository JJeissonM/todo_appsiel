<!DOCTYPE html>
<html lang="es">
  <head>

    <title>{{ $pagina->descripcion }}</title>

    <link rel="shortcut icon" href="{{ $pagina->get_url_favicon() }}" type="image/x-icon">

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Estilos Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <!-- Add icon library -->
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- JavaScript Bootstrap And Jquery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Merienda+One&display=swap" rel="stylesheet">

    <!-- ESTILOS DE MÓDULOS 
      Pendiente
    -->

    <style type="text/css">
        .seccion_padre {
            /**/border: dashed 1px red;
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 5px;
            
        }

        .seccion_hija {
            border: solid 1px black;
            padding: 10px;/**/            
        }
    </style>


    @if($pagina->codigo_google_analitics != '')
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $pagina->codigo_google_analitics }}"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '{{ $pagina->codigo_google_analitics }}');
    </script>
    @endif


    <!-- MEJORA: permitir agregar scripts desde el CRUD del modelo Pagina -->
    <script src='https://www.google.com/recaptcha/api.js'></script>

    <!-- Estilo default de la página web -->
    <link rel="stylesheet" href="{{ asset('assets/css/pw_default.css') }}">
  </head>

  <body id="myPage" data-spy="scroll" data-target=".navbar" data-offset="60">

    {!! $cadena_secciones !!}

  </body>
</html>