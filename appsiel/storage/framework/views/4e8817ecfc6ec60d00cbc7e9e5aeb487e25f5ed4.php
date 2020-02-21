<?php if(Session::has('flash_message')): ?>
    <div class="container-fluid">
        <div class="alert alert-success">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <em> <?php echo session('flash_message'); ?></em>
        </div>
    </div>
<?php endif; ?>

<?php if(Session::has('mensaje_error')): ?>
    <div class="container-fluid">
        <div class="alert alert-danger">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <em> <?php echo session('mensaje_error'); ?></em>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8 col-md-offset-2">
        <?php echo $__env->make('errors.list', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?> <?php /*Including error file */ ?>
    </div>
</div>
