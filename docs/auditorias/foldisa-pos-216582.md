# Foldisa: duplicado POS 216582

Revisión del 09-10-2026 en `u883985631_foldisa`. Consultas directas PDO en transacciones `READ ONLY`; cierre explícito con `ROLLBACK` en la segunda y tercera sesión. La primera sesión se cerró al terminar la conexión: PDO no reconoció para `rollBack()` la transacción iniciada con SQL. Todas las consultas fueron de lectura. No se modificaron datos ni esquema de producción.

## Evidencia

Ambas facturas corresponden a empresa 1, transacción 47, tipo de documento 18 (prefijo POS), consecutivo 216582 y estado Contabilizado.

| ID | Creación almacenada | PDV | Cajero | Total | Remisión |
| --- | --- | --- | --- | --- | --- |
| 264128 | 2026-10-08 20:58:15 | 1 | 5 | $56.000 | 458141 |
| 264129 | 2026-10-08 20:58:16 | 2 | 16 | $24.000 | 458146 |

Los `uniqid` son diferentes. Los detalles vinculados por ID de encabezado son cuatro líneas por $56.000 y una línea por $24.000, respectivamente. Son documentos distintos; la coincidencia afecta la misma serie, no solamente el número entre series diferentes.

La secuencia vecina es 216580, 216581, 216582, 216582, 216584, 216585. No existe encabezado POS 216583 para esta empresa, transacción y tipo.

Existe una sola fila del contador para empresa 1/documento 18: ID 4. En la lectura registraba `consecutivo_actual=216684`. Tanto el contador como los encabezados usan InnoDB. El encabezado tiene índices únicos sobre `id` y `uniqid`, pero no sobre la identidad empresa/tipo/consecutivo.

## Causa probable

El código local de `FacturaPosController::store` abre una transacción, pero el camino `TransaccionController::crear_encabezado_documento` → `EncabezadoDocumentoTransaccion::crear_nuevo` asigna el consecutivo leyendo el contador y sumando uno, guarda el encabezado y después incrementa el contador.

`TipoDocApp::get_consecutivo_actual` hace una lectura ordinaria sin `lockForUpdate`. Dos transacciones pueden leer el mismo valor, guardar ambas 216582 y después incrementar el contador dos veces. El siguiente documento recibe 216584. La transacción por sí sola no impide esta secuencia.

La diferencia de un segundo en `created_at` no implica que una solicitud hubiera terminado antes de iniciar la otra: los encabezados se insertan antes de completar la facturación. La primera factura tiene `updated_at=20:58:19` y la segunda `20:58:23`.

Los datos y el código local son consistentes con una condición de carrera. No se inspeccionaron el código desplegado ni los logs de solicitudes, por lo que no se presenta como una reconstrucción demostrada de la ejecución en producción.

## Alcance e impacto

- En el histórico de empresa 1/tipo 18 existen 17 consecutivos repetidos, cada uno en dos encabezados (17 facturas adicionales respecto de la unicidad esperada).
- Desde el 08-10-2026 se encontraron dos grupos: 216556 y 216582. El primero corresponde a IDs 264102 y 264103, creados a las 20:40:32 y 20:40:34, por $18.000 y $144.000.
- Para 216582, los movimientos POS conservan por PDV cuatro líneas/$56.000 y una línea/$24.000. Tesorería conserva un movimiento por cada PDV con esos mismos importes.
- Contabilidad contiene los dos conjuntos con el mismo consecutivo: débitos de $56.000 y $24.000, y créditos equivalentes con diferencias residuales de punto flotante inferiores a un peso.
- No se encontraron movimientos CxC ni encabezados en `vtas_doc_encabezados` para empresa 1/transacción 47/tipo 18 y consecutivos 216556, 216582 o 216583.

La evidencia consultada no muestra pérdida de estas dos ventas: sus detalles e importes están presentes. Las consultas o procesos que identifican documentos por empresa/transacción/tipo/consecutivo pueden mezclar movimientos de las dos facturas. Por ello, corregir solo el número del encabezado dejaría inconsistencias.

## Corrección propuesta

1. Reservar el consecutivo mediante lectura bloqueante del contador (`SELECT ... FOR UPDATE`) e incremento dentro de la misma transacción de creación. Cubrir también la creación inicial del contador con unicidad empresa/documento y revisar los demás caminos que asignan números.
2. Conciliar los 17 grupos y sus referencias antes de renumerar. Para este caso, 216583 es un candidato por estar vacío en las tablas y filtros revisados; falta revisar todas las referencias y posibles documentos externos antes de usarlo.
3. Una vez saneados los datos, agregar una restricción única con la identidad documental validada. El contador actual se comparte por empresa/tipo de documento.
4. Verificar la solución con facturas concurrentes desde dos PDV y rollback de una solicitud fallida, en un entorno de pruebas.

Esta revisión no implementó cambios de código, renumeraciones ni índices.
