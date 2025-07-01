CREATE 
    ALGORITHM = UNDEFINED 
    DEFINER = `root`@`%` 
    SQL SECURITY DEFINER
VIEW `oster_crm_ds_prd`.`vista_preliquidaciones` AS
    SELECT 
        `os`.`ods_id` AS `ods_id`,
        `os`.`liq_id` AS `liq_id`,
        `tk`.`ces_id` AS `ces_id`,
        `cl`.`cli_nombre` AS `cli_nombre`,
        `ca`.`cat_denominacion` AS `cat_denominacion`,
        `prc`.`pro_modelo` AS `tik_modelo`,
        `est`.`est_descripcion` AS `est_descripcion`,
        `est`.`est_id` AS `est_id`,
        `os`.`ods_fecha_finalizacion` AS `ods_fecha_atencion`,
        SUM((CASE
            WHEN (`c`.`ocs_descripcion` = 'REVISION') THEN `c`.`cos_costo`
            ELSE 0
        END)) AS `liq_revision`,
        SUM((CASE
            WHEN (`c`.`ocs_descripcion` LIKE '%MOVILIZACION%') THEN `c`.`cos_costo`
            WHEN (`c`.`ocs_descripcion` LIKE '%MOBILIZATION%') THEN `c`.`cos_costo`
            ELSE 0
        END)) AS `liq_movilizacion`,
        SUM((CASE
            WHEN (`c`.`ocs_descripcion` IN ('REPARACION' , 'REPAIR')) THEN `c`.`cos_costo`
            ELSE 0
        END)) AS `liq_reparacion`,
        SUM((CASE
            WHEN (`c`.`ocs_descripcion` LIKE '%COMPRA LOCAL%') THEN `c`.`cos_costo`
            WHEN (`c`.`ocs_descripcion` LIKE '%LOCAL PURCHASE%') THEN `c`.`cos_costo`
            ELSE 0
        END)) AS `liq_refaccion`,
        SUM((CASE
            WHEN (`c`.`ocs_descripcion` LIKE '%CAMBIO%') THEN `c`.`cos_costo`
            WHEN (`c`.`ocs_descripcion` LIKE '%CHANGE%') THEN `c`.`cos_costo`
            ELSE 0
        END)) AS `liq_cambio_producto`,
        SUM((CASE
            WHEN (`c`.`ocs_descripcion` = 'CARGA DE GAS') THEN `c`.`cos_costo`
            ELSE 0
        END)) AS `liq_carga_gas`,
        SUM((CASE
            WHEN (`c`.`ocs_descripcion` IN ('OTROS GASTOS' , 'OTHER EXPENSES')) THEN `c`.`cos_costo`
            ELSE 0
        END)) AS `liq_otros_gastos`,
        IFNULL(SUM(`c`.`cos_impuesto`), 0.00) AS `cos_impuesto`,
        IFNULL(SUM(`c`.`cos_costo`), 0.00) AS `cos_subtotal`,
        SUM((CASE
            WHEN (`c`.`ocs_descripcion` IN ('DESPIECE' , 'SCRAP')) THEN `c`.`cos_costo`
            ELSE 0
        END)) AS `liq_despiece`,
        (CASE
            WHEN (`os`.`ods_reingreso` = 1) THEN 'SI'
            ELSE 'NO'
        END) AS `ods_reingreso`,
        (CASE
            WHEN (`os`.`ods_garantia_fabrica` = 1) THEN 'SI'
            ELSE 'NO'
        END) AS `ods_garantia_fabrica`,
        `tk`.`tik_numero_serie` AS `tik_numero_serie`,
        `tic`.`tic_descripcion` AS `tic_descripcion`
    FROM
        (((((((`oster_crm_ds_prd`.`orden_servicio` `os`
        LEFT JOIN `oster_crm_ds_prd`.`costos` `c` ON ((`os`.`ods_id` = `c`.`ods_id`)))
        LEFT JOIN `oster_crm_ds_prd`.`ticket_cliente` `tk` ON ((`tk`.`tik_id` = `os`.`tik_id`)))
        LEFT JOIN `oster_crm_ds_prd`.`clientes` `cl` ON ((`cl`.`cli_id` = `tk`.`cli_id`)))
        LEFT JOIN `oster_crm_ds_prd`.`categorias` `ca` ON ((`ca`.`cat_id` = `tk`.`cat_id`)))
        LEFT JOIN `oster_crm_ds_prd`.`estados` `est` ON ((`os`.`est_id` = `est`.`est_id`)))
        LEFT JOIN `oster_crm_ds_prd`.`productos` `prc` ON ((`tk`.`tik_modelo` = `prc`.`pro_id`)))
        LEFT JOIN `oster_crm_ds_prd`.`tipos_reclamo` `tic` ON ((`tic`.`tic_id` = `os`.`tic_id`)))
    GROUP BY `os`.`ods_id` , `os`.`ods_fecha_atencion`