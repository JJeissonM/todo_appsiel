<div class="form-group" style="padding-left: 10px;">
	<label class="control-label" for="{{$name}}" style="padding-left: 5px;"> <?php echo $lbl?>:</label>
	<div class="col-sm-12">
		<?php 
			if(strncasecmp($lbl,"<i class='fa fa-asterisk'></i>",30) == 0) {
				$lbl = substr($lbl,30);
			}
		?>
		{{ Form::textarea($name, $value, array_merge(['class' => 'form-control','id'=>$name, 'rows' => '4','placeholder'=>$lbl], $attributes)) }}
	</div>
</div>