CREATE TABLE `liquidaciones_fechas_cierre` (
  `liq_fecha_id` int NOT NULL AUTO_INCREMENT,
  `liq_fecha_cierre` datetime DEFAULT NULL,
  `liq_plazo` int DEFAULT NULL,
  PRIMARY KEY (`liq_fecha_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
