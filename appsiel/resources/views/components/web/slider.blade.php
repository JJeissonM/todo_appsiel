<style>

    #main-slider {
        /*margin-top: 40px;*/
    }

    #main-slider .item {
        height: 450px;
    }

    .owl-carousel img {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

</style>

<section id="main-slider">
    @if($slider != null && $slider->items->count() > 0)
        <div class="owl-carousel">
            @foreach($slider->items as $item)
                <div class="item">
                    <div class="slider-inner">
                        <div class="container">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="carousel-content">

                                        @if( $item->titulo != '' )
                                            <h2 style="text-shadow: 1px 1px 2px black;">{{$item->titulo}}</h2>
                                        @endif

                                        @if( $item->descripcion != '' )
                                            <p style="text-shadow: 1px 1px 2px black;">{{$item->descripcion}}</p>
                                        @endif

                                        @if( $item->button != '')
                                            <a class="btn btn-primary btn-lg"
                                               href="{{$item->enlace}}">{{$item->button}}</a>
                                        @endif

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <img class="image" src="{{asset($item->imagen)}}" alt="">
                </div>
            @endforeach
        </div><!--/.item-->
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
            </div><!--/.item-->
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
            </div><!--/.item-->
        </div><!--/.owl-carousel-->
    @endif
</section><!--/#main-slider-->
