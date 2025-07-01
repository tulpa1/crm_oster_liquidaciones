<?php
try {
    $ces_id = {ces_id};
    $anio = date("Y");
    $liq_id = {liq_id};
    $contador = 0; 

    $sql = "SELECT name,
                    email,
                    (SELECT ces_descripcion FROM centro_servicio WHERE ces_id = $ces_id) AS centro,
                    (SELECT ces_tipo_pago FROM centro_servicio WHERE ces_id = $ces_id) AS tipo_pago
            FROM app_users
            WHERE type = 6 and active = 'Y'";

    sc_lookup(dataset, $sql);
    
	$nombre = {dataset[0][0]};
	$correo = {dataset[0][1]};
	$des_centro = {dataset[0][2]};
	$tipo_pago = {dataset[0][3]}; 

    // Inicializar el array de adjuntos
    $attachments = [];

    // Adjuntar el reporte PDF
    $pdf_path = crear_reporte_pdf({ces_id}, {liq_id}, {mano_obra_prd}, {kilometraje}, {repuestos}, {liq_numero_factura});
    if ($pdf_path && file_exists($pdf_path)) {
        $attachments[] = $pdf_path;
    } 

    // Adjuntar los documentos de facturas (usando array_merge para evitar anidamiento)
    $factura_paths = url_facturas({liq_id});
    if (is_array($factura_paths) && !empty($factura_paths)) {
        $attachments = array_merge($attachments, $factura_paths);
    }

    // Configuración del correo
    $var_config = array(
        'profile' => 'SMTP_OFFICE_ALIDAS', 
        'message' => [
            'html'        => 'Hola estimado(a) ' . $nombre . '. <br><br>La Liquidación N.º ' . $liq_id . ' fue aprobada. <br><br>El medio de pago sera - ' . $tipo_pago . ' <br><br> Adjunto encontrará la Factura de Cobro.<br><br>Saludos. ',
            'text'        => '', 
            'to'          => $correo,
            'subject'     => 'Liquidación ' . $anio . '-' . $liq_id . ' Aprobada - ' . $des_centro,
            'attachments' => $attachments
        ]
    );

    // Enviar el correo
	sc_send_mail_api($var_config);

} catch (Exception $e) {
    sc_error_message('Error inesperado: ' . $e->getMessage());
}
?>