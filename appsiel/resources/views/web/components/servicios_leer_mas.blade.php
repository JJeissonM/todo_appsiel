
<div class="container-fluid">
      <p>
            
            <a href="{{url('/')}}"> <i class="fa fa-home"></i> </a>
            &nbsp; / &nbsp; 
            <a onclick="ver_contenedor_seccion()" href="#"> {!! $empresa->servicio->titulo !!} </a>
            &nbsp; / &nbsp; 
            {{$empresa->titulo}}
            
      </p>

      <section>
            <div class='row'>
                  <div class='col-sm-12'>
                        <div class='blog-post blog-large wow fadeInLeft' data-wow-duration='300ms' data-wow-delay='0ms'>
                              <article>

                                    <header class='entry-header'>
                                          <div class='entry-thumbnail'>
                                          </div>
                                          <div class='entry-date'>
                                                {{$empresa->created_at}}
                                          </div>
                                          <h2 class='entry-title'>
                                                <a href='#'>{{$empresa->titulo}}</a></h2>
                                    </header>
                                    
                                    <div class='entry-content'>
                                          <p>
                                                <h4>RESUMEN</h4>
                                                {!! $empresa->descripcion !!}
                                          </p>
                                          <p>
                                                {!! $empresa->empresa !!}
                                          </p>
                                    </div>
                                    

                              </article>
                        </div>
                  </div>
            </div>
      </section>
      
</div>