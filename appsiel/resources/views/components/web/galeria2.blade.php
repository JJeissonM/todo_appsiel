

<style type="text/css">

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

</style>

<section id="portfolio" style="background: white">
    <div class="container">
    
        @if($galeria != null)
            <div class="section-header">
                <h2 class="section-title text-center wow fadeInDown animated"
                    style="visibility: visible; animation-name: fadeInDown;">{{$galeria->titulo}}</h2>
            </div>


            @foreach ($galeria->albums()->orderBy('created_at','DESC')->get()->chunk(4) as $chunk)
                <div class="row">
                    @foreach ($chunk as $album)
                        <div class="col-md-3 col-xs-6">
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
      <div class="modal-dialog modal-lg modal-dialog-centered" style="height: 100% !important;">
        <div class="modal-content">
          
          <div style="text-align: center; position: absolute; z-index: 9999; right: 50%; padding-top: 20px; color: #ddd;">
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