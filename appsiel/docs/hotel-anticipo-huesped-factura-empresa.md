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

## Implementación automática al guardar la factura

Cuando los anticipos seleccionados pertenecen al huésped del pedido y la factura se emite a otro cliente, `HotelAdvancePaymentService` realiza internamente:

1. Una nota contable con débito al huésped (`crear_cxc`) y crédito al destinatario (`crear_anticipo_cxc`). Usa la cuenta real de cada anticipo, determinada desde su contabilidad origen; agrupa los importes por cuenta y traslada únicamente el valor aplicado.
2. Un cruce a nombre del huésped: los anticipos originales contra los movimientos positivos de la nota.
3. Un cruce a nombre del destinatario: los saldos a favor de la nota contra la factura. Si existen anticipos propios del destinatario seleccionados, los incluye en este cruce sin trasladarlos.

La nota y los cruces conservan referencias al pedido, factura, terceros y movimientos de anticipos originales. El RC original permanece a nombre del huésped. No se registra otro ingreso de dinero por el traslado. Si hay otros medios de pago, el recaudo del importe restante sigue el flujo de tesorería existente.

Si anticipo y factura pertenecen al mismo tercero, se conserva un solo cruce sin nota de traslado.

`HotelService::generatePosInvoice` ejecuta estos pasos dentro de la misma transacción que genera y contabiliza la factura POS y marca el pedido como facturado. Un fallo revierte la factura POS, la nota, los cruces, los saldos, los consecutivos reservados y los demás movimientos de esa transacción. La conversión/envío electrónico conserva el comportamiento anterior: ocurre después de confirmar esta transacción; un fallo de envío no revierte los documentos ya guardados.

## Validaciones y anulación

- Se bloquean los movimientos seleccionados durante la transacción y se valida empresa, tercero, naturaleza de anticipo, estado pendiente y saldo disponible.
- Se admiten únicamente anticipos del huésped del pedido o del destinatario. Se suman selecciones repetidas para impedir sobregiros.
- Los anticipos no pueden superar el total de la factura y deben coincidir con el importe del medio de pago `0-Anticipo`.
- Los cruces siguen exigiendo el mismo tercero entre cartera y saldo a favor.
- Las notas contabilizan y crean auxiliares con el tercero de cada línea, aunque sea diferente del encabezado.
- Se impide anular la nota mientras sus movimientos positivos o negativos tengan abonos/cruces. Para una reversión posterior, deben anularse ambos cruces antes de anular la nota. No se añadió una anulación automática conjunta.

## Uso después de desplegar

1. Verificar que HOT-542 siga abierto y sin factura, porque el estado puede haber cambiado después de la consulta de diagnóstico.
2. Aplicar RC 672 por $120.000 desde la lista de anticipos del pedido.
3. Seleccionar **Otro cliente → GLOBAL CAR SOLUCIONES SAS** y el tipo de factura requerido. Conservar el medio de pago **Anticipo por $120.000**. No hace falta registrar una nota o un traslado manual previo: se generarán internamente al guardar.
4. Guardar la factura una sola vez y revisar el resultado antes de reintentar, especialmente si hay un fallo de comunicación en el envío electrónico.
5. Verificar la nota, los dos cruces y el saldo de la factura desde Contabilidad/CxC.

Requiere las configuraciones existentes `contabilidad.transaction_type_id_default`, `contabilidad.document_type_id_default`, los parámetros `ventas.cruces_cxc_*`, el modelo de notas 47 y la contabilidad origen del anticipo. No requiere migraciones.

La validación automatizada utiliza exclusivamente SQLite en memoria, sin cargar la aplicación ni el archivo `.env`. No se ejecutó este flujo en producción ni se desplegaron cambios durante la implementación.

## Pago combinado: anticipo más efectivo

En el caso local HOT-343 se seleccionaron $140.000 de anticipo y $2.800 de efectivo. La configuración `tesoreria.recaudos_cxc_motivo_id` apuntaba al ID 4, inexistente en el catálogo local; el motivo seleccionado, ID 1 (`Recaudo clientes`), sí existía. El reemplazo incondicional del motivo produjo `Trying to get property 'movimiento' of non-object` al registrar el efectivo.

`TreasuryService` usa ahora el motivo configurado únicamente si existe y corresponde a una entrada de `recaudo-cartera`. En caso contrario usa el motivo seleccionado si cumple esas condiciones. Si ninguno es válido, aborta con un mensaje explícito. Excluye el anticipo del recaudo nuevo y conserva el importe de $2.800. No se cambió el catálogo ni la configuración.

La regresión de este caso verifica en memoria el traslado de $140.000, los dos cruces, el saldo restante de $2.800 y la solicitud de tesorería con el motivo válido; intercepta el guardado del recaudo para no ejecutar movimientos reales.
