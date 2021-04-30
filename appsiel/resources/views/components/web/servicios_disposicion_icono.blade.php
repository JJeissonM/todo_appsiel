<div class="col-md-4 col-sm-6 wow fadeInUp animated service-info" data-wow-duration="300ms" data-wow-delay="0ms" style="visibility: visible; animation-duration: 300ms; animation-delay: 0ms; animation-name: fadeInUp; margin-bottom: 20px;">
    <div style="background-color: #f8f8f8; padding: 20px; border-radius: 20px !important; -webkit-box-shadow: 1px 1px 100px var(--color-terciario); -moz-box-shadow: 1px 1px 100px var(--color-terciario); box-shadow: 1px 1px 100px var(--color-terciario); opacity: 0.8;" class="col-md-12">
        <div style="border-top: 10px solid; border-color: #7bb0e7; top: 0;"></div>
        
        <div class="media service-box" style="height: 150px;margin: 20px 0;">
            <div class="pull-left">
                <i class="fa fa-{{$item->icono}}"></i>
            </div>
            <div class="media-body">
                <h4 class="media-heading servicios-font" style="margin-top: 0px;">{{ str_limit($item->titulo,45) }}...</h4>
                <p class="servicios-font">{!! str_limit($item->descripcion,70) !!}... </p>
            </div>
        </div>
        
        <div class="pull-right">
            @if($item->url!='')
                <a class="btn btn-primary animate btn-sm servicios-font" href="{{$item->url}}" style="cursor: pointer; color: #fff;">Ver <i class="fa fa-plus"></i></a>
            @else
                <a class="btn btn-primary animate btn-sm servicios-font" onclick="visor_contenido_servicios({{ $item->id }})" style="cursor: pointer; color: #fff;">Ver <i class="fa fa-plus"></i></a>
            @endif
        </div>

    </div>
</div>