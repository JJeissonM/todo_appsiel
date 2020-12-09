<div class="container-fluid">
      <p>
            <a href="{{url('/')}}"> <i class="fa fa-home"></i> </a>
            /
            <a onclick="ver_contenedor_seccion_aboutus()" style="text-decoration: none; color: #2a95be; cursor: pointer;"> Volver </a>
      </p>

      <div class="content-txt" style="padding: 20px; margin: 10px !important; font-size: 14px; border-radius: 20px !important; -webkit-box-shadow: 1px 1px 100px #cf9ec3; -moz-box-shadow: 1px 1px 100px #cf9ec3; box-shadow: 1px 1px 100px #cf9ec3;">
            <div class='blog-post blog-large wow fadeInLeft' data-wow-duration='300ms' data-wow-delay='0ms' style="border: none;">
                  <article class="media clearfix">
                        <div class="media-body">

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
                                                <div class="col-md-12" style="text-align: center;">
                                                      {!! $empresa->valores !!}
                                                </div>
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
                  </article>
            </div>
      </div>

</div>