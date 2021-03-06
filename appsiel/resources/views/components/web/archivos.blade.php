<style type="text/css">

    #blog {
        
        <?php
        if ($archivo != null) {
            if ($archivo->tipo_fondo == 'COLOR') {
                echo "background-color: " . $archivo->fondo . ";";
            } else {
        ?>background: url('{{$archivo->fondo}}') {{$archivo->repetir}} center {{$archivo->direccion}};
        <?php
            }
        }
        ?>
    }

    .archivos-font {
        @if( !is_null($archivo) )
            @if( !is_null($archivo->configuracionfuente ) )
                font-family: <?php echo $archivo->configuracionfuente->fuente->font; ?> !important;
            @endif
        @endif
    }

    .table-archivos{
        display: grid;
        grid-template-columns: repeat(4 , 1fr);
        width: 100%;
        grid-gap: 6px;
    }

    .thead{
        display: flex;
        justify-content: center;
        border-bottom: 2px solid #64686d;
        background-color: #F2F2F2;
        font-weight: bolder;
    }

    .ttitulo{
        grid-column: 1 / 2;
        display: flex;
        justify-content: center;
        
    }

    .tdescripcion{
        grid-column: 2 / 3;
        display: flex;
        justify-content: center;
        
    }

    .tfecha{
        grid-column: 3 / 4;
        display: flex;
        justify-content: center;
        
    }

    .tdescarga{
        grid-column: 4 / 5;
        display: flex;
        justify-content: center;
        flex-grow: 0;
    }

</style>


<section id="blog" class="archivos-font">
    <div class="container">
        @if($archivo!=null)
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown">{{$archivo->titulo}}</h2>
            <p class="text-center wow fadeInDown">{{$archivo->descripcion}}</p>
            <p class="text-center wow fadeInDown">
                <input type="text" class="form-control" id="buscar" name="buscar" onkeyup="buscar()" placeholder="Escriba título para filtrar..." />
            </p>
        </div>
        <div class="row col-md-12 wow fadeInDown" id="txt">
            @if($archivo->formato=='LISTA')
            <div class="table-archivos">
                <div class="thead">TÍTULO</div>
                <div class="thead">DESCRIPCIÓN</div>
                <div class="thead">FECHA PUBLICACIÓN</div>
                <div class="thead">DESCARGAR</div>

                @foreach($items as $a)
                @if($a->estado=='VISIBLE')
                
                    <div class="ttitulo">{{$a->titulo}}</div>
                    <div class="tdescripcion">{{$a->descripcion}}</div>
                    <div class="tfecha">{{$a->created_at}}</div>
                    <div class="tdescarga"><a target="_blank" href="{{ asset('docs/'.$a->file)}}" class="btn btn-primary btn-block btn-sm"><i class="fa fa-download"></i></a></div>
                
                @endif
                @endforeach
            </div>
            @else
                @foreach($items as $a)    

                @if($a->estado=='VISIBLE')
                <!-- TIPO SANTILLANA -->
                <div class="col-md-4 col-sm-6 wow fadeInUp animated service-info" data-wow-duration="300ms" data-wow-delay="0ms" style="visibility: visible; animation-duration: 300ms; animation-delay: 0ms; animation-name: fadeInUp; margin-bottom: 20px;">
                    <div style="border-radius: 20px !important; -webkit-box-shadow: 1px 1px 100px var(--color-terciario); -moz-box-shadow: 1px 1px 100px var(--color-terciario); box-shadow: 1px 1px 100px var(--color-terciario);">
                        <div style="background-color: #fff; border-top-right-radius: 20px !important; border-top-left-radius: 20px !important;"><center><i class="my-4 fa fa-file-o" title="{{$a->titulo}}" style="width: 100%; height: 100px; font-size: 120px;"></i></center></div>
                        <div style="background-color: #fff; padding: 20px; border-bottom-right-radius: 20px !important; border-bottom-left-radius: 20px !important;">
                            <h4 class="media-heading servicios-font" style="margin-top: 0px;">{{$a->titulo}}</h4>
                            <?php
                                $fecha = date_create($a->created_at);
                                $meses = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");                       
                                $fecha_final = date_format($fecha,"d")." ".$meses[date_format($fecha,"n")-1]." ".date_format($fecha,"Y");
                            ?>
                            <p><em>{{$fecha_final}}</em></p>
                            <p class="servicios-font">{!! str_limit($a->descripcion,90) !!}</p>
                            <div class="pull-right">
                                
                                <a class="btn btn-primary animate btn-sm servicios-font"  href="{{ asset('docs/'.$a->file)}}" target="_blank" style="cursor: pointer; color: #fff;">Descargar <i class="fa fa-download"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                @endif
                @endforeach
            @endif
        </div>
        @else
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown">Sección</h2>
            <p class="text-center wow fadeInDown">Sin configuración</p>
        </div>
        @endif
    </div>

    <script type="text/javascript">
        var array = <?php echo json_encode($items); ?>;
        var archivo = <?php echo json_encode($archivo); ?>;

        console.log(archivo);

        function buscar() {
            $("#txt").html("");
            var texto = $("#buscar").val();
            var nuevoArray = [];
            array.forEach(function(i) {
                if (i.titulo.indexOf(texto) != -1) {
                    nuevoArray.push(i);
                }
            });
            arrayDraw(nuevoArray);
        }

        function arrayDraw(array) {
            var html = "";
            if (archivo.formato == 'BLOG') {
                //BLOG
            } else {
                //LISTA
                html = html + "<div class='table-responsive'>" +
                    "<table class='table table-bordered table-striped table-hover' style='width: 100%;'>" +
                    "<thead><tr class='danger'><th>TÍTULO</th><th>DESCRIPCIÓN</th><th>FECHA PUBLICACIÓN</th>" +
                    "<th>DESCARGAR</th></tr></thead><tbody>";
            }
            array.forEach(function(i) {
                if (archivo.formato == 'BLOG') {
                    //BLOG
                    if (i.estado == 'VISIBLE') {
                        html = html + "<div class='col-md-4' style='margin-top:5px; padding: 5px;'>" +
                            "<div style='padding: 10px; border: 1px solid rgba(107,115,130,0.73); border-radius: 5px 5px 5px 5px; width: 100%; height: 100%;'>" +
                            "<div class='profile_title'><div class='col-md-12'><h5 title='" + i.titulo + "'>" + i.titulo + "</h5>" +
                            "</div></div><a target='_blank' href='{{url('')}}/docs/"+i.file+"'><center><i class='fa fa-file-o' title='" + i.titulo + "' style='width: 100%; height: 100px; font-size: 80px;'></i></center>" +
                            "</a><p title='" + i.descripcion + ">" + i.descripcion + "</p><center style='bottom: 5px;'>" +
                            "<a style='background-color: #65696ead; color: #FFF;' href='{{url('')}}/docs/"+i.file+"' target='_blank' class='btn btn-default btn-block btn-sm' data-toggle='tooltip' data-placement='top' title='Descargar Archivo'><i class='fa fa-download'></i> DESCARGAR ARCHIVO</a>" +
                            "</center></div></div>";
                    }
                } else {
                    //LISTA
                    if (i.estado == 'VISIBLE') {
                        html = html + "<tr><td>" + i.titulo + "</td><td>" + i.descripcion + "</td><td>" + i.created_at + "</td>" +
                            "<td><a target='_blank' href='{{url('')}}/docs/"+i.file+"' class='btn btn-primary btn-block btn-sm'><i class='fa fa-download'></i></a></td></tr>";
                    }
                }
            });
            if (archivo.formato == 'BLOG') {
                //BLOG
            } else {
                //LISTA
                html = html + "</tbody></table></div>";
            }
            $("#txt").html(html);
        }
    </script>

</section>