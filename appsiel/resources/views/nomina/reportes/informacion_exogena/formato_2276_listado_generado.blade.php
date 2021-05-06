<div class="table-responsive">
    <table id="myTable" class="table table-striped table-bordered">
        {{ Form::bsTableHeader( ['Entidad Informante', 'Tipo de documento del beneficiario', 'Número de identificación del beneficiario', 'Primer Apellido del beneficiario', 'Segundo Apellido del beneficiario', 'Primer nombre del beneficiario', 'Otros nombres del beneficiario', 'Dirección', 'Departamento', 'Municipio', 'País', 'Pagos por salarios', 'Pagos por emolumentos eclesiásticos', 'Pagos por honorarios', 'Pagos por servicios', ' Pagos por comisiones', 'Pagos por prestaciones sociales', ' Pagos por viáticos', 'Pagos por gastos de representación', 'Pagos por compensaciones por el trabajo asociado cooperativo', 'Otros pagos', 'Cesantías e intereses de cesantías efectivamente pagadas, consignadas o reconocidas en el periodo', 'Pensiones de jubilación, vejez o invalidez', 'Total ingresos brutos',   'Aportes obligatorios por salud', 'Aportes obligatorios a fondos de pensiones y solidaridad pensional y Aportes voluntarios al – RAIS', 'Aportes voluntarios a fondos de pensiones voluntarias', 'Aportes a cuentas AFC', 'Aportes a cuentas AVC', 'Valor de las retenciones en la fuente por pagos de rentas de trabajo o pensiones', 'Pagos realizados con bonos electrónicos o de papel de servicio, cheques, tarjetas, vales, etc', 'Apoyos económicos no reembolsables o condonados, entregados por el Estado o financiados con recursos públicos, para financiar programas educativos', 'Pagos por alimentación mayores a 41 UVT', 'Pagos por alimentación hasta a 41 UVT', 'Identificación del fideicomiso o contrato', 'Tipo documento participante en contrato de colaboración', 'Identificación participante en contrato colaboración'] ) }}
        <tbody>
        	@foreach( $datos as $linea_empleado )
                <tr>
                    <td> {{ $linea_empleado->tipo_entidad_informanate }} </td>
                    <td> {{ $linea_empleado->tipo_documento }} </td>
                     <td> {{ $linea_empleado->numero_identificacion }} </td>
                     <td> {{ $linea_empleado->apellido1 }} </td>
                     <td> {{ $linea_empleado->apellido2 }} </td>
                     <td> {{ $linea_empleado->nombre1 }} </td>
                     <td> {{ $linea_empleado->otros_nombres }} </td>
                     <td> {{ $linea_empleado->direccion1 }} </td>
                     <td> {{ $linea_empleado->departamento }} </td>
                     <td> {{ $linea_empleado->municipio }} </td>
                     <td> {{ $linea_empleado->pais }} </td>
                     <td> {{ $linea_empleado->pagos_salarios }} </td>
                     <td> {{ $linea_empleado->pagos_emolumentos_eclesiasticos }} </td>
                     <td> {{ $linea_empleado->pagos_honorarios  }} </td>
                     <td> {{ $linea_empleado->pagos_servicios  }} </td>
                     <td> {{ $linea_empleado->pagos_comisiones  }} </td>
                     <td> {{ $linea_empleado->pagos_prestaciones_sociales  }} </td>
                     <td> {{ $linea_empleado->pagos_viaticos  }} </td>
                     <td> {{ $linea_empleado->pagos_gastos_representacion  }} </td>
                     <td> {{ $linea_empleado->pagos_trabajo_cooperativo  }} </td>
                     <td> {{ $linea_empleado->pagos_otros_pagos  }} </td>
                     <td> {{ $linea_empleado->pagos_cesantias_e_intereses_pagadas   }} </td>
                     <td> {{ $linea_empleado->pagos_pensiones_jubilacion  }} </td>
                     <td> {{ $linea_empleado->total_ingresos_brutos }} </td>
                     <td> {{ $linea_empleado->aportes_salud_obligatoria  }} </td>
                     <td> {{ $linea_empleado->aportes_pension_obligatoria_y_fsp  }} </td>
                     <td> {{ $linea_empleado->aportes_voluntarios_pension  }} </td>
                     <td> {{ $linea_empleado->aportes_afc }} </td>
                     <td> {{ $linea_empleado->aportes_avc }} </td>
                     <td> {{ $linea_empleado->valores_retefuente  }} </td>
                     <td> {{ $linea_empleado->pagos_bonos }} </td>
                     <td> {{ $linea_empleado->pagos_desde_recursos_publicos_para_educacion }} </td>
                     <td> {{ $linea_empleado->pagos_alimentacion_mayores_41uvt }} </td>
                     <td> {{ $linea_empleado->pagos_alimentacion_hasta_41uvt }} </td>
                     <td> {{ $linea_empleado->identificacion_fideicomisio }} </td>
                     <td> {{ $linea_empleado->tipo_documento_contrato_colaboracion }} </td>
                     <td> {{ $linea_empleado->identificacion_contrato_colaboracion }} </td>
	            </tr>
            @endforeach
        </tbody>
    </table>
</div>
