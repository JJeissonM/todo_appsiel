<!-- Start WOWSlider.com HEAD section --> <!-- add to the <head> of your page -->
<link rel="stylesheet" type="text/css" href="{{asset('engine0/style.css')}}"/>
<script type="text/javascript" src="{{asset('engine0/jquery.js')}}"></script>
<!-- End WOWSlider.com HEAD section --></head>
<section id="main-sliderpremiun">
@if($slider != null && $slider->items->count() > 0)
    <!-- Start WOWSlider.com BODY section --> <!-- add to the <body> of your page -->
        <div id="wowslider-container0">
            <div class="ws_images" style="width: 100%;">
                <ul>
                    {{$count = 0}}
                    @foreach($slider->items as $item)
                        <li><img src="{{asset($item->imagen)}}" alt="{{$item->titulo}}" title="" id="wows0_{{$count}}"/>
                        </li>
                        {{$count = $count+1}}
                    @endforeach
                </ul>
            </div>
            <div class="ws_bullets">
                <div>
                </div>
            </div>
            <div class="ws_shadow"></div>
        </div>

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
<script type="text/javascript" src="{{asset('engine0/wowslider.js')}}"></script>
<script type="text/javascript" src="{{asset('engine0/script.js')}}"></script>
<!-- End WOWSlider.com BODY section -->



