<?php     
  $carousels = App\PaginaWeb\Carousel::orderBy('id','DESC')->get();

  $cant_cols=3;
  $i=0;
?>

<div class="post" >
  <h3><i class="fa fa-book"></i>Álbumes</h3>
  
  <div class="container">
    @foreach ($carousels as $un_carousel)

      @if($i % $cant_cols == 0)
         <div class="row">
      @endif
        
        <?php 
          $url_img = App\PaginaWeb\Carousel::get_url_primera_imagen( $un_carousel->id );
        ?>
      
        @include('pagina_web.front_end.modulos.galeria_imagenes.un_album',[ 'id' => $un_carousel->id, 'descripcion' => $un_carousel->descripcion, 'url_img' => $url_img])
      

      <?php $i++; ?>

      @if($i % $cant_cols == 0)
        <!-- se CIERRA una linea de aplicaciones -->
        </div>
        <br>
      @endif


    @endforeach
  </div>

</div>

<div id="myModal" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">

    <!-- Modal content-->
    <div class="modal-content" style="z-index: 999 !important;">
      
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        {{ Form::Spin(64) }}
        <div id="contenido_modal">
          
        </div>
      </div>

    </div>
  </div>
</div>

@section('scripts')
  <script type="text/javascript">
    var URL = "{{ url('/') }}";
    $(document).ready(function(){

      $(".ver_un_album_galeria").on('click', function(e) {
        $("#myModal").modal({keyboard: "true"});
        $("#div_spin").show();

        var url = URL + '/ajax_galeria_imagenes/' + $(this).attr('data-carousel_id');

        $.get( url, function( resultado ){ 
          $("#div_spin").hide();
          $("#contenido_modal").html( resultado );
        });
        
      });

    });
  </script>

@endsection