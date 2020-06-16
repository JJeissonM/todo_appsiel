<style>
    #testimonial-area {
        padding: 115px 0 0;
        background-image: url({{asset('img/lading-page/'.$testimonial->imagen_fondo)}});
        background-repeat: no-repeat;
        background-position: center;
    }

    #testimonial-area .section-heading h2 {
        font-size: 48px;
        line-height: 58px;
    }

    .testi-wrap {
        position: relative;
        height: 725px;
        margin-top: -80px
    }

    .section-heading {
        margin-bottom: 54px;
    }

    *, ::after, ::before {
        box-sizing: border-box;
    }

    .section-heading h5 {
        font-size: 14px;
        line-height: 26px;
    }

    .section-heading h5 {
        color: #0084ff;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 10px;
    }

    img {
        vertical-align: middle;
        border-style: none;
    }

    #testimonial-area .section-heading h2 {
        font-size: 30px;
        line-height: 40px;
    }

    .section-heading h2 {
        font-weight: 700;
        margin-bottom: 10px;
    }

    .section-heading p {
        font-size: 16px;
        line-height: 26px;
    }

    p {
        color: #505b6d;
        font-family: 'Open Sans', sans-serif;
    }

    .client-single {
        text-align: center;
        position: absolute;
        -webkit-transition: all 1s ease;
        transition: all 1s ease;
    }

    .client-info {
        -webkit-transition: all 0.3s ease;
        transition: all 0.3s ease
    }

    .client-comment {
        -webkit-transition: all 0.3s ease;
        transition: all 0.3s ease
    }

    .client-single.inactive .client-comment,
    .client-single.inactive .client-info {
        display: none
    }

    .client-single.inactive .client-comment,
    .client-single.inactive .client-info {
        opacity: 0;
        visibility: hidden
    }

    .client-single.position-1 {
        -webkit-transform: scale(0.65);
        transform: scale(0.65);
    }

    .client-single.position-2 {
        left: -330px;
        top: 105px;
    }

    .client-single.position-3 {
        left: -320px;
        top: 240px;
        -webkit-transform: scale(.40) !important;
        transform: scale(.40) !important;
    }

    .client-single.position-4 {
        left: -255px;
        top: 380px;
    }

    .client-single.position-5 {
        top: 30px;
        right: -180px;
    }

    .client-single.position-6 {
        top: 225px;
        right: -270px;
    }

    .client-single.position-7 {
        top: 400px;
        right: -160px;
        -webkit-transform: scale(.40) !important;
        transform: scale(.40) !important;
    }

    .client-single.position-3, .client-single.position-7 {
        -webkit-transform: scale(.25) !important;
        transform: scale(.25) !important;
    }

    .client-single.active {
        top: 10%;
        left: 50%;
        -webkit-transform: translateX(-50%);
        transform: translateX(-50%);
        z-index: 10;
        width: 100%
    }

    .client-single.active .client-comment,
    .client-single.active .client-info {
        -webkit-transition-delay: 0.6s;
        transition-delay: 0.6s
    }

    .client-single:not(.active) {
        -webkit-transform: scale(0.55);
        transform: scale(0.55);
        z-index: 99;
    }

    .client-single.active .client-img {
        width: 160px;
        height: 160px;
        margin: 0 auto 24px;
        position: relative;
    }

    .client-single.active .client-img:before {
        border-radius: 100%;
        content: '';
        background-image: -webkit-gradient(linear, left top, left bottom, from(rgb(157, 91, 254)), to(rgb(56, 144, 254)));
        background-image: linear-gradient(180deg, rgb(157, 91, 254) 0%, rgb(56, 144, 254) 100%);
        padding: 5px;
        width: 160px;
        height: 160px;
        top: -4px;
        left: 0px;
        position: absolute;
        z-index: -1;
    }

    .client-single .client-img img {
        border-radius: 50%;
        border: 8px solid #d1e9ff;
        cursor: pointer
    }

    .client-single.active .client-img img {
        max-width: 152px;
        margin: 0 auto 24px;
        border: 0;
        width: 160px;
        height: 152px;
    }

    .client-comment {
        padding: 0 30px;
    }

    .client-comment h3 {
        font-size: 18px;
        line-height: 20px;
        color: #505b6d;
    }

    .client-comment span i {
        font-size: 60px;
        color: #0084ff;
        margin: 40px 0 24px;
        display: inline-block
    }

    .client-info h3 {
        color: #000;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .client-info p {
        color: #0084ff;
        text-transform: uppercase;
    }

    @media only screen and (max-width: 479px) and (min-width: 360px) {
        h2 {
            font-size: 30px;
            line-height: 40px
        }

        h5 {
            font-size: 16px;
            line-height: 26px;
        }

        .awesome-feat-img img {
            max-width: 300px;
        }

        #pricing-wrap .yearly,
        #pricing-wrap .monthly {
            right: -30px;
        }

        #testimonial-area .section-heading h2 {
            font-size: 30px;
            line-height: 40px;
        }

        .client-comment h3 {
            font-size: 14px;
            line-height: 26px;
        }

        .client-single.active {
            width: 80%;
        }

        .client-comment span i {
            font-size: 40px;
        }

        .client-single:not(.active) {
            -webkit-transform: scale(0.25);
            transform: scale(0.25);
        }

        .client-single.position-5,
        .client-single.position-7,
        .client-single.position-6 {
            right: -70px;
        }

        .client-single.position-4 {
            left: -60px;
        }

        .client-single.position-3 {
            left: -75px;
        }

        .client-single.position-3,
        .client-single.position-7 {
            -webkit-transform: scale(.25) !important;
            transform: scale(.25) !important;
        }

        .client-single.active .client-img img {
            max-width: 80px;
        }

        .client-single.active .client-img::before {
            padding: 5px;
            width: 88px;
            height: 88px;
            top: -4px;
            left: 16px;
        }

        .client-single.active .client-img {
            width: 120px;
            height: 100px;
        }

        .testi-wrap {
            height: 600px;
        }
    }

    /*--------------------------------------------------------------
    large mobile
----------------------------------------------------------------*/

    @media only screen and (min-width: 480px) and (max-width: 767px) {
        h2 {
            font-size: 30px;
            line-height: 40px
        }

        h5 {
            font-size: 16px;
            line-height: 26px
        }

        .awesome-feat-img img {
            max-width: 300px;
        }

        .awesome-feat-carousel.owl-carousel .owl-nav > div {
            bottom: -80px;
        }

        #pricing-wrap .yearly,
        #pricing-wrap .monthly {
            right: -30px;
        }

        #testimonial-area .section-heading h2 {
            font-size: 30px;
        }

        .client-comment h3 {
            font-size: 14px;
            line-height: 26px;
        }

        .client-single.active {
            width: 60%;
        }

        .client-comment span i {
            font-size: 40px;
        }

        .client-single:not(.active) {
            -webkit-transform: scale(0.55);
            transform: scale(0.35);
        }

        .client-single.position-5,
        .client-single.position-7 {
            right: 0;
        }

        .client-single.position-4 {
            left: 0;
        }

        .client-single.position-3,
        .client-single.position-7 {
            -webkit-transform: scale(.30) !important;
            transform: scale(.30) !important;
        }

        .client-single.active .client-img img {
            max-width: 80px;
        }

        .client-single.active .client-img::before {
            padding: 5px;
            width: 88px;
            height: 88px;
            top: -4px;
            left: 16px;
        }

        .client-single.active .client-img {
            width: 120px;
            height: 100px;
        }

        .testi-wrap {
            height: 630px;
        }
    }

</style>
<section id="testimonial-area">
    <div class="container" style="max-width: 540px">
        <div class="row" style="margin-left: -50px; margin-right: -405px;">
            <!--start section heading-->
            <div class="col-md-8 offset-md-2">
                <div class="section-heading text-center">
                    <h5></h5>
                    <h2>{{$testimonial->titulo}}</h2>
                    <p>{{$testimonial->descripcion}}</p>
                </div>
            </div>
            <!--end section heading-->
        </div>
        <div class="testi-wrap">
            @if(count($testimonial->itemtestimonials)>0)
                <?php $aux = 0; ?>
                @foreach($testimonial->itemtestimonials as $item)
                    <?php $aux = $aux + 1; ?>
                    @if($aux <= 7)
                        @if($aux == 1)
                            <?php $class = 'active'; ?>
                        @else
                            <?php $class = 'inactive'; ?>
                        @endif
                        <div class="client-single {{$class}} position-{{$aux}}" data-position="position-{{$aux}}">
                            <div class="client-img">
                                <img width="200px" height="200px"
                                     src="{{asset($item->foto)}}" alt="">
                            </div>
                            <div class="client-comment">
                                <h3>{{$item->testimonio}}</h3>
                                <span><i class="fa fa-quote-left"></i></span>
                            </div>
                            <div class="client-info">
                                <h3>{{$item->nombre}}</h3>
                                <p>{{$item->cargo}}</p>
                            </div>
                        </div>
                    @else
                        @break
                    @endif
                @endforeach
            @endif
        </div>
    </div>
</section>