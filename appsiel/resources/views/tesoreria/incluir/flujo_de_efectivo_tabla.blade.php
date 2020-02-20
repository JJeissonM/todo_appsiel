PENDIENTE POR TERMINAR

<h3> Flujo de efectivo </h3>

<table class="table table-striped tabla_registros" style="margin-top: -4px;">
    <thead>
        <tr>
            <th>
               &nbsp;
            </th>
            <th>
               Motivo
            </th>
            <th>
               Valor Movimiento
            </th>
            <th>
               Saldo
            </th>
        </tr>
    </thead>
                        <tbody>';

        $tabla2 .= '<tr  class="fila-'.$this->j.'" >
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                               '.number_format( $saldo_inicial , 0, ',', '.').'
                            </td>
                        </tr>';

        $this->saldo = $saldo_inicial;

        $tabla2.=$this->seccion_tabla_movimiento('ENTRADAS', $movimiento_entradas, $saldo_inicial);

        $gran_total = $this->total_valor_movimiento;

        $tabla2.=$this->seccion_tabla_movimiento('SALIDAS', $movimiento_salidas, $this->saldo);

        $gran_total += $this->total_valor_movimiento;

        $tabla2.='<tr  class="fila-'.$this->j.'" >
                            <td colspan="2">
                               <b>FLUJO NETO</b>
                            </td>
                            <td>
                               '.number_format($gran_total, 0, ',', '.').'
                            </td>
                            <td>
                               '.number_format($this->saldo, 0, ',', '.').'
                            </td>
                        </tr>';
        $tabla2.='</tbody></table>