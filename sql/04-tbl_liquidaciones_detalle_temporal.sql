CREATE TABLE `liquidaciones_detalle_temporal` (
  `ldt_id` int NOT NULL AUTO_INCREMENT,
  `liq_id` int DEFAULT NULL,
  `tik_id` int DEFAULT NULL,
  PRIMARY KEY (`ldt_id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
