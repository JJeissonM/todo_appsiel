<style type="text/css">

        #portfolio {
            position: relative;
            /*z-index: 80 !important;*/
            padding: 100px 0 75px;

            <?php
            if ($galeria != null) {
                if ($galeria->tipo_fondo == 'COLOR') {
                    echo "background-color: " . $galeria->fondo . ";";
                } else {
            ?>background: url('{{$galeria->fondo}}') {{$galeria->repetir}} center {{$galeria->direccion}};
            <?php
                }
            }
            ?>
        }

        .galeria-font {
            @if( !is_null($galeria) )
                @if( !is_null($galeria->configuracionfuente ) )
                    font-family: <?php echo $galeria->configuracionfuente->fuente->font; ?> !important;
                @endif
            @endif
        }

        .abrir_modal:hover img {
          opacity: 0.7;
          transform: scale(1.04);
        } 

        .abrir_modal:hover {
          cursor: pointer;
        } 

        .abrir_modal:hover .titulo {
          text-decoration: underline;
        }

        .btn_cerrar {
          background: #7b7b7b85;
          border-radius: 50%;
          height: 50px;
          width: 50px;
          display: inline;
          cursor: pointer;
          margin-top: 5px;
        }

        .btn_cerrar:hover {
          background: #ddddddb5;
        }

        #portfolio{
          /*border-radius: 30% 70% 70% 30% / 55% 30% 70% 45%;*/
          /*background-image: linear-gradient(45deg, #3023AE 0%, #f09 100%);*/
        }

        .abrir_modal{
          border: 1px solid #e6e6e6 ;
          box-shadow: 10px 10px 5px 0px #e6e6e6;
        }

        .abrir_modal:hover{
          transform: rotate(4deg); /* Standard syntax */
        }


        .modal{
          width: 100vw;
          right: unset;
          bottom: unset;
          height: 100vh;
        }

        .show{
          display: flex !important;
        }

        .modal-dialog-centered{
          width: 100vw;
          display: flex;
          justify-content: center;
          align-items: center;
        }

        .modal-dialog{
          max-width: 100%;
          margin: 1rem 0;
        }

        .modal-content{
          border-radius: 0;
          border: none;
          box-shadow: 
          box-shadow: 0 5px 15px rgba(0,0,0,.5);
        }

        @media (min-width: 576px){
          .modal-dialog {
              max-width: 100%;
              margin: 30px auto;
          }
        }

        .img-fluid{
          max-height: 80vh;
        }

        .carousel-control-prev{
          justify-content: flex-end;
          background: linear-gradient(to Left, rgba(255,255,255,0) 50%, rgba(219,219,219,1) 100%);
        }
        .carousel-control-next{
          justify-content: flex-end;
          background: linear-gradient(to right, rgba(255,255,255,0) 50%, rgba(219,219,219,1) 100%);
        } 


</style>

<section id="portfolio" class="galeria-font">
    <div class="container">
    
        @if($galeria != null)
            <div class="section-header">
                <h2 class="section-title text-center wow fadeInDown animated"
                    style="visibility: visible; animation-name: fadeInDown;">{{$galeria->titulo}}</h2>
            </div>


            @foreach ($galeria->albums()->orderBy('created_at','DESC')->get()->chunk(4) as $chunk)
                <div class="row">
                    @foreach ($chunk as $album)
                        <div class="col-md-3 col-sm-4 col-6 un_album" style="text-align: center;">
                            {!! $album->dibujar_individual() !!}
                        </div>
                    @endforeach
                </div>
            @endforeach


        @else
            <div class="section-header">
                <p class="text-center wow fadeInDown">Galería no configurada.</p>
            </div>
        @endif
    </div>

    <!-- The Modal -->
    <div class="modal" id="myModal" data-url_busqueda="{{ url('galeria_ver_album_carousel') }}">
      <!--<div class="modal-dialog modal-lg modal-dialog-centered" style="height: 100% !important;">-->
        <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          
          <div style="text-align: center; position: absolute; z-index: 9999; right: 0; color: #ddd;">
            <button type="button" class="btn_cerrar" data-dismiss="modal">&times;</button>  
          </div>          
          
          <!-- Modal body -->
          <div id="modal-body" style="margin: 15px;">
            Modal body..
          </div>
          
        </div>
      </div>
    </div>

</section>