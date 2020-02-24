<section id="main-slider">
    @if($slider != null)
            <div class="owl-carousel">
                @foreach($slider->items as $item)
                    <div class="item" style="background-image: url('{{asset($item->imagen)}}');">
                    <div class="slider-inner">
                        <div class="container">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="carousel-content">
                                        <h2>{{$item->titulo}}</h2>
                                        <p>{{$item->descripcion}}</p>
                                        <a class="btn btn-primary btn-lg" href="{{$item->enlace}}">{{$item->button}}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--/.item-->
                @endforeach
            </div><!--/.owl-carousel-->
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
