<header id="mu-hero">
    <div class="container">
        <nav class="navbar navbar-expand-lg navbar-light mu-navbar">
            <!-- Text based logo -->
            <a class="navbar-brand mu-logo" href=""><span><?php echo e($nav->logo); ?></span></a>
            <!-- image based logo -->
            <!-- <a class="navbar-brand mu-logo" href="index.html"><img src="assets/images/logo.png" alt="logo"></a> -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mr-auto mu-navbar-nav">

                    <?php foreach($nav->menus as $item): ?>
                        <?php if($item->parent_id == 0): ?>
                            <?php if($item->subMenus()->count()>0): ?>
                                <li class="nav-item dropdown">
                                    <a class="dropdown-toggle" href="<?php echo e($item->enlace); ?>" role="button" id="navbarDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><?php echo e($item->titulo); ?></a>
                                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                        <?php foreach($item->subMenus() as $subItems): ?>
                                            <a class="dropdown-item" href="<?php echo e($subItems->enlace); ?>"><?php echo e($subItems->titulo); ?></a>
                                        <?php endforeach; ?>
                                    </div>
                                </li>
                            <?php else: ?>
                                <li class="nav-item"><a href="<?php echo e($item->enlace); ?>"><?php echo e($item->titulo); ?></a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </div>
</header>