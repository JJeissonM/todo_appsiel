<section id="blog">
    <div class="container">
        @if($archivo!=null)
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown">{{$archivo->titulo}}</h2>
            <p class="text-center wow fadeInDown">{{$archivo->descripcion}}</p>
        </div>

        <div class="row col-md-12 wow fadeInDown">
            @foreach($items as $a)
            <div class="col-md-4" style="padding-top: 20px;">
                <div class="profile_title">
                    <div class="col-md-12">
                        <h5>{{$a->file}}</h5>
                    </div>
                </div>
                <a target="_blank" href="{{asset('docs/'.$a->file)}}">
                    <center><i class="fa fa-file-o" style="width: 100%; height: 100px; font-size: 80px;"></i></center>
                </a>
                <center style='padding-top: 5px;'>
                    <a href="{{ asset('docs/'.$a->file)}}" target="_blank" class="btn btn-success btn-block btn-sm" data-toggle="tooltip" data-placement="top" title="Descargar Archivo"><i class="fa fa-download"></i> DESCARGAR ARCHIVO</a>
                </center>
            </div>
            @endforeach
            <div class="col-md-12">
                {{$items->render()}}
            </div>
        </div>
        @else
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown">Sección</h2>
            <p class="text-center wow fadeInDown">Sin configuración</p>
        </div>
        @endif
    </div>

</section>