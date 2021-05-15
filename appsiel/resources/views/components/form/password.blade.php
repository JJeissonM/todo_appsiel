<div class="form-group">
	<label class="control-label col-sm-3" for="{{$name}}"><?php echo $lbl?>:</label>
	<div class="col-sm-9">
		{{ Form::password($name, array_merge(['class' => 'form-control'], $attributes)) }}
	</div>
</div>