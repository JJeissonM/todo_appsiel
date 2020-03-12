<style>
    ::after, ::before {
        box-sizing: border-box;
    }
    user agent stylesheet
    div {
        display: block;
    }
    body {
        font-family: 'Poppins', sans-serif;
        font-weight: 400;
        color: #000;
        position: relative;
        overflow-x: hidden;
    }
    .card {
        margin-bottom: 20px;
        border-radius: 10px;
        border: 0;
    }
    .card .card-header {
        background-color: #fff;
        -webkit-box-shadow: 0px 0px 15px 0px rgba(52, 69, 199, 0.4);
        box-shadow: 0px 0px 15px 0px rgba(52, 69, 199, 0.4);
        border: 0;
        border-radius: 10px;
        padding: 0;
    }
    .card-header:first-child {
        border-radius: calc(.25rem - 1px) calc(.25rem - 1px) 0 0;
    }
    .card .card-header.active, .card .card-header:hover {
        background-image: -webkit-gradient( linear, left top, right top, from(rgb(32, 0, 126)), to(rgb(230, 30, 182)));
        background-image: linear-gradient( 90deg, rgb(32, 0, 126) 0%, rgb(230, 30, 182) 100%);
    }
    .card .card-header.active {
        border-radius: 10px 10px 0 0;
    }
    .mb-0, .my-0 {
        margin-bottom: 0!important;
    }
    h5{
        font-size: 16px;
        line-height: 26px;
    }
    a, a:visited, a:focus, a:active, a:hover {
        text-decoration: none;
        outline: none;
    }
    .card .card-header a {
        font-size: 15px;
        line-height: 25px;
        padding: 15px 15px;
    }
    .card .card-header a {
        font-size: 18px;
        line-height: 28px;
        font-weight: 600;
        color: #000;
        display: block;
        padding: 20px 30px;
        position: relative;
    }
    .card .card-header.active a, .card .card-header:hover a, .card-body p, .card.v-dark .card-header a {
        color: #fff;
    }
    .card .card-header a {
        font-size: 15px;
        line-height: 25px;
        padding: 15px 15px;
    }
    .card .card-header a:after {
        content: '\eab2';
        font-family: 'IcoFont';
        position: absolute;
        right: 30px;
    }
    .card .card-header.active a:after {
        content: '\eab9';
        font-family: 'IcoFont';
    }
    .card-body {
        background-image: -webkit-gradient( linear, left top, right top, from(rgb(32, 0, 126)), to(rgb(230, 30, 182)));
        background-image: linear-gradient( 90deg, rgb(32, 0, 126) 0%, rgb(230, 30, 182) 100%);
        border-radius: 0 0 10px 10px;
        padding: 0 30px 10px 30px;
    }
    .card-body {
        -ms-flex: 1 1 auto;
        flex: 1 1 auto;
        padding: 1.25rem;
    }
    .card .card-header.active a, .card .card-header:hover a, .card-body p, .card.v-dark .card-header a {
        color: #fff;
    }
    p {
        font-size: 16px;
        color: #505b6d;
        line-height: 26px;
        font-family: 'Open Sans', sans-serif;
    }
    p {
        margin-top: 0;
        margin-bottom: 1rem;
    }
</style>
<div class="col-sm-12">
    <div class="section-header">
        <h2 class="section-title text-center wow fadeInDown animated"
            style="visibility: visible; animation-name: fadeInDown;">PREGUNTAS FRECUENTES</h2>
    </div>
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
    <div class="col-md-7">
        <div id="accordion" role="tablist">
            <!--start faq single-->
            <div class="card">
                <div class="card-header" role="tab" id="faq1">
                    <h5 class="mb-0">
                        <a data-toggle="collapse" href="#collapse1" aria-expanded="false" aria-controls="collapse1" class="collapsed">Is the Mobile App Secure?</a>
                    </h5>
                </div>
                <div id="collapse1" class="collapse" role="tabpanel" aria-labelledby="faq1" data-parent="#accordion" style="">
                    <div class="card-body">
                        <p>Both the Mobile Apps and the Mobile Web App give you the ability to you to access your account information, view news releases, report an outage, and contact us via email or phone. Once you've installed a Mobile App on your phone, you'll also have the ability to view a map of our offices and payment locations.</p>
                    </div>
                </div>
            </div>
            <!--end faq single-->
            <!--start faq single-->
            <div class="card">
                <div class="card-header" role="tab" id="faq2">
                    <h5 class="mb-0">
                        <a class="collapsed" data-toggle="collapse" href="#collapse2" aria-expanded="false" aria-controls="collapse2">What features does the Mobile App have?</a>
                    </h5>
                </div>
                <div id="collapse2" class="collapse" role="tabpanel" aria-labelledby="faq2" data-parent="#accordion" style="">
                    <div class="card-body">
                        <p>Both the Mobile Apps and the Mobile Web App give you the ability to you to access your account information, view news releases, report an outage, and contact us via email or phone. Once you've installed a Mobile App on your phone, you'll also have the ability to view a map of our offices and payment locations.</p>
                    </div>
                </div>
            </div>
            <!--end faq single-->
            <!--start faq single-->
            <div class="card">
                <div class="card-header" role="tab" id="faq3">
                    <h5 class="mb-0">
                        <a class="collapsed" data-toggle="collapse" href="#collapse3" aria-expanded="false" aria-controls="collapse3">How do I get the Mobile App for my phone?</a>
                    </h5>
                </div>
                <div id="collapse3" class="collapse" role="tabpanel" aria-labelledby="faq3" data-parent="#accordion" style="">
                    <div class="card-body">
                        <p>Both the Mobile Apps and the Mobile Web App give you the ability to you to access your account information, view news releases, report an outage, and contact us via email or phone. Once you've installed a Mobile App on your phone, you'll also have the ability to view a map of our offices and payment locations.</p>
                    </div>
                </div>
            </div>
            <!--end faq single-->
            <!--start faq single-->
            <div class="card">
                <div class="card-header" role="tab" id="faq4">
                    <h5 class="mb-0">
                        <a class="collapsed" data-toggle="collapse" href="#collapse4" aria-expanded="false" aria-controls="collapse4">How does Arribo differ from usual apps? </a>
                    </h5>
                </div>
                <div id="collapse4" class="collapse" role="tabpanel" aria-labelledby="faq4" data-parent="#accordion">
                    <div class="card-body">
                        <p>Both the Mobile Apps and the Mobile Web App give you the ability to you to access your account information, view news releases, report an outage, and contact us via email or phone. Once you've installed a Mobile App on your phone, you'll also have the ability to view a map of our offices and payment locations.</p>
                    </div>
                </div>
            </div>
            <!--end faq single-->
        </div>
    </div>
</div>