<?php 
    $referencia = '';
    if($item->referencia != '')
    {
        $referencia = ' - ' . $item->referencia;
    }
?>
<button onclick="mandar_codigo2({{ $item->id }},1)" class="btn btn-block btn-default btn-xs" title="{{ $item->descripcion . $referencia }}" style="height: 100%;">
    <br>
    @if($item->imagen!='')
        <img style="width: 100px; height: 100px; border-radius:4px;" src="{{url('/')}}/appsiel/storage/app/inventarios/{{$item->imagen}}">
    @else
        <img style="width: 100px; height: 100px;" src="{{url('/')}}/assets/img/box.png">
    @endif
    <p style="text-align: center; white-space: nowrap; overflow: hidden; white-space: initial;">{{ $item->descripcion . $referencia }}</p>
</button>