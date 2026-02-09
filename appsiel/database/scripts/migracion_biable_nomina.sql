/* ==========================================================
   0) PARÁMETROS
   ========================================================== */
SET NAMES utf8mb4;
SET @OLD_FOREIGN_KEY_CHECKS := @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS := 0;

SET @CORE_EMPRESA_ID := 1;
SET @CORE_TIPO_TRANSACCION_ID := 14;
SET @CORE_TIPO_DOC_APP_ID := 21;

/* ==========================================================
   1) CATÁLOGOS MÍNIMOS EN APPSIEL (para NOT NULL)
   ========================================================== */

/* 1.1 Tipos documento identificación (core_tipos_docs_id)
   - Ajusta IDs si en tu Appsiel existen otros valores.
*/
INSERT INTO appsiel.core_tipos_docs_id (id, descripcion, abreviatura)
VALUES
(1,'Cédula de ciudadanía','CC'),
(2,'NIT','NIT')
ON DUPLICATE KEY UPDATE
  descripcion=VALUES(descripcion),
  abreviatura=VALUES(abreviatura);

/* 1.2 Modo liquidación (nom_modos_liquidacion) */
INSERT INTO appsiel.nom_modos_liquidacion (id, descripcion, detalle, estado, created_at, updated_at)
VALUES
(1,'Valor','Liquidación por valor', 'Activo', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  descripcion=VALUES(descripcion),
  detalle=VALUES(detalle),
  estado=VALUES(estado);

/* 1.3 Agrupación conceptos (nom_agrupaciones_conceptos) */
INSERT INTO appsiel.nom_agrupaciones_conceptos (id, core_empresa_id, descripcion, nombre_corto, estado, created_at, updated_at)
VALUES
(1, @CORE_EMPRESA_ID, 'General', 'GEN', 'Activo', NOW(), NOW())
ON DUPLICATE KEY UPDATE
  descripcion=VALUES(descripcion),
  nombre_corto=VALUES(nombre_corto),
  estado=VALUES(estado);

/* 1.4 Clase riesgo laboral (nom_clases_riesgos_laborales) */
INSERT INTO appsiel.nom_clases_riesgos_laborales
(id, descripcion, detalle, porcentaje_liquidacion, estado, created_at, updated_at)
VALUES
(1,'Sin definir','Migración Biable: pendiente parametrizar',0,'Activo',NOW(),NOW())
ON DUPLICATE KEY UPDATE
  descripcion=VALUES(descripcion),
  detalle=VALUES(detalle);

/* 1.5 Cargo raíz y grupo raíz (porque en Appsiel son NOT NULL) */
INSERT INTO appsiel.nom_cargos (id, descripcion, estado, cargo_padre_id, rango_salarial_id, created_at, updated_at)
VALUES (1,'RAÍZ','Activo',1,0,NOW(),NOW())
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), estado=VALUES(estado);

INSERT INTO appsiel.nom_grupos_empleados (id, core_empresa_id, grupo_padre_id, descripcion, nombre_corto, estado, created_at, updated_at)
VALUES (1,@CORE_EMPRESA_ID,1,'RAÍZ','RAIZ','Activo',NOW(),NOW())
ON DUPLICATE KEY UPDATE descripcion=VALUES(descripcion), estado=VALUES(estado);

/* 1.6 Cargos (biable01.cargos → nom_cargos) */
INSERT INTO appsiel.nom_cargos (descripcion, estado, cargo_padre_id, rango_salarial_id, created_at, updated_at)
SELECT DISTINCT
  TRIM(c.DESCRIPCION) AS descripcion,
  'Activo' AS estado,
  1 AS cargo_padre_id,
  0 AS rango_salarial_id,
  NOW(), NOW()
FROM biable01.cargos c
WHERE TRIM(c.DESCRIPCION) <> '';


/* ==========================================================
   2) TABLAS PUENTE (MAPEOS)
   ========================================================== */
DROP TABLE IF EXISTS appsiel.map_concepto;
CREATE TABLE appsiel.map_concepto (
  biable_concepto_codigo CHAR(3) PRIMARY KEY,
  appsiel_nom_concepto_id INT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS appsiel.map_contrato;
CREATE TABLE appsiel.map_contrato (
  biable_contrato_codigo CHAR(19) PRIMARY KEY,
  appsiel_nom_contrato_id INT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS appsiel.map_cargo;
CREATE TABLE appsiel.map_cargo (
  biable_cargo_codigo CHAR(6) PRIMARY KEY,
  appsiel_nom_cargo_id INT UNSIGNED NOT NULL
);

DROP TABLE IF EXISTS appsiel.map_entidad;
CREATE TABLE appsiel.map_entidad (
  biable_entidad_codigo CHAR(3) NOT NULL,
  tipo_entidad VARCHAR(10) NOT NULL,
  appsiel_nom_entidad_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (biable_entidad_codigo, tipo_entidad)
);


/* ==========================================================
   3) STAGING CON LIMPIEZA (MySQL 8+)
   ========================================================== */

/* 3.1 Resumen pagos nómina (históricos): limpieza ID_TERC, fechas, ids */
DROP TABLE IF EXISTS appsiel.stg_nmresumen_pagos_nomina;
CREATE TABLE appsiel.stg_nmresumen_pagos_nomina AS
SELECT
  NULLIF(REGEXP_REPLACE(TRIM(r.ID_TERC), '[^0-9]', ''), '') AS ID_TERC_LIMPIO,
  STR_TO_DATE(r.FECHA_INICIAL, '%Y%m%d') AS FECHA_INICIAL_DT,
  STR_TO_DATE(r.FECHA_FINAL,   '%Y%m%d') AS FECHA_FINAL_DT,
  TRIM(r.ID_CONTRATO) AS ID_CONTRATO,
  TRIM(r.ID_CPTO) AS ID_CPTO,
  CAST(r.ID_IND_DEV_DED AS UNSIGNED) AS ID_IND_DEV_DED,
  CAST(r.NMMOV_VALOR AS DECIMAL(15,2)) AS NMMOV_VALOR,
  CAST(COALESCE(r.NMMOV_HORAS,0) AS DECIMAL(10,2)) AS NMMOV_HORAS,
  r.CO_MOV_DESC
FROM biable01.nmresumen_pagos_nomina r;

/* 3.2 Contratos: limpieza de ID_TERC por si se necesita validar pertenencia */
DROP TABLE IF EXISTS appsiel.stg_contratos;
CREATE TABLE appsiel.stg_contratos AS
SELECT
  TRIM(c.CODIGO) AS CODIGO_LIMPIO,
  NULLIF(REGEXP_REPLACE(TRIM(c.ID_TERC), '[^0-9]', ''), '') AS ID_TERC_LIMPIO,
  c.*
FROM biable01.contratos c;

/* 3.3 Empleados: limpieza de NIT (para core_terceros.numero_identificacion) */
DROP TABLE IF EXISTS appsiel.stg_empleados;
CREATE TABLE appsiel.stg_empleados AS
SELECT
  n.EMPLEADO,
  NULLIF(REGEXP_REPLACE(TRIM(n.NIT), '[^0-9]', ''), '') AS NIT_LIMPIO,
  TRIM(n.NOMBRE_COMPLETO) AS NOMBRE_COMPLETO,
  TRIM(n.APELLIDO1) AS APELLIDO1,
  TRIM(n.APELLIDO2) AS APELLIDO2,
  TRIM(n.NOMBRES) AS NOMBRES,
  TRIM(n.TELEFONO_2) AS TELEFONO_2,
  n.ESTADO_CONTRATO
FROM biable01.nmempleados n;


/* ==========================================================
   4) MIGRACIÓN: ENTIDADES (ENTIDADES → core_terceros + nom_entidades + map_entidad)
   ========================================================== */

/* 4.1 Terceros (entidades) por NIT limpio */
INSERT INTO appsiel.core_terceros
(descripcion, core_empresa_id, imagen, tipo, razon_social, nombre1, otros_nombres, apellido1, apellido2,
 id_tipo_documento_id, numero_identificacion, digito_verificacion, direccion1, direccion2, barrio, codigo_ciudad,
 codigo_postal, telefono1, telefono2, email, pagina_web, estado, user_id, contab_anticipo_cta_id,
 contab_cartera_cta_id, contab_cxp_cta_id, tax_level_code, creado_por, modificado_por, created_at, updated_at)
SELECT DISTINCT
  TRIM(e.DESCRIPCION) AS descripcion,
  @CORE_EMPRESA_ID AS core_empresa_id,
  '' AS imagen,
  'Entidad' AS tipo,
  TRIM(e.DESCRIPCION) AS razon_social,
  '' AS nombre1, '' AS otros_nombres, '' AS apellido1, '' AS apellido2,
  2 AS id_tipo_documento_id, /* NIT */
  CAST(NULLIF(REGEXP_REPLACE(TRIM(e.NIT), '[^0-9]', ''), '') AS UNSIGNED) AS numero_identificacion,
  0 AS digito_verificacion,
  '' AS direccion1, '' AS direccion2, '' AS barrio, 0 AS codigo_ciudad,
  '' AS codigo_postal, '' AS telefono1, '' AS telefono2, '' AS email, '' AS pagina_web,
  'Activo' AS estado,
  1 AS user_id, 0 AS contab_anticipo_cta_id, 0 AS contab_cartera_cta_id, 0 AS contab_cxp_cta_id,
  NULL AS tax_level_code, 'Migracion' AS creado_por, 'Migracion' AS modificado_por,
  NOW(), NOW()
FROM biable01.entidades e
WHERE NULLIF(REGEXP_REPLACE(TRIM(e.NIT), '[^0-9]', ''), '') IS NOT NULL;

/* 4.2 nom_entidades + clasificación por banderas */
INSERT INTO appsiel.nom_entidades (core_tercero_id, descripcion, codigo_nacional, tipo_entidad, estado, created_at, updated_at)
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
  END AS tipo_entidad,
  'Activo',
  NOW(), NOW()
FROM biable01.entidades e
JOIN appsiel.core_terceros t
  ON t.numero_identificacion = CAST(NULLIF(REGEXP_REPLACE(TRIM(e.NIT), '[^0-9]', ''), '') AS UNSIGNED)
WHERE TRIM(e.CODIGO) <> '';

/* 4.3 map_entidad */
INSERT IGNORE INTO appsiel.map_entidad (biable_entidad_codigo, tipo_entidad, appsiel_nom_entidad_id)
SELECT DISTINCT
  TRIM(e.CODIGO),
  ne.tipo_entidad,
  ne.id
FROM biable01.entidades e
JOIN appsiel.core_terceros t
  ON t.numero_identificacion = CAST(NULLIF(REGEXP_REPLACE(TRIM(e.NIT), '[^0-9]', ''), '') AS UNSIGNED)
JOIN appsiel.nom_entidades ne
  ON ne.core_tercero_id = t.id
 AND ne.codigo_nacional = TRIM(e.CODIGO)
WHERE TRIM(e.CODIGO) <> '';


/* ==========================================================
   5) MIGRACIÓN: EMPLEADOS (nmempleados → core_terceros)
   ========================================================== */
INSERT INTO appsiel.core_terceros
(descripcion, core_empresa_id, imagen, tipo, razon_social, nombre1, otros_nombres, apellido1, apellido2,
 id_tipo_documento_id, numero_identificacion, digito_verificacion, direccion1, direccion2, barrio, codigo_ciudad,
 codigo_postal, telefono1, telefono2, email, pagina_web, estado, user_id, contab_anticipo_cta_id,
 contab_cartera_cta_id, contab_cxp_cta_id, tax_level_code, creado_por, modificado_por, created_at, updated_at)
SELECT DISTINCT
  COALESCE(se.NOMBRE_COMPLETO,'') AS descripcion,
  @CORE_EMPRESA_ID AS core_empresa_id,
  '' AS imagen,
  'Persona' AS tipo,
  COALESCE(se.NOMBRE_COMPLETO,'') AS razon_social,
  COALESCE(se.NOMBRES,'') AS nombre1,
  '' AS otros_nombres,
  COALESCE(se.APELLIDO1,'') AS apellido1,
  COALESCE(se.APELLIDO2,'') AS apellido2,
  1 AS id_tipo_documento_id, /* CC: si tienes regla mejor, ajusta aquí */
  CAST(se.NIT_LIMPIO AS UNSIGNED) AS numero_identificacion,
  0 AS digito_verificacion,
  '' AS direccion1, '' AS direccion2, '' AS barrio, 0 AS codigo_ciudad,
  '' AS codigo_postal, '' AS telefono1, COALESCE(se.TELEFONO_2,'') AS telefono2,
  '' AS email, '' AS pagina_web,
  CASE WHEN se.ESTADO_CONTRATO='1' THEN 'Activo' ELSE 'Inactivo' END AS estado,
  1 AS user_id, 0 AS contab_anticipo_cta_id, 0 AS contab_cartera_cta_id, 0 AS contab_cxp_cta_id,
  NULL AS tax_level_code, 'Migracion' AS creado_por, 'Migracion' AS modificado_por,
  NOW(), NOW()
FROM appsiel.stg_empleados se
WHERE se.NIT_LIMPIO IS NOT NULL;


/* ==========================================================
   6) MIGRACIÓN: CONCEPTOS (conceptos_nomina → nom_conceptos + map_concepto)
   ========================================================== */
INSERT INTO appsiel.nom_conceptos
(descripcion, modo_liquidacion_id, naturaleza, porcentaje_sobre_basico, valor_fijo, abreviatura,
 forma_parte_basico, nom_agrupacion_id, cpto_dian_id, estado, created_at, updated_at)
SELECT DISTINCT
  TRIM(c.DESCRIPCION) AS descripcion,
  1 AS modo_liquidacion_id,
  CASE WHEN c.NATURALEZA='2' THEN 'Deducción' ELSE 'Devengo' END AS naturaleza,
  0, 0,
  TRIM(c.CODIGO) AS abreviatura,
  0,
  1 AS nom_agrupacion_id,
  0 AS cpto_dian_id,
  'Activo',
  NOW(), NOW()
FROM biable01.conceptos_nomina c
WHERE TRIM(c.CODIGO) <> '';

INSERT INTO appsiel.map_concepto (biable_concepto_codigo, appsiel_nom_concepto_id)
SELECT
  TRIM(c.CODIGO),
  nc.id
FROM biable01.conceptos_nomina c
JOIN appsiel.nom_conceptos nc
  ON nc.abreviatura = TRIM(c.CODIGO)
WHERE TRIM(c.CODIGO) <> '';


/* ==========================================================
   7) MIGRACIÓN: CONTRATOS (contratos → nom_contratos + map_contrato)
   ========================================================== */
INSERT INTO appsiel.nom_contratos
(core_tercero_id, clase_contrato, cargo_id, grupo_empleado_id, clase_riesgo_laboral_id,
 horas_laborales, sueldo, salario_integral, fecha_ingreso, contrato_hasta,
 entidad_salud_id, entidad_pension_id, entidad_arl_id, estado, created_at, updated_at,
 liquida_subsidio_transporte, planilla_pila_id, es_pasante_sena, entidad_cesantias_id,
 entidad_caja_compensacion_id, genera_planilla_integrada, tipo_cotizante, turno_default_id, fingerprint_reader_id)
SELECT
  t.id AS core_tercero_id,
  'MIGRADO' AS clase_contrato,
  1 AS cargo_id,
  1 AS grupo_empleado_id,
  1 AS clase_riesgo_laboral_id,
  0 AS horas_laborales,
  COALESCE(c.SALARIO,0) AS sueldo,
  0 AS salario_integral,
  STR_TO_DATE(c.FECHA_INGRESO,'%Y%m%d') AS fecha_ingreso,
  NULLIF(STR_TO_DATE(c.FECHA_RETIRO,'%Y%m%d'), STR_TO_DATE('00000000','%Y%m%d')) AS contrato_hasta,

  (SELECT me.appsiel_nom_entidad_id FROM appsiel.map_entidad me WHERE me.biable_entidad_codigo = TRIM(c.EPS)    AND me.tipo_entidad='EPS' LIMIT 1) AS entidad_salud_id,
  (SELECT me.appsiel_nom_entidad_id FROM appsiel.map_entidad me WHERE me.biable_entidad_codigo = TRIM(c.PENSION) AND me.tipo_entidad='AFP' LIMIT 1) AS entidad_pension_id,
  (SELECT me.appsiel_nom_entidad_id FROM appsiel.map_entidad me WHERE me.biable_entidad_codigo = TRIM(c.ARP)    AND me.tipo_entidad='ARP' LIMIT 1) AS entidad_arl_id,

  CASE WHEN c.FECHA_RETIRO IS NULL OR TRIM(c.FECHA_RETIRO)='' OR TRIM(c.FECHA_RETIRO)='00000000'
       THEN 'Activo' ELSE 'Inactivo' END AS estado,
  NOW(), NOW(),

  0, 0, 0, 0,
  (SELECT me.appsiel_nom_entidad_id FROM appsiel.map_entidad me WHERE me.biable_entidad_codigo = TRIM(c.CAJA) AND me.tipo_entidad='CCF' LIMIT 1) AS entidad_caja_compensacion_id,
  0, '', 0, 0
FROM biable01.contratos c
JOIN appsiel.core_terceros t
  ON t.numero_identificacion = CAST(NULLIF(REGEXP_REPLACE(TRIM(c.ID_TERC), '[^0-9]', ''), '') AS UNSIGNED)
WHERE TRIM(c.CODIGO) <> '';

/* Map contrato: CODIGO (Biable) → id (Appsiel)
   Como Appsiel está vacío, hacemos join por tercero + orden (no ideal) NO.
   Mejor: llenamos map_contrato usando una tabla staging de CODIGO y el tercero.
   Para eso usamos stg_contratos y buscamos el contrato Appsiel del mismo tercero con fecha_ingreso/sueldo si lo deseas.
   En vacío, lo más estable es: insertar contratos en Appsiel en el mismo orden que Biable y mapear por CODIGO usando una tabla auxiliar.
*/
DROP TABLE IF EXISTS appsiel.stg_contratos_insertados;
CREATE TABLE appsiel.stg_contratos_insertados AS
SELECT
  TRIM(c.CODIGO) AS CODIGO,
  CAST(NULLIF(REGEXP_REPLACE(TRIM(c.ID_TERC), '[^0-9]', ''), '') AS UNSIGNED) AS ID_TERC_NUM,
  STR_TO_DATE(c.FECHA_INGRESO,'%Y%m%d') AS FECHA_INGRESO_DT,
  COALESCE(c.SALARIO,0) AS SALARIO
FROM biable01.contratos c
WHERE TRIM(c.CODIGO) <> ''
  AND NULLIF(REGEXP_REPLACE(TRIM(c.ID_TERC), '[^0-9]', ''), '') IS NOT NULL;

INSERT IGNORE INTO appsiel.map_contrato (biable_contrato_codigo, appsiel_nom_contrato_id)
SELECT DISTINCT
  sci.CODIGO,
  nc.id
FROM appsiel.stg_contratos_insertados sci
JOIN appsiel.core_terceros t
  ON t.numero_identificacion = sci.ID_TERC_NUM
JOIN appsiel.nom_contratos nc
  ON nc.core_tercero_id = t.id
 AND nc.fecha_ingreso = sci.FECHA_INGRESO_DT
 AND nc.sueldo = sci.SALARIO;

/* Map cargo: CODIGO (Biable) → id (Appsiel) */
INSERT IGNORE INTO appsiel.map_cargo (biable_cargo_codigo, appsiel_nom_cargo_id)
SELECT DISTINCT
  TRIM(c.CODIGO),
  nc.id
FROM biable01.cargos c
JOIN appsiel.nom_cargos nc
  ON nc.descripcion = TRIM(c.DESCRIPCION)
WHERE TRIM(c.CODIGO) <> ''
  AND TRIM(c.DESCRIPCION) <> '';

/* Actualizar cargo en contratos usando map_contrato + biable01.contratos */
UPDATE appsiel.nom_contratos nc
JOIN appsiel.map_contrato mc
  ON mc.appsiel_nom_contrato_id = nc.id
JOIN biable01.contratos bc
  ON bc.CODIGO = mc.biable_contrato_codigo
JOIN appsiel.map_cargo mcar
  ON mcar.biable_cargo_codigo = TRIM(bc.ID_CARGO)
SET nc.cargo_id = mcar.appsiel_nom_cargo_id;


/* ==========================================================
   8) ENCABEZADOS POR PERIODO (FECHA_INICIAL/FECHA_FINAL)
   ========================================================== */
DROP TABLE IF EXISTS appsiel.stg_periodos_nomina;
CREATE TABLE appsiel.stg_periodos_nomina AS
SELECT DISTINCT
  FECHA_INICIAL_DT AS fecha_inicial,
  FECHA_FINAL_DT   AS fecha_final
FROM appsiel.stg_nmresumen_pagos_nomina
WHERE FECHA_INICIAL_DT IS NOT NULL
  AND FECHA_FINAL_DT IS NOT NULL;

SET @base := (
  SELECT IFNULL(MAX(CAST(consecutivo AS UNSIGNED)), 0)
  FROM appsiel.nom_doc_encabezados
  WHERE core_empresa_id = @CORE_EMPRESA_ID
    AND core_tipo_transaccion_id = @CORE_TIPO_TRANSACCION_ID
    AND core_tipo_doc_app_id = @CORE_TIPO_DOC_APP_ID
);
SET @i := 0;

INSERT INTO appsiel.nom_doc_encabezados
(core_tipo_transaccion_id, core_tipo_doc_app_id, consecutivo, fecha, core_empresa_id,
 descripcion, total_devengos, total_deducciones, estado, creado_por, modificado_por, created_at, updated_at)
SELECT
  @CORE_TIPO_TRANSACCION_ID,
  @CORE_TIPO_DOC_APP_ID,
  (@base + (@i := @i + 1)) AS consecutivo,
  p.fecha_final AS fecha,
  @CORE_EMPRESA_ID,
  CONCAT(CONVERT(UNHEX('4D696772616369C3B36E206EC3B36D696E6120') USING utf8mb4),
         DATE_FORMAT(p.fecha_inicial,'%Y-%m-%d'),
         ' a ', DATE_FORMAT(p.fecha_final,'%Y-%m-%d')) AS descripcion,
  0, 0,
  'Activo',
  'Migracion','Migracion',
  NOW(), NOW()
FROM appsiel.stg_periodos_nomina p
ORDER BY p.fecha_inicial, p.fecha_final;

DROP TABLE IF EXISTS appsiel.map_nom_periodo_encabezado;
CREATE TABLE appsiel.map_nom_periodo_encabezado AS
SELECT
  e.id AS nom_doc_encabezado_id,
  p.fecha_inicial,
  p.fecha_final
FROM appsiel.nom_doc_encabezados e
JOIN appsiel.stg_periodos_nomina p
  ON e.fecha = p.fecha_final
 AND e.descripcion = CONCAT('Migración nómina ', DATE_FORMAT(p.fecha_inicial,'%Y-%m-%d'),
                            ' a ', DATE_FORMAT(p.fecha_final,'%Y-%m-%d'))
WHERE e.core_empresa_id = @CORE_EMPRESA_ID
  AND e.core_tipo_transaccion_id = @CORE_TIPO_TRANSACCION_ID
  AND e.core_tipo_doc_app_id = @CORE_TIPO_DOC_APP_ID
  AND e.creado_por = 'Migracion';


/* ==========================================================
   9) INSERTAR HISTÓRICOS EXACTOS (nom_doc_registros)
   ========================================================== */

/* Limpieza preventiva: si hay registros sin identificación limpia, se excluyen */
INSERT INTO appsiel.nom_doc_registros
(nom_doc_encabezado_id, core_tercero_id, nom_contrato_id, fecha, core_empresa_id,
 detalle, nom_concepto_id, cantidad_horas, porcentaje, valor_devengo, valor_deduccion,
 estado, creado_por, modificado_por, created_at, updated_at, codigo_referencia_tercero)
SELECT
  mpe.nom_doc_encabezado_id,
  t.id AS core_tercero_id,
  mc.appsiel_nom_contrato_id AS nom_contrato_id,
  mpe.fecha_final AS fecha,
  @CORE_EMPRESA_ID AS core_empresa_id,
  COALESCE(s.CO_MOV_DESC,'') AS detalle,
  mp.appsiel_nom_concepto_id AS nom_concepto_id,
  COALESCE(s.NMMOV_HORAS,0) AS cantidad_horas,
  0 AS porcentaje,
  CASE WHEN s.ID_IND_DEV_DED=1 THEN s.NMMOV_VALOR ELSE 0 END AS valor_devengo,
  CASE WHEN s.ID_IND_DEV_DED=2 THEN s.NMMOV_VALOR ELSE 0 END AS valor_deduccion,
  'Activo',
  'Migracion','Migracion',
  NOW(), NOW(),
  t.numero_identificacion AS codigo_referencia_tercero
FROM appsiel.stg_nmresumen_pagos_nomina s
JOIN appsiel.map_nom_periodo_encabezado mpe
  ON mpe.fecha_inicial = s.FECHA_INICIAL_DT
 AND mpe.fecha_final   = s.FECHA_FINAL_DT
JOIN appsiel.core_terceros t
  ON t.numero_identificacion = CAST(s.ID_TERC_LIMPIO AS UNSIGNED)
JOIN appsiel.stg_contratos c
  ON c.CODIGO_LIMPIO = s.ID_CONTRATO
 AND c.ID_TERC_LIMPIO = s.ID_TERC_LIMPIO   /* asegura que el contrato pertenece a la identificación */
JOIN appsiel.map_contrato mc
  ON mc.biable_contrato_codigo = c.CODIGO
JOIN appsiel.map_concepto mp
  ON mp.biable_concepto_codigo = s.ID_CPTO
WHERE s.ID_TERC_LIMPIO IS NOT NULL;


/* ==========================================================
   10) ACTUALIZAR TOTALES EN ENCABEZADOS
   ========================================================== */
UPDATE appsiel.nom_doc_encabezados e
JOIN (
  SELECT
    nom_doc_encabezado_id,
    SUM(valor_devengo) AS total_dev,
    SUM(valor_deduccion) AS total_ded
  FROM appsiel.nom_doc_registros
  WHERE creado_por='Migracion'
    AND core_empresa_id=@CORE_EMPRESA_ID
  GROUP BY nom_doc_encabezado_id
) t ON t.nom_doc_encabezado_id = e.id
SET e.total_devengos = t.total_dev,
    e.total_deducciones = t.total_ded
WHERE e.core_empresa_id=@CORE_EMPRESA_ID
  AND e.core_tipo_transaccion_id=@CORE_TIPO_TRANSACCION_ID
  AND e.core_tipo_doc_app_id=@CORE_TIPO_DOC_APP_ID
  AND e.creado_por='Migracion';


/* ==========================================================
   11) VALIDACIONES
   ========================================================== */

/* 11.1 Huérfanos (debe ser 0) */
SELECT COUNT(*) AS huerfanos
FROM appsiel.nom_doc_registros
WHERE creado_por='Migracion'
  AND (core_tercero_id IS NULL OR nom_contrato_id IS NULL OR nom_concepto_id IS NULL);

/* 11.2 Conciliación Biable por periodo */
SELECT
  DATE_FORMAT(FECHA_INICIAL_DT,'%Y-%m-%d') AS fecha_inicial,
  DATE_FORMAT(FECHA_FINAL_DT,'%Y-%m-%d')   AS fecha_final,
  SUM(CASE WHEN ID_IND_DEV_DED=1 THEN NMMOV_VALOR ELSE 0 END) AS devengos,
  SUM(CASE WHEN ID_IND_DEV_DED=2 THEN NMMOV_VALOR ELSE 0 END) AS deducciones
FROM appsiel.stg_nmresumen_pagos_nomina
GROUP BY FECHA_INICIAL_DT, FECHA_FINAL_DT
ORDER BY FECHA_INICIAL_DT, FECHA_FINAL_DT;

/* 11.3 Conciliación Appsiel por documento */
SELECT
  descripcion, fecha, total_devengos, total_deducciones
FROM appsiel.nom_doc_encabezados
WHERE core_empresa_id=@CORE_EMPRESA_ID
  AND core_tipo_transaccion_id=@CORE_TIPO_TRANSACCION_ID
  AND core_tipo_doc_app_id=@CORE_TIPO_DOC_APP_ID
  AND creado_por='Migracion'
ORDER BY fecha;

SET FOREIGN_KEY_CHECKS := @OLD_FOREIGN_KEY_CHECKS;
