# Resumen de funcionalidades y parches (enero-marzo 2026)

Periodo analizado: **01-ene-2026 a 31-mar-2026**  
Fuente: historial de commits Git del repositorio

## Panorama general

- Total commits en el periodo: **90**
- Enero 2026: **15** commits
- Febrero 2026: **50** commits
- Marzo 2026: **25** commits

## Enero 2026

### Funcionalidades

- Mejoras en **ventas POS** para manejo de impoconsumo en contabilizacion, facturacion electronica y reportes.
- Finalizacion de funcionalidad de **impresion de codigos de barras** y mejora de busqueda por codigo de barras en ventas estandar.
- Creacion de helper `enlace_show_documento()`.
- Nuevo **perfil de Empleado**.
- Mejoras en modulos academicos:
  - Gestion de aspectos de evaluacion en configuracion y planeacion academica.
  - Sugerencias de estudiantes en nivelaciones.
  - Verificacion unica para encabezados en calificaciones.

### Parches y ajustes

- Correccion en `InvoicingService` para `crear_registros_documento_pos`.
- Correcciones para agregar `impuesto_id` cuando no llega en payload POS y en conversion a factura electronica.
- Ajustes en impresion de facturas (cambios labrl INC).

## Febrero 2026

### Funcionalidades

- Integracion de **Appsiel Print Manager (APM)** y cliente WebSocket para pruebas de conexion.
- Implementacion de **anulacion FUEC** con permisos y cambios de UI.
- Implementacion de **filtrado por usuario** en modelos/servicios (incluyendo tesoreria y movimientos).
- Funcionalidad para **excluir empleados de nomina electronica**.
- Nueva funcionalidad academica:
  - Tutores academicos y permisos.
  - Configuracion de guias academicas con cantidades por defecto.
- Tesoreria y reportes:
  - Correccion de medio de recaudo en movimientos.
  - Validacion exclusiva Caja/Cuenta bancaria en reportes.
  - Reportes de guias y reportes de pagos estudiantiles.
- Inventarios:
  - Servicio `AjustarSaldosBodegaService` integrado en inventario fisico.
  - Fusion de items duplicados en `InvDocRegistros`.
- Nomina:
  - Clonacion de contratos de nomina.
  - Seeder y vistas para actualizacion de sueldos.
  - Exportacion de empleados en XLSX.
- SIESA:
  - Nuevas migraciones/datasets y vista de tabla de descuentos con filtros/exportacion.
- Bancos:
  - Gestion de cuentas bancarias y chequeras.

### Parches y ajustes

- Correcciones de referencias nulas en configuraciones de desarme y ajuste de costos en lineas.
- Fix de import `PHPExcel_IOFactory` y ajustes menores en `NominaController`.
- Fix en indice de proveedores.
- Ajustes de bloqueo de mesa, manejo de errores y bloqueo de botones para evitar doble envio en POS/restaurante.
- Refinamientos de interfaz (fechas de creacion, alertas de vencimiento de resolucion, etiquetas de porcentaje, tooltips y mejoras visuales).
- Refactors de estructura de codigo y servicio contable.

## Marzo 2026

### Funcionalidades

- Implementacion de **Cotizante 51**:
  - Servicio/controlador para gestion de dias laborados.
  - Integracion con `NomContrato`/`TiempoLaborado`.
  - Nuevos campos y actualizaciones en migraciones/seeders.
- Facturacion POS:
  - Auditoria de ediciones y reutilizacion en `FacturaPosController`.
  - Reconstruccion de movimientos de facturas con modal de confirmacion.
- Tesoreria:
  - Caja por defecto para transferencias en efectivo.
  - Ordenamiento de movimientos por fecha.
  - Mejoras de contexto de usuario en `TesoCaja`.
- Contratos/FUEC:
  - Manejo dinamico de representante legal y mejoras de redireccion.
- Ventas/Inventario y restaurante:
  - Mejora de procesamiento de pedidos y manejo de errores.
  - Ajustes de validacion en pedidos y `doble_click.js` para paquetes con materiales ocultos.

### Parches y ajustes

- Correcciones en validaciones de lineas de pedidos y `cocina_index` ausente en URL.
- Fix de seeders.
- Fix de conexion APM en `pedidos_restaurantes`.
- Refactors para simplificar logica de caja, redondeo y horas laborales dinamicas.
- Validacion reforzada en cuenta por pagar de `Proveedor` con excepcion si no existe cuenta valida.

## Nota sobre trazabilidad

- Se detectaron varios commits con mensaje `idem`, sin detalle funcional explicito.
- El resumen prioriza commits con descripcion clara; los `idem` se consideran ajustes complementarios dentro de los bloques del mismo periodo.
