<section id="blog">
    <div class="container">
        @if($archivo!=null)
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown">{{$archivo->titulo}}</h2>
            <p class="text-center wow fadeInDown">{{$archivo->descripcion}}</p>
        </div>
        <div class="row col-md-12 wow fadeInDown">
            @if($archivo->formato=='LISTA')
            <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover" style="width: 100%;">
                        <thead>
                            <tr class="danger">
                                <th>TÍTULO</th>
                                <th>DESCRIPCIÓN</th>
                                <th>FECHA PUBLICACIÓN</th>
                                <th>DESCARGAR</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $a)
                            @if($a->estado=='VISIBLE')
                            <tr>
                                <td>{{$a->titulo}}</td>
                                <td>{{$a->descripcion}}</td>
                                <td>{{$a->created_at}}</td>
                                <td><a target="_blank" href="{{ asset('docs/'.$a->file)}}" class="btn btn-primary btn-block btn-sm"><i class="fa fa-download"></i></a></td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
            </div>
            @else
            @foreach($items as $a)
            @if($a->estado=='VISIBLE')
            <div class="col-md-4" style="margin-top:5px; padding: 5px;">
                <div style="padding: 10px; border: 1px solid rgba(107,115,130,0.73); border-radius: 5px 5px 5px 5px; width: 100%; height: 100%;">
                    <div class="profile_title">
                        <div class="col-md-12">
                            <h5 title="{{$a->titulo}}">{{str_limit($a->titulo, $limit = 25, $end = '...')}}</h5>
                        </div>
                    </div>
                    <a target="_blank" href="{{asset('docs/'.$a->file)}}">
                        <center><i class="fa fa-file-o" title="{{$a->titulo}}" style="width: 100%; height: 100px; font-size: 80px;"></i></center>
                    </a>
                    <p title="{{$a->descripcion}}">{{str_limit($a->descripcion, $limit = 85, $end = '...')}}</p>
                    <center style="bottom: 5px;">
                        <a style="background-color: #65696ead; color: #FFF;" href="{{ asset('docs/'.$a->file)}}" target="_blank" class="btn btn-default btn-block btn-sm" data-toggle="tooltip" data-placement="top" title="Descargar Archivo"><i class="fa fa-download"></i> DESCARGAR ARCHIVO</a>
                    </center>
                </div>
            </div>
            @endif
            @endforeach
            @endif
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