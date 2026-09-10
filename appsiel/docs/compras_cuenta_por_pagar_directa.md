# Cuenta por pagar directa en compras

En la creación, **Cuenta por pagar directa** (`cta_x_pagar_id`) es un combobox opcional con las cuentas activas de la empresa. Aparece al seleccionar crédito.

- Crédito y cuenta seleccionada: el crédito contable del saldo neto del proveedor utiliza esa cuenta.
- Crédito y campo vacío: conserva `Proveedor::get_cuenta_por_pagar($proveedor_id)`.
- Contado: el servidor guarda el campo como `null` y conserva la contabilización de tesorería.

La cuenta se persiste en `compras_doc_encabezados`, se muestra en la consulta del documento y se conserva en la confirmación y recontabilización. Las notas crédito vinculadas usan la cuenta de la factura original. El pago de CxP continúa recuperando la cuenta del asiento causado.

La anulación elimina movimientos contables y de CxP por la identidad del documento, independientemente de cuál sea la cuenta usada. Mantiene el ID de la cuenta en el encabezado anulado como referencia histórica. Los abonos y las notas crédito vigentes siguen impidiendo la anulación; una cuenta desactivada posteriormente no impide anular.

Aplicar la migración `2026_09_10_000002_add_cta_x_pagar_id_to_compras_doc_encabezados.php` antes de desplegar el código. La columna es nullable y tiene una clave foránea que impide eliminar cuentas referenciadas por documentos.

Pruebas: `php vendor/bin/phpunit tests/ComprasCuentaPorPagarDirectaTest.php`, desde `appsiel/`. Los registros de prueba se revierten mediante transacciones.
