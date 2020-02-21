<div class="aboutus">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="mu-about-area">
                    <!-- Title -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mu-title">
                                <h2>{{$aboutus->titulo}}</h2>
                                <p>{{$aboutus->descripcion}}</p>
                            </div>
                        </div>
                    </div>
                    <!-- Start Feature Content -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mu-about-left">
                                <img class="" src="{{$aboutus->imagen}}" alt="img" width="568" height="460">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mu-about-right">
                                <ul>
                                    <li>
                                        <h3>Misión</h3>
                                        <p>{{$aboutus->mision}}</p>
                                    </li>
                                    <li>
                                        <h3>Visión</h3>
                                        <p>{{$aboutus->vision}}</p>
                                    </li>
                                    <li>
                                        <h3>Valores</h3>
                                        <p>{{$aboutus->valores}}</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- End Feature Content -->
                </div>
            </div>
        </div>
    </div>
</div>