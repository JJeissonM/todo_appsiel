<div class="col-md-4 col-sm-6 wow fadeInUp animated service-info" data-wow-duration="300ms" data-wow-delay="0ms" style="visibility: visible; animation-duration: 300ms; animation-delay: 0ms; animation-name: fadeInUp; margin-bottom: 20px;">
    <div style="border-radius: 20px !important;cursor: pointer;" onclick="visor_contenido_servicios({{ $item->id }})" >
        
        <div style="background-color: #fff; border-top-right-radius: 20px !important; border-top-left-radius: 20px !important;">
            <img style="width: 100%; border-radius: 20px 20px 0 0" src="{{asset($item->icono)}}">
        </div>
        
        <div style="background-color: #fff; padding: 20px; border-bottom-right-radius: 20px !important; border-bottom-left-radius: 20px !important;">
            <h4 class="media-heading servicios-font" style="margin-top: 0px;">{{ strlen($item->titulo) >= 45 ? str_limit($item->titulo,45) : $item->titulo }}</h4>
            <p class="servicios-font">{!! strlen($item->descripcion) >= 70 ? str_limit($item->descripcion,70) : $item->descripcion !!} </p>
            <!--<div class="pull-right">
                @if($item->url!='')
                    <a class="btn btn-primary animate btn-sm servicios-font" href="{{$item->url}}" style="cursor: pointer; color: #fff;">Ver <i class="fa fa-plus"></i></a>
                @else
                    <a class="btn btn-primary animate btn-sm servicios-font"  style=" color: #fff;">Ver <i class="fa fa-plus"></i></a>
                @endif
            </div>-->
        </div>
    </div>
</div>