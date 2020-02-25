<div class="aboutus">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;">{{$aboutus->titulo}}</h2>
            <p class="text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;">{{$aboutus->descripcion}}
        </div>
        <div class="row">
            <div class="col-sm-6 wow fadeInLeft animated" style="visibility: visible; animation-name: fadeInLeft;">
                <img class="img-responsive" src="{{url($aboutus->imagen)}}" alt="">
            </div>
            <div class="col-sm-6">
                <div class="media service-box wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight;">
                    <div class="pull-left">
                        <i class="fa fa-line-chart"></i>
                    </div>
                    <div class="media-body">
                        <h4 class="media-heading">Misión</h4>
                        <p>{{$aboutus->mision}}</p>
                    </div>
                </div>

                <div class="media service-box wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight;">
                    <div class="pull-left">
                        <i class="fa fa-cubes"></i>
                    </div>
                    <div class="media-body">
                        <h4 class="media-heading">Visión</h4>
                        <p>{{$aboutus->vision}}</p>
                    </div>
                </div>

                <div class="media service-box wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight;">
                    <div class="pull-left">
                        <i class="fa fa-pie-chart"></i>
                    </div>
                    <div class="media-body">
                        <h4 class="media-heading">Valores</h4>
                        <p>{{$aboutus->valores}}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>