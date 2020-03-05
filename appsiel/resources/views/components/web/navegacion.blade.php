<<<<<<< HEAD
<header id="mu-hero" style="background-color: #275093;">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light mu-navbar">
            <!-- Text based logo 
            <a class="navbar-brand mu-logo" href=""><span> { {$nav->logo}}</span></a>-->
            <!-- image based logo -->
            <!-- <a class="navbar-brand mu-logo" href="index.html"><img src="assets/images/logo.png" alt="logo"></a> -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto mu-navbar-nav">
                    @foreach($nav->menus as $item)
                        @if($item->parent_id == 0)
                            @if($item->subMenus()->count()>0)
                                <li class="nav-item dropdown">
                                    <a class="dropdown-toggle" href="{{$item->enlace}}" role="button" id="navbarDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{$item->titulo}}</a>
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        @foreach($item->subMenus() as $subItems)
                                            <a class="dropdown-item" href="{{$subItems->enlace}}">{{$subItems->titulo}}</a>
                                        @endforeach
                                    </div>
                                </li>
                            @else
                                <li class="nav-item"><a href="{{$item->enlace}}">{{$item->titulo}}</a></li>
=======
@if($nav->fixed)
    <header id="mu-hero" class="fixed-top" style="background-color: {{$nav->background}};">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light mu-navbar d-flex ">
                <!-- Text based logo -->
                <a class="navbar-brand mu-logo" href="" style="color:{{$nav->color}}"><span>{{$nav->logo}}</span></a>
                <!-- image based logo -->
                <!-- <a class="navbar-brand mu-logo" href="index.html"><img src="assets/images/logo.png" alt="logo"></a> -->
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                    <span class="fa fa-bars"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto mu-navbar-nav">
                        @foreach($nav->menus as $item)
                            @if($item->parent_id == 0)
                                @if($item->subMenus()->count()>0)
                                    <li class="nav-item dropdown">
                                        <a class="dropdown-toggle" style="color: {{$nav->color}}"
                                           href="{{$item->enlace}}" role="button" id="navbarDropdown"
                                           data-toggle="dropdown" aria-haspopup="true"
                                           aria-expanded="false"><i class="fa fa-{{$item->icono}}" style="font-size: 20px;"></i>{{' '.$item->titulo}}</a>
                                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                            @foreach($item->subMenus() as $subItems)
                                                <a style="color: {{$nav->color}}" class="dropdown-item"
                                                   href="{{$subItems->enlace}}"><i class="fa fa-{{$subItems->icono}}" style="font-size: 20px;"></i>{{' '.$subItems->titulo}}</a>
                                            @endforeach
                                        </div>
                                    </li>
                                @else
                                    <li class="nav-item"><a href="{{$item->enlace}}"
                                                            style="color: {{$nav->color}}">{{$item->titulo}}</a></li>
                                @endif
>>>>>>> 1df8d0758b1018d4979a8d84982ffe3da5ee7045
                            @endif
                        @endforeach
                    </ul>
                </div>
            </nav>
        </div>
    </header>
@else
    <header id="mu-hero" style="background-color: {{$nav->background}};">
        <div class="container">
            <nav class="navbar navbar-expand-lg navbar-light mu-navbar d-flex ">
                <!-- Text based logo -->
                <a class="navbar-brand mu-logo" href="" style="color:{{$nav->color}}"><span>{{$nav->logo}}</span></a>
                <!-- image based logo -->
                <!-- <a class="navbar-brand mu-logo" href="index.html"><img src="assets/images/logo.png" alt="logo"></a> -->
                <button class="navbar-toggler" type="button" data-toggle="collapse"
                        data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                        aria-expanded="false" aria-label="Toggle navigation">
                    <span class="fa fa-bars"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav mr-auto mu-navbar-nav">
                        @foreach($nav->menus as $item)
                            @if($item->parent_id == 0)
                                @if($item->subMenus()->count()>0)
                                    <li class="nav-item dropdown">
                                        <a class="dropdown-toggle" style="color: {{$nav->color}}"
                                           href="{{$item->enlace}}" role="button" id="navbarDropdown"
                                           data-toggle="dropdown" aria-haspopup="true"
                                           aria-expanded="false"><i class="fa fa-{{$item->icono}}" style="font-size: 20px;"></i>{{' '.$item->titulo}}</a>
                                        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                            @foreach($item->subMenus() as $subItems)
                                                <a style="color: {{$nav->color}}" class="dropdown-item"
                                                   href="{{$subItems->enlace}}">{{$subItems->titulo}}</a>
                                            @endforeach
                                        </div>
                                    </li>
                                @else
                                    <li class="nav-item"><a href="{{$item->enlace}}"
                                                            style="color: {{$nav->color}}"><i class="fa fa-{{$item->icono}}" style="font-size: 20px;"></i>{{' '.$item->titulo}}</a></li>
                                @endif
                            @endif
                        @endforeach
                    </ul>
                </div>
            </nav>
        </div>
    </header>
@endif