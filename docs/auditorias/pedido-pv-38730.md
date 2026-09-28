# PV 38730: relación de facturación POS

Revisión del 28-09-2026 de `u883985631_tcsimonbolivar`. Consultas PDO en transacción `READ ONLY`, finalizada con rollback. No se modificaron datos de producción.

## Hallazgos

- Pedido PV 38730: ID 38934, creado 20-09-2026 20:07:14, cliente MESA 24, total $23.000, estado Facturado, `ventas_doc_relacionado_id=20021`.
- Pedido PV 38731: ID 38935, creado 20-09-2026 20:07:38, mismo cliente y relación, total $5.000.
- POS ID 20021: transacción 52, tipo documento 11, consecutivo 189, fecha 20-09-2026, creado 20:32:54, total $28.000, cliente ID 856, estado Enviada.
- Electrónica correspondiente: ID 38951, FES 189, fecha 20-09-2026, creada 20:32:56, total $28.000, cliente ROLLERPOINTS SAS, estado Enviada.
- Factura mostrada incorrectamente: ID 20021 **en otra tabla**, FES 70, fecha 19-04-2026, total $40.800, cliente ID 228.

Los registros de ambos pedidos suman exactamente los de POS 20021 y electrónica 38951:

| Producto | Cantidad | Total |
| --- | ---: | ---: |
| Chuzo de pollo (ID 2) | 1 | $11.000 |
| Chuzo de res y cerdo (ID 7) | 1 | $12.000 |
| Bebida (ID 378), pedido PV 38731 | 1 | $5.000 |

El pedido referencia un ID de `vtas_pos_doc_encabezados`, pero la consulta anterior priorizaba ese ID en `vtas_doc_encabezados`. La coincidencia numérica no representa una relación documental. La electrónica histórica 38951 tiene `ventas_doc_relacionado_id=0`; se identifica por la misma combinación empresa/transacción/tipo/consecutivo del POS y se corrobora con fecha, total y productos.

El cambio de MESA 24 a ROLLERPOINTS SAS está registrado en la facturación; las consultas no permiten determinar quién realizó esa selección. No se deduce que ese cambio de cliente sea un error.

## Corrección en código

Resolución específica para pedidos facturados: se descartan documentos de otra empresa, tipos incompatibles o creados antes del pedido; se localiza la conversión electrónica por la identidad contable del POS. Si ambos espacios de IDs producen candidatos diferentes válidos, no se muestra un enlace arbitrario.

La carga AJAX de pedidos descarta respuestas anteriores cuando se solicita otro pedido o se cancela la carga. No se cambia la semántica de las relaciones almacenadas ni se migran datos históricos.

Pruebas de resolución ejecutadas en SQLite en memoria, independiente de producción.
