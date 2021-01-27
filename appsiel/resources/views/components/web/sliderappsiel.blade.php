<style>   


    .slider-font {
        @if( !is_null($slider) )
            @if( !is_null($slider->configuracionfuente ) )
                font-family: <?php echo $slider->configuracionfuente->fuente->font; ?> !important;
            @endif
        @endif
    }

    .owl-carousel img {
        top: 0;
        right: 0;
        width: 100%;
        object-position: center;
        background-repeat: no-repeat;
        object-fit: cover;
    }

    #main-slider .item {
        width: 100% !important;
        height: auto !important;
    }

    @media (max-width: 769px){
        #main-slider .owl-next,
        #main-slider .owl-prev{
            top: 25%;
        } 
    }

    @media @media (min-width: 768px) and (max-width: 1024px){
        #main-slider .owl-next,
        #main-slider .owl-prev{
            top: 40%;
        } 
    }
    

    #main-slider .owl-prev{
        border-radius: 0 35px 35px 0;
        width: 35px;
        left: 0;
        text-indent: 0;
    }

    #main-slider .owl-next{
        border-radius: 35px 0 0 35px;
        width: 35px;
        right: 0;
        text-indent: 0;
    }


</style>
<section id="main-slider" class="slider-font">
    @if($slider != null && $slider->items->count() > 0)
    <div class="owl-carousel slider-font">
        @foreach($slider->items as $item)
        <div class="item" style="width: 100%;">            
           <img id="image" src="{{asset($item->imagen)}}" alt="{{$item->titulo}}">
        </div>
        @endforeach
    </div>
    <!--/.item-->
    @else
    <div class="owl-carousel">
        <div class="item" style="background-image: url('{{asset('images/slider/bg1.jpg')}}');">
            <div class="slider-inner">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="carousel-content">
                                <h2><span>Multi</span> is the best Onepage html template</h2>
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                    incididunt ut labore et dolore magna incididunt ut labore aliqua. </p>
                                <a class="btn btn-primary btn-lg" href="#">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/.item-->
        <div class="item" style="background-image: url({{asset('images/slider/bg2.jpg')}});">
            <div class="slider-inner">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="carousel-content">
                                <h2>Beautifully designed <span>free</span> one page template</h2>
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                    incididunt ut labore et dolore magna incididunt ut labore aliqua. </p>
                                <a class="btn btn-primary btn-lg" href="#">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/.item-->
    </div>
    <!--/.owl-carousel-->
    @endif
</section>
<!--/#main-slider-->