# Certificados de Retefuente e ICA

Proceso: `contab_certificados_retenciones?id=<aplicación contabilidad>`. La migración registra el enlace **Certificados de Retefuente e ICA** en Procesos (o como entrada principal si ese menú no existe).

## Uso

1. Seleccionar Retefuente o ICA, período anual, bimestral (dos meses) o entre fechas. Un rango de varios años produce certificados separados por año gravable.
2. Escoger todos los proveedores con operaciones, un tercero o una clase de proveedores.
3. Incluir todas las compras contabilizadas o únicamente documentos con retención del impuesto seleccionado. El total de compras corresponde al valor completo del documento con IVA; la base retenida es independiente y procede del registro de retención. Una factura con varias tarifas se suma una sola vez al total de compras.
   **Mostrar detalle de operaciones: Sí/No** controla la tabla de documentos en la previsualización y el PDF, incluido el adjunto enviado por correo. Por defecto es Sí. Elegir No conserva el resumen por concepto/tarifa y todos los totales.
4. Indicar ciudad de consignación y seleccionar la **Firma autorizada** mediante autocomplete (nombre, identificación o cargo). Las opciones proceden de `core_firmas_autorizadas`, únicamente activas de la empresa actual y con tercero de la misma empresa. Nombre, identificación, cargo e imagen se toman del catálogo. Para ICA, indicar municipio, código DANE y norma municipal. Si los registros históricos carecen de municipio, su asignación exige confirmación explícita del responsable.
5. Previsualizar cada certificado. La aplicación muestra bloqueos por información incompleta. Las consultas sin emitir vencen a las 24 horas; las correcciones de datos contables posteriores a la consulta exigen previsualizar nuevamente.
6. Descargar PDF individual o seleccionar certificados y descargar un ZIP (máximo 200) con un PDF por proveedor/año. La primera descarga emite y archiva el PDF.
7. Enviar seleccionados. Cada envío usa únicamente el correo del proveedor guardado en la previsualización y su propio PDF. Se presentan enviados, fallidos y restantes. Los fallos pueden reintentarse; un certificado ya enviado no se vuelve a enviar automáticamente. El historial permite recuperar los últimos 50 emitidos por el usuario.

Para ICA o Retefuente con firma manual, la firma autorizada debe tener una imagen adjunta válida en el catálogo (`storage/app/firmas_autorizadas`, PNG/JPG/GIF, hasta 2 MB y 3000 × 3000 píxeles). Se comprueba el archivo real; no basta con tener un nombre de imagen registrado. Si falta, no permite previsualizar ni emitir. Ya no se carga una firma desde el formulario del certificado. Retefuente permite emisión por computador sin imagen sólo cuando la empresa está registrada como persona jurídica. Una firma modificada o desactivada después de previsualizar exige una nueva consulta antes de emitir; los PDF ya emitidos conservan su contenido histórico. Los certificados con retención neta cero se identifican como constancia de operaciones, no como acreditación de una retención inexistente.

## Fuentes de datos y alcance

- Fuente: `contab_registros_retenciones` y clasificación anual del reporte existente. Sólo retenciones practicadas vinculadas a documentos de compras de la empresa activa; los certificados laborales permanecen en Nómina. Los asientos suplementarios del reporte general no se certifican: carecen de base y tarifa verificables y pueden repetir auxiliares si cambió el catálogo de cuentas. Para certificar retenciones manuales se debe completar su registro auxiliar.
- Compras y documentos soporte: transacciones 25 y 48. Devoluciones de compras: transacción 36, con signo negativo. Se excluyen documentos anulados, operaciones sin contabilización y documentos ajenos a la empresa. El grupo corresponde a `compras_proveedores.clase_proveedor_id`.
- Bases y valores se acumulan en centavos por concepto y tarifa; ICA presenta tarifa en porcentaje y por mil. No se inventan bases ni se certifican impuestos sin clasificar o conceptos sin descripción. El concepto puede ser el nombre descriptivo de la retención; no exige una casilla del formulario 350.
- Los datos se congelan al previsualizar; la primera emisión verifica que continúen vigentes. Después de emitir, las descargas y correos reutilizan el PDF archivado con verificación SHA-256. Los asientos contables y auxiliares fuente no se modifican.
- Acceso separado por empresa y usuario. Permisos: `contab_certificados_retenciones` y `contab_enviar_certificados_retenciones`; se asignan inicialmente a SuperAdmin y Administrador. Operaciones de emisión/envío por POST con CSRF.
- Correo: mismo transporte Laravel y plantilla `layouts.cuerpo_email` que Nómina. El adjunto proviene del archivo emitido; los errores no interrumpen los demás destinatarios. «Enviado» significa aceptado por el transporte, no confirmación de recepción en la bandeja del proveedor.

## Fundamento y configuración territorial

- [Estatuto Tributario, artículo 381](https://normograma.dian.gov.co/dian/compilacion/docs/estatuto_tributario.htm): certificado anual de conceptos distintos a los laborales, año gravable, ciudad de consignación, nombre/NIT/dirección del retenedor, identificación del beneficiario, monto y concepto del pago sujeto, concepto y cuantía retenida y firma. También contempla certificado por retención a solicitud del beneficiario. Los períodos bimestrales/entre fechas son certificados parciales, no sustituyen automáticamente la obligación anual.
- [Decreto 1625 de 2016, artículo 1.6.1.12.12](https://normograma.dian.gov.co/dian/compilacion/docs/decreto_1625_2016.htm): emisión por computador sin firma autógrafa para personas jurídicas.
- ICA es municipal, no un certificado nacional uniforme de la DIAN. [Secretaría Distrital de Hacienda de Bogotá: requisitos del certificado](https://www.haciendabogota.gov.co/es/noticias/ultima-fecha-de-reteica-2025-agentes-retenedores-tienen-plazo-hasta-el-16-de-enero). La periodicidad, norma local y eventual autorización de mecanismos automáticos deben verificarse en cada jurisdicción. La aplicación no presupone que las reglas de Bogotá se apliquen a Valledupar.

## Despliegue y verificación

Aplicar en orden las migraciones `2026_11_19_000001_create_retention_certificates.php` y `2026_11_19_000002_register_retention_certificates_process.php`. Requieren las migraciones del reporte de retenciones existentes. Se crean `contab_certificados_retenciones` y `contab_certificados_envios`, además de los permisos y la entrada del menú. Requiere almacenamiento local privado escribible, Dompdf, GD, ZipArchive y el correo configurado del sistema. No guarda archivos en el disco público.

Pruebas: `php vendor/bin/phpunit tests/Contabilidad/CertificadosRetencionesTest.php`. Usan SQLite en memoria, PDF real, ZIP real y transporte simulado: no envían correos ni modifican producción.
