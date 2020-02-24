<div class="aboutus">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;"><?php echo e($aboutus->titulo); ?></h2>
            <p class="text-center wow fadeInDown animated" style="visibility: visible; animation-name: fadeInDown;"><?php echo e($aboutus->descripcion); ?><br> et dolore magna aliqua. Ut enim ad minim veniam</p>
        </div>
        <div class="row">
            <div class="col-sm-6 wow fadeInLeft animated" style="visibility: visible; animation-name: fadeInLeft;">
                <img class="img-responsive" src="<?php echo e(url($aboutus->imagen)); ?>" alt="">
            </div>
            <div class="col-sm-6">
                <div class="media service-box wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight;">
                    <div class="pull-left">
                        <i class="fa fa-line-chart"></i>
                    </div>
                    <div class="media-body">
                        <h4 class="media-heading">Misión</h4>
                        <p><?php echo e($aboutus->mision); ?></p>
                    </div>
                </div>

                <div class="media service-box wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight;">
                    <div class="pull-left">
                        <i class="fa fa-cubes"></i>
                    </div>
                    <div class="media-body">
                        <h4 class="media-heading">Visión</h4>
                        <p><?php echo e($aboutus->vision); ?></p>
                    </div>
                </div>

                <div class="media service-box wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight;">
                    <div class="pull-left">
                        <i class="fa fa-pie-chart"></i>
                    </div>
                    <div class="media-body">
                        <h4 class="media-heading">Valores</h4>
                        <p><?php echo e($aboutus->valores); ?></p>
                    </div>
                </div>

                <div class="media service-box wow fadeInRight animated" style="visibility: visible; animation-name: fadeInRight;">
                    <div class="pull-left">
                        <i class="fa fa-pie-chart"></i>
                    </div>
                    <div class="media-body">
                        <h4 class="media-heading">SEO Services</h4>
                        <p>Backed by some of the biggest names in the industry, Firefox OS is an open platform that fosters greater</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>