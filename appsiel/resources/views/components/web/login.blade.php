<link rel="stylesheet" type="text/css" href="{{asset('css/login/style.min.css')}}"/>
<link rel="stylesheet" type="text/css" href="{{asset('css/login/facade.min.css')}}"/>
<link rel="stylesheet" type="text/css" href="{{asset('css/login/max-sh-shbp.min.css')}}"/>
<style>
    .estilo {
        margin-left: 800px;
        margin-top: -120px;
    }

    @media screen and (max-width: 782px) {

        .estilo {
            margin-top: 0px !important;
            margin-left: 0px !important;
            margin-bottom: 10px;
        }
    }
</style>
@if($login != null)
    <section id="login">
        <header class="fusion-header-wrapper">
            <div class="fusion-secondary-header" style="background-color: #2FAF72">
                <div class="fusion-row">
                    <div class="fusion-alignleft">
                        <div class="fusion-contact-info"><span class="fusion-contact-info-phone-number"><span style="color: #ffffff;">Llámanos hoy al:</span> <span style="color: #fff200;">  +57 333.333.3333</span></span></div>			</div>
                </div>
            </div>
            <div class="fusion-sticky-header-wrapper" style="height: 263px">
                <div class="fusion-header" style="margin-bottom: 10px">
                    <div class="fusion-row">
                        <div class="fusion-logo" data-margin-top="0px" data-margin-bottom="0px" data-margin-left="0px"
                             data-margin-right="0px" style="">
                            <a class="fusion-logo-link" style="margin-top: 50px">

                                <!-- standard logo -->
                                <img src="{{asset($login->imagen)}}" width="360" height="111"
                                     style="max-height:111px;height:auto;">
                            </a>
                            <div class="fusion-header-banner estilo">
                                <p class="mobileOnlyItem"></p>
                                <h4 style="color: #2FAF72; margin: 0; margin-bottom: 5px;">{{$login->titulo}}</h4>
                                <div class="wpcf7-form">
                                    <form action="https://appsiel.com.co/blog"
                                          method="GET" target="_blank"><input style="margin-bottom: 7px;"
                                                                               name="email"
                                                                               type="text" placeholder="Username">
                                        <input class="wpcf7-text" style="padding: 8px 15px; margin-bottom: 7px;"
                                               name="password" type="password" placeholder="Password">
                                        <input style="background-color: #2FAF72" name="submit" type="submit"
                                               value="Login"></form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="fusion-secondary-main-menu" style="background-color: #46AED6">
                <div class="fusion-row" style="text-align: center">
                    <nav class="fusion-main-menu" aria-label="Main Menu" >
                        <ul id="menu-mani-menu" class="fusion-menu" style="display: flex;justify-content: center">
                            <?php $count = 31; ?>
                            @foreach($nav->menus as $item)
                                <?php $count++;?>
                                @if($item->parent_id == 0)
                                    @if($item->subMenus()->count()>0)
                                        <li id="mobile-menu-item-{{$count}}"
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-15 current_page_item menu-item-{{$count}} nav-item dropdown {{request()->url() == $item->enlace ? 'active':''}}"
                                            data-item-id="{{$count}}" style="color: #46AED6 !important;">
                                            <a href="{{$item->enlace}}" role="button" id="navbarDropdown"
                                               aria-haspopup="true" aria-expanded="false"
                                               class="fusion-bar-highlight">
                                                <span class="menu-text">{{' '.$item->titulo}}</span>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                                @foreach($item->subMenus() as $subItems)
                                                    <a style="color: {{$nav->color}}" class="dropdown-item"
                                                       href="{{$subItems->enlace}}"><i
                                                                class="fa fa-{{$subItems->icono}}"
                                                                style="font-size: 20px;"></i>{{' '.$subItems->titulo}}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </li>
                                    @else
                                        <li id="mobile-menu-item-{{$count}}"
                                            class="menu-item menu-item-type-post_type menu-item-object-page menu-item-home current-menu-item page_item page-item-15 current_page_item menu-item-{{$count}} nav-item dropdown {{request()->url() == $item->enlace ? 'active':''}}"
                                            data-item-id="{{$count}}" style="">
                                            <a href="{{$item->enlace}}" role="button" id="navbarDropdown"
                                               aria-haspopup="true" aria-expanded="false"
                                                class="fusion-bar-highlight">
                                                <span class="menu-text">{{' '.$item->titulo}}</span>
                                            </a></li>
                                    @endif
                                @endif
                            @endforeach
                    </nav>
                    <nav class="fusion-mobile-nav-holder fusion-mobile-menu-text-align-left"
                         aria-label="Main Menu Mobile">
                        <button class="fusion-mobile-selector" aria-expanded="false"
                                aria-controls="mobile-menu-mani-menu"><span>Go to...</span>
                            <div class="fusion-selector-down">+</div>
                        </button>
                        <ul id="mobile-menu-mani-menu" class="fusion-menu">
                            @foreach($nav->menus as $item)
                                @if($item->parent_id == 0)
                                    @if($item->subMenus()->count()>0)
                                        <li id="mobile-menu-item-32"
                                            class="fusion-mobile-nav-item fusion-mobile-current-nav-item nav-item dropdown {{request()->url() == $item->enlace ? 'active':''}}"
                                            data-item-id="32" style="">
                                            <a href="{{$item->enlace}}" role="button" id="navbarDropdown"
                                               aria-haspopup="true" aria-expanded="false"
                                               class="fusion-bar-highlight">
                                                <span class="menu-text">{{' '.$item->titulo}}</span>
                                            </a>
                                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                                @foreach($item->subMenus() as $subItems)
                                                    <a style="color: {{$nav->color}}" class="dropdown-item"
                                                       href="{{$subItems->enlace}}"><i
                                                                class="fa fa-{{$subItems->icono}}"
                                                                style="font-size: 20px;"></i>{{' '.$subItems->titulo}}
                                                    </a>
                                                @endforeach
                                            </div>
                                        </li>
                                    @else
                                        <li id="mobile-menu-item-32"
                                            class="fusion-mobile-nav-item fusion-mobile-current-nav-item nav-item dropdown {{request()->url() == $item->enlace ? 'active':''}}"
                                            data-item-id="32" style="">
                                            <a href="{{$item->enlace}}" role="button" id="navbarDropdown"
                                               aria-haspopup="true" aria-expanded="false"
                                               class="fusion-bar-highlight">
                                                <span class="menu-text">{{' '.$item->titulo}}</span>
                                            </a></li>
                                    @endif
                                @endif
                            @endforeach
                        </ul>
                    </nav>
                </div>
            </div>
        </header>
    </section>
{{--    <script type="text/javascript" src="{{asset('css/login/imagen.min.js')}}"></script>--}}
{{--    <script type="text/javascript" src="{{asset('css/login/ded1aeb6f7defedf658095eb8eb251cd.min.js')}}"></script>--}}
{{--    <script type="text/javascript" src="{{asset('css/login/jquery.js')}}"></script>--}}
@endif