
<div class="container-fluid">
      <p>
            
            <a href="{{url('/')}}"> <i class="fa fa-home"></i> </a>
            /
            <a onclick="ver_contenedor_seccion()" href="#"> Volver </a>
            
      </p>

      @if ( $empresa->mision != null )
            <h2 class='section-title text-center wow fadeInDown'> MISIÓN </h2>
            <div class='col-sm-12'>
                  <div class='media service-box wow fadeInRight'>
                        <div class='media-body'>
                              <p> 
                                    {!! $empresa->mision !!}
                              </p>
                        </div>
                  </div>
            </div>
      @endif

      @if ( $empresa->vision != null )
            <h2 class='section-title text-center wow fadeInDown'> VISIÓN </h2>
            <div class='col-sm-12'>
                  <div class='media service-box wow fadeInRight'>
                        <div class='media-body'>
                              <p> 
                                    {!! $empresa->vision !!}
                              </p>
                        </div>
                  </div>
            </div>
      @endif

      @if ( $empresa->valores != null )
            <h2 class='section-title text-center wow fadeInDown'> VALORES </h2>
            <div class='col-sm-12'>
                  <div class='media service-box wow fadeInRight'>
                        <div class='media-body'>
                              <p> 
                                    {!! $empresa->valores !!}
                              </p>
                        </div>
                  </div>
            </div>
      @endif

      @if ( $empresa->resenia != null )
            <h2 class='section-title text-center wow fadeInDown'> RESEÑA HISTORICA </h2>
            <div class='col-sm-12'>
                  <div class='media service-box wow fadeInRight'>
                        <div class='media-body'>
                              <p> 
                                    {!! $empresa->resenia !!}
                              </p>
                        </div>
                  </div>
            </div>
      @endif
      
</div>