@if($clientes != null)
    <link rel="stylesheet" href="{{asset('css/swiper.css')}}">
    <link rel="stylesheet" href="{{asset('css/main_galeria.css')}}">
    <div class="section-header">
        <h2 class="section-title text-center wow fadeInDown animated"
            style="visibility: visible; animation-name: fadeInDown;">CLIENTES</h2>
    </div>
<div class="col-md-12" style="position: relative; margin-top: 250px; margin-bottom: 250px;">
    <main class="contenidoGaleria">
        <div class="contenedor">
            <section>
                <div class="swiper-container">
                    <div class="swiper-wrapper">
                        @foreach($clientes as $cli)
                            <div class="swiper-slide"><img style="object-fit: cover; width: 100%" src="{{url($cli->logo)}}" alt="{{$cli->nombre}}"></div>
                        @endforeach
                    </div>
                    <!-- Add Pagination -->
                    <div class="swiper-pagination"></div>

                    <!-- If we need navigation buttons -->
                    <div class="swiper-button-prev"></div>
                    <div class="swiper-button-next"></div>
                </div>
            </section>
        </div>
    </main>
</div>
    <script src="{{asset('js/swiper.js')}}"></script>
    <script>
        var swiper = new Swiper('.swiper-container', {
            loop: true,
            effect: 'coverflow',
            grabCursor: true,
            centeredSlides: true,
            slidesPerView: 'auto',
            coverflowEffect: {
                rotate: 50,
                stretch: 0,
                depth: 100,
                modifier: 1,
                slideShadows: true,
            },
            pagination: {
                el: '.swiper-pagination',
            },
            // Navigation arrows
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        });
    </script>
@else
    <div class="section-header">
        <h2 class="section-title text-center wow fadeInDown">Sección</h2>
        <p class="text-center wow fadeInDown">Sin configuración</p>
    </div>
@endif
