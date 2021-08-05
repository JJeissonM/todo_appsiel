<div class="col-md-4 col-sm-6 wow fadeInUp animated service-info" data-wow-duration="300ms" data-wow-delay="0ms" style="visibility: visible; animation-duration: 300ms; animation-delay: 0ms; animation-name: fadeInUp; margin-bottom: 20px;">
    <div class="px-5 p-md-1">
    <a class="card" style="border-radius: 20px !important;cursor: pointer; color: black"  href="{{$item->url}}">        
        <div style="background-color: #fff; border-top-right-radius: 20px !important; border-top-left-radius: 20px !important;">
            <img style="width: 100%; border-radius: 20px 20px 0 0" src="{{asset($item->icono)}}">
        </div>
        
        <div style="background-color: #fff; padding: 20px; border-bottom-right-radius: 20px !important; border-bottom-left-radius: 20px !important;">
            <h4 class="media-heading servicios-font text-center" style="margin-top: 0px;">{{ strlen($item->titulo) >= 90 ? str_limit($item->titulo,90) : $item->titulo }}</h4>
            <p class="servicios-font text-center">{!! $item->descripcion !!} </p>
            <!--<div class="pull-right">
                @if($item->url!='')
                    <a class="btn btn-primary animate btn-sm servicios-font" href="{{$item->url}}" style="cursor: pointer; color: #fff;">Ver <i class="fa fa-plus"></i></a>
                @else
                    <a class="btn btn-primary animate btn-sm servicios-font" onclick="visor_contenido_servicios({{ $item->id }})" style=" color: #fff;">Ver <i class="fa fa-plus"></i></a>
                @endif
            </div>-->
        </div>
    </a>    
    </div>
    
</div>