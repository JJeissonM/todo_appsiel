<div class="form-group">
	<label class="control-label col-sm-3 col-md-3" for="{{$name}}"><?php echo $lbl?> :</label>
	<div class="col-sm-9 col-md-9">
		<?php 
			if(strncasecmp($lbl,"<i class='fa fa-asterisk'></i>",30) == 0) {
				 $lbl = substr($lbl,30);
			}
		?>
		{{ Form::text( $name, $value, array_merge( ['class' => 'form-control','id'=>$name,'placeholder'=>$lbl,'autocomplete'=>'off'], $attributes ) ) }}
	</div>
</div>