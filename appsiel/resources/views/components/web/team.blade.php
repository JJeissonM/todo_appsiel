<style>

    #team {
        position: relative;
        z-index: 80 !important;
        padding: 100px 0 100px;
        <?php
        if ($team != null) {
            if ($team->tipo_fondo == 'COLOR') {
                echo "background-color: " . $team->fondo . ";";
            } else {
        ?>background: url('{{$team->fondo}}') {{$team->repetir}} center {{$team->direccion}};
        <?php
            }
        }
        ?>
    }

    .image-flip:hover .backside,
    .image-flip.hover .backside {
        -webkit-transform: rotateY(0deg);
        -moz-transform: rotateY(0deg);
        -o-transform: rotateY(0deg);
        -ms-transform: rotateY(0deg);
        transform: rotateY(0deg);
        border-radius: .25rem;
    }

    .image-flip:hover .frontside,
    .image-flip.hover .frontside {
        -webkit-transform: rotateY(180deg);
        -moz-transform: rotateY(180deg);
        -o-transform: rotateY(180deg);
        transform: rotateY(180deg);
    }

    .mainflip {
        -webkit-transition: 1s;
        -webkit-transform-style: preserve-3d;
        -ms-transition: 1s;
        -moz-transition: 1s;
        -moz-transform: perspective(1000px);
        -moz-transform-style: preserve-3d;
        -ms-transform-style: preserve-3d;
        transition: 1s;
        transform-style: preserve-3d;
        position: relative;
    }

    .frontside {
        border-radius: 20px;
        position: relative;
        -webkit-transform: rotateY(0deg);
        -ms-transform: rotateY(0deg);
        z-index: 2;
        margin-bottom: 30px;
    }

    .backside {
        border-radius: 20px;
        position: absolute;
        top: 0;
        left: 0;
        background: white;
        -webkit-transform: rotateY(-180deg);
        -moz-transform: rotateY(-180deg);
        -o-transform: rotateY(-180deg);
        -ms-transform: rotateY(-180deg);
        transform: rotateY(-180deg);
        -webkit-box-shadow: 5px 7px 9px -4px rgb(158, 158, 158);
        -moz-box-shadow: 5px 7px 9px -4px rgb(158, 158, 158);
        box-shadow: 5px 7px 9px -4px rgb(158, 158, 158);
    }

    .frontside,
    .backside {
        -webkit-backface-visibility: hidden;
        -moz-backface-visibility: hidden;
        -ms-backface-visibility: hidden;
        backface-visibility: hidden;
        -webkit-transition: 1s;
        -webkit-transform-style: preserve-3d;
        -moz-transition: 1s;
        -moz-transform-style: preserve-3d;
        -o-transition: 1s;
        -o-transform-style: preserve-3d;
        -ms-transition: 1s;
        -ms-transform-style: preserve-3d;
        transition: 1s;
        transform-style: preserve-3d;
    }

    .frontside .cardTeam,
    .backside .cardTeam {
        min-height: 350px;
    }

    .cardTeam-body {
    -ms-flex: 1 1 auto;
    flex: 1 1 auto;
    padding: 1.25rem;
}

    .cardTeam {
    position: relative;
    display: -ms-flexbox;
    display: flex;
    -ms-flex-direction: column;
    flex-direction: column;
    min-width: 0;
    word-wrap: break-word;
    background-color: #fff;
    background-clip: border-box;
    border: 1px solid rgba(0,0,0,.125);
    border-radius: .25rem;
}

    .backside .cardTeam a {
        font-size: 18px;
        color: #007b5e !important;
    }

    .frontside .cardTeam .cardTeam-title,
    .backside .cardTeam .cardTeam-title {
        color: #007b5e !important;
    }

    .frontside .cardTeam .cardTeam-body img {
        width: 180px;
        height: 160px;
        border-radius: 50%;
    }

    .cardTeam-title {
        /*margin-bottom: .75rem;*/
    }

    .cardTeam-text:last-child {
        margin-bottom: 0;
    }

    @media (max-width: 468px) {
        .container h2 {
            font-size: 28px !important;
        }

        .container p {
            font-size: 16px !important;
        }

    }
</style>
@if($team!=null)
<section id="team">
    <div id="visor_contenido_servicios">

    </div>
    <div class="container" id="contenedor_seccion_servicios">
        @if($team!=null)
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown; color: {{$team->title_color}} !important;">{{$team->title}}</h2>
            <p class="text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown; color: {{$team->description_color}} !important;">{{$team->description}}</p>
        </div>
        <div class="row">
            @if(count($team->teamitems) > 0)
            @foreach($team->teamitems as $item)
            <!-- Team member -->
            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="image-flip">
                    <div class="mainflip flip-0" style="background-color: transparent;">
                        <div class="frontside" style="border-radius: 20px; background-color: {{$item->background_color}} !important; opacity: 0.8;">
                            <div class="cardTeam">
                                <div class="cardTeam-body text-center">
                                    <p><img class=" img-fluid" src="{{asset($item->imagen)}}" alt="cardTeam image"></p>
                                    <h4 class="cardTeam-title" style="color: {{$item->title_color}};">{{$item->title}}</h4>
                                    <p class="cardTeam-text" style="color: {{$item->text_color}};">{{$item->description}}</p>
                                    <a class="btn btn-primary btn-sm" style="color: #fff;"><i class="fa fa-refresh"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="backside" style="border-radius: 20px; background-color: {{$item->background_color}} !important; opacity: 0.8;">
                            <div class="cardTeam">
                                <div class="cardTeam-body text-center mt-4">
                                    <h4 class="cardTeam-title" style="color: {{$item->title_color}};">{{$item->title}}</h4>
                                    <p class="cardTeam-text" style="color: {{$item->text_color}};">{{$item->more_details}}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- ./Team member -->
            @endforeach
            @endif
        </div>
        <!--/.row-->
        @else
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown">Sección</h2>
            <p class="text-center wow fadeInDown">Sin configuración</p>
        </div>
        @endif
    </div>
    <!--/.container-->

</section>
@endif
<script type="text/javascript">
    
</script>