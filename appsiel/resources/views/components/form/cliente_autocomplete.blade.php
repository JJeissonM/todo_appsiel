<?php
    if (strncasecmp($lbl, "<i class='fa fa-asterisk'></i>", 30) == 0) {
        $placeholder = substr($lbl, 30);
    } else {
        $placeholder = $lbl;
    }

    $inputValue = '';
    $hiddenValue = '';
    if (is_array($value)) {
        $inputValue = isset($value[0]) ? $value[0] : '';
        $hiddenValue = isset($value[1]) ? $value[1] : '';
    } else {
        $hiddenValue = $value;
    }

    $inputAttributes = $attributes;
    $inputAttributes['class'] = trim('form-control hotel-cliente-autocomplete-input ' . (isset($attributes['class']) ? $attributes['class'] : ''));
    $inputAttributes['id'] = $name . '_input';
    $inputAttributes['data-target'] = $name;
    $inputAttributes['placeholder'] = $placeholder;
    $inputAttributes['autocomplete'] = 'off';
?>

<div class="form-group hotel-cliente-autocomplete-wrap">
    <label class="control-label col-sm-3" for="{{ $name }}_input"><?php echo $lbl ?>:</label>
    <div class="col-sm-9" style="position:relative;">
        {{ Form::text($name . '_input', $inputValue, $inputAttributes) }}
        {{ Form::hidden($name, $hiddenValue, array(
            'id' => $name,
            'class' => 'hotel-cliente-autocomplete-id'
        )) }}
        <div class="hotel-cliente-autocomplete-results" style="display:none; position:absolute; z-index:1050; left:15px; right:15px;"></div>
    </div>
</div>
