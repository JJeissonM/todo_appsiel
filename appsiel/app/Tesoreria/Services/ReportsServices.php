<?php 

namespace App\Tesoreria\Services;

use App\Tesoreria\TesoMovimiento;

class ReportsServices
{
    public function build_array( $data )
    {
        $teso_movements1 = TesoMovimiento::whereBetween('fecha', [ $data['fecha_desde'], $data['fecha_hasta'] ] )
                                ->orderBy('fecha')
                                ->get();

                                
        $teso_movements2 = TesoMovimiento::whereBetween('fecha', [ $data['fecha_desde'], $data['fecha_hasta'] ] )
                                ->orderBy('valor_movimiento', 'DESC')
                                ->get();

        $dates = $teso_movements1->groupBy('fecha')->all();

        $purposes = $teso_movements2->groupBy('teso_motivo_id')->all();

        $matriz = $this->get_array_empty( count($purposes) + 2, count($dates) + 1);
        $matriz = $this->fill_first_column( $matriz, $purposes );
        $matriz = $this->fill_first_row( $matriz, $dates );

        $column_d = 1; // Columns are dates
        foreach ($dates as $date => $date_collect)
        {
            $row_p = 1; // Rows are purposes
            $total_columns = 0;
            $drawn = false;
            foreach ($purposes as $purpose_id => $purpose_collect)
            {
                $purpose = $purpose_collect->first()->motivo;
                
                if ( $purpose->movimiento == 'salida' && !$drawn ) {
                    $matriz[$row_p][$column_d] = $total_columns;
                    $row_p++;                    
                    $total_columns = 0;
                    $drawn = true;
                }

                $amount = $teso_movements1->where('fecha',$date)->where('teso_motivo_id', $purpose_id)->sum('valor_movimiento');

                $matriz[$row_p][$column_d] = $amount;

                $total_columns += $amount;
                
                $row_p++;
            }

            $column_d++;               
        }

        return $matriz;
    }

    /**
     * 
     */
    public function get_array_empty($rows, $columns)
    {
        $matriz = array();
        for ($i=0; $i < $rows; $i++) { 
            
            for ($j=0; $j < $columns; $j++) { 
                $matriz[$i][$j] = null;
            }
        }

        return $matriz;        
    }

    /**
     * 
     */
    public function fill_first_column( $matriz, $purposes )
    {
        $row_p = 1;
        $drawn = false;
        foreach ($purposes as $key => $purpose_collect) {

            $purpose = $purpose_collect->first()->motivo;

            if ( $purpose == null ) {
                dd( 'Motivo no esta creado en la Base de datos.', $purpose_collect->first() );
            }

            if ( $purpose->movimiento == 'salida' && !$drawn ) {
                $matriz[$row_p][0] = 'TOTAL ENTRADAS';
                $row_p++;
                $drawn = true;
            }

            $matriz[$row_p][0] = $purpose->descripcion;
            $row_p++;
        }

        return $matriz;
    }

    /**
     * 
     */
    public function fill_first_row( $matriz, $dates )
    {
        $column_p = 1;
        foreach ($dates as $date => $date_collect) {
            $matriz[0][$column_p] = $date;
            $column_p++;
        }

        return $matriz;
    }
}