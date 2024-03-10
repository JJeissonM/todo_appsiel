
<h4><i class="fa fa-money"></i> Movimientos del sistema </h4>
<hr>
<div class="row">
    <div class="col-md-6">

        <div class="well">
            <h5 style="text-align: center;"> Mov. de entradas de efectivo <small> <a
                            class="btn btn-xs btn-primary" id="btn_get_mov_entrada"> Obtener </a> </small>
                <small> <a class="btn btn-xs btn-info" id="btn_reset_mov_entrada"> Reset </a> </small></h5>
            <div id="div_mov_entrada">

            </div>
            <input type="hidden" id="movimientos_entradas" value="0" name="movimientos_entradas">
            <input type="hidden" id="total_mov_entradas" value="0" name="total_mov_entradas">
        </div>

    </div>

    <div class="col-md-6">

        <div class="well">
            <h5 style="text-align: center;"> Mov. de salidas de efectivo <small> <a
                            class="btn btn-xs btn-primary" id="btn_get_mov_salida"> Obtener </a> </small>
                <small> <a class="btn btn-xs btn-info" id="btn_reset_mov_salida"> Reset </a> </small></h5>
            <div id="div_mov_salida">

            </div>
            <input type="hidden" id="movimientos_salidas" value="0" name="movimientos_salidas">
            <input type="hidden" id="total_mov_salidas" value="0" name="total_mov_salidas">
        </div>

    </div>
</div>

<div class="well">
    <?php  
        $title = 'Inicial + Entradas - Salidas';
        $message = '(Sumando El Saldo inicial)';
        if( config('ventas_pos.sumar_efectivo_base_en_saldo_esperado') == 0)
        {
            $title = 'Entradas - Salidas';
            $message = '(Sin sumar El Saldo inicial)';
        }
    ?>
    <h4> <a href="#" data-toggle="tooltip" data-placement="right" title="{{$title}}" style="text-decoration: none;"> <i class="fa fa-question-circle"></i> </a> Saldo esperado:
        <div id="lbl_total_sistema" style="display: inline;"> $0</div>
        <br>
        <span style="font-size: 0.6em; color: #979696;">{{ $message }}</span>
    </h4>
    <input type="hidden" id="total_sistema" name="lbl_total_sistema" value="0">
</div>