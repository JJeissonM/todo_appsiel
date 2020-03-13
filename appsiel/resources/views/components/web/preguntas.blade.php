<style>
    #faq-area {
        margin: 118px 0 100px;
        background-repeat: no-repeat;
        background-position: left;
        background-size: 25% 95%;
    }

    #faq-area.bg-1 {
        background-image: url('{{asset('img/lading-page/1583859741codelco.png')}}')
    }

    #faq-area.bg-2 {
        background-image: url(../images/faq-bg-2.png)
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
        background-image: -webkit-gradient(linear, left top, right top, from(rgb(32, 0, 126)), to(rgb(230, 30, 182)));
        background-image: linear-gradient(90deg, rgb(32, 0, 126) 0%, rgb(230, 30, 182) 100%);
    }

    .card.two .card-header.active,
    .card.two .card-header:hover {
        background-image: linear-gradient(45deg, rgb(157, 91, 254) 0%, rgb(56, 144, 254) 100%);
    }

    ::after, ::before {
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
        background-image: -webkit-gradient(linear, left top, right top, from(rgb(32, 0, 126)), to(rgb(230, 30, 182)));
        background-image: linear-gradient(90deg, rgb(32, 0, 126) 0%, rgb(230, 30, 182) 100%);
        border-radius: 0 0 10px 10px;
        padding: 0 30px 10px 30px
    }

    .card.two .card-body {
        background-image: linear-gradient(45deg, rgb(157, 91, 254) 0%, rgb(56, 144, 254) 100%);

    }

    .faq-img img {
        max-width: 350px;
        margin-left: 130px;
    }
</style>

{{--    <div class="section-header">--}}
{{--        <h2 class="section-title text-center wow fadeInDown animated"--}}
{{--            style="visibility: visible; animation-name: fadeInDown;">PREGUNTAS FRECUENTES</h2>--}}
{{--    </div>--}}
{{--    <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">--}}
{{--        @foreach($preguntas as $item)--}}
{{--            <div class="panel panel-collapse col-md-12">--}}
{{--                <div class="panel-heading" role="tab" id="heading{{$item->id}}">--}}
{{--                    <h4 class="panel-title">--}}
{{--                        <button class="col-md-12 collapsed"--}}
{{--                                style="padding: 15px; cursor: pointer;text-align: left;border-radius: 5px; border: 1px solid; border-color: #1b6d85;"--}}
{{--                                data-toggle="collapse" data-parent="#accordion" href="#collapse{{$item->id}}"--}}
{{--                                aria-expanded="false" aria-controls="collapse{{$item->id}}">--}}
{{--                            {{$item->pregunta}}<i class="fa fa-plus" style="margin-right: 0px; float: right;"></i>--}}
{{--                        </button>--}}
{{--                    </h4>--}}
{{--                </div>--}}
{{--                <div id="collapse{{$item->id}}" class="panel-collapse collapse" role="tabpanel"--}}
{{--                     aria-labelledby="heading{{$item->id}}" aria-expanded="false" style="height: 0px;">--}}
{{--                    <div class="panel-body">--}}
{{--                        {{$item->respuesta}}--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        @endforeach--}}
{{--    </div>--}}
<section id="faq-area" class="bg-1">
    <div class="container">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="section-heading text-center">
                    <h5>Take A look</h5>
                    <h2>Frequently Asked Questions</h2>
                    <p>Our Mobile App can be downloaded and installed on your compatible mobile device easily. If you
                        have any questions - please look through the most frequently asked questions or contact us for
                        more details.</p>
                </div>
            </div>
        </div>
        <div class="row">

            <div class="col-md-7">
                <div id="accordion" role="tablist">
                    <!--start faq single-->
                    @foreach($preguntas as $item)
                        <div class="card">
                            <div class="card-header" role="tab" id="faq{{$item->id}}" onclick="agregar(event)" onfocusout="agregar(event)">
                                <h5 class="mb-0">
                                    <a data-toggle="collapse" href="#collapse{{$item->id}}" aria-expanded="false"
                                       aria-controls="collapse{{$item->id}}"
                                       class="collapsed">{{$item->pregunta}}</a>
                                </h5>
                            </div>
                            <div id="collapse{{$item->id}}" class="collapse" role="tabpanel"
                                 aria-labelledby="faq{{$item->id}}"
                                 data-parent="#accordion"
                                 style="">
                                <div class="card-body">
                                    <p>{{$item->respuesta}}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-md-5">
                <div class="faq-img">
                    <img src="{{asset('img/lading-page/faq-img-1.png')}}" class="img-fluid"
                         style="margin-top: -50px; margin-left: 55px;" alt="">
                </div>
            </div>
        </div>
    </div>

</section>
<script type="text/javascript">
    function agregar(event) {
        event.target.parentElement.parentElement.classList.toggle('active');
    }
</script>