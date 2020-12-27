<!DOCTYPE html>
<html lang="es">
<head>

  <title>{{ $pagina->descripcion }}</title>
  
  @if($pagina->favicon != '')
    <link rel="shortcut icon" href="{{$pagina->favicon}}" type="image/x-icon">
  @else
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
  @endif

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  @if($pagina->codigo_google_analitics != '')
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{$pagina->codigo_google_analitics}}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{{$pagina->codigo_google_analitics}}');
    </script>
  @endif



  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <link href="https://fonts.googleapis.com/css?family=Montserrat" rel="stylesheet" type="text/css">
  <link href="https://fonts.googleapis.com/css?family=Roboto+Slab" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Lato" rel="stylesheet" type="text/css">
  <link href="{{ asset('assets/css/pagina_web_sticky_social_bar.css') }}" rel="stylesheet" type="text/css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

  <link rel="stylesheet" href="{{ asset('assets/css/pagina_web_principal.css') }}">
  
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src='https://www.google.com/recaptcha/api.js'></script>
  
  <link href="https://fonts.googleapis.com/css?family=Noto+Sans+JP&display=swap" rel="stylesheet">
  <style type="text/css">
    .mision{
        font-family: 'Noto Sans JP', sans-serif;
    }
  </style>
</head>
<body id="myPage" data-spy="scroll" data-target=".navbar" data-offset="60">

@include('web.secciones.menu_principal')
<br>
<div id="myCarousel" class="carousel slide" data-ride="carousel">
    <!-- Indicators -->
    <ol class="carousel-indicators">
      <li data-target="#myCarousel" data-slide-to="0" class="active"></li>
      <li data-target="#myCarousel" data-slide-to="1"></li>
      <li data-target="#myCarousel" data-slide-to="2"></li>
    </ol>

    <!-- Wrapper for slides -->
    <div class="carousel-inner" role="listbox">
      <div class="item active">
        <img src="{{ asset('web') }}" alt="appsiel" class="img-responsive" style="max-height: 500px;" width="100%">
      </div>

      <div class="item">
        <img src="{{ asset('assets/img/pagina_web/banner2.jpg') }}" alt="plataforma_educativa" class="img-responsive" style="max-height: 500px;" width="100%">
      </div>
    
      <div class="item">
        <img src="{{ asset('assets/img/pagina_web/banner3.jpg') }}" alt="descripcion_software" class="img-responsive" style="max-height: 500px;" width="100%">   
      </div>
    </div>

    <!-- Left and right controls -->
    <a class="left carousel-control" href="#myCarousel" role="button" data-slide="prev">
      <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
      <span class="sr-only">Anterior</span>
    </a>
    <a class="right carousel-control" href="#myCarousel" role="button" data-slide="next">
      <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
      <span class="sr-only">Siguiente</span>
    </a>
</div>

<!-- Container (About Section) -->
<div id="about" class="container-fluid" itemscope itemtype="http://schema.org/about">
  <div class="row slideanim">
    <div class="col-sm-4">
      <h2>A cerca de AppSiel</h2>
      <p style="text-align: justify; background-image: url('{{ asset('assets/img/pagina_web/logo.png') }}'); font-size: 17px; line-height: 2;">
        Somos una empresa dedicada al desarrollo de sistemas de información que permitan aumentar la productividad de nuestros clientes.
        <br/><br/>
        Siempre buscamos ofrecer un excelente servicio al mejor precio.
        <br/><br/>
        No solo contamos con servicios accesibles técnica y financieramente, sino que ponemos a disposición de nuestros clientes la experiencia y conocimientos de nuestro equipo de trabajo para ayudarles a alcanzar sus objetivos.

        <?php //echo '<br/>bootstrap: '.__DIR__.'/bootstrap/'; ?>

        <?php //echo '<br/>base_path: '.base_path(); ?>
      </p>
    </div>
    <div class="col-sm-8">
      <br/>
      <button class="accordion">MISIÓN<a href="#" class="close" data-dismiss="alert" aria-label="close">&plus;</a></button>
      <div class="panel_acordion show">
        <spam class="mision">Crear sistemas que aporten grandes beneficios a la vida de millones de personas en el mundo.</spam>
      </div>

      <button class="accordion">VISIÓN<a href="#" class="close" data-dismiss="alert" aria-label="close">&plus;</a></button>
      <div class="panel_acordion">
        <p>Para el año 2023 tendremos más de mil (1.000) clientes, con miles de usuarios utilizando nuestros productos y servicios. Seremos una empresa prestigiosa; reconocida por la calidad de servicio y la felicidad y satisfacción de nuestros accionistas, empleados, clientes y proveedores.</p>
      </div>

      <button class="accordion">VALORES<a href="#" class="close" data-dismiss="alert" aria-label="close">&plus;</a></button>
      <div class="panel_acordion">
        <p>
          Nuestros valores nos impulsan a dar lo mejor: 
           <ul style="list-style-type: none;">
            <li>&#10084; Amabilidad</li>
            <li>&#9994; Pasión</li>
            <li>&#10166; Persistencia</li>
            <li>&#9472; Sencillez</li>
            <li>&#9670; Integridad</li>
            <li>&#10032; Excelencia</li>
            <li>&#9822; Liderazgo</li>
          </ul>
          
          Si eres una persona con estas cualidades y quieres formar parte de algo grandioso, <a href="#contact"> Contactanos </a></div>
        </p>
      </div>
    </div>
  </div>
</div>

<!-- Container (Services Section) -->
<div id="services" class="container-fluid text-center">
  <h2>Software para distintos sectores
    <br>
    <small>Todas las herramientas en un solo lugar.</small>
</h2>
  <div class="row slideanim">
    <div class="col-sm-4">
      <span class="glyphicon glyphicon-education logo-small"></span>
      <h4>Gestión Educativa</h4>
      <p>Académia y Administración para su institución.</p>
      <ul class="list-group" style="text-align: left;">
        <li class="list-group-item">Inscripciones y matrículas</li>
        <li class="list-group-item">Calificaciones y certificados</li>
        <li class="list-group-item">Académico docente</li>
        <li class="list-group-item">Académico estudiante</li>
        <li class="list-group-item">Tesorería y libretas de pagos</li>
        <li class="list-group-item"><i> Todas la aplicaciones de la gestión empresarial. </i></li>
      </ul>
    </div>
    <div class="col-sm-4">
      <span class="fa fa-industry logo-small"></span>
      <h4>Gestión empresarial</h4>
      <p>Administre en tiempo real el flujo financiero de su empresa.</p>
      <ul class="list-group" style="text-align: left;">
        <li class="list-group-item">Compras</li>
        <li class="list-group-item">Inventarios</li>
        <li class="list-group-item">Ventas</li>
        <li class="list-group-item">Gestión de cobros</li>
        <li class="list-group-item">Tesorería</li>
        <li class="list-group-item">Nómina</li>
        <li class="list-group-item">Contabilidad (NIIF nativas)</li>
      </ul>
    </div>
    <div class="col-sm-4">
      <span class="fa fa-heartbeat logo-small"></span>
      <h4>Gestión Salud</h4>
      <p>Sus pacientes no se perderán una cita.</p>
      <ul class="list-group" style="text-align: left;">
        <li class="list-group-item">Gestión de consultorios</li>
        <li class="list-group-item">Control de historias clinicas de pacientes</li>
        <li class="list-group-item">Fórmulas, diagnósticos, control de citas y mucho más.</li>
        <li class="list-group-item"><i> Todas la aplicaciones de la gestión empresarial. </i></li>
      </ul>
    </div>
  </div>
</div>

<!-- Container (Portfolio Section) -->
<div id="portfolio" class="container-fluid text-center bg-grey">
  <h2>DEMO</h2><br>
  <h4>Conoce cómo funcionan nuestras aplicaciones.</h4>
  <div class="row text-center slideanim">
    <div class="col-sm-4">
      <div class="embed-responsive embed-responsive-16by9">
        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/06-_7C5S1Ko" allowfullscreen></iframe>
      </div>
        <p><strong>Proceso de matrículas</strong></p>
        <p>Aprende cómo crear y matricular estudiantes.</p>
    </div>
    <div class="col-sm-4">
        <div class="panel panel-info">
          <div class="panel-heading">Acceso a los demás videos</div>
          <div class="panel-body">Para acceder al resto de los videos debes registrarte en nuestra plataforma de trainig. Contáctanos para conocer más. <a href="#contact"> <span class="badge">Contactanos</span> </a></div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="well well-lg">
            <h1><a class="btn btn-primary" href="http://demo.appsiel.com.co/inicio" target="_blank">Ingresar</a></h1>
        </div>
        <p><strong>DEMO ONLINE</strong></p>
        <p>Navega por todas las opciones de la aplicación.</p>
    </div>
  </div>
  
  <h2>Has crecer tu organización</h2>
  <div id="myCarousel2" class="carousel slide text-center" data-ride="carousel">
    <!-- Indicators -->
    <ol class="carousel-indicators">
      <li data-target="#myCarousel2" data-slide-to="0" class="active"></li>
      <li data-target="#myCarousel2" data-slide-to="1"></li>
      <li data-target="#myCarousel2" data-slide-to="2"></li>
    </ol>

    <!-- Wrapper for slides -->
    <div class="carousel-inner" role="listbox">
      <div class="item active">
        <h4>"Optimice su tiempo y sea más productivo en sus actividades."<br><span>Permanesca a la vanguardia.</span></h4>
      </div>
      <div class="item">
        <h4>"La tecnología puede ser su mejor aliada."<br><span>Aproveche esta época de cambios.</span></h4>
      </div>
      <div class="item">
        <h4>"Automatizar sus tareas nunca fue tan fácil."<br><span>Organice su información de manera rápida y segura.</span></h4>
      </div>
    </div>

    <!-- Left and right controls -->
    <a class="left carousel-control" href="#myCarousel2" role="button" data-slide="prev">
      <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
      <span class="sr-only">Anterior</span>
    </a>
    <a class="right carousel-control" href="#myCarousel2" role="button" data-slide="next">
      <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
      <span class="sr-only">Siguiente</span>
    </a>
  </div>
</div>

<!-- Container (Pricing Section) -->
<div id="pricing" class="container-fluid">
  
  <div class="text-center">
    <h2>Precios</h2>
    <h4>Selecciona las aplicaciones que se ajusten a tus necesidades.</h4>
  </div>

  <div class="row slideanim">

    <div class="col-sm-8 col-xs-12">
        <?php
          $app = App\Sistema\Aplicacion::where('mostrar_en_pag_web',1)->orderBy('ambito')->get()->toArray();        
          $cant_cols=3;
          $i=1;
        ?>
        @foreach ($app as $fila)

          @if($i % $cant_cols == 0)
             <!-- se ABRE una linea de aplicaciones -->
             <div class="row">
          @endif
          
          @include('web.secciones.dibujar_icono_app')
          

          @if($i % $cant_cols == 0)
            <!-- se CIERRA una linea de aplicaciones -->
            </div>
          @endif

          <?php $i++; ?>

        @endforeach

        @for($j=0; $j < ( ($i % $cant_cols) - 1);$j++)
          @include('web.secciones.dibujar_icono_vacio')
        @endfor
    </div>

    <div class="col-sm-4 col-xs-12">
      <ul class="nav nav-tabs">
        <li class="active"><a data-toggle="tab" href="#mensual">Mensual</a></li>
        <li><a data-toggle="tab" href="#anual">Anual</a></li>
      </ul>
      <div class="tab-content"  style="border-left: solid 1px #DDDDDD;border-bottom: solid 1px #DDDDDD;border-right: solid 1px #DDDDDD; padding: 5px;">
        <div id="mensual" class="tab-pane fade in active">
          <b style="font-size: 1.6em; color: black; text-align: center;">
            <div id="total">* $0 COP / mes</div>
          </b>
        </div>
        <div id="anual" class="tab-pane fade">
          <b style="font-size: 1.6em; color: black;  text-align: center;">
            <div id="total_anual">* $0 COP</div>
          </b>
        </div>
        <p>
          * Los precios no incluyen costos de implementación. Estos pueden variar de una organización a otra. Contáctanos para saber más. 
        </p>
        <p>
          <div class="alert alert-info">
            <strong>¡Oferta!</strong> Contrate la plataforma por un año y recibirá 
            <br/>
             <img src="{{ asset('assets/img/pagina_web/dcto_10_porciento.png') }}" height="60">
          </div>
        </p>
        <p> <a href="#contact" class="btn btn-lg">Contactanos</a> </p>
      </div>    
    </div>
  
  </div> <!-- slideanim -->

</div> <!-- pricing -->

@include('web.front_end.modulos.clientes.index')

<!-- Container (Contact Section) -->
<div id="contact" class="container-fluid bg-grey">
  <h2 class="text-center">CONTACTANOS</h2>
  <div class="row">
    <div class="col-sm-5">
      <p>Contactanos y nos comunicaremos contigo lo más pronto posible.</p>
      <p><span class="glyphicon glyphicon-map-marker"></span> Valledupar, Colombia</p>
      <p><a href="https://api.whatsapp.com/send?phone=573146561062" target="_blank" class="fa fa-whatsapp" title="+57 3146561062"> +57 314 656 1062</a></p>
      <p><span class="glyphicon glyphicon-envelope"></span> contacto@appsiel.com.co</p>
    </div>
    @include('web.scripts.php.formulario_contactenos')
  </div>
</div>

@include('web.front_end.modulos.preguntas_frecuentes.index')

<div class="sm-icon-bar">
  <a href="https://fb.me/appsiel" target="_blank" class="sm-facebook"><i class="fa fa-facebook"></i></a>
  <a href="https://twitter.com/appsiel" target="_blank" class="sm-twitter"><i class="fa fa-twitter"></i></a>
   <a href="https://api.whatsapp.com/send?phone=573146561062" target="_blank" class="sm-whatsapp"><i class="fa fa-whatsapp"></i></a> <!---->
  <a href="https://www.linkedin.com/company/appsiel/" target="_blank" class="sm-linkedin"><i class="fa fa-linkedin"></i></a>
  <!-- <a href="#" class="sm-youtube"><i class="fa fa-youtube"></i></a> -->
</div>

<!-- Add Google Maps -->
<!--    -->

<footer class="container-fluid text-center">
  <div class="row">
    <div class="col-sm-2">
        
    </div>
    <div class="col-sm-6">
        <a href="#">Políticas de tratamiento de datos personales</a>
    </div>
    <div class="col-sm-4">
        
    </div>
  </div>
  <a href="#myPage" title="To Top">
    <span class="glyphicon glyphicon-chevron-up"></span>
  </a>
  <p>Copyright © {{ date('Y') }} AppSiel S.A.S.</p>
</footer>

<script>
$(document).ready(function(){

  $('.imagen_proceso').fadeIn(1000);

  // Add smooth scrolling to all links in navbar + footer link
  $(".navbar a, footer a[href='#myPage']").on('click', function(event) {
    // Make sure this.hash has a value before overriding default behavior
    if (this.hash !== "") {
      // Prevent default anchor click behavior
      event.preventDefault();

      // Store hash
      var hash = this.hash;

      // Using jQuery's animate() method to add smooth page scroll
      // The optional number (900) specifies the number of milliseconds it takes to scroll to the specified area
      $('html, body').animate({
        scrollTop: $(hash).offset().top
      }, 900, function(){
   
        // Add hash (#) to URL when done scrolling (default click behavior)
        window.location.hash = hash;
      });
    } // End if
  });
  
  $(window).scroll(function() {
    $(".slideanim").each(function(){
      var pos = $(this).offset().top;

      var winTop = $(window).scrollTop();
        if (pos < winTop + 600) {
          $(this).addClass("slide");
        }
    });
  });

  // Accordion
  var acc = document.getElementsByClassName("accordion");
  var i;

  for (i = 0; i < acc.length; i++) {
      acc[i].addEventListener("click", function() {
          this.classList.toggle("active");
          var panel = this.nextElementSibling;
          if (panel.style.display === "block") {
              panel.style.display = "none";
          } else {
              panel.style.display = "block";
          }
      });
  }

  // Precios
  $(".seleccionar").change(function(){
    recalculate();
  });

  function recalculate(){
      var sum = 0;

      $("input[type=checkbox]:checked").each(function(){
        if ( $(this).attr('id') != 'acepto_terminos' ) {
          sum += parseInt( $(this).val() );
        }
      });

      //alert(sum);
      var total = new Intl.NumberFormat("de-DE").format(sum);
      $("#total").text("*$" + total + " COP / mes");
      var total_anual = sum * 12;
      total_anual = new Intl.NumberFormat("de-DE").format(total_anual);
      $("#total_anual").text("*$" + total_anual + " COP / Año");
  }

  $("input[type=number]").change(function(){
        var cod_id = $(this).attr('id');
        var id = cod_id.split("-");
        var precio = $("#precio-"+id[1]).val();
        var cantidad = $("#cantidad-"+id[1]).val();

        var total = precio * cantidad;
        $("#sub_total-"+id[1]).val(total); // Se le asigna el valor al checkbox
        $("#lbl_sub_total-"+id[1]).text("$" + new Intl.NumberFormat("de-DE").format(total) + " COP / mes");

        recalculate();
      });

  $("#tab_anual").click(function(e){
    e.stopImmediatePropagation();
    $("#div_costo_total_mensual").hide();
    $("#div_costo_total_anual").show(1000);
  });

  $("#tab_mensual").click(function(e){
    e.stopImmediatePropagation();
    $("#div_costo_total_mensual").show(1000);
    $("#div_costo_total_anual").hide();
  });

})
</script>

<script>
      $(document).ready( function () {
          // Click para generar la consulta
        $('#submit').click(function(event){
          event.preventDefault();
          $('#resultado_consulta').html( '' );

          //alert( $('#acepto_terminos').attr('checked') );


          if ( validar_requeridos() ) {
            $('#div_cargando').show();

            // Preparar datos de los controles para enviar formulario
            var form_contacto = $('#form_contacto');
            var url = form_contacto.attr('action');
            var datos = form_contacto.serialize();
            // Enviar formulario de ingreso de productos vía POST
            $.post(url,datos,function(respuesta){
              $('#div_cargando').hide();
              $('#resultado_consulta').html(respuesta);
              $('#nombre').val('');
              $('#email').val('');
              $('#telefono').val('');
              $('#ciudad').val('');
              $('#comentarios').val('');
            });
          }else{
            alert("Debe ingresar todos los datos.");
          }
        });

        function validar_requeridos(){
          if( $('#nombre').val()=='' || $('#email').val()=='' || $('#telefono').val()=='' || $('#ciudad').val()=='' || $('#comentarios').val()=='' )
          {
            var valida = false;
          }else{
            var valida = true;
          }
          return valida;
        }

      });
    </script>

</body>
</html>