CREATE 
    ALGORITHM = UNDEFINED 
    DEFINER = `root`@`%` 
    SQL SECURITY DEFINER
VIEW `vista_liquidaciones` AS
    SELECT 
        `tk`.`tik_id` AS `tik_id`,
        CONCAT(`cl`.`cli_nombre`,
                ' ',
                `cl`.`cli_apellido_paterno`,
                '',
                `cl`.`cli_apellido_materno`) AS `cl_nombre`,
        `tk`.`cli_id` AS `cli_id`,
        `es`.`est_descripcion` AS `est_descripcion`,
        `es`.`est_id` AS `est_id`,
        `tk`.`mar_id` AS `mar_id`,
        `tk`.`lin_id` AS `lin_id`,
        `tk`.`sul_id` AS `sul_id`,
        `tk`.`cat_id` AS `cat_id`,
        `ca`.`cat_denominacion` AS `cat_denominacion`,
        `tk`.`suc_id` AS `suc_id`,
        `tk`.`fab_id` AS `fab_id`,
        `tk`.`tik_garantia_meses` AS `tik_garantia_meses`,
        `tk`.`tik_descripcion_producto` AS `tik_descripcion_producto`,
        `tk`.`dis_id` AS `dis_id`,
        `dis`.`dis_razon_social` AS `dis_razon_social`,
        `tk`.`tik_numero_serie` AS `tik_numero_serie`,
        `tk`.`tik_numero_factura` AS `tik_numero_factura`,
        `tk`.`tik_observacion_problema` AS `tik_observacion_problema`,
        `tk`.`tie_id` AS `tie_id`,
        `tk`.`fac_id` AS `fac_id`,
        `tk`.`tik_fecha_reclamo` AS `tik_fecha_reclamo`,
        `tk`.`tik_modelo` AS `tik_modelo`,
        `tk`.`tik_fecha` AS `tik_fecha`,
        `tk`.`tik_usuario` AS `tik_usuario`,
        `tk`.`tik_fecha_actualizacion` AS `tik_fecha_actualizacion`,
        `tk`.`ces_id` AS `ces_id`,
        `tk`.`cld_id` AS `cld_id`,
        `tk`.`liq_id` AS `liq_id`,
        `tk`.`ods_liquidacion_estado` AS `ods_liquidacion_estado`,
        (CASE
            WHEN (`os`.`ods_reingreso` = 1) THEN 'SI'
            ELSE 'NO'
        END) AS `ods_reingreso`,
        (CASE
            WHEN (`os`.`ods_garantia_fabrica` = 1) THEN 'SI'
            ELSE 'NO'
        END) AS `ods_garantia_fabrica`,
        `tic`.`tic_descripcion` AS `tic_descripcion`
    FROM
        ((((((`ticket_cliente` `tk`
        LEFT JOIN `orden_servicio` `os` ON ((`tk`.`tik_id` = `os`.`ods_id`)))
        LEFT JOIN `estados` `es` ON ((`es`.`est_id` = `os`.`est_id`)))
        LEFT JOIN `clientes` `cl` ON ((`cl`.`cli_id` = `tk`.`cli_id`)))
        LEFT JOIN `categorias` `ca` ON ((`ca`.`cat_id` = `tk`.`cat_id`)))
        LEFT JOIN `distribuidores` `dis` ON ((`dis`.`dis_id` = `tk`.`dis_id`)))
        LEFT JOIN `tipos_reclamo` `tic` ON ((`tic`.`tic_id` = `os`.`tic_id`)))
    WHERE
        (`os`.`est_archivado` IS NULL)