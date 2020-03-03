<div class="aboutus" style="margin-top:60px;">
    <div class="container">
        @if($aboutus!=null)
            <div class="section-header">
                <h2 class="section-title text-center wow fadeInDown animated"
                    style="visibility: visible; animation-name: fadeInDown;">{{$aboutus->titulo}}</h2>
                <p class="text-center wow fadeInDown animated"
                   style="visibility: visible; animation-name: fadeInDown;">{{$aboutus->descripcion}}
                <center><a class="btn btn-primary btn-md text-center" href="{{route('aboutus.leer_institucional',$aboutus->id)}}">Leer mas...</a></center>
            </div>
            <div class="row">
                <div class="col-sm-6 wow fadeInLeft animated" style="visibility: visible; animation-name: fadeInLeft;">
                    <img class="img-responsive" src="{{url($aboutus->imagen)}}" alt="">
                </div>
                <div class="col-sm-6">
                    <div class="media service-box wow fadeInRight animated"
                         style="visibility: visible; animation-name: fadeInRight;">
                        <div class="pull-left">
                            <i class="fa fa-line-chart"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">Misión</h4>
                            <p>{{$aboutus->mision}}</p>
                        </div>
                    </div>

                    <div class="media service-box wow fadeInRight animated"
                         style="visibility: visible; animation-name: fadeInRight;">
                        <div class="pull-left">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">Visión</h4>
                            <p>{{$aboutus->vision}}</p>
                        </div>
                    </div>

                    <div class="media service-box wow fadeInRight animated"
                         style="visibility: visible; animation-name: fadeInRight;">
                        <div class="pull-left">
                            <i class="fa fa-pie-chart"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">Valores</h4>
                            <p>{{$aboutus->valores}}</p>
                        </div>
                    </div>
                    <div class="media service-box wow fadeInRight animated"
                         style="visibility: visible; animation-name: fadeInRight;">
                        <div class="pull-left">
                            <i class="fa fa-pie-chart"></i>
                        </div>
                        <div class="media-body">
                            <h4 class="media-heading">Reseña Historica</h4>
                            <p>{{str_limit($aboutus->resenia),80}}</p>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="section-header">
                <h2 class="section-title text-center wow fadeInDown">Sección</h2>
                <p class="text-center wow fadeInDown">Sin configuración</p>
            </div>
        @endif
    </div>
</div>