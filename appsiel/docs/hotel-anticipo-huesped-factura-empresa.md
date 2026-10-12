# Anticipo del huésped y factura a una empresa

## Diagnóstico del caso de la habitación 205

Revisión de solo lectura del 11 de octubre de 2026 en `u883985631_casaverona`:

- Pedido 678, HOT-542, estadía 542: abierto; sin `pos_doc_id` ni `sales_doc_id`.
- Huésped: VICTOR MANUEL ALFONSO, cliente 1694, tercero 2173.
- Destinatario: GLOBAL CAR SOLUCIONES SAS, cliente 1697, tercero 2176.
- Anticipo: RC 672, movimiento CxC 1019, empresa 1, tercero 2173.
- Valor documento y saldo pendiente: -120.000; valor pagado: 0; estado: Pendiente.

El anticipo existe. `DocCruceController::creacion_abonos_cxc` busca el saldo a favor por empresa y por tercero del encabezado del cruce. Al facturar a la empresa busca el tercero 2176, pero el RC pertenece al 2173. Esa diferencia origina el mensaje observado.

No se debe quitar simplemente el filtro por tercero: los abonos, los asientos y la reversión del cruce también presuponen un mismo tercero.

## Comportamiento recuperado

La facturación hotelera guarda `core_tercero_id` con el destinatario de la factura y `cliente_id` con el huésped del pedido, tanto en POS como en el encabezado de ventas derivado al convertir a electrónica.

Al guardar se aplica el anticipo original directamente a la cartera de la factura mediante un solo cruce (`HotelAdvancePaymentService::applyDirect`). No se crea nota contable interna ni segundo cruce. El abono conserva al tercero dueño del anticipo; el cruce contabiliza el débito con ese tercero y el crédito de cartera con el destinatario. La reversión del cruce restaura ambos movimientos respetando sus respectivos terceros.

El flujo valida empresa, huésped/destinatario, naturaleza y saldo disponible; suma selecciones repetidas y bloquea los movimientos durante la transacción. Los otros medios de pago siguen el flujo de tesorería existente. La excepción para cruzar terceros distintos se habilita internamente para hotel, conservando la restricción habitual en el formulario general de CxC.

La relación `cliente_facturacion` obtiene el cliente de `core_tercero_id`. La contabilización, los datos del destinatario electrónico, el correo y la presentación POS la utilizan para no confundir al huésped con la empresa. La validación de conversión admite esta separación únicamente para una factura relacionada con un pedido hotelero del mismo huésped y empresa.

Factura POS, cruce, saldos, consecutivos y actualización del pedido se guardan dentro de la misma transacción. La conversión/envío electrónico sigue ocurriendo después de confirmar esa transacción, como antes.

Las notas internas y los dos cruces ya registrados permanecen intactos. Se conserva la resolución visual de esos documentos históricos hacia los recaudos originales. No hay migraciones ni correcciones automáticas de datos históricos.

## Uso

Aplicar el anticipo del huésped, seleccionar **Otro cliente** con el destinatario de la factura, conservar el importe del medio de pago Anticipo y guardar. Revisar el único cruce de CxC y el saldo de la factura. Para anular posteriormente, reversar primero los abonos/cruces que impidan la anulación.

Las pruebas utilizan exclusivamente SQLite en memoria. No se generaron, anularon ni enviaron documentos reales durante esta implementación.

## Pago combinado: anticipo más efectivo

En el caso local HOT-343 se seleccionaron $140.000 de anticipo y $2.800 de efectivo. La configuración `tesoreria.recaudos_cxc_motivo_id` apuntaba al ID 4, inexistente en el catálogo local; el motivo seleccionado, ID 1 (`Recaudo clientes`), sí existía. El reemplazo incondicional del motivo produjo `Trying to get property 'movimiento' of non-object` al registrar el efectivo.

`TreasuryService` usa ahora el motivo configurado únicamente si existe y corresponde a una entrada de `recaudo-cartera`. En caso contrario usa el motivo seleccionado si cumple esas condiciones. Si ninguno es válido, aborta con un mensaje explícito. Excluye el anticipo del recaudo nuevo y conserva el importe de $2.800. No se cambió el catálogo ni la configuración.

La regresión de este caso verifica en memoria el traslado de $140.000, los dos cruces, el saldo restante de $2.800 y la solicitud de tesorería con el motivo válido; intercepta el guardado del recaudo para no ejecutar movimientos reales.
