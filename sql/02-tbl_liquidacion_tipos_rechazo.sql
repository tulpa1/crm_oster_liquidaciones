CREATE TABLE `liquidacion_tipos_rechazo` (
  `ltr_id` int NOT NULL AUTO_INCREMENT,
  `ltr_descripcion` varchar(50) NOT NULL DEFAULT 'null',
  `ltr_descripcion_detallada` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`ltr_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `oster_crm_ds_prd`.`liquidacion_tipos_rechazo` (`ltr_descripcion`, `ltr_descripcion_detallada`) VALUES ('Error en documento', 'Información de boleta o factura CDS no corresponde con el monto de la liquidación');
INSERT INTO `oster_crm_ds_prd`.`liquidacion_tipos_rechazo` (`ltr_descripcion`, `ltr_descripcion_detallada`) VALUES (' Revisión de tarifa', 'Las tarifas asignadas no corresponden al tipo de servicio y/o gasto realizado');