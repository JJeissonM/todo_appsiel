<section id="main-slider" >
    <?php if($slider != null && $slider->items->count() > 0): ?>
            <div class="owl-carousel">
                <?php foreach($slider->items as $item): ?>
                    <div class="item" style="background-image: url('<?php echo e(asset($item->imagen)); ?>');">
                        <div class="slider-inner">
                            <div class="container">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="carousel-content">
                                            <h2 style="text-shadow: 1px 1px 2px black;"><?php echo e($item->titulo); ?></h2>
                                            <p style="text-shadow: 1px 1px 2px black;"><?php echo e($item->descripcion); ?></p>
                                            <a class="btn btn-primary btn-lg" href="<?php echo e($item->enlace); ?>"><?php echo e($item->button); ?></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div><!--/.item-->
    <?php else: ?>
        <div class="owl-carousel">
            <div class="item" style="background-image: url('<?php echo e(asset('images/slider/bg1.jpg')); ?>');">
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
            <div class="item" style="background-image: url(<?php echo e(asset('images/slider/bg2.jpg')); ?>);">
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
    <?php endif; ?>
</section><!--/#main-slider-->
