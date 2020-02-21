<?php $__env->startSection('style'); ?>
    <style>
        .card-body {
            padding: 0 !important;
            overflow-y: hidden;
        }

        #wrapper {
            overflow-y: scroll;
            width: 30%;
            height: 72.3vh;
            margin-right: 0;
        }

        .list-group-item {
            background-color: transparent;
            font-size: 16px;
        }

        .list-group-item:hover {
            background-color: #3d6983;
            color: white;
            cursor: pointer;
        }

        .widgets {
            width: 70%;
        }

        .widgets img {
            width: 100%;
            object-fit: cover;
            height: 72.5vh;
            max-width: 100%;
        }

        .widgets .card-body {
            position: relative;
        }

        .activo {

        }

        .contenido {
            display: flex;
            padding: 5px;
            border: 1px solid #3d6983;
            border-radius: 5px;
        }

        .contenido img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }

        .descripcion {
            padding: 5px;
        }

        .descripcion h5 {
            color: black;
            font-size: 16px;
        }

        .add {
            margin-top: 20px;
        }

        .add a {
            color: #1c85c4;
        }

    </style>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <?php if($aboutus != null): ?>
                    <div class="contenido">
                        <img src="<?php echo e(url($aboutus->imagen)); ?>" alt="" class="imagen">
                        <div class="descripcion">
                            <h5 class="titulo"><?php echo e($aboutus->titulo); ?></h5>
                            <p><?php echo e($aboutus->descripcion); ?></p>
                        </div>
                    </div>
                    <div class="add d-flex justify-content-end">
                        <a href="<?php echo e(url('aboutus/create').'/'.$widget.$variables_url); ?>"> Editar</a>
                    </div>
                <?php else: ?>
                    <div class="add d-flex justify-content-end">
                        <a href="<?php echo e(url('aboutus/create').'/'.$widget.$variables_url); ?>"> Agregar</a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="widgets" id="widgets">
                <?php if($aboutus != null): ?>
                    <?php echo Form::aboutus($aboutus); ?>

                <?php endif; ?>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('script'); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('web.templates.main', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>