# Ajuste de saldos de apertura al 31 de diciembre de 2025

Ejecutado el 19 de noviembre de 2026 en producción `u883985631_colegiobilingu`, empresa 1. El usuario autorizó ajustar los saldos iniciales para coincidir con los estados financieros adjuntos.

## Fuentes y criterio

- `Estado de cambio situacion financiera 2025 - Presentado Asamblea.pdf`: una página del estado de situación financiera comparativo 2025/2024, expresado en **miles de pesos**. SHA-256: `51443bd554f57ae6ff19a39175aa6c43154fa040a9e70eed2d528bec7937f33b`.
- `Estado de cambio situacion financiera 2025 - SIESA.xlsx`: hoja `UFCG104Q`, estado NCIF con detalle de cuentas y saldos de diciembre de 2025 en **pesos**. SHA-256: `553e6acda051f86b50b034755dbc7fb0248fe5f9139dbf95d5c0023983dd393f`.

Se tomaron una sola vez las 99 cuentas de detalle de seis u ocho dígitos del Excel, sin sumar nuevamente sus agregados. Los saldos con sufijo `CR` se interpretaron como acreedores. Los importes se normalizaron al centavo antes de calcular diferencias. Las cuentas de apertura existentes que no aparecen en el detalle del estado se llevaron a saldo neto cero; sus movimientos originales se conservaron.

El Excel presenta patrimonio antes del resultado pendiente por $128.766.667.572,49 y señala expresamente al pie **«DIFERENCIA POR CUENTAS DE RESULTADO» por $1.824.277.006,43 CR**. Se incorporó esa diferencia en la cuenta existente `360505 UTILIDAD DEL EJERCICIO`: saldo del Excel $16.830.155.989,71 CR + diferencia de resultados $1.824.277.006,43 CR = saldo final $18.654.432.996,14 CR. Así el patrimonio asciende a $130.590.944.578,92 y coincide con los $130.590.945 miles del PDF. Esta asignación a la cuenta de resultados es el criterio contable aplicado al conciliar ambos soportes.

Se conservó la precisión del Excel. El renglón de efectivo del PDF muestra 19.394.061 miles, mientras el detalle del Excel suma $19.394.060.450,15 (19.394.060 miles al redondear el agregado). Esa diferencia de una unidad de presentación no se convirtió en un ajuste artificial de efectivo. El total de activos de ambos soportes sí concilia al redondear a miles.

## Registro aplicado

Se creó **SI 2**, encabezado **1746**, fechado **2025-12-31**, con soporte `AJ-SI-2025-ASAMBLEA` y marca `Ajuste SI 2025 EEFF Asamblea 20261119`.

- 32 líneas de documento: IDs 9008–9039.
- 32 movimientos contables: IDs 34880–34911.
- Débitos: **$6.621.076.035,43**.
- Créditos: **$6.621.076.035,43**.
- Tercero del ajuste: la propia fundación, ID 2121. Los adjuntos no incluyen distribución nueva por tercero; se registraron las diferencias a nivel de cuenta, conservando todos los auxiliares anteriores.
- Se actualizó únicamente el contador del documento SI de 1 a 2.

El [detalle por cuenta](soportes/colegio-bilingue-ajuste-saldos-2025.csv) contiene saldo anterior, saldo objetivo y valor del ajuste, en pesos con signo contable (deudor positivo, acreedor negativo).

| Concepto | Antes | Después |
| --- | ---: | ---: |
| Activos | $133.668.990.512,27 | $135.751.020.746,15 |
| Pasivos | $5.160.645.526,23 | $5.160.076.167,23 |
| Patrimonio | $128.508.344.986,04 | $130.590.944.578,92 |
| Activos menos pasivos y patrimonio | $0,00 | $0,00 |

## Respaldo y validaciones

Respaldo privado en el contenedor de aplicación:

`/var/www/html/appsiel/storage/app/backups/ajuste_saldos_20251231_20261119_081618.json`

Contiene los 3.748 movimientos originales del SI 1, encabezado y consecutivo anteriores, plan por cuenta, objetivos e IDs insertados. Permisos del archivo: `0600`. La carga se ejecutó en una transacción sobre tablas InnoDB, bloqueando empresa, consecutivo y movimientos de apertura y rechazando cualquier cambio frente al respaldo de conciliación.

Antes de confirmar se verificaron todos los saldos por cuenta, tanto con `SUM(valor_saldo)` como con `SUM(valor_debito + valor_credito)`, el cuadre por clases y la preservación íntegra de las filas originales del SI 1. Los créditos se almacenaron negativos en movimientos y positivos en líneas del documento, según la convención de Appsiel.

Los **19.895 movimientos posteriores a 2025-12-31** conservaron exactamente sus valores; la huella SHA-256 antes y después de la transacción fue `1bf28ea203bcdfd755c9296732b6bd424f71d964e007ed7e8c8346499376649b`. Los saldos acumulados de 2026 reflejarán naturalmente el nuevo saldo de apertura. No se modificaron tablas operativas de cartera, proveedores, nómina, tesorería ni retenciones.

Una nueva conexión de lectura, posterior al COMMIT, confirmó el encabezado 1746, sus 32 líneas cuadradas y todos los saldos objetivo por cuenta. Estado final: **COMMITTED y verificado**.
