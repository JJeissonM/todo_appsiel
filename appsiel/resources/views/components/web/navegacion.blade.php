<?php


if (is_null($nav)) {
    $fondos['background_0'] = "#574696";
    $fondos['background_1'] = "#42A3DC"; 
    
    $logo['imagen_logo'] = 'generic-logo-pngpng-5f09af84834b6.png';
    $logo['altura_logo'] = 50;
    $logo['anchura_logo'] = 80;

    $clase_header = 'no-fixed';

    $textcolor = '#fff';

    $fontfamily = 'sans-serif';

    $alpha = '10';
}else{
    if($nav->background != ''){
        $fondos = json_decode($nav->background, true);
    }else{
        $fondos['background_0'] = "#574696";
        $fondos['background_1'] = "#42A3DC"; 
    }
    if($nav->logo != ''){
        $logo = json_decode($nav->logo, true);
    }else{
        $logo['imagen_logo'] = '';
        $logo['altura_logo'] = 50;
        $logo['anchura_logo'] = 80;
    }
    
    $textcolor = $nav->color;
    $alpha = $nav->alpha;

    if ($nav->fixed) {
        $clase_header = 'fixed-top';
    } else {
        $clase_header = 'no-fixed';
    }

    if(!is_null($nav->configuracionfuente)){
        $fontfamily = $nav->configuracionfuente->fuente->font;
    }else {
        $fontfamily = 'sans-serif';
    }


}



?>

<style>




    #myHeader {
        /*position: fixed;
        z-index: 99999;
        top: 50px;*/
        width: 100%;
        height: 66px;
    }

    #myHeader .navegacion-font {
        font-family: <?php echo $fontfamily; ?> !important;
    }

    #myHeader {
        color: <?php echo $textcolor;   ?>;
        background: <?php echo $fondos['background_0'];   ?>;
        z-index: 1000;
        box-shadow: 0px 5px 5px 0px rgb(0 0 0 / 25%);
        position: relative;
    }

    .sticky {
        @if ($clase_header == 'fixed-top')
        position: fixed;
        /*background: {{$fondos['background_0']}} !important; opacity: {{$nav->alpha/10}};*/
        animation-name: fixedo;
        animation-duration: 1s;
        @endif
        z-index: 1000;
        top: 0;
        width: 100%;
        background: <?php echo $fondos['background_0'];
        ?>;
        -webkit-box-shadow: 0px 5px 5px 0px rgba(0, 0, 0, 0.75);
        -moz-box-shadow: 0px 5px 5px 0px rgba(0, 0, 0, 0.75);
        box-shadow: 0px 5px 5px 0px rgba(0, 0, 0, 0.75);        
        animation-fill-mode: both; 
    }

    @keyframes fixedo {
        to{
            background: {{$fondos['background_0']}} !important; opacity: {{ $alpha/10 }};                       
        }
    }

    @keyframes img-fixed{
        to{
            height: 50px;     
        }
           
    }


    #myHeader .icono img {
        height: <?php echo $logo['altura_logo'];
        ?>px;
        /*width: <?php echo $logo['anchura_logo'];
        ?>px;*/
        filter: drop-shadow(0 2px 2px rgba(0, 0, 0, 0.7));
    }


    .sticky .icono img {
        min-height: 50px;
        height: <?php echo $logo['altura_logo'];
        ?>;
        max-height: 80px;
        width: auto;
        animation-name: img-fixed;
        animation-duration: 1s;
        animation-timing-function: ease;
        animation-fill-mode: both;
    }


    #myHeader li .active a {
        color: <?php echo $textcolor;  ?> !important;
        background-color: <?php echo $fondos['background_1'];  ?> !important;
    }

    #myHeader .navbar-nav>li>a:hover {
        color: <?php echo $textcolor;  ?> !important;
        background-color: <?php echo $fondos['background_1'];  ?> !important;
        transition: all 0.5s;
    }

    #myHeader .mu-navbar-nav li.active a, .mu-navbar-nav li a:hover, .mu-navbar-nav li a:focus {
        background-color: <?php echo $fondos['background_1'];?> !important;
        color: <?php echo $textcolor;   ?> !important;
    }
    #myHeader .mu-navbar-nav li a {
        background-color:<?php echo $fondos['background_0'];?> !important;
        color: <?php echo $textcolor;   ?> !important;
    }

    .owl-prev:hover,
    .owl-next:hover {
        background-color: <?php echo $fondos['background_0'];
        ?> !important;
    }

    #myHeader .btn. btn-primary {
        background-color: <?php echo $fondos['background_0'];
        ?> !important;
        border-color: <?php echo $fondos['background_1'];
        ?> !important;
    }

    #myHeader .dropdown-menu {
        background-color: <?php echo $fondos['background_1'];
        ?> !important;
        border-radius: 0 !important;
        border: 1px solid !important;
        border-color: <?php echo $fondos['background_1'];
        ?> !important;
    }

    #myHeader .dropdown-item:hover {
        background-color: <?php echo $fondos['background_0'];
        ?> !important;
    }
/*
    #myHeader .sticky-top {
        position: sticky;
        top: 0;
        z-index: 1020;
    }*/
</style>



<header id="myHeader">
    <div class="">
        <nav id="nav" class="navbar navbar-expand-lg navbar-light justify-content-between align-items-center" style="height: 66px">
            <!-- mu-navbar  d-flex -->

            <!-- Text based logo -->
            @if( !is_null($nav) )
            <a style="height: 50px" class="navbar-brand p-0 icono" href="{{url('/')}}" style="position: relative">
                <img src="{{asset( $logo['imagen_logo'] )}}" style="position: absolute; z-index: 11000">
            </a>
            @else
            <a style="height: 50px" class="navbar-brand p-0 icono" href="{{url('/')}}" style="position: relative">
                <h1>logo</h1>
            </a>
            @endif

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="fa fa-bars" style="color: {{$textcolor}}"></span>
            </button>
            <div class="collapse navbar-collapse ml-auto navegacion-font" id="navbarSupportedContent">

                <ul class="navbar-nav mu-navbar-nav">
                    @if(!is_null($nav))
                    @foreach($nav->menus as $item)
                    @if($item->parent_id == 0)
                    @if($item->subMenus()->count()>0)
                    <li class="nav-item dropdown {{request()->url() == $item->enlace ? 'active':''}}">
                        <a class="dropdown-toggle"
                            style="color: {{$textcolor}}; text-transform: none !important; font-weight: 100;"
                            href="{{$item->enlace}}" role="button" id="navbarDropdown" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false"><i class="fa fa-{{$item->icono}}"
                                style="font-size: 20px;"></i>{{' '.$item->titulo}}</a>
                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                            @foreach($item->subMenus() as $subItems)
                            <a style="color: {{$textcolor}}; text-transform: none !important; font-weight: 100;"
                                class="dropdown-item" href="{{$subItems->enlace}}"><i class="fa fa-{{$subItems->icono}}"
                                    style="font-size: 20px;"></i>{{' '.$subItems->titulo}}</a>
                            @endforeach
                        </div>
                    </li>
                    @else
                    <li class="nav-item {{request()->url() == $item->enlace ? 'active':''}}"><a href="{{$item->enlace}}"
                            style="text-transform: none !important; font-weight: 100;"><i
                                class="fa fa-{{$item->icono}}" style="font-size: 20px;"></i>{{' '.$item->titulo}}</a>
                    </li>
                    @endif
                    @endif
                    @endforeach
                    @endif
                </ul>
            </div>
        </nav>
    </div>
</header>
