<section id="portfolio">
    <div class="container">
        @if($galeria != null)
            <div class="section-header">
                <h2 class="section-title text-center wow fadeInDown animated"
                    style="visibility: visible; animation-name: fadeInDown;">{{$galeria->titulo}}</h2>
            </div>


            @foreach ($galeria->albums()->orderBy('created_at','DESC')->get()->chunk(4) as $chunk)
                <div class="row">
                    @foreach ($chunk as $album)
                        <div class="col-md-3 col-xs-6">
                            {!! $album->dibujar_individual() !!}
                        </div>
                    @endforeach
                </div>
            @endforeach


        @else
            <div class="section-header">
                <p class="text-center wow fadeInDown">Galería no configurada.</p>
            </div>
        @endif
    </div>
</section>

@section('script')
    <script type="text/javascript">
        $('ul.pagination').hide();
        $(function () {
            $('.infinite-scroll').jscroll({
                autoTrigger: true,
                loadingHtml: '<img class="center-block" src="{{asset('img/lading-page/loading.git')}}" alt="Loading..." />',
                padding: 0,
                nextSelector: '.pagination li.active + li a',
                contentSelector: 'div.infinite-scroll',
                callback: function () {
                    $('ul.pagination').remove();
                }
            });
        });
    </script>
@endsection