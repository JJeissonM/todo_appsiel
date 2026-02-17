# SRS - Sistema de Facturacion Ventas POS

Version: 1.0  
Fecha: 2026-02-16  
Estado: Borrador para validacion funcional

## 1. Introduccion

### 1.1 Proposito
Definir los requisitos de software para migrar el modulo de Ventas POS actual a una nueva arquitectura con Backend en Laravel 12 y Frontend en ReactJS, manteniendo la funcionalidad operativa existente y mejorando mantenibilidad, estabilidad e integraciones.

### 1.2 Alcance
El sistema cubre la facturacion POS de mostrador, incluyendo:
- Construccion de factura (encabezado, lineas de item, totales).
- Gestion de cliente, vendedor y condiciones de pago.
- Gestion de medios de pago, anticipos/saldos a favor, propinas y comision de datafono.
- Integracion con pedidos de venta para facturar pedidos pendientes.
- Impresion de factura/comanda (navegador, servidor de impresion, APM) y prefactura.
- Soporte opcional de Factura Electronica (FE).
- Operaciones de caja asociadas (consultas de estado y documentos, ingresos/salidas).

### 1.3 Definiciones
- POS: Point of Sale.
- PDV: Punto de venta.
- FE: Factura Electronica.
- APM: Integracion de impresion por cliente/aplicacion externa.
- Idempotencia: evitar duplicidad de facturas por reintentos de red.

## 2. Referencias del sistema actual

- Vista principal: `appsiel/resources/views/ventas_pos/crud_factura.blade.php`
- Parciales POS: `appsiel/resources/views/ventas_pos/*`
- JS POS: `assets/js/ventas_pos/*`
- JS medios de recaudo: `assets/js/tesoreria/medios_recaudos.js`

## 3. Descripcion general

### 3.1 Perspectiva del producto
Aplicacion web transaccional de caja para operacion de venta rapida, con alta dependencia de:
- Configuracion por feature flags.
- Validaciones de negocio en tiempo real.
- Flujos de teclado/foco para uso continuo por cajeros.

### 3.2 Tipos de usuario
- Cajero POS.
- Supervisor/Administrador.
- Personal de caja/tesoreria.
- Usuario con permisos de anulacion/consulta.

### 3.3 Restricciones
- Backend obligatorio: Laravel 12.
- Frontend obligatorio: ReactJS.
- Debe conservar reglas criticas del POS existente.

## 4. Requisitos funcionales

### 4.1 Gestion de factura POS
- RF-001: El sistema debe permitir crear factura POS nueva.
- RF-002: El sistema debe permitir editar factura POS segun permisos y estado.
- RF-003: El sistema debe permitir crear factura a partir de pedido.
- RF-004: El sistema debe mantener encabezado de factura con fecha, vencimiento, PDV, caja y metadatos.
- RF-005: El sistema debe bloquear doble envio de guardado y garantizar idempotencia por `uniqid`/token de transaccion.

### 4.2 Gestion de items
- RF-010: El sistema debe permitir agregar item por busqueda, filtro rapido, vista tactil y modal de productos.
- RF-011: El sistema debe permitir eliminar item de la factura.
- RF-012: El sistema debe permitir edicion en linea de cantidad/precio unitario y precio total segun permisos.
- RF-013: El sistema debe calcular por linea: base impuesto, valor impuesto, descuento y total.
- RF-014: El sistema debe validar stock por bodega y fecha cuando aplique.
- RF-015: El sistema debe impedir venta por debajo del costo cuando configuracion lo exija.
- RF-016: El sistema debe impedir precio unitario cero y, segun configuracion, precio negativo.

### 4.3 Totales y calculo comercial
- RF-020: El sistema debe calcular subtotal, descuento total, impuestos, total factura y total cambio.
- RF-021: El sistema debe aplicar redondeo al peso/centena segun configuracion.
- RF-022: El sistema debe soportar valor de bolsas por categoria configurada.
- RF-023: El sistema debe actualizar totales en tiempo real al modificar lineas o pagos.

### 4.4 Clientes y vendedores
- RF-030: El sistema debe buscar y seleccionar clientes por autocompletado.
- RF-031: El sistema debe permitir crear cliente rapido sin salir del POS.
- RF-032: El sistema debe actualizar listas de precios/descuentos e impuestos al cambiar cliente.
- RF-033: El sistema debe impedir cambio de cliente si ya hay lineas y la lista de precios no coincide.
- RF-034: El sistema debe permitir seleccionar vendedor activo y asociarlo al documento.

### 4.5 Medios de pago y recaudos
- RF-040: El sistema debe permitir uno o varios medios de pago por factura.
- RF-041: El sistema debe manejar motivo, caja o cuenta bancaria por linea de pago.
- RF-042: El sistema debe calcular total de medios de pago y validar consistencia con total factura.
- RF-043: Si no existen lineas de pago, el sistema debe generar pago por defecto en efectivo para completar guardado.
- RF-044: El sistema debe permitir aplicar anticipos/saldos a favor del cliente como medio de pago.
- RF-045: El sistema debe impedir aplicar multiples bloques de anticipos simultaneos sin limpiar el anterior.

### 4.6 Propinas y datafono (opcionales)
- RF-050: El sistema debe permitir activar/desactivar modulo de propinas por configuracion.
- RF-051: El sistema debe calcular propina por porcentaje y permitir ajuste manual.
- RF-052: El sistema debe validar motivo tesoreria de propina y su coherencia con lineas de pago.
- RF-053: El sistema debe permitir activar/desactivar comision de datafono por configuracion.
- RF-054: El sistema debe calcular comision de datafono y validar motivo tesoreria requerido.
- RF-055: En caso de una sola linea de pago, el sistema debe poder separar automaticamente linea base y linea de recargo (propina/datafono).

### 4.7 Pedidos y operaciones de caja
- RF-060: El sistema debe listar pedidos pendientes para facturar por PDV.
- RF-061: El sistema debe cargar pedido completo a factura (cliente, lineas, descripcion).
- RF-062: El sistema debe permitir cancelar carga de pedido y resetear la ventana.
- RF-063: El sistema debe permitir anular pedido pendiente desde flujo de revision.
- RF-064: El sistema debe permitir registrar ingresos/salidas de caja desde modal operativo.
- RF-065: El sistema debe consultar estado del PDV por rango de fecha.
- RF-066: El sistema debe consultar facturas/documentos pendientes del PDV.

### 4.8 Guardado, anulacion y FE
- RF-070: El sistema debe guardar factura POS enviando encabezado, lineas, medios de pago y recargos.
- RF-071: El sistema debe mostrar respuesta operativa del guardado y consecutivo generado.
- RF-072: El sistema debe permitir anular factura POS segun permisos.
- RF-073: El sistema debe soportar anulacion de factura contabilizada con validacion de negocio.
- RF-074: El sistema debe permitir guardar como FE cuando modulo FE este activo y exista resolucion valida.
- RF-075: El sistema debe validar datos minimos de tercero para FE (documento, etc.).

### 4.9 Impresion
- RF-080: El sistema debe generar prefactura.
- RF-081: El sistema debe imprimir factura y/o comanda en navegador.
- RF-082: El sistema debe soportar envio a servidor de impresion externo.
- RF-083: El sistema debe soportar integracion APM para impresion.
- RF-084: El sistema debe soportar reglas de impresion automatica, pregunta al usuario o manual.

### 4.10 Cargue de archivo plano
- RF-090: El sistema debe permitir cargar archivo plano de lineas para facturacion.
- RF-091: El sistema debe recalcular totales tras cargar lineas del archivo.

### 4.11 Modo Offline POS
- RF-100: El sistema debe permitir operar facturacion POS sin conectividad a Internet.
- RF-101: En modo offline, el sistema debe permitir crear facturas, agregar lineas, calcular totales y registrar pagos localmente.
- RF-102: El sistema debe persistir localmente documentos pendientes de sincronizacion en almacenamiento durable del navegador (IndexedDB).
- RF-103: El sistema debe asignar identificador local temporal a cada factura offline y mantener trazabilidad con el identificador remoto al sincronizar.
- RF-104: El sistema debe sincronizar automaticamente al recuperar conectividad y tambien permitir sincronizacion manual por el usuario.
- RF-105: El sistema debe mostrar estado por documento: `Pendiente`, `Sincronizando`, `Sincronizado`, `Error`.
- RF-106: El sistema debe impedir anulaciones remotas en modo offline y encolar la solicitud cuando el negocio lo permita.
- RF-107: El sistema debe mantener cola de eventos offline para: facturas, pagos, anticipos aplicados, y operaciones de caja habilitadas para desconexion.
- RF-108: El sistema debe validar dependencias minimas en local (cliente, precios, impuestos, productos activos, motivos de recaudo) y bloquear guardado si falta catalogo critico.
- RF-109: El sistema debe conservar impresiones locales (prefactura/ticket) en modo offline segun capacidad del dispositivo.
- RF-110: El sistema debe registrar bitacora local de errores de sincronizacion para soporte.

## 5. Requisitos no funcionales

### 5.1 Rendimiento
- RNF-001: La respuesta de calculo en pantalla debe percibirse inmediata para operacion de caja.
- RNF-002: El guardado de factura debe manejar timeout configurable y feedback visible.

### 5.2 Disponibilidad y resiliencia
- RNF-010: El sistema debe tolerar errores de red con mensajes claros y recuperacion operativa.
- RNF-011: El sistema debe prevenir duplicidad por reintento de peticion.

### 5.3 Seguridad
- RNF-020: Toda operacion sensible debe estar controlada por permisos/roles.
- RNF-021: Toda API debe exigir autenticacion y autorizacion.
- RNF-022: El sistema debe protegerse frente a CSRF/XSS/validacion de payload.

### 5.4 Auditabilidad
- RNF-030: El sistema debe registrar eventos de guardado, anulacion, impresion y operaciones de caja.
- RNF-031: Debe existir trazabilidad por usuario, PDV, fecha/hora y consecutivo.

### 5.5 UX operacional POS
- RNF-040: El flujo debe optimizar uso por teclado (Enter, F2, ESC) y foco continuo.
- RNF-041: Debe haber retroalimentacion visual de estados (guardando, error, bloqueado, pendiente).

### 5.6 Requisitos no funcionales de Offline
- RNF-050: El cambio Online/Offline debe detectarse en menos de 5 segundos.
- RNF-051: El guardado offline de factura no debe tardar mas de 300 ms en condiciones normales del dispositivo.
- RNF-052: La sincronizacion debe ser idempotente y tolerante a reintentos.
- RNF-053: Los datos offline deben cifrarse en reposo cuando sea posible y protegerse por sesion de usuario.
- RNF-054: Debe existir politica de retencion local y limpieza segura de datos sincronizados.
- RNF-055: La aplicacion debe soportar recuperacion ante cierre inesperado del navegador sin perdida de documentos offline confirmados localmente.

## 6. Reglas de negocio clave

- RB-001: No se guarda factura sin lineas de producto.
- RB-002: El cambio de cliente con lista de precios distinta requiere limpiar lineas existentes.
- RB-003: Cuando se valida stock estricto, no se permite sobreventa.
- RB-004: Si venta bajo costo no esta permitida, bloquear linea.
- RB-005: El total de pagos debe ser coherente con el total factura mas recargos.
- RB-006: Propina/datafono requieren motivo tesoreria configurado.
- RB-007: En pagos con multiples lineas, debe existir linea de motivo especifico para propina/datafono.
- RB-008: FE solo disponible con modulo activo y resolucion vigente.
- RB-009: No se debe permitir numeracion fiscal definitiva offline; se usa consecutivo temporal local hasta sincronizar.
- RB-010: En conflicto de datos al sincronizar, prevalece la validacion de negocio del servidor y el documento queda en estado `Error` con motivo.
- RB-011: Una factura offline sincronizada no debe reenviarse si ya fue aceptada por el servidor (idempotencia por llave unica).

## 7. Requisitos de integracion (API)

### 7.1 Endpoints funcionales esperados
- RI-001: API para guardar factura POS (transaccional).
- RI-002: API para guardar factura FE desde POS.
- RI-003: API para consultar stock por item/bodega/fecha.
- RI-004: API para consultar pedidos pendientes y cargar pedido.
- RI-005: API para anular pedido.
- RI-006: API para consultar y aplicar anticipos/saldos a favor.
- RI-007: API para operaciones de caja (ingresos/salidas).
- RI-008: API para consultar estado PDV y documentos pendientes.
- RI-009: API para anulacion de factura POS y contabilizada.
- RI-010: API para envio de impresion a servidor/APM.
- RI-011: API para sincronizacion por lotes de facturas offline.
- RI-012: API para confirmacion de estado de sincronizacion por identificador local/remoto.
- RI-013: API para descarga incremental de catalogos requeridos por modo offline.

### 7.2 Contrato de guardado (alto nivel)
Payload minimo:
- Encabezado: PDV, caja, cliente, vendedor, fechas, observaciones, estado, tipo.
- Lineas: item, cantidad, precio unitario, descuentos, impuesto, totales, metadatos.
- Medios pago: medio, motivo, caja/banco, valor.
- Extras: propina, datafono, ajuste, anticipos, `uniqid`.

Respuesta minima:
- Id factura/documento.
- Consecutivo.
- Estado de impresion.
- Mensajes de validacion/advertencia.

## 8. Parametrizacion y feature flags

Se requiere migrar y homologar configuraciones actuales, incluyendo:
- `ventas_pos.manejar_propinas`
- `ventas_pos.manejar_datafono`
- `ventas_pos.modulo_fe_activo`
- `ventas_pos.permite_facturacion_con_archivo_plano`
- `ventas_pos.ocultar_cinta_de_busqueda_items`
- `ventas_pos.ocultar_boton_guardar_factura_pos`
- `ventas.permitir_venta_menor_costo`
- `ventas.permitir_inventarios_negativos`
- `ventas_pos.permitir_precio_unitario_negativo`
- `ventas_pos.tiempo_espera_guardar_factura`
- Otras configuraciones de impresion, redondeo, bolsas y acumulacion.

## 9. Criterios de aceptacion global

- CA-001: Un cajero puede facturar de inicio a fin sin recargar pagina.
- CA-002: Los totales calculados coinciden con la logica vigente para casos equivalentes.
- CA-003: No se generan facturas duplicadas por doble click o retry de red.
- CA-004: Las validaciones de negocio bloquean escenarios invalidos.
- CA-005: Las impresiones (manual y automatica) funcionan segun configuracion.
- CA-006: Los modulos opcionales (FE, propina, datafono, archivo plano) son activables por configuracion.
- CA-007: Permisos restringen acciones criticas (precios, anulaciones, consultas sensibles).
- CA-008: En perdida de Internet, el cajero puede facturar en modo offline y visualizar documentos pendientes.
- CA-009: Al recuperar Internet, los documentos offline se sincronizan sin duplicados y con trazabilidad completa.
- CA-010: Si una sincronizacion falla, el usuario ve el motivo y puede reintentar sin perder la data local.

## 9.1 Criterios de aceptacion especificos de Offline

- CAO-001: Desconectar red, crear 3 facturas y cerrar navegador; al reabrir, las 3 facturas deben seguir en cola local.
- CAO-002: Reconectar red; las 3 facturas deben pasar a `Sincronizado` y obtener consecutivo remoto.
- CAO-003: Reintentar sincronizacion de una factura ya sincronizada no debe crear duplicado.
- CAO-004: Si servidor rechaza una factura por validacion, debe quedar en `Error` con detalle y opcion de correccion/reintento.
- CAO-005: Los catalogos vencidos o incompletos deben bloquear operacion offline con mensaje explicito.

## 10. Fuera de alcance (esta fase)

- Rediseño contable profundo fuera de equivalencia funcional POS.
- Cambios fiscales/regulatorios no presentes en el sistema actual.
- Reingenieria completa de inventarios no requerida por el flujo POS.

## 11. Riesgos y dependencias

- RSK-001: Diferencias de calculo por redondeo e impuestos entre version actual y nueva.
- RSK-002: Riesgo de regresion en integraciones de impresion.
- RSK-003: Riesgo de duplicidad documental sin idempotencia robusta.
- RSK-004: Dependencia de calidad de datos maestros (clientes, precios, motivos, cajas).
- RSK-005: Riesgo de desactualizacion de catalogos en modo offline.
- RSK-006: Riesgo de conflictos al sincronizar operaciones concurrentes entre cajas/dispositivos.
- RSK-007: Riesgo de almacenamiento local sensible si no se define estrategia de seguridad y retencion.

## 12. Plan de validacion recomendado

- Pruebas unitarias de calculo (impuestos, descuentos, redondeos, propina, datafono).
- Pruebas de integracion API transaccional (guardado/anulacion/anticipos/pedidos).
- Pruebas E2E de caja (flujo completo con teclado y errores de red).
- Pruebas comparativas contra casos reales del sistema legado.

## 13. Diseno tecnico propuesto para modo Offline

### 13.1 Arquitectura objetivo

- Frontend React como PWA (Service Worker + App Shell cacheado).
- Motor de persistencia local con IndexedDB (wrapper recomendado: Dexie).
- Motor de sincronizacion (Sync Engine) desacoplado del UI.
- API Laravel 12 con endpoints idempotentes y versionados (`/api/v1/pos/*`).
- Canal de observabilidad (logs cliente + logs backend) para soporte.

Flujo general:
1. Usuario crea factura.
2. Si hay red: envio inmediato a API online.
3. Si no hay red: guardado local en IndexedDB con estado `Pendiente`.
4. Sync Engine detecta reconexion y sincroniza por lotes.
5. API responde con `remote_id`, `consecutivo`, `status`.
6. Documento local se marca `Sincronizado` o `Error`.

### 13.2 Modelo de datos local (IndexedDB)

Tablas/colecciones recomendadas:
- `catalog_products`
- `catalog_prices`
- `catalog_customers`
- `catalog_taxes`
- `catalog_payment_reasons`
- `catalog_cashboxes`
- `pos_invoices_offline`
- `pos_invoice_lines_offline`
- `pos_invoice_payments_offline`
- `pos_sync_queue`
- `pos_sync_log`

Campos minimos en `pos_invoices_offline`:
- `local_id` (UUID)
- `uniqid` (idempotency key)
- `pdv_id`, `cajero_id`, `cliente_id`, `vendedor_id`
- `fecha`, `fecha_vencimiento`
- `totales_json`
- `status` (`Pendiente`, `Sincronizando`, `Sincronizado`, `Error`)
- `retry_count`
- `last_error`
- `remote_id` (nullable)
- `remote_consecutivo` (nullable)
- `created_at_local`, `updated_at_local`

### 13.3 Estrategia de catalogos para offline

- Descarga incremental por `updated_at`/`version`.
- Validacion de frescura por TTL (ejemplo: 12 horas para precios y clientes).
- Si catalogo critico expirado, bloquear guardado offline con mensaje de accion.
- Mecanismo de "Pre-carga de turno" al abrir caja para minimizar riesgo offline.

### 13.4 Protocolo de sincronizacion

Reglas:
- Lotes pequenos (ejemplo 20 facturas por ciclo).
- Orden FIFO por `created_at_local`.
- Reintentos con backoff exponencial (ejemplo: 5s, 15s, 45s, 120s).
- Corte por error no recuperable (validacion de negocio) y continuar con siguientes.

Estados de cola:
- `queued`
- `processing`
- `done`
- `failed_retryable`
- `failed_final`

Operacion recomendada:
1. Tomar lote `queued`.
2. Marcar `processing`.
3. Enviar a `/api/v1/pos/offline/sync-batch` con `idempotency_key`.
4. Procesar respuesta item por item.
5. Actualizar estado local y registrar log.

### 13.5 Contrato API de sincronizacion (sugerido)

Request:
- `device_id`
- `user_id`
- `pdv_id`
- `batch`: lista de facturas offline completas
- `sent_at`

Response:
- `results[]` por factura:
  - `local_id`
  - `status` (`synced`, `rejected`, `duplicate`, `retry`)
  - `remote_id`
  - `remote_consecutivo`
  - `error_code`
  - `error_message`

### 13.6 Resolucion de conflictos

- Conflicto por duplicado: servidor retorna `duplicate` y referencia `remote_id`; cliente marca `Sincronizado`.
- Conflicto por validacion (stock, configuracion, tercero): cliente marca `Error` y requiere accion manual.
- Conflicto de catalogo desactualizado: forzar refresh de catalogos y reintento.
- Regla principal: la verdad fiscal/contable final la define el servidor.

### 13.7 Seguridad offline

- Asociar datos offline a sesion de usuario y PDV.
- Cifrar payload local cuando el stack lo permita.
- Limpiar datos sincronizados con politica (ejemplo: conservar 30 dias para auditoria local).
- Al cerrar sesion, invalidar acceso a datos offline de otro usuario.

### 13.8 Impresion en modo offline

- Prioridad 1: impresion local de ticket/prefactura via navegador.
- Si impresora de red no disponible, registrar evento en `pos_sync_log`.
- No bloquear guardado offline por falla de impresion.

### 13.9 Observabilidad y soporte

Metricas minimas:
- cantidad de facturas `Pendiente`
- edad maxima de pendientes
- tasa de exito de sincronizacion
- errores por `error_code`

Logs minimos:
- transicion de estado por `local_id`
- payload hash (no datos sensibles en texto plano)
- timestamp y usuario

### 13.10 Plan de implementacion por fases

Fase 1 (MVP Offline):
- Guardado offline de factura POS.
- Cola local + sincronizacion manual.
- Estados visuales por documento.

Fase 2:
- Sincronizacion automatica + backoff.
- Catalogos incrementales + bloqueo por expiracion critica.
- Logs y panel basico de diagnostico.

Fase 3:
- Manejo avanzado de conflictos.
- Mejoras de seguridad/cifrado local.
- Telemetria operacional completa.

## 14. Backlog tecnico estimable por sprint

Escala sugerida:
- SP 1: cambio pequeno
- SP 2: cambio simple
- SP 3: cambio medio
- SP 5: cambio complejo
- SP 8: cambio muy complejo

### 14.1 Sprint 1 - Base offline (MVP)

Backend (Laravel 12):
- T-BE-001 (SP 5): Crear endpoint `POST /api/v1/pos/offline/sync-batch` con validacion de payload y respuesta por item.
- T-BE-002 (SP 3): Implementar idempotencia por `uniqid` en guardado de factura POS.
- T-BE-003 (SP 3): Exponer endpoint de catalogos minimos para offline (`products`, `prices`, `customers`, `taxes`, `payment_reasons`).

Frontend (React):
- T-FE-001 (SP 5): Implementar IndexedDB (Dexie) y repositorios locales de factura, lineas y pagos.
- T-FE-002 (SP 3): Implementar detector online/offline y banner de estado.
- T-FE-003 (SP 5): Guardado offline de factura con estados `Pendiente/Error`.
- T-FE-004 (SP 3): Vista de cola offline con contador de pendientes.

QA:
- T-QA-001 (SP 3): Casos E2E de guardado offline sin red.
- T-QA-002 (SP 2): Prueba de persistencia tras cierre de navegador.
- T-QA-003 (SP 2): Validar no duplicidad en reintentos manuales.

Definicion de terminado (Sprint 1):
- Se pueden crear facturas offline y quedan persistidas localmente.
- Existe endpoint de sync por lote funcionando en ambiente de pruebas.
- Se visualiza estado de conectividad y cantidad de pendientes.

### 14.2 Sprint 2 - Sincronizacion automatica y catalogos

Backend (Laravel 12):
- T-BE-010 (SP 3): Endpoint de confirmacion de estado por `local_id/uniqid`.
- T-BE-011 (SP 5): Descarga incremental de catalogos por `updated_at`.
- T-BE-012 (SP 3): Estandarizar codigos de error de sync (`duplicate`, `rejected`, `retry`).

Frontend (React):
- T-FE-010 (SP 5): Sync Engine automatico con FIFO + lotes + backoff exponencial.
- T-FE-011 (SP 3): Validacion de frescura de catalogos y bloqueo operativo si falta catalogo critico.
- T-FE-012 (SP 3): Pantalla de detalle de error por documento y reintento manual.
- T-FE-013 (SP 2): Mapeo `local_id -> remote_id/consecutivo` en UI.

QA:
- T-QA-010 (SP 3): Pruebas de reconexion y sincronizacion automatica.
- T-QA-011 (SP 2): Pruebas de catalogo expirado/incompleto.
- T-QA-012 (SP 2): Pruebas de idempotencia (reenvios repetidos del mismo lote).

Definicion de terminado (Sprint 2):
- La sincronizacion automatica funciona al recuperar red.
- Se evita duplicidad documental por reintentos.
- Errores quedan trazables y reintentables por usuario.

### 14.3 Sprint 3 - Conflictos, seguridad y observabilidad

Backend (Laravel 12):
- T-BE-020 (SP 5): Politicas de resolucion de conflictos con respuesta deterministica por item.
- T-BE-021 (SP 3): Auditoria detallada de sync (usuario, PDV, hash payload, timestamps).
- T-BE-022 (SP 3): Endpoints de metricas operativas de sincronizacion.

Frontend (React):
- T-FE-020 (SP 5): Consola de diagnostico offline (filtros por estado/error_code).
- T-FE-021 (SP 3): Cifrado/obfuscacion de datos locales sensibles (segun capacidad tecnica).
- T-FE-022 (SP 3): Politica de retencion y limpieza de documentos sincronizados.
- T-FE-023 (SP 2): Export de log tecnico para soporte.

QA:
- T-QA-020 (SP 3): Matriz de conflictos (duplicado, validacion, catalogo desactualizado).
- T-QA-021 (SP 3): Pruebas de seguridad local y aislamiento por usuario.
- T-QA-022 (SP 2): Pruebas de rendimiento de cola con volumen alto.

Definicion de terminado (Sprint 3):
- Conflictos se gestionan con reglas claras y mensajes accionables.
- Existe trazabilidad completa de ciclo offline->sync.
- Operacion estable con volumen de facturacion esperado.

### 14.4 Dependencias y orden de ejecucion

1. `T-BE-001` + `T-BE-002` deben completarse antes de `T-FE-010`.
2. `T-FE-001` debe completarse antes de `T-FE-003` y `T-FE-010`.
3. `T-BE-011` y `T-FE-011` deben implementarse en paralelo.
4. QA E2E de cada sprint debe ejecutarse antes de promover a produccion.

### 14.5 Capacidad sugerida por sprint

Recomendacion para equipo mediano:
- Backend: 8 a 13 SP por sprint.
- Frontend: 10 a 15 SP por sprint.
- QA: 5 a 8 SP por sprint.

Con esta capacidad, el modo offline completo puede entregarse en 3 sprints.

## 15. Roadmap de sprints del proyecto completo

Supuesto de planificacion:
- Duracion de sprint: 2 semanas.
- Horizonte total: 10 sprints (20 semanas).
- Equipo base: Backend, Frontend, QA, apoyo funcional.

### 15.1 Plan maestro por sprint

Sprint 0 - Descubrimiento y base:
- Cierre de alcance funcional contra sistema legado.
- Arquitectura objetivo Laravel 12 + React + Offline.
- Ambientes DEV/QA y pipeline CI/CD base.
- Backlog refinado y criterios de aceptacion por modulo.

Sprint 1 - Fundaciones tecnicas:
- Estructura de proyecto, autenticacion y autorizacion.
- Base de UI POS React (layout, estado global, rutas).
- API base con manejo estandar de errores y validaciones.
- Feature flags iniciales.

Sprint 2 - Catalogos y reglas comerciales:
- Catalogos de productos/clientes/precios/descuentos/impuestos.
- Motor de calculo comercial en frontend y backend (consistencia).
- Validaciones de venta (precio, costo, impuestos, stock base).

Sprint 3 - Facturacion POS core (online):
- Flujo completo de creacion de factura POS.
- Gestion de lineas de item, edicion en linea y totales.
- Guardado transaccional online con idempotencia.
- Flujo de vendedor, cliente y condiciones de pago.

Sprint 4 - Medios de pago y caja:
- Multiples medios de pago con motivo/caja/banco.
- Cuadre de total factura vs pagos.
- Anticipos/saldos a favor en la factura.
- Ingresos/salidas y consultas operativas de PDV.

Sprint 5 - Pedidos y operacion restaurante:
- Revision de pedidos pendientes y cargar para facturar.
- Cancelacion/anulacion de pedido en flujo POS.
- Prefactura operativa y mejoras de flujo de atajos.

Sprint 6 - Impresion e integraciones:
- Impresion navegador.
- Impresion via servidor externo.
- Integracion APM para comanda y factura.
- Politicas de impresion automatica/pregunta/manual.

Sprint 7 - Modulos opcionales de negocio:
- Propinas.
- Comision de datafono.
- Facturacion de bolsas.
- Cargue por archivo plano.
- Permisos avanzados de cambio de precio/descuento.

Sprint 8 - Factura Electronica:
- Flujo de guardar FE desde POS.
- Validaciones de tercero y resolucion.
- Manejo de errores FE y trazabilidad.

Sprint 9 - Offline end-to-end:
- Cola local, guardado offline, sync manual/automatico.
- Resolucion de conflictos e idempotencia de sincronizacion.
- Catalogos incrementales y manejo de expiracion.
- Diagnostico de errores de sync y soporte operativo.

Sprint 10 - Hardening, UAT y salida a produccion:
- Pruebas de regresion completas y performance.
- Seguridad, auditoria y observabilidad final.
- Piloto controlado y correcciones de estabilizacion.
- Go-Live y plan de soporte post-produccion.

### 15.2 Hitos de control

1. Hito A (fin Sprint 3): POS online facturable en ambiente QA.
2. Hito B (fin Sprint 7): Paridad funcional alta con modulo legado.
3. Hito C (fin Sprint 9): Offline operativo con sincronizacion robusta.
4. Hito D (fin Sprint 10): Produccion estabilizada.

### 15.3 Dependencias criticas

1. El motor de calculo comercial debe cerrarse antes de FE y Offline.
2. Idempotencia backend es prerequisito para sincronizacion offline.
3. Integraciones de impresion deben validarse antes de piloto.
4. FE requiere definicion y pruebas de resoluciones activas.

### 15.4 Riesgos de calendario

- Deriva de alcance por cambios funcionales no priorizados.
- Retrasos por integraciones externas (impresion/FE).
- Gaps de datos maestros para operacion POS y offline.
- Cobertura de pruebas insuficiente en escenarios de reconexion.
