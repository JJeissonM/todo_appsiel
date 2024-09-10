<?php 
    $campo = \App\Sistema\Campo::where('name','formato_impresion_facturas_ventas')->get()->first();

    $arr_select = ['pos'=>'POS','estandar'=>'Estándar','estandar2'=>'Estándar v2','colegio'=>'Colegio','estandar_con_copia'=>'Estándar con copia'];
    $default_key = 'pos';
    if ( $campo != null ) {
        $arr_select = json_decode($campo->opciones,true);
        //$default_key = array_key_first($arr_select);
        $default_key = array_keys($arr_select)[0];
    }
?>
<div>
    Formato: {{ Form::select('formato_impresion_id', $arr_select, $default, [ 'id' =>'formato_impresion_id' ]) }}
	{{ Form::bsBtnPrint( 'vtas_imprimir/'.$id.$variables_url.'&formato_impresion_id=' . $default ) }}
	{{ Form::bsBtnEmail( 'vtas_enviar_por_email/'.$id.$variables_url.'&formato_impresion_id=' . $default ) }}
</div>