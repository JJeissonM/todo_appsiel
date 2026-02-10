<?php 
    $referencia = '';
    if($item->referencia != '')
    {
        $referencia = ' - ' . $item->referencia;
    }
?>

<div id="btn_{{ $item->id }}">
    <button onclick="mandar_codigo2({{ $item->id }},1)" class="btn btn-block btn-default btn-xs" title="{{ $item->descripcion . $referencia }}" style="height: 100%;">
        <br>
        @php
            $inventario_img_path = storage_path('app/inventarios/' . $item->imagen);
            $inventario_img_url = url('/') . '/appsiel/storage/app/inventarios/' . $item->imagen;
            $fallback_img_url = url('/') . '/assets/img/box.png';
        @endphp
        @if($item->imagen != '' && file_exists($inventario_img_path))
            <img style="width: 100px; height: 100px; border-radius:4px;" src="{{ $inventario_img_url }}">
        @else
            <img style="width: 100px; height: 100px;" src="{{ $fallback_img_url }}">
        @endif
        <p style="text-align: center; white-space: nowrap; overflow: hidden; white-space: initial;"> {{ $item->descripcion . $referencia }} <b> <span class="lbl_precio_item" data-item_id="{{ $item->id }}"> ${{ number_format($item->precio_venta,0,',','.') }}  <span> </b></p>
    </button>
    <br>
</div>
