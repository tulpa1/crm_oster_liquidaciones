INSERT INTO `oster_crm_ds_prd`.`otros_gastos`
(
`concepto`,
`detalle`,
`centro_costo`,
`cuenta_contable`,
`valor_unida`,
`cantidad`,
`sub_total`,
`estado`)
VALUES
(
'Compra producto LB',
'Cocina Electrica Mod. AABBCCDO',
'90HSPE0015',
'7211000001',
100,
0,
0,
1);


INSERT INTO `oster_crm_ds_prd`.`otros_gastos`
(
`concepto`,
`detalle`,
`centro_costo`,
`cuenta_contable`,
`valor_unida`,
`cantidad`,
`sub_total`,
`estado`)
VALUES
(
'Reworks',
'Torres de lavado de Azufre',
'90HSPE0015',
'7211000001',
100,
0,
0,
1);


INSERT INTO `oster_crm_ds_prd`.`otros_gastos`
(
`concepto`,
`detalle`,
`centro_costo`,
`cuenta_contable`,
`valor_unida`,
`cantidad`,
`sub_total`,
`estado`)
VALUES
(
'Almacenaje',
'Almacenamiento de respuestos May25',
'90HSPE0015',
'7211000001',
100,
0,
0,
1);

INSERT INTO `oster_crm_ds_prd`.`otros_gastos`
(
`concepto`,
`detalle`,
`centro_costo`,
`cuenta_contable`,
`valor_unida`,
`cantidad`,
`sub_total`,
`estado`)
VALUES
(
'Comision Bancaria',
'Por pago de liquidacion May25',
'90HSPE0015',
'7211000001',
100,
0,
0,
1);

CREATE TABLE `oster_crm_ds_prd`.`detalle_otros_gastos` (
  `iddetalle_otros_gastos` INT NOT NULL AUTO_INCREMENT,
  `id_otros_gastos` INT NULL,
  `liq_id` INT NULL,
  PRIMARY KEY (`iddetalle_otros_gastos`));
  
  CREATE TABLE `oster_crm_ds_prd`.`otros_gastos_documentos` (
  `id_doc_otros_gastos` INT NOT NULL AUTO_INCREMENT,
  `id_otros_gastos` INT NULL,
  `gastos_file_name` VARCHAR(60) NULL,
  `gastos_file_path` VARCHAR(60) NULL,
  `gastos_file_size` INT NULL,
  PRIMARY KEY (`id_doc_otros_gastos`));

CREATE TABLE `oster_crm_ds_prd`.`centro_coste` (
  `idcentro_coste` INT NOT NULL AUTO_INCREMENT,
  `centro_coste` VARCHAR(13) NULL,
  `Denominacion` VARCHAR(45) NULL,
  `Responsable` VARCHAR(45) NULL,
  `Departamento` VARCHAR(60) NULL,
  `Moneda` VARCHAR(3) NULL,
  PRIMARY KEY (`idcentro_coste`));
  
  INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSAG0001','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES( '90HSAH0024', 'ANTILLAS HOLANDESAS','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSBE0019','Belice','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSBO0002','Bolivia','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSBR0022','BRASIL', 'GERENCIA OPERACIONES','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSCH0003','Chile','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSCH0003','Chile','Gerencia Operaciones','USD');


INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSCH0018','Colombia','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSCR0005','Costa Rica','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSEQ0006','Ecuador','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSGT0009','Guatemala','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSGU0023','GUYANA','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSHK0021','Hong Kong','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSHN0010','Honduras','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSJA0001','JAMAICA','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSLC0001','SANTA LUCIA','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSMX0011','Mexico','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSNI0012','Nicaragua','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSOF0013','Oficina','Gerencia Operaciones','USD');


INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSPA0014','Panamá','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSPE0015','Perú','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSPG0013','PARAGUAY','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSPR0021','Puerto Rico','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSRD0016','República Dominicana','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSSA0008','El Salvador','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSSALV08','El Salvador','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSUG0017','Uruguay','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSUS0007','EEUU','Gerencia Operaciones','USD');
INSERT INTO centro_coste(centro_coste,Denominacion,Responsable,Moneda)VALUES('90HSVE0020','Venezuela','Gerencia Operaciones','USD');


CREATE TABLE `oster_crm_ds_prd`.`otros_gastos_conceptos` (
  `idotros_gastos_conceptos` INT NOT NULL AUTO_INCREMENT,
  `concepto_ingle` VARCHAR(50) NULL,
  `concepto_esp` VARCHAR(50) NULL,
  `estado` INT NULL,
  PRIMARY KEY (`idotros_gastos_conceptos`));


INSERT INTO `oster_crm_ds_prd`.`otros_gastos_conceptos` (`concepto_esp`, `estado`) VALUES ('Reworks', '1');
INSERT INTO `oster_crm_ds_prd`.`otros_gastos_conceptos` (`concepto_esp`, `estado`) VALUES ('Almacenaje', '1');
INSERT INTO `oster_crm_ds_prd`.`otros_gastos_conceptos` (`concepto_esp`, `estado`) VALUES ('Comision Bancaria', '1');
