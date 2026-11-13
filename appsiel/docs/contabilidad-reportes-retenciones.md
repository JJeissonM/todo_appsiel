# Reportes contables de retenciones

## Acceso y operación

Contabilidad → Reportes → **Retenciones: Fuente, ICA e IVA**. El formulario usa
`vista_reporte` y publica a `POST contab_reporte_retenciones`. La migración registra
el reporte sin depender de un ID fijo, sus campos y dos permisos:
`contab_reporte_retenciones` y `contab_configurar_retenciones`. Los administradores
reciben los permisos; los demás usuarios los reciben mediante la administración de roles.

1. En **Clasificación fiscal por año**, asocie cada retención a Fuente, ICA o IVA y
   a su concepto. Para ICA registre municipio DANE y actividad; use una retención
   por municipio y tarifa. Esta configuración pertenece a la empresa y al año.
2. Seleccione fechas, impuesto, practicadas/sufridas y presentación. Puede filtrar
   tercero, naturaleza jurídica, retención, concepto, municipio y tarifa porcentual.
3. Seleccione **Detalla tercero**: **Sí** conserva el detalle de la presentación
   elegida; **No** muestra únicamente el consolidado fiscal y las casillas, sin
   identificar terceros. Los totales se conservan en ambas opciones.
4. Genere el informe. Excel incluye las tablas visibles mediante `data-excel-export`.
   PDF usa el botón habitual, con orientación horizontal inicial. El contenido del
   PDF está aislado por empresa, usuario y reporte y caduca a las dos horas.
5. Revise las observaciones antes de preparar la declaración. Los registros sin
   clasificación conservan sus valores y no reciben casillas fiscales inventadas.

## Presentaciones y cálculos

- Resumen por mes, impuesto, concepto, persona y jurisdicción, con base y retención.
- Acumulado por tercero y tarifa. ICA muestra porcentaje y equivalente por mil;
  el sistema almacena sus tarifas en porcentaje (0,414 % = 4,14 ‰).
- Detalle por documento con enlaces, certificado y observaciones.
- Casillas del formulario 350: bases y retenciones de personas naturales y jurídicas,
  IVA a responsables y no residentes, devoluciones y totales clasificados 130/134.
  Se acumula cada casilla mensual antes de aproximarla a miles. Las devoluciones
  se presentan con signo negativo en los netos y positivas en casillas 129/133.

Referencia verificada: [Formulario 350 de 2026, DIAN](https://www.dian.gov.co/atencionciudadano/formulariosinstructivos/Formularios/2026/Formulario_350_2026.pdf).
La estructura validada es 2026; otros años conservan los auxiliares y señalan que
las casillas requieren validación. El informe apoya la preparación: no presenta
la declaración ni produce un archivo de carga de la DIAN. Autorretenedores,
Timbre, anexos de pagos al exterior, sanciones y ajustes de períodos anteriores
requieren su tratamiento específico. ReteICA se declara ante la autoridad
territorial; su jurisdicción no se deduce del domicilio del tercero.

Las bases sumadas por retención no equivalen al valor total de compras. Las
retenciones sufridas nunca se imputan como obligaciones practicadas del 350.

## Fuentes, trazabilidad y datos históricos

El origen principal es `contab_registros_retenciones`. La migración agrega empresa,
fecha, tercero, naturaleza del tercero y referencia de documento, sin cambiar
bases ni valores retenidos. Los registros nuevos reciben esos datos al crearse.
La naturaleza histórica recuperada corresponde al catálogo existente al recuperar
la información y debe revisarse si hubo cambios en el tercero.

La reconstrucción usa la línea de compra cuando existe. En los demás casos exige
un único encabezado compatible con transacción, tipo y consecutivo, y empresa si
se conoce. Nunca resuelve una coincidencia entre varias empresas por aproximación.

```bash
php artisan contab:reconstruir-retenciones
```

El comando es repetible y muestra recuperados y pendientes. Los registros que no
puedan asignarse a una empresa/fecha quedan pendientes de conciliación y no se
atribuyen a una compañía arbitraria. Se pueden conciliar documentando los campos
de trazabilidad y ejecutar nuevamente el comando; no hay una atribución masiva
automática por fecha de creación ni fecha del certificado.

Para incluir contabilizaciones manuales y de nómina, el informe también consulta
las cuentas de compras/ventas/devoluciones asociadas al catálogo de retenciones.
Excluye el asiento si el mismo documento, naturaleza y cuenta ya está soportado
por el auxiliar. Los asientos sin auxiliar mantienen el importe retenido y marcan
la base como desconocida, sin derivarla de una tarifa supuesta. Si varias
retenciones comparten una cuenta, sólo reutiliza la clasificación cuando todas
coinciden; en otro caso exige revisión. Las cuentas de retenciones deben estar
asociadas al catálogo para reconocer estos movimientos. Se excluyen los documentos
anulados aunque su registro auxiliar siga activo.

## Instalación y comprobación

Migraciones:

- `2026_11_13_000003_add_retention_reporting_trace`
- `2026_11_13_000004_register_retention_reports`
- `2026_11_13_000005_add_retention_report_third_party_detail_filter`

El despliegue utiliza el procedimiento habitual `php artisan migrate --force`.
En el entorno local se aplicaron únicamente las migraciones de este reporte. No se
aplicaron las otras migraciones pendientes de Compras y Ventas.

Pruebas principales: `tests/Contabilidad/RetencionesReportTest.php` y
`tests/Contabilidad/RegistroReporteRetencionesTest.php`. Cubren separación de
empresas, reconstrucción ambigua, períodos, anulaciones, filtros, naturaleza,
casillas, redondeo, devoluciones, asientos sin base, configuración anual, permisos,
registro idempotente, Excel y aislamiento del PDF. También se verificó la generación
real de PDF en el entorno local y la suite transaccional de ReteICA de Compras.
