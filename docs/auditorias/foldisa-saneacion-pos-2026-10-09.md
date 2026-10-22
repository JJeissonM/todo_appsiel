# Foldisa: saneación de consecutivos POS en producción

Ejecución autorizada por el usuario el 09-10-2026 en `u883985631_foldisa`. Estado: **confirmada (COMMITTED)**.

## Resultado

- Se resolvieron los 17 grupos duplicados identificados en `vtas_pos_doc_encabezados`, con identidad empresa/transacción/tipo de documento/consecutivo.
- Todas las facturas intervenidas corresponden a empresa 1, transacción 47 y tipo de documento 18 (POS).
- Se usaron los consecutivos siguientes que estaban libres en esta serie. El contador no se modificó.
- Se actualizaron 195 filas en seis tablas. Los cambios de numeración y referencias se confirmaron en una sola transacción InnoDB.
- Después del commit, una conexión independiente comparó las filas con el resultado esperado y consultó todo el histórico POS: **cero grupos duplicados** por los cuatro campos de identidad.

## Facturas renumeradas

| ID renumerado | ID que conserva el número | Anterior | Nuevo | Estado |
| --- | --- | --- | --- | --- |
| 51524 | 51523 | 42928 | 42929 | Contabilizado |
| 52865 | 52864 | 44084 | 44085 | Contabilizado |
| 91504 | 91503 | 73792 | 73793 | Contabilizado |
| 127566 | 127565 | 102299 | 102300 | Contabilizado |
| 141787 | 141786 | 112575 | 112576 | Contabilizado |
| 151565 | 151564 | 119537 | 119538 | Contabilizado |
| 184306 | 184305 | 145624 | 145625 | Contabilizado |
| 187566 | 187565 | 148549 | 148550 | Contabilizado |
| 213895 | 213894 | 172093 | 172094 | Contabilizado |
| 214869 | 214868 | 172943 | 172944 | Contabilizado |
| 221615 | 221616 | 179005 | 179006 | Anulado |
| 222333 | 222332 | 179664 | 179665 | Contabilizado |
| 232375 | 232374 | 188773 | 188774 | Contabilizado |
| 260456 | 260455 | 213323 | 213324 | Contabilizado |
| 263899 | 263898 | 216404 | 216405 | Contabilizado |
| 264103 | 264102 | 216556 | 216557 | Contabilizado |
| 264129 | 264128 | 216582 | 216583 | Contabilizado |

En el caso inicial, ID 264128 conserva POS 216582 ($56.000) e ID 264129 pasa a POS 216583 ($24.000).

## Filas actualizadas

| Tabla | Campo | Cantidad |
| --- | --- | --- |
| `contab_movimientos` | `consecutivo` | 84 |
| `inv_doc_encabezados` | `descripcion` | 9 |
| `teso_movimientos` | `consecutivo` | 19 |
| `vtas_movimientos` | `consecutivo` | 33 |
| `vtas_pos_doc_encabezados` | `consecutivo` | 17 |
| `vtas_pos_movimientos` | `consecutivo` | 33 |

Los documentos de inventario mantienen sus consecutivos propios; las nueve modificaciones corrigen la referencia textual a la factura POS en `descripcion`. Se asignaron mediante empresa, usuario y fecha/hora de creación, y se corroboró el ID de origen cuando estaba disponible.

## Controles aplicados

- Respaldo previo de encabezados, detalles, movimientos y referencias; manifiesto SHA-256 y plan inverso de datos para recuperación revisada. No se guardaron credenciales en el respaldo.
- Conciliación por encabezado, PDV/cajero, usuario, fecha, tercero, remisión, productos/cantidades/importes y balance contable. No se separaron movimientos utilizando únicamente el consecutivo o la hora.
- Revisión del esquema completo para referencias numéricas y consulta de los números originales y candidatos en 40 conjuntos, incluidas series de otros módulos. Se respetaron las coincidencias numéricas de otras identidades.
- Revisión de referencias textuales POS en encabezados de inventario, ventas, tesorería y contabilidad, y en movimientos contables, de ventas y tesorería.
- Protección transaccional: contador existente y filas de ambos documentos bloqueados por ID; revalidación de las filas frente al respaldo y de los destinos libres antes de escribir.
- Updates por listas explícitas de IDs; conteos exactos; comparación de todas las columnas antes del commit. Solamente cambiaron los consecutivos indicados y nueve descripciones. Importes, fechas, estados, IDs, relaciones por ID y otros campos permanecieron iguales.
- El asiento de cada factura con movimientos presentes se concilió dentro de un centavo de tolerancia por los valores double históricos.
- No se alteraron remisiones ni costos, ni se generaron movimientos nuevos. La cuenta por cobrar y su abono asociados a la factura que conserva 172093 permanecen con ese número.
- La transacción de corrección tomó aproximadamente 7.32 segundos. No se modificaron índices ni configuración global, ni se desplegó código.

## Anomalía histórica pendiente

El grupo POS 179005 incluye ID 221615, Anulado, por $86.900, e ID 221616, Contabilizado, por $17.500. Antes de esta intervención no existían movimientos POS, ventas, tesorería, contabilidad o CxC bajo esa identidad.

Se conservó 179005 en la factura vigente 221616 y se renumeró la anulada 221615 a 179006, junto con su referencia textual de inventario. Los detalles de ambas facturas se conservan. La intervención no reconstruyó movimientos ausentes ni cambió sus estados. La ausencia requiere una investigación separada; no se atribuye a esta saneación ni se afirma una causa demostrada.

## Respaldo y límites

Los archivos privados están en `/home/ing_adalberto/.local/state/appsiel/auditorias/foldisa-saneacion-2026-10-09/`, con directorio 0700 y archivos 0600. Incluyen `snapshot.json`, `inspect.json`, `extra.json`, `text.json`, `plan.json`, `rollback_plan.json`, `preflight.json`, `applied.json`, `verified.txt`, scripts sin credenciales y `sha256.json`. El plan inverso requiere revalidación del estado actual antes de cualquier restauración; no debe ejecutarse como una reversión indiscriminada.

La ausencia de duplicados se verificó en el histórico POS, no como una auditoría de unicidad de todos los encabezados de todos los módulos. La corrección del mecanismo de reserva y la restricción única siguen pendientes; la saneación por sí sola no impide nuevos duplicados. Los archivos y documentos externos a esta base de datos no se modificaron.
