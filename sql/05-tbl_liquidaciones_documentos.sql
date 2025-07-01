CREATE TABLE `liquidaciones_documentos` (
  `lio_id` int NOT NULL AUTO_INCREMENT,
  `liq_id` int DEFAULT NULL,
  `lio_file_name` varchar(255) DEFAULT NULL,
  `lio_file_path` varchar(255) DEFAULT NULL,
  `lio_file_size` int DEFAULT NULL,
  PRIMARY KEY (`lio_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
