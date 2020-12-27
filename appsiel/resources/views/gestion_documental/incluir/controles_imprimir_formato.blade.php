@if($nota_mensaje!='')
	<div class="alert alert-warning">
	  <strong>Nota!</strong> {{ $nota_mensaje}}
	</div>	
@endif

@if($maneja_anio!='')
	<div class="row" style="padding:5px;">
        {{ $maneja_anio }}
    </div>						
@endif

@if($maneja_periodo!='')
	<div class="row" style="padding:5px;">
        {!! $maneja_periodo !!}
    </div>
@endif

@if($maneja_curso!='')
	<div class="row" style="padding:5px;">
        {{ $maneja_curso }}
    </div>					
@endif

@if($maneja_estudiantes!='')
	<div class="row" style="padding:5px;">
        {{ $maneja_estudiantes }}
    </div>
@endif

@if($maneja_firma_autorizada!='')
	<div class="row" style="padding:5px;">
        {{ $maneja_firma_autorizada }}
    </div>
@endif

<div class="row" style="padding:5px;">
    {{ Form::bsSelect('tam_hoja',null,'Tamaño hoja',['letter'=>'Carta','legal'=>'Oficio'],[]) }}
</div>

<div class="row" style="padding:5px;">
    {{ Form::bsSelect('tam_letra','4.5','Tamaño Letra',['2'=>'10','2.5'=>'11','3'=>'12','3.5'=>'13','4'=>'14','4.5'=>'15','5'=>'16'],[]) }}
</div>

<div class="row" style="padding:5px;">
    {{ Form::bsSelect('orientacion','portrait','Orientación',['portrait'=>'Vertical','landscape'=>'Horizontal'],[]) }}
</div>

<div class="row" style="padding:5px;" align="center">
    {{ Form::bsButtonsForm('gestion_documental') }}
</div>