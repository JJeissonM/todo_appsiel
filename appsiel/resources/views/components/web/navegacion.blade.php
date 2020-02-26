<header id="mu-hero" style="background-color: #0A7195;">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light mu-navbar">
            <!-- Text based logo -->
            <a class="navbar-brand mu-logo" href=""><span>{{$nav->logo}}</span></a>
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
                            @endif
                        @endif
                    @endforeach
                </ul>
            </div>
        </nav>
    </div>
</header>