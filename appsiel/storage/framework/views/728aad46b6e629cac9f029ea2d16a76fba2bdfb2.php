<?php $__env->startSection('style'); ?>
    <style>
        .card-body {
            padding: 0 !important;
            overflow-y: hidden;
        }

        #wrapper {
            overflow-y: scroll;
            width: 30%;
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


    </style>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="card">
        <div class="card-body d-flex justify-content-between flex-wrap">
            <div id="wrapper">
                <?php if($aboutus != null): ?>
                    <?php echo Form::model($aboutus,['route'=>['aboutus.updated',$aboutus],'method'=>'PUT','class'=>'form-horizontal','files'=>'true']); ?>

                    <input type="hidden" name="widget_id" value="<?php echo e($widget); ?>">
                    <div class="form-group">
                        <label>Titulo</label>
                        <input name="titulo" type="text" placeholder="Titulo" value="<?php echo e($aboutus->titulo); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <input name="descripcion" type="text" placeholder="Descripción" value="<?php echo e($aboutus->descripcion); ?>" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Misión</label>
                        <textarea name="mision" class="form-control"><?php echo e($aboutus->mision); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Visión</label>
                        <textarea name="vision" class="form-control"><?php echo e($aboutus->vision); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Valores</label>
                        <textarea name="valores" class="form-control"><?php echo e($aboutus->valores); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label>Imagen</label>
                        <input name="imagen" type="file" placeholder="Agregar una imagen" class="form-control">
                    </div>
                    <div class="form-group">
                        <br/><br/><a href="<?php echo e(url('seccion/'.$widget).$variables_url); ?>"
                                     class="btn btn-danger">Cancelar</a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        <?php echo Form::submit('Guardar',['class'=>'btn btn-success waves-effect']); ?>

                    </div>
                    <?php echo Form::close(); ?>

                <?php else: ?>
                    <?php echo Form::open(['route'=>'aboutus.store','method'=>'POST','class'=>'form-horizontal','files'=>'true']); ?>

                    <input type="hidden" name="widget_id" value="<?php echo e($widget); ?>">
                    <div class="form-group">
                        <label>Titulo</label>
                        <input name="titulo" type="text" placeholder="Titulo" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <input name="descripcion" type="text" placeholder="Descripción" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Misión</label>
                        <textarea name="mision" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Visión</label>
                        <textarea name="vision" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Valores</label>
                        <textarea name="valores" class="form-control"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Imagen</label>
                        <input name="imagen" type="file" placeholder="Agregar una imagen" required="required"
                               class="form-control">
                    </div>
                    <div class="form-group">
                        <br/><br/><a href="<?php echo e(url('seccion/'.$widget).$variables_url); ?>"
                                     class="btn btn-danger">Cancelar</a>
                        <button class="btn  btn-info" type="reset">Limpiar Formulario</button>
                        <?php echo Form::submit('Guardar',['class'=>'btn btn-success waves-effect']); ?>

                    </div>
                    <?php echo Form::close(); ?>

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