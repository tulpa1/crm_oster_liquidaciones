CREATE TABLE `estado_archivado` (
  `est_id` int NOT NULL AUTO_INCREMENT,
  `est_descripcion_1` varchar(100) DEFAULT NULL,
  `est_descripcion_2` varchar(100) DEFAULT NULL,
  `est_des_ingles_1` varchar(100) DEFAULT NULL,
  `est_des_ingles_2` varchar(100) DEFAULT NULL,
  `est_fecha` datetime DEFAULT NULL,
  `est_fecha_actualizacion` datetime DEFAULT NULL,
  `est_usuario_actualizacion` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`est_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `oster_crm_ds_prd`.`estado_archivado`
(`est_descripcion_1`,
`est_descripcion_2`,
`est_des_ingles_1`,
`est_des_ingles_2`,
`est_fecha`)
VALUES
(
"Tarifa errónea",
"El CDS asigna una tarifa diferente por el trabajo realizado",
"Wrong rate",
"The CDS assigns a different rate for the work performed",
now());

INSERT INTO `oster_crm_ds_prd`.`estado_archivado`
(`est_descripcion_1`,
`est_descripcion_2`,
`est_des_ingles_1`,
`est_des_ingles_2`,
`est_fecha`)
VALUES
(
"Mal diagnóstico",
"El informe del CDS no contiene la información necesaria para sustentar el caso",
"Misdiagnosis",
"The CDS report does not contain the necessary information to support the case",
now());

INSERT INTO `oster_crm_ds_prd`.`estado_archivado`
(`est_descripcion_1`,
`est_descripcion_2`,
`est_des_ingles_1`,
`est_des_ingles_2`,
`est_fecha`)
VALUES
(
"Reparación fallida",
"La reparación realizada no solucionó el problema reportado",
"Repair failed",
"The repair performed did not solve the reported problem",
now());

INSERT INTO `oster_crm_ds_prd`.`estado_archivado`
(`est_descripcion_1`,
`est_descripcion_2`,
`est_des_ingles_1`,
`est_des_ingles_2`,
`est_fecha`)
VALUES
(
"Caso Duplicado",
"El caso fue atendido y pagado con un número de caso diferente",
"Duplicate Case",
"The case was attended to and paid for under a different case number",
now());

INSERT INTO `oster_crm_ds_prd`.`estado_archivado`
(`est_descripcion_1`,
`est_descripcion_2`,
`est_des_ingles_1`,
`est_des_ingles_2`,
`est_fecha`)
VALUES
(
"Fuera de plazo",
"Caso excede el plazo límite acordado con el centro de servicio",
"Out of date",
"Case exceeds the deadline agreed with the service center",
now());
