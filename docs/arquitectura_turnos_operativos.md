# Turnos operativos en Appsiel 1.0

## Principios y modos de operación

`core_turnos_operativos` es la fuente de verdad de una sesión operacional. La
pertenencia se expresa con `turno_operativo_id`: ni la fecha del documento, ni la
hora, ni `created_at` determinan el turno. `created_at` nunca debe cambiarse para
acomodar una operación en un periodo.

- `TRADICIONAL`: es el modo predeterminado cuando no hay configuración. Acepta
  históricos con turno nulo y conserva aperturas, cierres, reportes, filtros por
  fecha/hora y parches heredados.
- `TURNOS`: una operación incluida en el alcance debe recibir un turno válido. Si
  no hay turno abierto, se genera un error funcional; no se abre uno automáticamente.
- Los registros históricos con FK nula no se completan por aproximación ni se
  invalidan al activar posteriormente una configuración.

La configuración efectiva se busca desde el contexto y módulo más específicos
hasta la regla global de empresa. La propagación de una operación compuesta tiene
prioridad sobre el modo individual de un módulo derivado: si el documento origen
ya tiene turno, sus movimientos integrados conservan ese mismo turno.

Una configuración no puede cambiar el modo efectivo de ningún módulo integrado
mientras exista un turno abierto en el contexto afectado. La comparación se hace
antes y después de la regla candidata, por lo que también cubre reglas globales,
sobrescrituras por módulo/PDV, herencia y eliminación de configuraciones. La
apertura y la configuración toman el mismo bloqueo de empresa para evitar carreras.
Tampoco se activa `TURNOS` sobre un PDV que figure abierto en el modelo tradicional.

## Resolución estricta

El orden autoritativo es:

1. `TurnoContext` de la operación actual.
2. FK explícita validada contra empresa, contexto y estado.
3. Relación persistida con el documento origen.
4. Identidad documental exacta: empresa, transacción, tipo y consecutivo.
5. Turno abierto del contexto, únicamente cuando ese alcance está en `TURNOS`.
6. Compatibilidad tradicional; nunca reconstrucción horaria en modo `TURNOS`.

Una operación normal sólo admite un turno `ABIERTO`. Un derivado persistido o un
reintento puede conservar el turno original ya cerrado. Un turno de otra empresa o
contexto siempre se rechaza. Para ventas estándar sin PDV, la activación global del
módulo exige que el llamador propague `TurnoContext` o una FK; una venta sin contexto
queda fuera de un alcance configurado únicamente para un PDV concreto.

Los fallos del resolver escriben `turnos.assignment_failed` en el log existente,
con empresa, módulo, contexto, tipo/ID de operación, turno recibido/esperado,
estado y fuente. Las fuentes normalizadas son `CONTEXT`, `EXPLICIT_FK`, `ORIGIN`,
`DOCUMENT_IDENTITY`, `OPEN_CONTEXT` y `TRADITIONAL`; no se registran payloads ni
datos personales del documento.

## Contextos y operaciones compuestas

El núcleo trabaja con `contexto_tipo` + `contexto_id`. PDV es la primera integración,
no una suposición interna. `TurnoManager::openContext`, `currentForContext` y `close`
permiten integrar caja, recepción, terminal, bodega u otros contextos sin añadir
reglas específicas al gestor.

Durante una venta POS, `TurnoContext` envuelve los pasos derivados. Tesorería e
Inventario también recuperan el turno desde el encabezado persistido, lo que protege
flujos heredados que no comparten la misma pila de llamadas. La conversión electrónica
preserva la FK del documento origen y verifica que no sea recalculada.

En Hotelería, estadía, turno y evento permanecen separados. El pedido conserva el
turno de creación, cada `hotel_order_line` conserva el turno de su cargo, el recaudo
propaga el turno vigente y cada factura usa el turno de su evento. Por ello una sola
estadía puede contener cargos y pagos de varios turnos.

El arqueo de caja es una operación de control deliberadamente distinta de una
operación normal: selecciona una FK de turno explícita y validada contra empresa y
PDV, y por ello puede consultar un turno cerrado o auditado. En `TURNOS` su interfaz
lista cada turno de la fecha operativa y nunca fusiona la primera apertura con el
último cierre. En `TRADICIONAL` conserva el rango editable histórico.

### Hecho original frente a nueva operación

La herencia no depende sólo de que dos documentos estén relacionados:

| Flujo | Regla |
|---|---|
| Venta → inventario/tesorería derivados | Hereda obligatoriamente el turno de la venta |
| Documento → conversión/envío electrónico | Hereda obligatoriamente; es procesamiento técnico |
| Reintento/job del mismo documento | Hereda obligatoriamente mediante `TurnoEnvelope` |
| Cargo hotelero agregado a pedido abierto | Usa el turno vigente del nuevo cargo |
| Pago o recaudo hotelero posterior | Usa el turno vigente del pago |
| Checkout/factura comercial posterior | Usa el turno vigente de facturación |
| Ajuste histórico autorizado | Conserva el turno afectado y registra la fecha real del ajuste |

Una factura de checkout no reescribe los turnos del pedido, anticipos o cargos. Si
el flujo sólo transforma técnicamente una factura ya creada, conserva su FK.

## Procesos diferidos

`TurnoContext` es memoria de una ejecución, no almacenamiento para colas.
`TurnoEnvelope::fromOrigin($modelo)` serializa:

```php
[
    'turno_operativo_id' => 15,
    'origin_type' => App\VentasPos\FacturaPos::class,
    'origin_id' => 220,
]
```

El job o reintento reconstruye el sobre con `fromArray()` y ejecuta `run()`. Antes
de restaurar el contexto se relee el origen y se comprueba que su FK siga coincidiendo.
Así un proceso ejecutado después del cierre conserva el turno original y nunca usa
la hora actual. Los comandos históricos que sólo actualizan documentos existentes
deben dejar intacta la FK; la conversión diferida de facturas estudiantiles ya lo
verifica expresamente.

## Estados y auditoría

Transiciones admitidas:

```text
ABIERTO -> CERRADO -> AUDITANDO -> AUDITADO
                \--------------------------> ABIERTO (reapertura excepcional)
                              AUDITADO ----> ABIERTO (reapertura excepcional)
```

- Sólo `ABIERTO` permite operaciones normales.
- `CERRADO` permite ajustes mediante `assignAdjustment`.
- `AUDITANDO` no admite operaciones ni ajustes.
- `AUDITADO` es inmutable; sólo admite un ajuste trazado o reapertura excepcional.
- Los cambios de estado directos en el modelo son rechazados. Deben pasar por
  `TurnoManager` con usuario, motivo cuando corresponde y bloqueo transaccional.
- Una reapertura registra el estado anterior y los datos del cierre que se limpian.
- Un ajuste exige motivo y usuario, conserva su `created_at` real y genera evento.
- La empresa, contexto, PDV/caja, código, apertura y `fecha_operativa` son identidad
  inmutable después de crear el turno. No existe corrección silenciosa ni formulario
  administrativo para cambiarlos.

`core_turno_eventos` registra apertura, cierre, reapertura, inicio/fin de auditoría,
ajustes, usuario, entidad afectada, motivo, estados y metadatos. El índice de línea
de tiempo soporta consultas por turno y fecha del evento.

Las transiciones toman bloqueo de fila y transacción. Un segundo cierre, inicio/fin
de auditoría o reapertura se rechaza de manera controlada sin crear otro evento. Los
ajustes usan una clave idempotente en sus metadatos; para entidades existentes se
deriva de entidad, turno, usuario y motivo, y para entidades nuevas el llamador debe
proporcionarla. La reapertura limpia los campos del estado actual (`cerrado_en`,
`cerrado_por`, `saldo_cierre`), pero el evento de cierre conserva usuario, motivo,
fecha y entidad de cierre.

## Configuración y grupos relacionados

La activación de producción debe realizarse con
`TurnoConfigurationService::configure()`, no con SQL directo. El servicio valida
alcance y capacidad del módulo, limpia caché y devuelve advertencias de modos mixtos.

Los módulos y grupos se declaran en `config/turnos.php`. Un grupo puede advertir o
impedir modos mixtos con `enforce_uniform_mode`. Actualmente los grupos POS y Hotel
advierten porque los derivados ya integrados conservan la FK aun si su interfaz
continúa tradicional. Un módulo marcado `integrated=false` no puede activarse en
`TURNOS`. Para un flujo futuro basta declarar el módulo/grupo, añadir la FK y el
trait/resolver, y propagar el contexto.

### Catálogos administrativos

El seeder `TurnosAdminCrudSeeder` registra en la aplicación **Configuración** tres
catálogos basados en el CRUD genérico:

- **Configuración de turnos operativos**: permite crear, modificar y eliminar
  alcances. Las escrituras usan `TurnoConfigurationService`, están limitadas a la
  empresa activa y exigen el permiso `turnos.configuraciones.gestionar`.
- **Turnos operativos**: consulta de sólo lectura con el permiso
  `turnos.operativos.consultar`. Abrir, cerrar, auditar o reabrir nunca se hace por
  CRUD; continúa siendo responsabilidad de `TurnoManager` y de los flujos POS.
- **Auditoría de turnos**: consulta inmutable de `core_turno_eventos` con el permiso
  `turnos.eventos.consultar`.

El seeder es idempotente y concede los permisos a `SuperAdmin` y `Administrador`.
Puede ejecutarse aisladamente después de las migraciones:

```bash
composer dump-autoload
php artisan db:seed --class=TurnosAdminCrudSeeder
```

La identidad del alcance (empresa, módulo y contexto) queda bloqueada al editar;
para cambiarla se crea otro alcance. Desactivar o eliminar sigue siendo rechazado
si cambia el modo efectivo de un turno abierto o de una apertura tradicional.

### Selector de turno en transacciones

`TurnoFormService` extiende los campos del CRUD genérico sin agregar el campo a
instalaciones tradicionales. En `create`, sólo los modelos declarados en
`turnos.manual_assignment_models` muestran un selector con búsqueda AJAX. El HTML
inicial no serializa el histórico: el backend consulta como máximo 100 candidatos y
devuelve hasta 20 coincidencias válidas por búsqueda, limitadas por empresa, módulo,
PDV y permisos. Puede buscarse por código, fecha, estado o nombre del PDV. En `edit`,
el único turno persistido se muestra deshabilitado e inmutable.

Los usuarios con `turnos.ajustes.registrar` también pueden seleccionar turnos
`CERRADO` o `AUDITADO` al crear una corrección. En ese caso el formulario exige
`turno_ajuste_motivo`, conserva el `created_at` real de la nueva operación y genera
un evento `AJUSTE_POSTERIOR` con usuario, motivo y entidad afectada. El permiso se
concede inicialmente sólo a `SuperAdmin` y `Administrador`. Los perfiles de caja
no ven históricos y reciben automáticamente el único turno abierto de su PDV.
La búsqueda tiene espera corta (`debounce`), cancela la petición anterior y limpia
la FK oculta si se cambia el texto o el PDV, evitando guardar una selección obsoleta.

El selector es una ayuda de captura, no la garantía de integridad: al guardar,
`HasTurnoOperativo` y `TurnoAssignmentResolver` vuelven a validar empresa, contexto,
estado y origen. Un turno de otro PDV o contradictorio con el documento origen se
rechaza; un turno cerrado sólo se acepta mediante el flujo explícito de ajuste. Los
errores web vuelven al formulario con un mensaje funcional y los errores AJAX
responden `422`. Los derivados técnicos no se declaran como selección manual y
heredan el turno del origen.

## Matriz de módulos

| Módulo | Estado | Alcance actual |
|---|---|---|
| Ventas POS | Integrado | Apertura, cierre, factura y operación compuesta |
| Ventas estándar | Integrado | Encabezado, contexto explícito y derivados |
| Facturación electrónica | Integrado | Hereda y verifica el turno del origen |
| Inventarios | Integrado | Encabezados y movimientos |
| Tesorería | Integrado | Movimientos, recaudos y arqueos por FK |
| Hotelería | Integrado | Pedidos, cargos por línea, facturas y pagos |
| Restaurantes fuera del flujo POS | Parcial | Rutas POS cubiertas; procesos autónomos pendientes |
| Compras, CxP, CxC, Contabilidad | Pendiente | Continúan exclusivamente tradicionales |
| Producción | Pendiente | Continúa tradicional |
| Nómina | Exclusivamente tradicional | Su concepto laboral de turno es independiente |

## Migraciones y compatibilidad

Las FKs de movimientos son anulables para no bloquear históricos. No hay backfill.
La clave única `clave_contexto_abierto` sólo existe mientras el turno está abierto;
al cerrar queda nula, permitiendo múltiples turnos secuenciales. Los índices cubren
contexto/estado, empresa/fecha operativa, FK de movimientos y línea de tiempo de
auditoría. El rollback elimina únicamente columnas, índices y tablas creados por
estas migraciones.

No se añadieron constraints físicas desde todas las tablas heredadas porque algunas
instalaciones históricas tienen motores o catálogos sin integridad referencial
homogénea. La validación estricta se ejecuta en los modelos y servicios integrados;
las restricciones físicas pueden incorporarse por instalación tras auditar datos.

## Escapes y deuda técnica detectada

- `RunSqlFileWithChecks` y el proceso genérico de importación SQL pueden insertar en
  cualquier tabla sin eventos Eloquent. Son herramientas administrativas y deben
  considerarse fuera del API operacional; en producción requieren acceso restringido.
- `CorregirMedioRecaudoMovimientos` actualiza movimientos existentes por query
  builder. No crea movimientos ni cambia turno, pero debe mantener esa limitación.
- Persisten reportes especializados basados en fecha/hora. Son válidos sólo para
  históricos y alcances tradicionales; deben migrarse gradualmente a FK.
- Contabilidad, CxC y CxP reciben datos de operaciones integradas, pero sus propias
  tablas aún no persisten turno. No pueden declararse `TURNOS` hasta integrarse.
- Compras, producción, nómina y procesos autónomos de restaurante permanecen
  pendientes. Sus creaciones actuales son deliberadamente tradicionales.
- Los inserts directos futuros sobre tablas integradas eluden las garantías. La
  regla de desarrollo es usar modelos con `HasTurnoOperativo` o un servicio que
  restaure `TurnoEnvelope`.
- La revisión de escapes no encontró inserts directos operacionales adicionales
  sobre las tablas integradas. `TiendaController`, reportes de Hotel y Factura POS
  sólo consultan/actualizan relaciones; `CorregirMedioRecaudoMovimientos` sólo
  corrige filas existentes. Permanecen como bypass las herramientas SQL genéricas.
- `PagoAutomaticoNominaService` crea `TesoDocEncabezado` mediante Eloquent. En un
  piloto exacto por PDV permanece fuera del contexto; una regla global de Tesorería
  sí lo incluiría y por eso no debe usarse hasta definir explícitamente esa frontera.

El parche histórico de Tesorería que alinea `created_at` con un cierre permanece
exclusivamente en modo tradicional. La llamada duplicada del recaudo general fue
retirada; el modelo es ahora el único punto que decide entre FK estricta y parche
heredado.

`TesoDocEncabezado` también aplica `HasTurnoOperativo`: en operaciones compuestas
recibe el contexto propagado; un documento autónomo sin PDV sólo queda dentro del
piloto si su llamador proporciona contexto explícito.

## Diagnóstico previo al piloto

El comando de sólo lectura:

```bash
php artisan turnos:diagnosticar-piloto EMPRESA_ID pdv PDV_ID --dias=7
php artisan turnos:diagnosticar-piloto EMPRESA_ID pdv PDV_ID --dias=7 --json
```

muestra configuración efectiva, integración por módulo, grupos mixtos, turno
abierto, PDV tradicional incompatible, conteos/muestras recientes sin FK y la lista
de consultas heredadas. Sus fuentes se declaran en `config/turnos.php` para poder
ampliarlas sin modificar el núcleo. El procedimiento operativo está en
`docs/piloto_turnos_operativos.md`.
