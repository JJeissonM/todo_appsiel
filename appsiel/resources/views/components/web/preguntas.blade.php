<?php
$r=0;
$g=0;
$b=0;
$r1=0;
$g1=0;
$b1=0;
if($pregunta!=null){
    list($r, $g, $b) = sscanf($pregunta->color1, "#%02x%02x%02x");
    list($r1, $g1, $b1) = sscanf($pregunta->color2, "#%02x%02x%02x");
}
?>

<style>
    #faq-area {
        <?php
        if ($pregunta != null) {
            if ($pregunta->tipo_fondo == 'COLOR') {
                echo "background-color: " . $pregunta->fondo . ";";
            } else {
        ?>background: url('{{$pregunta->fondo}}') {{$pregunta->repetir}} center {{$pregunta->direccion}};
        <?php
            }
        }
        ?>
    }

    #faq-area.bg-1 {
        
    }

    .pregunta-font {
        @if( !is_null($pregunta) )
            @if( !is_null($pregunta->configuracionfuente ) )
                font-family: <?php echo $pregunta->configuracionfuente->fuente->font; ?> !important;
            @endif
        @endif
    }

    #faq-area .section-heading p {
        padding: 0 20px;
    }

    .card {
        margin-bottom: 20px;
        border-radius: 10px;
        border: 0
    }

    .card .card-header {
        background-color: #fff;
        -webkit-box-shadow: 0px 0px 15px 0px rgba(52, 69, 199, 0.4);
        box-shadow: 0px 0px 15px 0px rgba(52, 69, 199, 0.4);
        border: 0;
        border-radius: 10px;
        padding: 0
    }

    .card.v-dark .card-header {
        background-color: #0084ff;
    }

    .card .card-header.active {
        border-radius: 10px 10px 0 0
    }

    .card .card-header.active,
    .card .card-header:hover {
        background-image: -webkit-gradient(linear, left top, right top, from(rgb({{$r}}, {{$g}}, {{$b}})), to(rgb({{$r1}}, {{$g1}}, {{$b1}})));
        background-image: linear-gradient(90deg, rgb({{$r}}, {{$g}}, {{$b}}) 0%, rgb({{$r1}}, {{$g1}}, {{$b1}}) 100%);
    }

    .card.two .card-header.active,
    .card.two .card-header:hover {
        background-image: linear-gradient(45deg, rgb({{$r}}, {{$g}}, {{$b}}) 0%, rgb({{$r1}}, {{$g1}}, {{$b1}}) 100%);
    }

    ::after,
    ::before {
        box-sizing: border-box;
    }

    .card .card-header.active a,
    .card .card-header:hover a,
    .card-body p,
    .card.v-dark .card-header a {
        color: #fff !important;
    }

    .card .card-header a {
        font-size: 18px;
        line-height: 28px;
        font-weight: 600;
        color: #000;
        display: block;
        padding: 20px 30px;
        position: relative
    }

    .card .card-header a:after {
        content: '\f078';
        font-family: 'FontAwesome';
        position: absolute;
        right: 30px
    }

    .card .card-header.active a:after {
        content: '\f077';
        font-family: 'FontAwesome'
    }

    .card-body {
        background-image: -webkit-gradient(linear, left top, right top, from(rgb({{$r}}, {{$g}}, {{$b}})), to(rgb({{$r1}}, {{$g1}}, {{$b1}})));
        background-image: linear-gradient(90deg, rgb({{$r}}, {{$g}}, {{$b}}) 0%, rgb({{$r1}}, {{$g1}}, {{$b1}}) 100%);
        border-radius: 0 0 10px 10px;
        padding: 0 30px 10px 30px;
        color: white;
    }

    .card.two .card-body {
        background-image: linear-gradient(45deg, rgb({{$r}}, {{$g}}, {{$b}}) 0%, rgb({{$r1}}, {{$g1}}, {{$b1}}) 100%);

    }

    .faq-img img {
        max-width: 350px;
        margin-left: 130px;
    }
    .collapse{
        display: none;
    }
</style>

<section id="faq-area" class="bg-1 pregunta-font p-md-5 p-sm-2">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown animated pregunta-font" style="visibility: visible; animation-name: fadeInDown;">{{$pregunta->titulo}}</h2>
            <p class="text-center wow fadeInDown animated pregunta-font" style="visibility: visible; animation-name: fadeInDown; color: #000; font-weight: bold;">{{$pregunta->descripcion}}</p>
        </div>
        <div class="row">

            <div class="col-md-7">
                <div id="accordion" role="tablist">
                    <!--start faq single-->
                    @if(count($pregunta->itempreguntas) > 0)
                    <?php $ne = count($pregunta->itempreguntas) < 5 ? count($pregunta->itempreguntas) : 5; ?>
                    @for($i = 0; $i < $ne; $i++)
                    <div class="card pregunta-font" style="opacity: 0.8 !important;">
                        <div style="opacity: 0.8 !important;" class="card-header" role="tab" id="faq{{$pregunta->itempreguntas[$i]->id}}" onclick="agregar('collapse{{$pregunta->itempreguntas[$i]->id}}')">
                            <h5 class="mb-0">
                                <a data-toggle="collapse" href="#collapse{{$pregunta->itempreguntas[$i]->id}}" aria-expanded="false" aria-controls="collapse{{$pregunta->itempreguntas[$i]->id}}" class="collapsed pregunta-font">{{$pregunta->itempreguntas[$i]->pregunta}}</a>
                            </h5>
                        </div>
                        <div id="collapse{{$pregunta->itempreguntas[$i]->id}}" class="collapse" role="tabpanel" aria-labelledby="faq{{$pregunta->itempreguntas[$i]->id}}" data-parent="#accordion">
                            <div class="card-body pregunta-font">
                                <?php echo $pregunta->itempreguntas[$i]->respuesta ?>
                            </div>
                        </div>
                    </div>
                    @endfor
                    @if (count($pregunta->itempreguntas) > 5)   
                        @for($i = 5; $i < count($pregunta->itempreguntas); $i++)
                        <div class="colsh card pregunta-font collapse" style="opacity: 0.8 !important;" role="tabpanel" aria-labelledby="faqsm">
                            <div style="opacity: 0.8 !important;" class="card-header" role="tab" id="faq{{$pregunta->itempreguntas[$i]->id}}" onclick="agregar('collapse{{$pregunta->itempreguntas[$i]->id}}')">
                                <h5 class="mb-0">
                                    <a data-toggle="collapse" href="#collapse{{$pregunta->itempreguntas[$i]->id}}" aria-expanded="false" aria-controls="collapse{{$pregunta->itempreguntas[$i]->id}}" class="collapsed pregunta-font">{{$pregunta->itempreguntas[$i]->pregunta}}</a>
                                </h5>
                            </div>
                            <div id="collapse{{$pregunta->itempreguntas[$i]->id}}" class="collapse" role="tabpanel" aria-labelledby="faq{{$pregunta->itempreguntas[$i]->id}}" data-parent="#accordion">
                                <div class="card-body pregunta-font">
                                    <?php echo $pregunta->itempreguntas[$i]->respuesta ?>
                                </div>
                            </div>
                        </div>
                        @endfor 
                        <div class="card pregunta-font" style="opacity: 0.8 !important;">
                            <div style="opacity: 0.8 !important;" class="card-header" role="tab" id="faqsm" onclick="agregar('collapseshowmore')">
                                <h5 class="mb-0">
                                    <a data-toggle="collapse" href=".colsh.card" aria-expanded="false" aria-controls="collapsesm" class="collapsed pregunta-font">Mostrar Mas / Mostrar Menos</a>
                                </h5>
                            </div>
                        </div>                    
                    @endif
                    @endif
                </div>
            </div>
            <div class="col-md-5">
                <div class="faq-img">
                    <img src="{{asset($pregunta->imagen_fondo)}}" class="img-fluid" style="margin-top: -50px; margin-left: 55px;" alt="">
                </div>
            </div>
        </div>
    </div>

</section>
<script type="text/javascript">

    document.addEventListener('DOMContentLoaded',function(){
        $('#faq-area .collapse').on('hidden.bs.collapse', function (event) {
            event.target.parentElement.querySelector('.card-header').classList = 'card-header';
        })
    })
        

    function agregar(name) {     
        event.target.parentElement.parentElement.classList.toggle('active');
    }
</script>