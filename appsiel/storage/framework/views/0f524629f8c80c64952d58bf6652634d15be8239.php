<?php if($item['submenu'] == []): ?>
    <?php if (app('Illuminate\Contracts\Auth\Access\Gate')->check($item['name'])): ?>
        <li class="nav-item">
            <a href="<?php echo e(url($item['url'].'?id='.$item['core_app_id'].'&id_modelo='.$item['modelo_id'])); ?>"><?php echo e($item['descripcion']); ?> </a>
        </li>
    <?php endif; ?>
<?php else: ?>
    <li class="nav-item dropdown">
        <?php if (app('Illuminate\Contracts\Auth\Access\Gate')->check($item['name'])): ?>
            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?php echo e($item['descripcion']); ?> <span class="caret"></span></a>
        <?php endif; ?>
        <ul class="dropdown-menu sub-menu">
            <?php foreach($item['submenu'] as $submenu): ?>
                <?php if($submenu['submenu'] == []): ?>
                    <?php if (app('Illuminate\Contracts\Auth\Access\Gate')->check($item['name'])): ?>
                        <li class="nav-item dropdow">
                            <a class="" href="<?php echo e(url($submenu['url'].'?id='.$submenu['core_app_id'].'&id_modelo='.$submenu['modelo_id'])); ?>" role="button" id="navbarDropdown" aria-haspopup="true" aria-expanded="false"><?php echo e($submenu['descripcion']); ?></a>
                        </li>
                    <?php endif; ?>
                <?php else: ?>
                    <?php echo $__env->make('layouts.menu-item', [ 'item' => $submenu ], array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </li>
<?php endif; ?>
