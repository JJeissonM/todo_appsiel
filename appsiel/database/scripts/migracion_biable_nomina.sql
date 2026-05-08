/* ==========================================================
   Migracion Biable legacy -> Appsiel
   Origen:  biable_migracion
   Destino: col_bilingue (DB_DATABASE en .env)
   Compatible con MySQL 5.7.
   ========================================================== */

SET NAMES utf8mb4;
SET @OLD_FOREIGN_KEY_CHECKS := @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS := 0;

SET @CORE_EMPRESA_ID := 1;
SET @CORE_TIPO_TRANSACCION_NOMINA_ID := 14;

/* ==========================================================
   1) CATALOGOS BASE
   ========================================================== */

INSERT INTO col_bilingue.core_tipos_docs_id (id, descripcion, abreviatura)
VALUES
(1,'Cedula de ciudadania','CC'),
(2,'NIT','NIT')
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), abreviatura=VALUES(abreviatura);

INSERT INTO col_bilingue.nom_modos_liquidacion (id, descripcion, detalle, estado, created_at, updated_at)
VALUES
(1,'Tiempo Laborado','Sueldo, jornales, horas extra y recargos liquidados por tiempo laborado','Activo',NOW(),NOW()),
(2,'Manual','Conceptos migrados cuyo valor historico se conserva manualmente','Activo',NOW(),NOW()),
(3,'Cuota','Descuentos liquidados por cuota','Activo',NOW(),NOW()),
(4,'Prestamo','Descuentos asociados a prestamos','Activo',NOW(),NOW()),
(5,'Cruce de saldos de CxC','Cruce de saldos de cuentas por cobrar','Activo',NOW(),NOW()),
(6,'Auxilio de transporte','Auxilio legal o convencional de transporte','Activo',NOW(),NOW()),
(7,'Tiempo NO Laborado','Incapacidades, licencias, suspensiones, permisos y ausencias','Activo',NOW(),NOW()),
(8,'Seguridad social','Seguridad social general','Activo',NOW(),NOW()),
(9,'Prestaciones sociales','Prestaciones sociales generales','Activo',NOW(),NOW()),
(10,'FondoSolidaridadPensional','Fondo de solidaridad pensional','Activo',NOW(),NOW()),
(11,'Retefuente','Retencion en la fuente de empleados','Activo',NOW(),NOW()),
(12,'Salud obligatoria','Aportes obligatorios a salud','Activo',NOW(),NOW()),
(13,'Pension obligatoria','Aportes obligatorios a pension','Activo',NOW(),NOW()),
(14,'Prima Legal Pagada semestralmente','Prima de servicios','Activo',NOW(),NOW()),
(15,'Cesantias consignadas','Cesantias consignadas al fondo','Activo',NOW(),NOW()),
(16,'Intereses de cesantias','Intereses sobre cesantias','Activo',NOW(),NOW()),
(17,'Cesantias pagadas','Cesantias pagadas al empleado','Activo',NOW(),NOW()),
(18,'Parafiscales','SENA, ICBF y caja de compensacion','Activo',NOW(),NOW()),
(19,'PrimaAntiguedad','Prima o bonificacion por antiguedad','Activo',NOW(),NOW())
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), detalle=VALUES(detalle), estado=VALUES(estado);

INSERT INTO col_bilingue.nom_agrupaciones_conceptos
(id, core_empresa_id, descripcion, nombre_corto, estado, created_at, updated_at)
VALUES (1,@CORE_EMPRESA_ID,'General migracion Biable','GEN','Activo',NOW(),NOW())
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), nombre_corto=VALUES(nombre_corto), estado=VALUES(estado);

INSERT INTO col_bilingue.nom_clases_riesgos_laborales
(id, descripcion, detalle, porcentaje_liquidacion, estado, created_at, updated_at)
VALUES (1,'Sin definir','Migracion Biable: pendiente parametrizar',0,'Activo',NOW(),NOW())
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), detalle=VALUES(detalle), estado=VALUES(estado);

INSERT INTO col_bilingue.nom_cargos (id, descripcion, estado, cargo_padre_id, rango_salarial_id, created_at, updated_at)
VALUES (1,'RAIZ','Activo',1,0,NOW(),NOW())
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), estado=VALUES(estado);

INSERT INTO col_bilingue.nom_grupos_empleados
(id, core_empresa_id, grupo_padre_id, descripcion, nombre_corto, estado, created_at, updated_at)
VALUES (1,@CORE_EMPRESA_ID,1,'RAIZ','RAIZ','Activo',NOW(),NOW())
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), estado=VALUES(estado);

INSERT INTO col_bilingue.core_tipos_docs_apps (id, prefijo, descripcion, estado, created_at, updated_at)
VALUES
(21,'NM','Nomina Mensual','Activo',NOW(),NOW()),
(121,'LC','Liquidacion de Contrato','Activo',NOW(),NOW()),
(122,'LS','Liquidacion Semestral','Activo',NOW(),NOW()),
(123,'LV','Liquidacion de Vacaciones','Activo',NOW(),NOW()),
(124,'PL','Planilla / Prima Legal','Activo',NOW(),NOW()),
(125,'BN','Bonificaciones de Nomina','Activo',NOW(),NOW())
ON DUPLICATE KEY UPDATE prefijo=VALUES(prefijo), descripcion=VALUES(descripcion), estado=VALUES(estado);

DELETE FROM col_bilingue.core_transaccion_tiene_documento
WHERE core_tipo_transaccion_id = @CORE_TIPO_TRANSACCION_NOMINA_ID
  AND core_tipo_doc_id IN (21,121,122,123,124,125);

INSERT IGNORE INTO col_bilingue.core_transaccion_tiene_documento (orden, core_tipo_transaccion_id, core_tipo_doc_id)
VALUES
(1,@CORE_TIPO_TRANSACCION_NOMINA_ID,21),
(2,@CORE_TIPO_TRANSACCION_NOMINA_ID,121),
(3,@CORE_TIPO_TRANSACCION_NOMINA_ID,122),
(4,@CORE_TIPO_TRANSACCION_NOMINA_ID,123),
(5,@CORE_TIPO_TRANSACCION_NOMINA_ID,124),
(6,@CORE_TIPO_TRANSACCION_NOMINA_ID,125);

INSERT INTO col_bilingue.nom_cargos (descripcion, estado, cargo_padre_id, rango_salarial_id, created_at, updated_at)
SELECT DISTINCT TRIM(c.DESCRIPCION), 'Activo', 1, 0, NOW(), NOW()
FROM biable_migracion.CARGOS c
LEFT JOIN col_bilingue.nom_cargos nc ON nc.descripcion = TRIM(c.DESCRIPCION)
WHERE TRIM(c.DESCRIPCION) <> '' AND nc.id IS NULL;

/* ==========================================================
   2) MAPAS Y STAGING
   ========================================================== */

DROP TABLE IF EXISTS col_bilingue.map_concepto;
CREATE TABLE col_bilingue.map_concepto (
  biable_concepto_codigo CHAR(3) PRIMARY KEY,
  appsiel_nom_concepto_id INT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS col_bilingue.map_contrato;
CREATE TABLE col_bilingue.map_contrato (
  biable_contrato_codigo CHAR(19) PRIMARY KEY,
  appsiel_nom_contrato_id INT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS col_bilingue.map_cargo;
CREATE TABLE col_bilingue.map_cargo (
  biable_cargo_codigo CHAR(6) PRIMARY KEY,
  appsiel_nom_cargo_id INT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS col_bilingue.map_entidad;
CREATE TABLE col_bilingue.map_entidad (
  biable_entidad_codigo CHAR(3) NOT NULL,
  tipo_entidad VARCHAR(10) NOT NULL,
  appsiel_nom_entidad_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (biable_entidad_codigo, tipo_entidad)
);

DROP TABLE IF EXISTS col_bilingue.map_proveedor;
CREATE TABLE col_bilingue.map_proveedor (
  biable_tercero_codigo CHAR(13) PRIMARY KEY,
  appsiel_compras_proveedor_id INT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS col_bilingue.map_inv_grupo;
CREATE TABLE col_bilingue.map_inv_grupo (
  biable_tipo CHAR(1) NOT NULL,
  biable_grupo CHAR(6) NOT NULL,
  appsiel_inv_grupo_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (biable_tipo, biable_grupo)
);

DROP TABLE IF EXISTS col_bilingue.map_producto;
CREATE TABLE col_bilingue.map_producto (
  biable_item_codigo CHAR(20) PRIMARY KEY,
  appsiel_inv_producto_id INT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS col_bilingue.stg_nmresumen_pagos_nomina;
CREATE TABLE col_bilingue.stg_nmresumen_pagos_nomina AS
SELECT
  NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(r.ID_TERC),'.',''),'-',''),' ',''),',',''), '') AS ID_TERC_LIMPIO,
  STR_TO_DATE(r.FECHA_INICIAL, '%Y%m%d') AS FECHA_INICIAL_DT,
  STR_TO_DATE(r.FECHA_FINAL, '%Y%m%d') AS FECHA_FINAL_DT,
  TRIM(r.ID_CONTRATO) AS ID_CONTRATO,
  TRIM(r.ID_CPTO) AS ID_CPTO,
  CAST(r.ID_IND_DEV_DED AS UNSIGNED) AS ID_IND_DEV_DED,
  CAST(r.NMMOV_VALOR AS DECIMAL(15,2)) AS NMMOV_VALOR,
  CAST(COALESCE(r.NMMOV_HORAS,0) AS DECIMAL(10,2)) AS NMMOV_HORAS,
  TRIM(r.ID_TIPO_DOC) AS ID_TIPO_DOC,
  TRIM(r.TIPO_NOMINA) AS TIPO_NOMINA,
  TRIM(r.CO_MOV_DESC) AS CO_MOV_DESC
FROM biable_migracion.NMRESUMEN_PAGOS_NOMINA r;

DROP TABLE IF EXISTS col_bilingue.stg_contratos;
CREATE TABLE col_bilingue.stg_contratos AS
SELECT
  TRIM(c.CODIGO) AS CODIGO_LIMPIO,
  NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(c.ID_TERC),'.',''),'-',''),' ',''),',',''), '') AS ID_TERC_LIMPIO,
  c.*
FROM biable_migracion.CONTRATOS c;

DROP TABLE IF EXISTS col_bilingue.stg_empleados;
CREATE TABLE col_bilingue.stg_empleados AS
SELECT
  n.EMPLEADO,
  NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(n.NIT),'.',''),'-',''),' ',''),',',''), '') AS NIT_LIMPIO,
  TRIM(n.NOMBRE_COMPLETO) AS NOMBRE_COMPLETO,
  TRIM(n.APELLIDO1) AS APELLIDO1,
  TRIM(n.APELLIDO2) AS APELLIDO2,
  TRIM(n.NOMBRES) AS NOMBRES,
  TRIM(n.TELEFONO_2) AS TELEFONO_2,
  n.ESTADO_CONTRATO
FROM biable_migracion.NMEMPLEADOS n;

/* ==========================================================
   3) TERCEROS DE NOMINA Y ENTIDADES
   ========================================================== */

INSERT INTO col_bilingue.core_terceros
(descripcion, core_empresa_id, imagen, tipo, razon_social, nombre1, otros_nombres, apellido1, apellido2,
 id_tipo_documento_id, numero_identificacion, digito_verificacion, direccion1, direccion2, barrio, codigo_ciudad,
 codigo_postal, telefono1, telefono2, email, pagina_web, estado, user_id, contab_anticipo_cta_id,
 contab_cartera_cta_id, contab_cxp_cta_id, tax_level_code, creado_por, modificado_por, created_at, updated_at)
SELECT DISTINCT
  TRIM(e.DESCRIPCION), @CORE_EMPRESA_ID, '', 'Entidad', TRIM(e.DESCRIPCION),
  '', '', '', '', 2,
  CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(e.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED),
  0, '', '', '', 0, 0, '', '', '', '', 'Activo', 1, 0, 0, 0, NULL, 'Migracion', 'Migracion', NOW(), NOW()
FROM biable_migracion.ENTIDADES e
LEFT JOIN col_bilingue.core_terceros t
  ON t.numero_identificacion = CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(e.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED)
WHERE NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(e.NIT),'.',''),'-',''),' ',''),',',''), '') IS NOT NULL
  AND t.id IS NULL;

INSERT INTO col_bilingue.nom_entidades (core_tercero_id, descripcion, codigo_nacional, tipo_entidad, estado, created_at, updated_at)
SELECT DISTINCT
  t.id,
  TRIM(e.DESCRIPCION),
  TRIM(e.CODIGO),
  CASE
    WHEN e.IND_EPS = '1' THEN 'EPS'
    WHEN e.IND_AFP = '1' THEN 'AFP'
    WHEN e.IND_ARP = '1' THEN 'ARP'
    WHEN e.IND_CCF = '1' THEN 'CCF'
    ELSE 'OTRA'
  END,
  'Activo', NOW(), NOW()
FROM biable_migracion.ENTIDADES e
JOIN col_bilingue.core_terceros t
  ON t.numero_identificacion = CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(e.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED)
LEFT JOIN col_bilingue.nom_entidades ne
  ON ne.codigo_nacional = TRIM(e.CODIGO)
WHERE TRIM(e.CODIGO) <> '' AND ne.id IS NULL;

INSERT IGNORE INTO col_bilingue.map_entidad (biable_entidad_codigo, tipo_entidad, appsiel_nom_entidad_id)
SELECT DISTINCT TRIM(e.CODIGO), ne.tipo_entidad, ne.id
FROM biable_migracion.ENTIDADES e
JOIN col_bilingue.core_terceros t
  ON t.numero_identificacion = CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(e.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED)
JOIN col_bilingue.nom_entidades ne
  ON ne.core_tercero_id = t.id AND ne.codigo_nacional = TRIM(e.CODIGO)
WHERE TRIM(e.CODIGO) <> '';

INSERT INTO col_bilingue.core_terceros
(descripcion, core_empresa_id, imagen, tipo, razon_social, nombre1, otros_nombres, apellido1, apellido2,
 id_tipo_documento_id, numero_identificacion, digito_verificacion, direccion1, direccion2, barrio, codigo_ciudad,
 codigo_postal, telefono1, telefono2, email, pagina_web, estado, user_id, contab_anticipo_cta_id,
 contab_cartera_cta_id, contab_cxp_cta_id, tax_level_code, creado_por, modificado_por, created_at, updated_at)
SELECT DISTINCT
  COALESCE(se.NOMBRE_COMPLETO,''), @CORE_EMPRESA_ID, '', 'Persona', COALESCE(se.NOMBRE_COMPLETO,''),
  COALESCE(se.NOMBRES,''), '', COALESCE(se.APELLIDO1,''), COALESCE(se.APELLIDO2,''),
  1, CAST(se.NIT_LIMPIO AS UNSIGNED), 0, '', '', '', 0, 0, '', COALESCE(se.TELEFONO_2,''),
  '', '', CASE WHEN se.ESTADO_CONTRATO='1' THEN 'Activo' ELSE 'Inactivo' END,
  1, 0, 0, 0, NULL, 'Migracion', 'Migracion', NOW(), NOW()
FROM col_bilingue.stg_empleados se
LEFT JOIN col_bilingue.core_terceros t ON t.numero_identificacion = CAST(se.NIT_LIMPIO AS UNSIGNED)
WHERE se.NIT_LIMPIO IS NOT NULL AND t.id IS NULL;

/* ==========================================================
   4) CONCEPTOS, CONTRATOS Y DOCUMENTOS DE NOMINA
   ========================================================== */

INSERT INTO col_bilingue.nom_conceptos
(descripcion, modo_liquidacion_id, naturaleza, porcentaje_sobre_basico, valor_fijo, abreviatura,
 forma_parte_basico, nom_agrupacion_id, cpto_dian_id, estado, created_at, updated_at)
SELECT DISTINCT
  TRIM(c.DESCRIPCION),
  CASE
    WHEN TRIM(c.CODIGO) IN ('001','002','003','004','006','007','008','009','010','011','012','013','014','015','020','022','023','093','150','151','152') THEN 1
    WHEN TRIM(c.CODIGO) = '005' OR UPPER(c.DESCRIPCION) LIKE '%TRANSPORTE%' THEN 6
    WHEN UPPER(c.DESCRIPCION) LIKE '%INCAPAC%' OR UPPER(c.DESCRIPCION) LIKE '%LICENCIA%' OR UPPER(c.DESCRIPCION) LIKE '%SUSPENSION%' OR UPPER(c.DESCRIPCION) LIKE '%INASISTENCIA%' OR UPPER(c.DESCRIPCION) LIKE '%PERMISO%' OR TRIM(c.CODIGO) = '688' THEN 7
    WHEN UPPER(c.DESCRIPCION) LIKE '%RETE%' OR UPPER(c.DESCRIPCION) LIKE '%RETENCION%' THEN 11
    WHEN UPPER(c.DESCRIPCION) LIKE '%SALUD%' OR UPPER(c.DESCRIPCION) LIKE '%EPS%' THEN 12
    WHEN UPPER(c.DESCRIPCION) LIKE '%PENSION%' OR UPPER(c.DESCRIPCION) LIKE '%AFP%' THEN 13
    WHEN UPPER(c.DESCRIPCION) LIKE '%SOLIDARIDAD%' THEN 10
    WHEN UPPER(c.DESCRIPCION) LIKE '%PRESTAMO%' THEN 4
    WHEN UPPER(c.DESCRIPCION) LIKE '%CUOTA%' THEN 3
    WHEN UPPER(c.DESCRIPCION) LIKE '%PRIMA DE SERVICIOS%' THEN 14
    WHEN UPPER(c.DESCRIPCION) LIKE '%INTERESES%CESANT%' OR TRIM(c.CODIGO) IN ('031','058','153') THEN 16
    WHEN UPPER(c.DESCRIPCION) LIKE '%CONSIGNACION%CESANT%' OR TRIM(c.CODIGO) = '059' THEN 15
    WHEN UPPER(c.DESCRIPCION) LIKE '%CESANT%' THEN 17
    WHEN UPPER(c.DESCRIPCION) LIKE '%ANTIGUEDAD%' THEN 19
    WHEN UPPER(c.DESCRIPCION) LIKE '%VACACION%' THEN 9
    ELSE 2
  END,
  CASE WHEN c.NATURALEZA='2' THEN 'Deduccion' ELSE 'Devengo' END,
  0, 0, TRIM(c.CODIGO), 0, 1, 0, 'Activo', NOW(), NOW()
FROM biable_migracion.CONCEPTOS_NOMINA c
LEFT JOIN col_bilingue.nom_conceptos nc ON nc.abreviatura = TRIM(c.CODIGO)
WHERE TRIM(c.CODIGO) <> '' AND nc.id IS NULL;

INSERT INTO col_bilingue.map_concepto (biable_concepto_codigo, appsiel_nom_concepto_id)
SELECT TRIM(c.CODIGO), MIN(nc.id)
FROM biable_migracion.CONCEPTOS_NOMINA c
JOIN col_bilingue.nom_conceptos nc ON nc.abreviatura = TRIM(c.CODIGO)
WHERE TRIM(c.CODIGO) <> ''
GROUP BY TRIM(c.CODIGO);

INSERT INTO col_bilingue.nom_contratos
(core_tercero_id, clase_contrato, cargo_id, grupo_empleado_id, clase_riesgo_laboral_id,
 horas_laborales, sueldo, salario_integral, fecha_ingreso, contrato_hasta,
 entidad_salud_id, entidad_pension_id, entidad_arl_id, estado, created_at, updated_at,
 liquida_subsidio_transporte, planilla_pila_id, es_pasante_sena, entidad_cesantias_id,
 entidad_caja_compensacion_id, genera_planilla_integrada, tipo_cotizante, turno_default_id, fingerprint_reader_id,
 dias_laborados_mes, excluir_documentos_nomina_electronica)
SELECT
  t.id, 'MIGRADO', 1, 1, 1, 0, COALESCE(c.SALARIO,0), IF(c.IND_SAL_INT='1',1,0),
  STR_TO_DATE(c.FECHA_INGRESO,'%Y%m%d'),
  CASE
    WHEN c.FECHA_RETIRO IS NULL OR TRIM(c.FECHA_RETIRO)='' OR TRIM(c.FECHA_RETIRO)='00000000' THEN '2999-12-31'
    ELSE COALESCE(STR_TO_DATE(c.FECHA_RETIRO,'%Y%m%d'),'2999-12-31')
  END,
  COALESCE((SELECT me.appsiel_nom_entidad_id FROM col_bilingue.map_entidad me WHERE me.biable_entidad_codigo = TRIM(c.EPS) AND me.tipo_entidad='EPS' LIMIT 1),0),
  COALESCE((SELECT me.appsiel_nom_entidad_id FROM col_bilingue.map_entidad me WHERE me.biable_entidad_codigo = TRIM(c.PENSION) AND me.tipo_entidad='AFP' LIMIT 1),0),
  COALESCE((SELECT me.appsiel_nom_entidad_id FROM col_bilingue.map_entidad me WHERE me.biable_entidad_codigo = TRIM(c.ARP) AND me.tipo_entidad='ARP' LIMIT 1),0),
  CASE WHEN c.FECHA_RETIRO IS NULL OR TRIM(c.FECHA_RETIRO)='' OR TRIM(c.FECHA_RETIRO)='00000000' THEN 'Activo' ELSE 'Inactivo' END,
  NOW(), NOW(), IF(c.IND_AUX='1',1,0), 0, 0, 0,
  COALESCE((SELECT me.appsiel_nom_entidad_id FROM col_bilingue.map_entidad me WHERE me.biable_entidad_codigo = TRIM(c.CAJA) AND me.tipo_entidad='CCF' LIMIT 1),0),
  0, '', 0, 0, 30, 0
FROM biable_migracion.CONTRATOS c
LEFT JOIN (
  SELECT TRIM(CODIGO) AS CODIGO, MIN(TRIM(NIT)) AS NIT
  FROM biable_migracion.TERCEROS
  GROUP BY TRIM(CODIGO)
) bt ON bt.CODIGO = TRIM(c.ID_TERC)
JOIN (
  SELECT numero_identificacion, MIN(id) AS id
  FROM col_bilingue.core_terceros
  GROUP BY numero_identificacion
) t ON t.numero_identificacion = CASE
  WHEN NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(bt.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
    THEN CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(bt.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED)
  WHEN NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(c.ID_TERC),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
    THEN CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(c.ID_TERC),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED)
  ELSE 0
END
WHERE TRIM(c.CODIGO) <> ''
  AND (
    NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(bt.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
    OR NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(c.ID_TERC),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
  );

INSERT IGNORE INTO col_bilingue.map_contrato (biable_contrato_codigo, appsiel_nom_contrato_id)
SELECT sc.CODIGO_LIMPIO, MIN(nc.id)
FROM col_bilingue.stg_contratos sc
LEFT JOIN (
  SELECT TRIM(CODIGO) AS CODIGO, MIN(TRIM(NIT)) AS NIT
  FROM biable_migracion.TERCEROS
  GROUP BY TRIM(CODIGO)
) bt ON bt.CODIGO = TRIM(sc.ID_TERC)
JOIN (
  SELECT numero_identificacion, MIN(id) AS id
  FROM col_bilingue.core_terceros
  GROUP BY numero_identificacion
) t ON t.numero_identificacion = CASE
  WHEN NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(bt.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
    THEN CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(bt.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED)
  WHEN sc.ID_TERC_LIMPIO REGEXP '^[0-9]+$' THEN CAST(sc.ID_TERC_LIMPIO AS UNSIGNED)
  ELSE 0
END
JOIN col_bilingue.nom_contratos nc
  ON nc.core_tercero_id = t.id
 AND nc.fecha_ingreso = STR_TO_DATE(sc.FECHA_INGRESO,'%Y%m%d')
 AND nc.sueldo = COALESCE(sc.SALARIO,0)
WHERE sc.CODIGO_LIMPIO <> '' AND sc.ID_TERC_LIMPIO IS NOT NULL
  AND (
    sc.ID_TERC_LIMPIO REGEXP '^[0-9]+$'
    OR NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(bt.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
  )
GROUP BY sc.CODIGO_LIMPIO;

DROP TABLE IF EXISTS col_bilingue.stg_contratos_faltantes_resumen;
CREATE TABLE col_bilingue.stg_contratos_faltantes_resumen AS
SELECT
  s.ID_CONTRATO AS CODIGO_LIMPIO,
  s.ID_TERC_LIMPIO,
  MIN(s.FECHA_INICIAL_DT) AS FECHA_INGRESO_DT
FROM col_bilingue.stg_nmresumen_pagos_nomina s
LEFT JOIN col_bilingue.map_contrato mc ON mc.biable_contrato_codigo = s.ID_CONTRATO
WHERE mc.appsiel_nom_contrato_id IS NULL
  AND s.ID_TERC_LIMPIO IS NOT NULL
  AND s.ID_TERC_LIMPIO REGEXP '^[0-9]+$'
GROUP BY s.ID_CONTRATO, s.ID_TERC_LIMPIO;

INSERT INTO col_bilingue.nom_contratos
(core_tercero_id, clase_contrato, cargo_id, grupo_empleado_id, clase_riesgo_laboral_id,
 horas_laborales, sueldo, salario_integral, fecha_ingreso, contrato_hasta,
 entidad_salud_id, entidad_pension_id, entidad_arl_id, estado, created_at, updated_at,
 liquida_subsidio_transporte, planilla_pila_id, es_pasante_sena, entidad_cesantias_id,
 entidad_caja_compensacion_id, genera_planilla_integrada, tipo_cotizante, turno_default_id, fingerprint_reader_id,
 dias_laborados_mes, excluir_documentos_nomina_electronica)
SELECT
  t.id, 'MIGRADO', 1, 1, 1, 0, 0, 0,
  COALESCE(f.FECHA_INGRESO_DT, '1900-01-01'),
  '2999-12-31',
  0, 0, 0, 'Activo', NOW(), NOW(),
  0, 0, 0, 0, 0, 0, '', 0, 0, 30, 0
FROM col_bilingue.stg_contratos_faltantes_resumen f
LEFT JOIN (
  SELECT TRIM(CODIGO) AS CODIGO, MIN(TRIM(NIT)) AS NIT
  FROM biable_migracion.TERCEROS
  GROUP BY TRIM(CODIGO)
) bt ON bt.CODIGO = TRIM(f.ID_TERC_LIMPIO)
JOIN (
  SELECT numero_identificacion, MIN(id) AS id
  FROM col_bilingue.core_terceros
  GROUP BY numero_identificacion
) t ON t.numero_identificacion = CASE
  WHEN NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(bt.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
    THEN CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(bt.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED)
  WHEN f.ID_TERC_LIMPIO REGEXP '^[0-9]+$' THEN CAST(f.ID_TERC_LIMPIO AS UNSIGNED)
  ELSE 0
END;

INSERT IGNORE INTO col_bilingue.map_contrato (biable_contrato_codigo, appsiel_nom_contrato_id)
SELECT f.CODIGO_LIMPIO, MIN(nc.id)
FROM col_bilingue.stg_contratos_faltantes_resumen f
LEFT JOIN (
  SELECT TRIM(CODIGO) AS CODIGO, MIN(TRIM(NIT)) AS NIT
  FROM biable_migracion.TERCEROS
  GROUP BY TRIM(CODIGO)
) bt ON bt.CODIGO = TRIM(f.ID_TERC_LIMPIO)
JOIN (
  SELECT numero_identificacion, MIN(id) AS id
  FROM col_bilingue.core_terceros
  GROUP BY numero_identificacion
) t ON t.numero_identificacion = CASE
  WHEN NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(bt.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
    THEN CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(bt.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED)
  WHEN f.ID_TERC_LIMPIO REGEXP '^[0-9]+$' THEN CAST(f.ID_TERC_LIMPIO AS UNSIGNED)
  ELSE 0
END
JOIN col_bilingue.nom_contratos nc
  ON nc.core_tercero_id = t.id
 AND nc.fecha_ingreso = COALESCE(f.FECHA_INGRESO_DT, '1900-01-01')
 AND nc.sueldo = 0
WHERE f.CODIGO_LIMPIO <> ''
GROUP BY f.CODIGO_LIMPIO;

INSERT IGNORE INTO col_bilingue.map_cargo (biable_cargo_codigo, appsiel_nom_cargo_id)
SELECT DISTINCT TRIM(c.CODIGO), nc.id
FROM biable_migracion.CARGOS c
JOIN col_bilingue.nom_cargos nc ON nc.descripcion = TRIM(c.DESCRIPCION)
WHERE TRIM(c.CODIGO) <> '' AND TRIM(c.DESCRIPCION) <> '';

UPDATE col_bilingue.nom_contratos nc
JOIN col_bilingue.map_contrato mc ON mc.appsiel_nom_contrato_id = nc.id
JOIN biable_migracion.CONTRATOS bc ON TRIM(bc.CODIGO) = mc.biable_contrato_codigo
JOIN col_bilingue.map_cargo mcar ON mcar.biable_cargo_codigo = TRIM(bc.ID_CARGO)
SET nc.cargo_id = mcar.appsiel_nom_cargo_id;

DROP TABLE IF EXISTS col_bilingue.stg_periodos_nomina;
CREATE TABLE col_bilingue.stg_periodos_nomina AS
SELECT DISTINCT
  ID_TIPO_DOC,
  TIPO_NOMINA,
  CASE ID_TIPO_DOC
    WHEN 'LC' THEN 121
    WHEN 'LS' THEN 122
    WHEN 'LV' THEN 123
    WHEN 'PL' THEN 124
    WHEN 'BN' THEN 125
    ELSE 21
  END AS core_tipo_doc_app_id,
  CASE ID_TIPO_DOC
    WHEN 'LC' THEN 'liquidacion_contrato'
    WHEN 'LS' THEN 'liquidacion_semestral'
    WHEN 'LV' THEN 'liquidacion_vacaciones'
    WHEN 'PL' THEN 'prima_legal'
    WHEN 'BN' THEN 'bonificacion_nomina'
    ELSE 'nomina_mensual'
  END AS tipo_liquidacion,
  FECHA_INICIAL_DT AS fecha_inicial,
  FECHA_FINAL_DT AS fecha_final
FROM col_bilingue.stg_nmresumen_pagos_nomina
WHERE FECHA_INICIAL_DT IS NOT NULL AND FECHA_FINAL_DT IS NOT NULL;

SET @base := (SELECT IFNULL(MAX(consecutivo),0) FROM col_bilingue.nom_doc_encabezados WHERE core_empresa_id=@CORE_EMPRESA_ID);
SET @i := 0;

INSERT INTO col_bilingue.nom_doc_encabezados
(core_tipo_transaccion_id, core_tipo_doc_app_id, consecutivo, fecha, core_empresa_id,
 descripcion, tiempo_a_liquidar, total_devengos, total_deducciones, presupuesto, estado,
 creado_por, modificado_por, created_at, updated_at, tipo_liquidacion)
SELECT
  @CORE_TIPO_TRANSACCION_NOMINA_ID,
  p.core_tipo_doc_app_id,
  (@base + (@i := @i + 1)),
  p.fecha_final,
  @CORE_EMPRESA_ID,
  CONCAT(CASE p.tipo_liquidacion
      WHEN 'liquidacion_contrato' THEN 'Migracion liquidacion contrato '
      WHEN 'liquidacion_semestral' THEN 'Migracion liquidacion semestral '
      WHEN 'liquidacion_vacaciones' THEN 'Migracion liquidacion vacaciones '
      WHEN 'prima_legal' THEN 'Migracion prima legal '
      WHEN 'bonificacion_nomina' THEN 'Migracion bonificaciones nomina '
      ELSE 'Migracion nomina mensual '
    END, DATE_FORMAT(p.fecha_inicial,'%Y-%m-%d'), ' a ', DATE_FORMAT(p.fecha_final,'%Y-%m-%d')),
  DATEDIFF(p.fecha_final,p.fecha_inicial)+1,
  0, 0, 0, 'Activo', 'Migracion', 'Migracion', NOW(), NOW(), p.tipo_liquidacion
FROM col_bilingue.stg_periodos_nomina p
ORDER BY p.fecha_inicial, p.fecha_final, p.core_tipo_doc_app_id;

DROP TABLE IF EXISTS col_bilingue.map_nom_periodo_encabezado;
CREATE TABLE col_bilingue.map_nom_periodo_encabezado AS
SELECT e.id AS nom_doc_encabezado_id, p.ID_TIPO_DOC, p.TIPO_NOMINA, p.fecha_inicial, p.fecha_final
FROM col_bilingue.nom_doc_encabezados e
JOIN col_bilingue.stg_periodos_nomina p
  ON e.fecha = p.fecha_final
 AND e.core_tipo_doc_app_id = p.core_tipo_doc_app_id
 AND e.tipo_liquidacion = p.tipo_liquidacion
 AND e.descripcion LIKE CONCAT('%', DATE_FORMAT(p.fecha_inicial,'%Y-%m-%d'), ' a ', DATE_FORMAT(p.fecha_final,'%Y-%m-%d'))
WHERE e.core_empresa_id = @CORE_EMPRESA_ID
  AND e.core_tipo_transaccion_id = @CORE_TIPO_TRANSACCION_NOMINA_ID
  AND e.creado_por = 'Migracion';

INSERT INTO col_bilingue.nom_doc_registros
(nom_doc_encabezado_id, core_tercero_id, nom_contrato_id, fecha, core_empresa_id,
 detalle, nom_concepto_id, nom_cuota_id, nom_prestamo_id, novedad_tnl_id, orden_trabajo_id,
 cantidad_horas, porcentaje, valor_devengo, valor_deduccion, estado, creado_por, modificado_por,
 created_at, updated_at, codigo_referencia_tercero)
SELECT
  mpe.nom_doc_encabezado_id,
  nc.core_tercero_id,
  mc.appsiel_nom_contrato_id,
  mpe.fecha_final,
  @CORE_EMPRESA_ID,
  COALESCE(s.CO_MOV_DESC,''),
  mp.appsiel_nom_concepto_id,
  0, 0, NULL, 0,
  COALESCE(s.NMMOV_HORAS,0),
  0,
  CASE WHEN s.ID_IND_DEV_DED=1 THEN s.NMMOV_VALOR ELSE 0 END,
  CASE WHEN s.ID_IND_DEV_DED=2 THEN ABS(s.NMMOV_VALOR) ELSE 0 END,
  'Activo', 'Migracion', 'Migracion', NOW(), NOW(),
  CASE
    WHEN s.ID_TERC_LIMPIO REGEXP '^[0-9]+$' AND CAST(s.ID_TERC_LIMPIO AS DECIMAL(20,0)) <= 4294967295
      THEN CAST(s.ID_TERC_LIMPIO AS UNSIGNED)
    ELSE 0
  END
FROM col_bilingue.stg_nmresumen_pagos_nomina s
JOIN col_bilingue.map_nom_periodo_encabezado mpe
  ON mpe.fecha_inicial = s.FECHA_INICIAL_DT
 AND mpe.fecha_final = s.FECHA_FINAL_DT
 AND mpe.ID_TIPO_DOC = s.ID_TIPO_DOC
 AND mpe.TIPO_NOMINA = s.TIPO_NOMINA
JOIN col_bilingue.stg_contratos c ON c.CODIGO_LIMPIO = s.ID_CONTRATO AND c.ID_TERC_LIMPIO = s.ID_TERC_LIMPIO
JOIN col_bilingue.map_contrato mc ON mc.biable_contrato_codigo = c.CODIGO_LIMPIO
JOIN col_bilingue.nom_contratos nc ON nc.id = mc.appsiel_nom_contrato_id
JOIN col_bilingue.core_terceros t ON t.id = nc.core_tercero_id
JOIN col_bilingue.map_concepto mp ON mp.biable_concepto_codigo = s.ID_CPTO
WHERE s.ID_TERC_LIMPIO IS NOT NULL
  AND s.ID_TERC_LIMPIO REGEXP '^[0-9]+$';

SET @orden_empleado := 0;
SET @documento_empleado := 0;

INSERT INTO col_bilingue.nom_empleados_del_documento
(orden, nom_doc_encabezado_id, nom_contrato_id)
SELECT
  (@orden_empleado := IF(@documento_empleado = empleados.nom_doc_encabezado_id, @orden_empleado + 1, 1)) AS orden,
  (@documento_empleado := empleados.nom_doc_encabezado_id) AS nom_doc_encabezado_id,
  empleados.nom_contrato_id
FROM (
  SELECT DISTINCT
    rdr.nom_doc_encabezado_id,
    rdr.nom_contrato_id
  FROM col_bilingue.nom_doc_registros rdr
  JOIN col_bilingue.nom_doc_encabezados nde ON nde.id = rdr.nom_doc_encabezado_id
  WHERE rdr.creado_por = 'Migracion'
    AND nde.creado_por = 'Migracion'
  ORDER BY rdr.nom_doc_encabezado_id, rdr.nom_contrato_id
) empleados
ON DUPLICATE KEY UPDATE orden = VALUES(orden);

UPDATE col_bilingue.nom_doc_encabezados e
JOIN (
  SELECT nom_doc_encabezado_id, SUM(valor_devengo) AS total_dev, SUM(valor_deduccion) AS total_ded
  FROM col_bilingue.nom_doc_registros
  WHERE creado_por='Migracion' AND core_empresa_id=@CORE_EMPRESA_ID
  GROUP BY nom_doc_encabezado_id
) t ON t.nom_doc_encabezado_id = e.id
SET e.total_devengos = t.total_dev, e.total_deducciones = t.total_ded
WHERE e.core_empresa_id=@CORE_EMPRESA_ID
  AND e.core_tipo_transaccion_id=@CORE_TIPO_TRANSACCION_NOMINA_ID
  AND e.creado_por='Migracion';

/* ==========================================================
   4.1) EQUIVALENCIAS CONTABLES DE NOMINA
   ========================================================== */

DROP TABLE IF EXISTS col_bilingue.stg_nom_equivalencias_contables;
CREATE TABLE col_bilingue.stg_nom_equivalencias_contables AS
SELECT
  nc.id AS nom_concepto_id,
  nc.abreviatura AS codigo_concepto,
  nc.descripcion AS concepto,
  CASE
    WHEN nc.abreviatura IN ('001','002','003','004','022','050','051','052','053','054','060','061','093','152') THEN '510506'
    WHEN nc.abreviatura IN ('006','007','008','009','010','011','012','013','014','015','020','023','150','151') THEN '510515'
    WHEN nc.abreviatura = '005' THEN '510527'
    WHEN nc.abreviatura IN ('030','056') THEN '261005'
    WHEN nc.abreviatura IN ('031','058','153') THEN '261010'
    WHEN nc.abreviatura IN ('032','036','038','159','706','999') THEN '261015'
    WHEN nc.abreviatura IN ('029','033','034','035','037','135','157','599','651') THEN '261020'
    WHEN nc.abreviatura IN ('021','158','162','163') THEN '61600508'
    WHEN nc.abreviatura IN ('155') THEN '510545'
    WHEN nc.abreviatura IN ('080','081','082','510','511','730','732') THEN '23705001'
    WHEN nc.abreviatura IN ('090','091','092','515','516','518','519','731') THEN '23705002'
    WHEN nc.abreviatura IN ('095','500','501') THEN '236505'
    WHEN nc.abreviatura IN ('581','582','583','584','590','591','592','594','595','680','689','720','998') THEN '136526'
    WHEN nc.abreviatura IN ('572','573','685') THEN '237030'
    WHEN nc.abreviatura IN ('674','774') THEN '23704501'
    WHEN nc.abreviatura IN ('678','690','717') THEN '23704502'
    WHEN nc.abreviatura IN ('676') THEN '23704503'
    WHEN nc.abreviatura IN ('686','716') THEN '23704504'
    WHEN nc.abreviatura IN ('733') THEN '23704507'
    WHEN nc.abreviatura IN ('565') THEN '238040'
    WHEN nc.abreviatura IN ('579','580') THEN '41609508'
    WHEN nc.abreviatura IN ('670','701','702','715') THEN '416004'
    WHEN nc.abreviatura IN ('672','700','718','724','800') THEN '416007'
    WHEN nc.abreviatura IN ('682','692','698','714','737') THEN '13060503'
    WHEN nc.abreviatura IN ('693','696','697','704','729','799') THEN '13060504'
    WHEN nc.abreviatura IN ('024','062','063','096','100','156','160','161','688') THEN '519595'
    WHEN nc.naturaleza = 'Devengo' THEN '519595'
    ELSE '136095'
  END AS codigo_cuenta,
  CASE WHEN nc.naturaleza = 'Devengo' THEN 'debito' ELSE 'credito' END AS tipo_movimiento,
  CASE WHEN nc.naturaleza = 'Deduccion' AND nc.modo_liquidacion_id IN (10,12,13) THEN 'crear_cxp' ELSE 'causacion' END AS tipo_causacion,
  CASE WHEN nc.naturaleza = 'Deduccion' AND nc.modo_liquidacion_id IN (10,12,13) THEN 'entidad_relacionada' ELSE 'empleado' END AS tercero_movimiento
FROM col_bilingue.nom_conceptos nc;

INSERT INTO col_bilingue.nom_equivalencias_contables
(core_empresa_id, nom_concepto_id, nom_grupo_empleado_id, contab_cuenta_id, tipo_causacion,
 tipo_movimiento, tercero_movimiento, core_tercero_id, nom_entidad_id, estado, created_at, updated_at)
SELECT
  @CORE_EMPRESA_ID,
  se.nom_concepto_id,
  1,
  cc.id,
  se.tipo_causacion,
  se.tipo_movimiento,
  se.tercero_movimiento,
  0,
  0,
  'Activo',
  NOW(),
  NOW()
FROM col_bilingue.stg_nom_equivalencias_contables se
JOIN col_bilingue.contab_cuentas cc ON cc.codigo = se.codigo_cuenta
LEFT JOIN col_bilingue.nom_equivalencias_contables ne
  ON ne.core_empresa_id = @CORE_EMPRESA_ID
 AND ne.nom_concepto_id = se.nom_concepto_id
 AND ne.nom_grupo_empleado_id = 1
WHERE ne.id IS NULL;

/* ==========================================================
   5) CATALOGOS DE COMPRAS: PROVEEDORES, GRUPOS, ITEMS
   ========================================================== */

INSERT INTO col_bilingue.contab_impuestos
(id, descripcion, tasa_impuesto, cta_ventas_id, cta_ventas_devol_id, cta_compras_id, cta_compras_devol_id, tax_category, estado, created_at, updated_at)
VALUES (1,'No gravado',0,0,0,0,0,'O','Activo',NOW(),NOW())
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), tasa_impuesto=VALUES(tasa_impuesto), estado=VALUES(estado);

INSERT INTO col_bilingue.inv_bodegas (id, descripcion, core_empresa_id, estado, created_at, updated_at)
VALUES (1,'Almacen General',@CORE_EMPRESA_ID,'Activo',NOW(),NOW())
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), estado=VALUES(estado);

INSERT INTO col_bilingue.compras_clases_proveedores
(id, descripcion, cta_x_pagar_id, cta_anticipo_id, clase_padre_id, estado, created_at, updated_at)
VALUES (1,'Proveedores migrados Biable',0,0,1,'Activo',NOW(),NOW())
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), estado=VALUES(estado);

INSERT INTO col_bilingue.compras_clases_proveedores
(descripcion, cta_x_pagar_id, cta_anticipo_id, clase_padre_id, estado, created_at, updated_at)
SELECT DISTINCT CONCAT('Clase proveedor ', TRIM(t.PRO_CLASE)), 0, 0, 1, 'Activo', NOW(), NOW()
FROM biable_migracion.TERCEROS t
LEFT JOIN col_bilingue.compras_clases_proveedores cp
  ON cp.descripcion = CONCAT('Clase proveedor ', TRIM(t.PRO_CLASE))
WHERE t.IND_PRO='1' AND NULLIF(TRIM(t.PRO_CLASE),'') IS NOT NULL AND cp.id IS NULL;

INSERT INTO col_bilingue.core_terceros
(descripcion, core_empresa_id, imagen, tipo, razon_social, nombre1, otros_nombres, apellido1, apellido2,
 id_tipo_documento_id, numero_identificacion, digito_verificacion, direccion1, direccion2, barrio, codigo_ciudad,
 codigo_postal, telefono1, telefono2, email, pagina_web, estado, user_id, contab_anticipo_cta_id,
 contab_cartera_cta_id, contab_cxp_cta_id, tax_level_code, creado_por, modificado_por, created_at, updated_at)
SELECT DISTINCT
  TRIM(t.DESCRIPCION), @CORE_EMPRESA_ID, '',
  CASE WHEN t.TIPO_IDENTIFICA IN ('1','2','3') THEN 'Persona' ELSE 'Entidad' END,
  TRIM(t.DESCRIPCION), COALESCE(TRIM(t.NOMBRES),''), '', COALESCE(TRIM(t.APELLIDO1),''), COALESCE(TRIM(t.APELLIDO2),''),
  CASE WHEN LENGTH(TRIM(t.NIT)) >= 9 AND t.TIPO_IDENTIFICA NOT IN ('1','2','3') THEN 2 ELSE 1 END,
  CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(t.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED),
  CASE WHEN TRIM(t.NIT_DV) REGEXP '^[0-9]+$' THEN CAST(TRIM(t.NIT_DV) AS UNSIGNED) ELSE 0 END,
  COALESCE(TRIM(t.DIRECCION_1),''), COALESCE(TRIM(t.DIRECCION_2),''), COALESCE(TRIM(t.BARRIO),''), 0,
  0,
  COALESCE(TRIM(t.TELEFONO_1),''), COALESCE(TRIM(t.TELEFONO_2),''), COALESCE(TRIM(t.EMAIL),''), '',
  IF(t.PRO_ESTADO='1' OR t.ESTADO='1','Activo','Activo'), 1, 0, 0, 0, NULL, 'Migracion', 'Migracion', NOW(), NOW()
FROM biable_migracion.TERCEROS t
LEFT JOIN (
  SELECT numero_identificacion, MIN(id) AS id
  FROM col_bilingue.core_terceros
  GROUP BY numero_identificacion
) ct ON ct.numero_identificacion = CASE
    WHEN NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(t.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
    THEN CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(t.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED)
    ELSE 0
  END
WHERE t.IND_PRO='1'
  AND NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(t.NIT),'.',''),'-',''),' ',''),',',''), '') IS NOT NULL
  AND NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(t.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
  AND ct.id IS NULL;

INSERT INTO col_bilingue.compras_proveedores
(core_tercero_id, clase_proveedor_id, inv_bodega_id, liquida_impuestos, condicion_pago_id, codigo, estado,
 created_at, updated_at, declarante_renta, retencion_fuente_concepto_default_id)
SELECT
  ct.id,
  COALESCE(cp.id,1),
  1,
  IF(t.IND_LIQ_IMPTO_C='1',1,0),
  0,
  LEFT(TRIM(t.CODIGO),10),
  'Activo',
  NOW(), NOW(),
  CASE WHEN t.GRAN_CONTRIB_P='1' OR t.IND_RETERENTA_P='1' THEN 'declarante' ELSE 'no_declarante' END,
  COALESCE((
    SELECT cr.id
    FROM col_bilingue.compras_retencion_fuente_conceptos_anuales cr
    WHERE cr.anio = 2026
      AND cr.codigo = CASE
        WHEN EXISTS (
          SELECT 1 FROM biable_migracion.CGMOVIMIENTO_CONTABLE m
          WHERE m.TERC = t.CODIGO AND (m.ID_CUENTA LIKE '51%' OR m.ID_CUENTA LIKE '52%' OR UPPER(CONCAT(m.DETALLE1,' ',m.DETALLE2,' ',m.DETALLE_ADI_1)) LIKE '%SERVICIO%')
          LIMIT 1
        )
        THEN IF(t.GRAN_CONTRIB_P='1' OR t.IND_RETERENTA_P='1','servicios_general_declarante','servicios_general_no_declarante')
        ELSE IF(t.GRAN_CONTRIB_P='1' OR t.IND_RETERENTA_P='1','compras_general_declarante','compras_general_no_declarante')
      END
    LIMIT 1
  ),0)
FROM biable_migracion.TERCEROS t
JOIN (
  SELECT numero_identificacion, MIN(id) AS id
  FROM col_bilingue.core_terceros
  GROUP BY numero_identificacion
) ct ON ct.numero_identificacion = CASE
    WHEN NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(t.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
    THEN CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(t.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED)
    ELSE 0
  END
LEFT JOIN col_bilingue.compras_clases_proveedores cp
  ON cp.descripcion = CONCAT('Clase proveedor ', TRIM(t.PRO_CLASE))
LEFT JOIN col_bilingue.compras_proveedores p ON p.core_tercero_id = ct.id
WHERE t.IND_PRO='1'
  AND NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(t.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
  AND p.id IS NULL;

INSERT INTO col_bilingue.map_proveedor (biable_tercero_codigo, appsiel_compras_proveedor_id)
SELECT TRIM(t.CODIGO), MIN(p.id)
FROM biable_migracion.TERCEROS t
JOIN (
  SELECT numero_identificacion, MIN(id) AS id
  FROM col_bilingue.core_terceros
  GROUP BY numero_identificacion
) ct ON ct.numero_identificacion = CASE
    WHEN NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(t.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
    THEN CAST(NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(t.NIT),'.',''),'-',''),' ',''),',',''), '') AS UNSIGNED)
    ELSE 0
  END
JOIN col_bilingue.compras_proveedores p ON p.core_tercero_id = ct.id
WHERE t.IND_PRO='1'
  AND NULLIF(REPLACE(REPLACE(REPLACE(REPLACE(TRIM(t.NIT),'.',''),'-',''),' ',''),',',''), '') REGEXP '^[0-9]+$'
GROUP BY TRIM(t.CODIGO);

INSERT INTO col_bilingue.inv_grupos
(id, core_empresa_id, descripcion, nivel_padre, tipo_nivel, imagen, orden, cta_inventarios_id, cta_ingresos_id,
 mostrar_en_pagina_web, estado, created_at, updated_at)
VALUES (1,@CORE_EMPRESA_ID,'Grupos migrados Biable',1,'grupo','',1,0,0,0,'Activo',NOW(),NOW())
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), estado=VALUES(estado);

INSERT INTO col_bilingue.inv_grupos
(core_empresa_id, descripcion, nivel_padre, tipo_nivel, imagen, orden, cta_inventarios_id, cta_ingresos_id,
 mostrar_en_pagina_web, estado, created_at, updated_at)
SELECT DISTINCT @CORE_EMPRESA_ID, TRIM(g.CMGRUPOS_DESCRIPCION), 1, 'grupo', '', CAST(TRIM(g.ID_GRUPO) AS UNSIGNED), 0, 0, 0, 'Activo', NOW(), NOW()
FROM biable_migracion.GRUPO_INVENTARIO g
LEFT JOIN col_bilingue.inv_grupos ig ON ig.descripcion = TRIM(g.CMGRUPOS_DESCRIPCION)
WHERE TRIM(g.CMGRUPOS_DESCRIPCION) <> '' AND ig.id IS NULL;

INSERT INTO col_bilingue.map_inv_grupo (biable_tipo, biable_grupo, appsiel_inv_grupo_id)
SELECT g.ID_TIPO, g.ID_GRUPO, MIN(ig.id)
FROM biable_migracion.GRUPO_INVENTARIO g
JOIN col_bilingue.inv_grupos ig ON ig.descripcion = TRIM(g.CMGRUPOS_DESCRIPCION)
WHERE TRIM(g.CMGRUPOS_DESCRIPCION) <> ''
GROUP BY g.ID_TIPO, g.ID_GRUPO;

INSERT INTO col_bilingue.inv_productos
(core_empresa_id, tipo, descripcion, unidad_medida1, unidad_medida2, categoria_id, inv_grupo_id, impuesto_id,
 precio_compra, precio_venta, estado, referencia, codigo_barras, imagen, mostrar_en_pagina_web, prefijo_referencia_id,
 creado_por, modificado_por, created_at, updated_at, detalle)
SELECT
  @CORE_EMPRESA_ID, 'producto', TRIM(i.DESCRIPCION), COALESCE(NULLIF(TRIM(i.UNIMED_INV_1),''),'UND'), COALESCE(NULLIF(TRIM(i.UNIMED_INV_2),''),'UND'),
  COALESCE(NULLIF(TRIM(i.ID_TIPO),''),'0'), COALESCE(mg.appsiel_inv_grupo_id,1), 1,
  COALESCE(i.ULTIMO_COSTO_ED,0), 0, 'Activo', TRIM(i.ID_ITEM), NULLIF(TRIM(i.ID_CODBAR),''),
  '', 0, NULL, 'Migracion', 'Migracion', NOW(), NOW(),
  CONCAT('Legacy item ', TRIM(i.ID_ITEM), ' grupo ', COALESCE(TRIM(i.ID_GRUPO),''))
FROM biable_migracion.ITEMS i
LEFT JOIN col_bilingue.map_inv_grupo mg ON mg.biable_tipo = i.ID_TIPO AND mg.biable_grupo = i.ID_GRUPO
LEFT JOIN col_bilingue.inv_productos p ON p.referencia = TRIM(i.ID_ITEM)
WHERE TRIM(i.ID_ITEM) <> '' AND p.id IS NULL;

INSERT INTO col_bilingue.inv_productos
(core_empresa_id, tipo, descripcion, unidad_medida1, unidad_medida2, categoria_id, inv_grupo_id, impuesto_id,
 precio_compra, precio_venta, estado, referencia, codigo_barras, imagen, mostrar_en_pagina_web, prefijo_referencia_id,
 creado_por, modificado_por, created_at, updated_at, detalle)
SELECT
  @CORE_EMPRESA_ID, 'servicio', TRIM(s.DESCRIPCION), 'UND', 'UND', COALESCE(NULLIF(TRIM(s.ID_TIPO),''),'0'), 1, 1,
  0, 0, 'Activo', TRIM(s.ID_SERVICIO), NULL, '', 0, NULL, 'Migracion', 'Migracion', NOW(), NOW(),
  CONCAT('Legacy servicio ', TRIM(s.ID_SERVICIO))
FROM biable_migracion.SERVICIOS s
LEFT JOIN col_bilingue.inv_productos p ON p.referencia = TRIM(s.ID_SERVICIO) AND p.tipo='servicio'
WHERE TRIM(s.ID_SERVICIO) <> '' AND p.id IS NULL;

INSERT INTO col_bilingue.map_producto (biable_item_codigo, appsiel_inv_producto_id)
SELECT TRIM(i.ID_ITEM), MIN(p.id)
FROM biable_migracion.ITEMS i
JOIN col_bilingue.inv_productos p ON p.referencia = TRIM(i.ID_ITEM) AND p.tipo='producto'
GROUP BY TRIM(i.ID_ITEM);

/* ==========================================================
   6) VALIDACIONES
   ========================================================== */

SELECT COUNT(*) AS huerfanos_nomina
FROM col_bilingue.nom_doc_registros
WHERE creado_por='Migracion'
  AND (core_tercero_id IS NULL OR nom_contrato_id IS NULL OR nom_concepto_id IS NULL);

SELECT tipo_liquidacion, COUNT(*) AS documentos, SUM(total_devengos) AS devengos, SUM(total_deducciones) AS deducciones
FROM col_bilingue.nom_doc_encabezados
WHERE creado_por='Migracion'
GROUP BY tipo_liquidacion
ORDER BY tipo_liquidacion;

SELECT 'conceptos' AS catalogo, COUNT(*) AS registros FROM col_bilingue.nom_conceptos
UNION ALL SELECT 'contratos', COUNT(*) FROM col_bilingue.nom_contratos
UNION ALL SELECT 'registros_nomina_migrados', COUNT(*) FROM col_bilingue.nom_doc_registros WHERE creado_por='Migracion'
UNION ALL SELECT 'proveedores', COUNT(*) FROM col_bilingue.compras_proveedores
UNION ALL SELECT 'grupos_inventario', COUNT(*) FROM col_bilingue.inv_grupos
UNION ALL SELECT 'productos_servicios', COUNT(*) FROM col_bilingue.inv_productos;

SET FOREIGN_KEY_CHECKS := @OLD_FOREIGN_KEY_CHECKS;
