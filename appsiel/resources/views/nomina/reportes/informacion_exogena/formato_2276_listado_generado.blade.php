<div class="table-responsive">
    <table id="tbDatos" class="table table-striped table-bordered">
        {{ Form::bsTableHeader( ['Entidad Informante', 'Tipo de documento del beneficiario', 'Número de identificación del beneficiario', 'Primer Apellido del beneficiario', 'Segundo Apellido del beneficiario', 'Primer nombre del beneficiario', 'Otros nombres del beneficiario', 'Dirección', 'Departamento', 'Municipio', 'País', 'Pagos por salarios', 'Pagos por emolumentos eclesiásticos', 'Pagos por honorarios', 'Pagos por servicios', ' Pagos por comisiones', 'Pagos por prestaciones sociales', ' Pagos por viáticos', 'Pagos por gastos de representación', 'Pagos por compensaciones por el trabajo asociado cooperativo', 'Otros pagos', 'Cesantías e intereses de cesantías efectivamente pagadas, consignadas o reconocidas en el periodo', 'Pensiones de jubilación, vejez o invalidez', 'Total ingresos brutos',   'Aportes obligatorios por salud', 'Aportes obligatorios a fondos de pensiones y solidaridad pensional y Aportes voluntarios al – RAIS', 'Aportes voluntarios a fondos de pensiones voluntarias', 'Aportes a cuentas AFC', 'Aportes a cuentas AVC', 'Valor de las retenciones en la fuente por pagos de rentas de trabajo o pensiones', 'Pagos realizados con bonos electrónicos o de papel de servicio, cheques, tarjetas, vales, etc', 'Apoyos económicos no reembolsables o condonados, entregados por el Estado o financiados con recursos públicos, para financiar programas educativos', 'Pagos por alimentación mayores a 41 UVT', 'Pagos por alimentación hasta a 41 UVT', 'Identificación del fideicomiso o contrato', 'Tipo documento participante en contrato de colaboración', 'Identificación participante en contrato colaboración'] ) }}
        <tbody>
        	@foreach( $datos as $linea_empleado )
                <tr>
                    <td>{{ $linea_empleado->tipo_entidad_informanate }}</td>
                    <td>{{ $linea_empleado->tipo_documento }}</td>
                     <td class="text-center">{{ $linea_empleado->numero_identificacion }}</td>
                     <td>{{ $linea_empleado->apellido1 }}</td>
                     <td>{{ $linea_empleado->apellido2 }}</td>
                     <td>{{ $linea_empleado->nombre1 }}</td>
                     <td>{{ $linea_empleado->otros_nombres }}</td>
                     <td>{{ $linea_empleado->direccion1 }}</td>
                     <td>{{ $linea_empleado->departamento }}</td>
                     <td>{{ $linea_empleado->municipio }}</td>
                     <td>{{ $linea_empleado->pais }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_salarios, 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_emolumentos_eclesiasticos, 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_honorarios , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_servicios , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_comisiones , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_prestaciones_sociales , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_viaticos , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_gastos_representacion , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_trabajo_cooperativo , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_otros_pagos , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_cesantias_e_intereses_pagadas  , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_pensiones_jubilacion , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->total_ingresos_brutos, 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->aportes_salud_obligatoria , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->aportes_pension_obligatoria_y_fsp , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->aportes_voluntarios_pension , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->aportes_afc, 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->aportes_avc, 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->valores_retefuente , 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_bonos, 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_desde_recursos_publicos_para_educacion, 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_alimentacion_mayores_41uvt, 0, ',', '.') }}</td>
                     <td class="text-right">{{ number_format( $linea_empleado->pagos_alimentacion_hasta_41uvt, 0, ',', '.') }}</td>
                     <td>{{ $linea_empleado->identificacion_fideicomisio }}</td>
                     <td>{{ $linea_empleado->tipo_documento_contrato_colaboracion }}</td>
                     <td>{{ $linea_empleado->identificacion_contrato_colaboracion }}</td>
	            </tr>
            @endforeach
        </tbody>
    </table>
</div>
