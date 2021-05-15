<div class="form-group">
	<label class="control-label col-sm-3" for="{{$name}}"><?php echo $lbl?>:</label>
	<div class="col-sm-9">
		<?php 
			if(strncasecmp($lbl,"<i class='fa fa-asterisk'></i>",30) == 0) {
				 $lbl = substr($lbl,30);
			}
		?>
		{{ Form::text($name.'_aux', $value[0], array_merge( ['id'=>$name,'placeholder'=>$lbl,'autocomplete'=>'off'], $attributes ) ) }}
		<input type="hidden" name="{{$name}}" value="{{$value[1]}}"> 
	</div>
</div>