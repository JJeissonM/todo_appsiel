# Distrikarnikas: conciliación de consecutivos POS en producción

Ejecución autorizada por el usuario el 13-11-2026 en `u883985631_distrikarnikas`. Estado: **COMMITTED**, verificado desde una conexión independiente.

Se corrigieron los tres grupos duplicados del histórico de `vtas_pos_doc_encabezados` por empresa/transacción/tipo/consecutivo. Todos corresponden a empresa 1, transacción 47 y tipo 18. Las seis facturas estaban contabilizadas y representan ventas distintas.

| ID renumerado | ID que conserva número | Anterior | Nuevo | Total de líneas |
| --- | --- | --- | --- | --- |
| 29095 | 29094 | 28859 | 28860 | $195.440 |
| 31219 | 31218 | 30983 | 30984 | $9.790 |
| 34333 | 34332 | 34097 | 34098 | $13.200 |

Se utilizaron los números inmediatamente siguientes, libres en la serie POS. Las coincidencias numéricas con documentos de inventario pertenecen a otra identidad y se conservaron.

## Cambios confirmados

| Tabla | Campo | Filas |
| --- | --- | --- |
| `vtas_pos_doc_encabezados` | `consecutivo` | 3 |
| `vtas_pos_movimientos` | `consecutivo` | 8 |
| `vtas_movimientos` | `consecutivo` | 8 |
| `teso_movimientos` | `consecutivo` | 2 |
| `contab_movimientos` | `consecutivo` | 13 |
| `cxc_movimientos` | `consecutivo` | 1 |
| `cxc_abonos` | `doc_cxc_consecutivo` | 1 |

Total: **36 filas**, en una sola transacción InnoDB. No se eliminaron ni fusionaron facturas. No se cambiaron importes, ajustes al peso, fechas, estados, relaciones por ID, inventario ni contador de la serie.

La factura 31219 pasa a POS 30984 junto con su cuenta por cobrar ID 3060 y la referencia del abono ID 3072. Su total de líneas es $9.790 y su ajuste al peso es $10: el documento de cartera y el abono conservan $9.800 y estado pagado. La factura 31218 conserva POS 30983, su cartera y su abono por $105.000.

## Controles

- Se consultaron todas las tablas del esquema con columnas que contienen `consecutivo`, incluyendo referencias de abonos y conversiones. Se revisaron números originales y candidatos, detalles por ID y remisiones asociadas. Se buscaron los números en campos textuales de descripción, detalle, referencia, documento, observación y concepto del esquema; no se encontraron coincidencias.
- Se asignaron movimientos por identidad documental, usuario, fecha y tercero; se corroboraron PDV y cajero en POS, y remisión en movimientos de ventas. Para POS 30983, las dos facturas comparten cajero y hora, pero sus terceros, remisiones y productos son diferentes.
- Se cotejaron productos, cantidades e importes de detalles contra movimientos POS, ventas y líneas contables de productos. Se verificó equilibrio de cada asiento y conciliación de tesorería/cartera con ajustes al peso; en cartera se conciliaron también sus abonos.
- Se confirmó que las tablas intervenidas usan InnoDB y que no existen triggers. Se guardó un respaldo de las filas consultadas y planes directo e inverso antes de escribir.
- Se bloqueó el contador de la serie y se revalidaron bajo bloqueo las filas frente al respaldo y la disponibilidad de destinos. Cada UPDATE utilizó un ID explícito y el valor anterior esperado, y afectó exactamente una fila.
- Antes del commit se compararon todas las columnas de las filas consultadas con el resultado esperado y se comprobó que el contador no cambió. Después del commit, otra conexión repitió la comparación y verificó **cero grupos duplicados en todo el histórico POS**.

## Respaldo y alcance

Respaldo privado: `/home/ing_adalberto/.local/state/appsiel/auditorias/distrikarnikas-saneacion-2026-11-13/`, directorio 0700 y archivos 0600. Contiene esquema, grupos, encabezados, snapshot, consultas, búsquedas textuales, plan de cambios, plan inverso, preflight, resultado esperado, confirmación de commit, verificación independiente, scripts sin credenciales y manifiestos SHA-256. El plan inverso requiere revalidar el estado actual antes de usarlo.

La migración `2026_10_09_000001_make_document_counters_unique` ya figuraba aplicada al consultar producción. Esta intervención concilió datos; no ejecutó `php artisan migrate --force`, no agregó índices ni registró migraciones. El entorno local carece de PHP y de Docker operativo. Se puede reintentar la migración desde el servidor de despliegue para activar la unicidad POS. No se modificaron documentos externos a esta base de datos.
