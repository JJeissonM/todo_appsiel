
<div class="container-fluid">
      <p style="padding: 30px; font-size: 18px; font-weight: bold;" class="servicios-font">
            <a href="{{url('/')}}"> <i class="fa fa-home"></i> </a>
            &nbsp; / &nbsp; 
            <a class="servicios-font" onclick="ver_contenedor_seccion_servicios()" style="text-decoration: none; color: #2a95be; cursor: pointer;"> {!! $empresa->servicio->titulo !!} </a>
            &nbsp; / &nbsp; 
            {{$empresa->titulo}} 
      </p>

      <section class="servicios-font" style="padding: 20px; margin: 10px !important; font-size: 14px; border-radius: 20px !important; -webkit-box-shadow: 1px 1px 100px #cf9ec3; -moz-box-shadow: 1px 1px 100px #cf9ec3; box-shadow: 1px 1px 100px #cf9ec3;">
            <div class='row'>
                  <div class='col-sm-12'>
                        <div class='blog-post blog-large wow fadeInLeft' data-wow-duration='300ms' data-wow-delay='0ms' style="border: none;">
                              <article>

                                    <header class='entry-header' style="background-color: transparent !important;">
                                          <div class='entry-thumbnail'>
                                          </div>
                                          <div class='entry-date servicios-font'>
                                                {{$empresa->created_at}}
                                          </div>
                                          <h2 class='entry-title servicios-font'>
                                                <a class="servicios-font" href='#'>{{$empresa->titulo}}</a></h2>
                                    </header>
                                    
                                    <div class='entry-content servicios-font'>
                                          <p class="servicios-font">
                                                <h4 class="servicios-font">RESUMEN</h4>
                                                {!! $empresa->descripcion !!}
                                          </p>
                                          <p class="servicios-font">
                                                {!! $empresa->empresa !!}
                                          </p>
                                    </div>
                                    

                              </article>
                        </div>
                  </div>
            </div>
      </section>
      
</div>