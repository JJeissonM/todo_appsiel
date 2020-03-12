<section id="blog">
    <div class="container">
        @if($pedido!=null)
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown">{{$pedido->titulo}}</h2>
            <p class="text-center wow fadeInDown">{{$pedido->descripcion}}</p>
            <p class="text-center wow fadeInDown">
                <input type="text" class="form-control" id="buscar" name="buscar" onkeyup="buscar()" placeholder="Escriba título para filtrar..." />
            </p>
        </div>
        <div class="row col-md-12 wow fadeInDown" id="txt">
            @if(count($items)>0)
            @foreach($items as $i)
            <div class="col-md-6">
                <div class="blog-post blog-media fadeInRight">
                    <article class="media clearfix">
                        <div class="entry-thumbnail pull-left">
                            <img class="img-responsive" src="images/blog/02.jpg" alt="">
                            <span class="post-format post-format-gallery"><i class="fa fa-image"></i></span>
                        </div>
                        <div class="media-body">
                            <header class="entry-header">
                                <div class="entry-date">{{$i->descripcion}}</div>
                                <h2 class="entry-title"><a href="#">$ {{number_format($i->precio_venta,2)}} X {{$i->unidad_medida1}}</a></h2>
                            </header>

                            <div class="entry-content">
                                <P>Otra información</P>
                                <a class="btn btn-primary" href="#"><i class="fa fa-plus"></i> Añadir al carrito!</a>
                            </div>

                            <footer class="entry-meta">
                                <span class="entry-category"><i class="fa fa-folder-o"></i> <a href="#">{{$i->grupo}}</a></span>
                            </footer>
                        </div>
                    </article>
                </div>
            </div>
            @endforeach
            @endif
        </div>
        @else
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown">Sección</h2>
            <p class="text-center wow fadeInDown">Sin configuración</p>
        </div>
        @endif
    </div>

    <script type="text/javascript">
        var array = <?php echo json_encode($items); ?>;

        function buscar() {
            $("#txt").html("");
            var texto = $("#buscar").val();
            var nuevoArray = [];
            array.forEach(function(i) {
                if (i.descripcion.indexOf(texto) != -1) {
                    nuevoArray.push(i);
                }
            });
            arrayDraw(nuevoArray);
        }

        function arrayDraw(array) {
            var html = "";
            array.forEach(function(i) {
                html = html + "<p>" + i.descripcion + "</p>";
            });
            $("#txt").html(html);
        }
    </script>

</section>