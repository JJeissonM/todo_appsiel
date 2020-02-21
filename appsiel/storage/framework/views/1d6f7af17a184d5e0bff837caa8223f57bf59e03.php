<nav aria-label="breadcrumb">
  <ol class="breadcrumb">
  	<?php foreach($vec as $fila): ?>
  		<?php if($fila['url']!='NO'): ?>
  			<li class="breadcrumb-item">
  				<a href="<?php echo e(url($fila['url'])); ?>"><?php echo e($fila['etiqueta']); ?></a>
  			</li>
  		<?php else: ?>
  			<li class="breadcrumb-item active" aria-current="page"><?php echo e($fila['etiqueta']); ?></li>
  		<?php endif; ?>
  	<?php endforeach; ?>
  </ol>
</nav>