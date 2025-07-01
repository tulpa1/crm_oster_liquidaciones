<?php
sc_include_library("prj","libreria_fpdf", "fpdf/fpdf.php");
sc_include_library("prj","libreria_fpdf", "footer.php");

// Obtiene las variables globales de Scriptcase directamente
$anio = date("Y");
$liq_numero_factura = $num_factura; 
$mano_obra_prd = $mano_obra_prd; 
$kilometraje = $kilometraje; 
$repuestos = $repuestos; 
$impuesto = {impuesto}; 
$sub_total = {sub_total}; 
$total_liquidacion = $mano_obra_prd + $kilometraje + $repuestos; 
$moneda = "USD";

$sqlfechas = "select date(liq_fecha_creacion), date(liq_fecha_liquidacion) from liquidaciones where liq_id = " . {liq_id};
sc_lookup(dtsfechas, $sqlfechas);

$liq_fecha = {dtsfechas[0][0]};
$liq_fecha_liquidacion = {dtsfechas[0][1]};


// Consulta para obtener datos de configuración del contador
$sql = "SELECT nombre,
                email,
                (SELECT ces_descripcion FROM centro_servicio WHERE ces_id = $ces_id) AS centro,
                (SELECT ces_tipo_pago FROM centro_servicio WHERE ces_id = $ces_id) AS tipo_pago
        FROM liquidaciones_confi
        WHERE tipo = 'contador' AND estado = 1";

sc_lookup(dataset, $sql);

// Inicializa variables para los datos del contador
$nombre = '';
$correo = '';
$des_centro = '';
$tipo_pago = '';

// Asigna los datos del resultado de la consulta si existen
if (isset({dataset[0]})) {
    $nombre = {dataset[0][0]};
    $correo = {dataset[0][1]};
    $des_centro = {dataset[0][2]};
    $tipo_pago = {dataset[0][3]};
}

// --- Obtener datos del encabezado ---

// Formatea el número de liquidación
$numero_liquidacion_val = str_pad($liq_id, 8, "0", STR_PAD_LEFT);

// Obtiene y verifica el número de factura
$numero_factura_val = isset($liq_numero_factura) && !empty($liq_numero_factura) ? $liq_numero_factura : '';

// Obtiene y formatea la fecha de liquidación a dd/mm/yyyy
$fecha_val = date('d/m/Y', strtotime($liq_fecha));


// Obetener datos del centro de servicio
$sql_centro_service = "SELECT cs.ces_descripcion, cs.ces_sap_id, cs.ces_cuenta_bancaria, cs.ces_email, 
						pai.pai_nombre
						FROM centro_servicio cs
						inner join paises pai on pai.pai_id = cs.pai_id
						where cs.ces_id = ". $ces_id;
sc_lookup(dts_cs, $sql_centro_service);
$centro_servicio_val = '';
if (isset({dts_cs[0][0]})) {
    $centro_servicio_val = {dts_cs[0][0]};
	$cod_sap_val = {dts_cs[0][1]};
	$cuenta_bancaria_val = {dts_cs[0][2]};
	$email_val = {dts_cs[0][3]};
	$pais_val = {dts_cs[0][4]};
}

// Consulta para contar las órdenes de servicio en la liquidación temporal
$check_sql = "select count(liq_id) as contador
                from liquidaciones_detalle_temporal
                where liq_id = ". $liq_id;
sc_lookup(rs, $check_sql);
$ordenes_service_val = 0;
if (isset({rs[0][0]})) {
    $ordenes_service_val = {rs[0][0]};
}

// Consulta para obtener el estado de la liquidación
$sql_estado = "select
                  case when liq_estado = 0 then  'En Proceso'
                  when liq_estado = 1 then  'Pre - Liquidación'
                  when liq_estado = 2 then 'Liquidación'
                  when liq_estado = 3 then 'Rechazada'
                  when liq_estado = 4 then 'En Proceso de Pago'
                  when liq_estado = 5 then 'Pagada'
                  else null
                  end as estado
                  from liquidaciones
                  where liq_id = " . $liq_id;
sc_lookup(dts_estado, $sql_estado);
$estado_val = '';
if (isset({dts_estado[0][0]})) {
    $estado_val = {dts_estado[0][0]};
}


// Consulta para obtener el tipo de pago del centro de servicio
$sql_metedo_pago = "select cs.ces_tipo_pago
                  from centro_servicio cs
                        where cs.ces_id = " . $ces_id;
sc_lookup(dts_met_pago, $sql_metedo_pago);
$metodo_pago_val = isset({dts_met_pago[0][0]}) ? {dts_met_pago[0][0]} : '';


// --- Datos para el detalle de las cuentas ---
$item = 'Item';
$cuenta = 'Cuenta';
$centro_costo = 'CeCo'; // <-- Variable para la cabecera de la nueva columna
$detalle = 'Detalle';
$subtotal_label = 'Sub - Total'; // Etiqueta para la cabecera

// Define los códigos de cuenta
$cuenta_mano_obra = '7211000002';
$cuenta_kilometraje = '7211000003';
$cuenta_repuestos = '7211000005';

// Consulta para obtener el centro de costo para el detalle de cuentas
$sql_ceco = "SELECT centro_coste FROM oster_crm_ds_prd.centro_coste cc
            left join paises pai on pai.pai_nombre = cc.Denominacion
            left join centro_servicio cs on cs.pai_id = pai.pai_id
            where cs.ces_id = $ces_id";
sc_lookup(dts_ceco, $sql_ceco);
$ceco_valor = isset({dts_ceco[0][0]}) ? {dts_ceco[0][0]} : ''; // Obtener el valor de ceco

// --- Nombre de las columnas para el detalle de casos (Actualizado) ---
$orden = 'Orden';
$modelo = 'Modelo';
$tipo_reclamo = 'Reclamo';
$revision = 'Revision';
$Movilizacion = 'Movilizac.';
$reparacion = 'Reparación';
$refaccion = 'Refacción';
$cambio_pro = 'Cambio Prod.';
$carga_gas = 'Carga Gas';
$otros_gastos_col_label = 'Otros Gastos'; // Etiqueta para la columna de otros gastos en detalle de casos
$despiece = 'Despiece';

// Consulta para obtener los datos del detalle de los casos (Actualizado)
$sql_casos = 'select ods_id, tik_modelo, tic_descripcion, liq_revision, liq_movilizacion, liq_reparacion, liq_refaccion, liq_cambio_producto, liq_carga_gas, liq_otros_gastos, liq_despiece
from vista_preliquidaciones where liq_id = ' . $liq_id;

// Ejecutar la consulta para el detalle de casos
sc_lookup(dts_casos, $sql_casos);

// <-- INICIO MODIFICACIÓN: Consulta para el detalle de otros gastos
// -- Datos para el detalle de los otros gastos --
$sql_otros_gastos = "select
                    cc.centro_coste
                    , og.cuenta_contable
                    , ogc.concepto
                    , og.subtotal
                    from otros_gastos og
                    inner join centro_coste cc on cc.idcentro_coste = og.id_centro_coste
                    inner join otros_gastos_conceptos ogc on ogc.idotros_gastos_conceptos = og.id_concepto
                    where liq_id = $liq_id";
sc_lookup(dts_otros_gastos, $sql_otros_gastos);
// <-- FIN MODIFICACIÓN: Consulta para el detalle de otros gastos


// --- Datos para el pie de pagina ---

// Obtiene los valores de las variables de Scriptcase para el pie de página
$fecha_preliquidacion_val = $liq_fecha;
$fecha_apro_liquidacion_val = $liq_fecha_liquidacion;

// Consulta para obtener los nombres de los usuarios de preliquidación y liquidación
$sql_usr_liq = 'select
                  au1.name
                , au2.name
                from liquidaciones liq
                inner join app_users au1 on au1.login = liq.liq_usuario_creacion
                inner join app_users au2 on au2.login = liq.liq_usuario_liquidacion
                where liq_id = ' . $liq_id;

sc_lookup(dts_usr_liq, $sql_usr_liq);

// Asigna los nombres de usuario si existen
$liq_usr_preliq_val = isset({dts_usr_liq[0][0]}) ? {dts_usr_liq[0][0]} : '';
$liq_usr_liq_val = isset({dts_usr_liq[0][1]}) ? {dts_usr_liq[0][1]} : '';


// --- Define las etiquetas de encabezado (aplicando utf8_decode) ---
$label_numero_liquidacion = utf8_decode('Liquidación ');
$label_numero_factura = utf8_decode('Numero de Factura: ');
$label_fecha = utf8_decode('Fecha: ');
$label_centro_servicio = utf8_decode('Centro Servicio: ');
$label_ordenes_servicio = utf8_decode('Ordenes de Servicio: ');
$label_pais = utf8_decode('Pais: ');
$label_metodo_pago = utf8_decode('Tipo Pago: ');
$label_cod_sap = utf8_decode('Código SAP: ');
$label_cuenta_bancaria = utf8_decode('Cuenta Bancaria: ');
$label_email = utf8_decode('Email: ');
$label_moneda = utf8_decode('Moneda: ');

// --- Labels para el pie de página (aplicando utf8_decode) ---
$label_fecha_preliquidacion = utf8_decode('Creado el: ');
$label_usuario_preliquidacion = utf8_decode('Creada por: ');
$label_fecha_liquidacion = utf8_decode('Aprobada el: ');
$label_usuario_liquidacion = utf8_decode('Aprobado por: ');
$label_firma = utf8_decode('Firma _________________________');


// --- Creación del objeto PDF usando la nueva clase y configuración inicial ---
$pdf = new PDF('P', 'mm', 'Letter');

// Habilitar el alias de número de página total ({nb}) - MOVIDO AQUÍ
$pdf->AliasNbPages();

// Asignar los datos del pie de página a las propiedades del objeto PDF
$pdf->fecha_preliquidacion = $fecha_preliquidacion_val;
$pdf->liq_usr_preliq = $liq_usr_preliq_val;
$pdf->fecha_apro_liquidacion = $fecha_apro_liquidacion_val;
$pdf->liq_usr_liq = $liq_usr_liq_val;

// Asignar los labels del pie de página a las propiedades del objeto PDF
$pdf->label_fecha_preliquidacion = $label_fecha_preliquidacion;
$pdf->label_usuario_preliquidacion = $label_usuario_preliquidacion;
$pdf->label_fecha_liquidacion = $label_fecha_liquidacion;
$pdf->label_usuario_liquidacion = $label_usuario_liquidacion;
$pdf->label_firma = $label_firma;

// Configura márgenes y añade la primera página
$pdf->SetLeftMargin(5);
$pdf->SetRightMargin(5);
$top_margin = 10;
$pdf->SetTopMargin($top_margin);
$pdf->AddPage();


// --- SECCIÓN DEL ENCABEZADO DEL REPORTE (2 COLUMNAS) ---

$pdf->SetFont('Arial', '', 9); 
$line_height = 5; // Altura de cada línea
$col_width = (216 - 10) / 2; // Ancho para 2 columnas (ancho total - márgenes / 2)
$label_col_width = 35; // Ancho estimado para las descripciones en negrita (ajusta si es necesario)

// Guardar la posición Y inicial de las columnas para mantenerlas alineadas
$start_y_cols = $pdf->GetY(); // Empieza en el margen superior

// >>> COLUMNA 1 (Información de Liquidación y Centro) <<<
$pdf->SetX(5); // Posicionar en el margen izquierdo para la primera columna
$pdf->SetY($start_y_cols); // Asegurar la posición Y inicial

// Fila 1, Col 1: Liquidación N.º
$pdf->SetFont('Arial', 'B', 9); // Negrita para la descripción
$pdf->Cell(20, 6, $label_numero_liquidacion, 0, 0, 'L');
$pdf->SetFont('Arial', 'B', 9); // Normal para el valor
$pdf->Cell(18, 6, utf8_decode($numero_liquidacion_val), 0, 1, 'L'); 

// Fila 2, Col 1: Centro Servicio
$pdf->SetX(5); // Volver al inicio de la columna 1
$pdf->SetFont('Arial', 'B', 18); // Normal
$pdf->Cell($col_width - $label_col_width, 14, utf8_decode($centro_servicio_val), 0, 1, 'L'); 

// Fila 3, Col 1: Código SAP
$pdf->SetX(5);
$pdf->SetFont('Arial', 'B', 9); 
$pdf->Cell(30, $line_height, $label_cod_sap, 0, 0, 'L');
$pdf->SetFont('Arial', '', 9); 
$pdf->Cell(20, $line_height, utf8_decode($cod_sap_val), 0, 1, 'L'); 

// Fila 4, Col 1: Método Pago
$pdf->SetX(5); 
$pdf->SetFont('Arial', 'B', 9); 
$pdf->Cell(30, $line_height, $label_metodo_pago, 0, 0, 'L');
$pdf->SetFont('Arial', '', 9); 
$pdf->Cell(30, $line_height, utf8_decode($metodo_pago_val), 0, 1, 'L');

// Fila 5, Col 1: Pais
$pdf->SetX(5); 
$pdf->SetFont('Arial', 'B', 9); 
$pdf->Cell(30, $line_height, $label_pais, 0, 0, 'L');
$pdf->SetFont('Arial', '', 9); 
$pdf->Cell(20, $line_height, utf8_decode($pais_val), 0, 1, 'L'); 
// Fila 6, Col 1: Cuenta Bancaria
$pdf->SetX(5); 
$pdf->SetFont('Arial', 'B', 9); 
$pdf->Cell(30, $line_height, $label_cuenta_bancaria, 0, 0, 'L');
$pdf->SetFont('Arial', '', 9); 
$pdf->Cell(20, $line_height, utf8_decode($cuenta_bancaria_val), 0, 1, 'L'); 
// Fila 6, Col 1: Cuenta Bancaria
$pdf->SetX(5); 
$pdf->SetFont('Arial', 'B', 9); 
$pdf->Cell(30, $line_height, $label_email, 0, 0, 'L');
$pdf->SetFont('Arial', '', 9); 
$pdf->Cell(20, $line_height, utf8_decode($email_val), 0, 1, 'L');

// >>> COLUMNA 2 (Logo y Otros Datos) <<<
$end_y_col1 = $pdf->GetY();

$pdf->SetX(5 + $col_width ); 
$pdf->SetY($start_y_cols); 

// Fila 1, Col 2: Logo (en la esquina superior derecha de esta columna)
$logo_filepath = sc_url_library('prj','libreria_fpdf','img/logo_hs.png'); // Usando sc_url_library (como en tu código base)
$logo_width = 20; // Ancho del logo en mm
$logo_height_estimate = $logo_width * 0.75; // Estimación de altura

// Calcular la posición X para alinear a la derecha dentro de la columna 2
// Usamos la posición X 170mm como punto de inicio horizontal para el logo
$logo_x = 170; // Usando la posición X fija 170mm

// Guardamos la posición Y donde se coloca el logo
$logo_y_start = $pdf->GetY(); // La Y actual es el inicio de la columna 2
// Llamar a Image() directamente
$pdf->Image($logo_filepath, $logo_x, $logo_y_start, $logo_width, 0); // Posición en (Logo X fija, Y de inicio de Col 2)

// La posición Y será por debajo del logo.
// La posición X será la posición X donde comienza el logo ($logo_x).
$pdf->SetY($logo_y_start + $logo_height_estimate + 5); 
$text_fields_start_x = $logo_x - 20; 
// Calcular el ancho disponible para los campos de texto desde $text_fields_start_x hasta el margen derecho, menos un pequeño padding
$available_width_for_fields = (216 - 5) - $text_fields_start_x - 2; // Restamos 2mm para un pequeño margen antes del borde derecho


// >>> Volver al formato Etiqueta + Valor con Negrita y ajustar anchos <<<

// Fila 2, Col 2: Fecha (ahora alineada debajo del logo, empezando en $text_fields_start_x)
$pdf->SetX($text_fields_start_x); 
$pdf->SetFont('Arial', 'B', 9); 
$current_label_width = $pdf->GetStringWidth($label_fecha) + 24; 
$pdf->Cell($current_label_width, $line_height, $label_fecha, 0, 0, 'L');
$pdf->SetFont('Arial', '', 9); 
$pdf->Cell($available_width_for_fields - $current_label_width, $line_height, utf8_decode($fecha_val), 0, 1, 'L'); // ln=1

// Fila 3, Col 2: Numero de Factura (ahora alineada debajo del Fecha)
$pdf->SetX($text_fields_start_x);
$pdf->SetFont('Arial', 'B', 9); 
$current_label_width = $pdf->GetStringWidth($label_numero_factura) + 4;
$pdf->Cell($current_label_width, $line_height, $label_numero_factura, 0, 0, 'L');
$pdf->SetFont('Arial', '', 9); 
$pdf->Cell($available_width_for_fields - $current_label_width, $line_height, utf8_decode($numero_factura_val), 0, 1, 'L'); // ln=1

// Fila 4, Col 2: Ordenes de Servicio (ahora alineada debajo del Numero de Factura)
$pdf->SetX($text_fields_start_x); 
$pdf->SetFont('Arial', 'B', 9); 
$current_label_width = $pdf->GetStringWidth($label_ordenes_servicio) + 3; 
$pdf->Cell($current_label_width, $line_height, $label_ordenes_servicio, 0, 0, 'L');
$pdf->SetFont('Arial', '', 9); 
$pdf->Cell($available_width_for_fields - $current_label_width, $line_height, utf8_decode($ordenes_service_val), 0, 1, 'L'); // ln=1

// Fila 5, Col 2: Moneda
$pdf->SetX($text_fields_start_x); 
$pdf->SetFont('Arial', 'B', 9); 
$current_label_width = $pdf->GetStringWidth($label_moneda) + 22; 
$pdf->Cell($current_label_width, $line_height, $label_moneda, 0, 0, 'L');
$pdf->SetFont('Arial', '', 9);
$pdf->Cell($available_width_for_fields - $current_label_width, $line_height, utf8_decode($moneda), 0, 1, 'L'); // ln=1

// --- FIN: SECCIÓN DEL ENCABEZADO DEL REPORTE ---

// Posicionar el cursor después de la sección del encabezado
// Usamos la posición Y más baja de las dos columnas + un espacio
$end_y_cols = max($end_y_col1, $pdf->GetY());
$pdf->SetY($end_y_cols + 13); // Deja un espacio después del encabezado principal


// --- SECCIÓN CABECERA DETALLE DE CUENTAS ---
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 8, utf8_decode('Ordenes de servicio'), 0, 1, 'L');
$pdf->SetY($pdf->GetY() + 1);

// Dibuja la línea superior
$pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());

$pdf->SetY($pdf->GetY());

$pdf->SetFont('Arial', 'B', 9);
$detail_header_height = 7;
$total_width = 216 - 10;

// Definir anchos para las 5 columnas (Item, Cuenta, CecO, Detalle, Sub - Total)
$item_width = $total_width * 0.15;
$cuenta_width = $total_width * 0.15;
$ceco_width = $total_width * 0.15;
$detalle_width = $total_width * 0.35;
$subtotal_width = $total_width * 0.18;

// Imprimir las celdas de la cabecera del detalle
$pdf->SetX(5);
$pdf->Cell($item_width, $detail_header_height, utf8_decode($item), 0, 0, 'L');
$pdf->Cell($cuenta_width, $detail_header_height, utf8_decode($cuenta), 0, 0, 'L');
$pdf->Cell($ceco_width, $detail_header_height, utf8_decode($centro_costo), 0, 0, 'L'); 
$pdf->Cell($detalle_width, $detail_header_height, utf8_decode($detalle), 0, 0, 'L');
$pdf->Cell($subtotal_width, $detail_header_height, utf8_decode($subtotal_label), 0, 1, 'R');


$pdf->SetY($pdf->GetY());

// Dibuja la línea inferior
$pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());

$pdf->SetY($pdf->GetY() + 1);


// --- SECCIÓN FILAS DETALLE DE CUENTAS ---
$pdf->SetY($pdf->GetY() + 1);

$pdf->SetFont('Arial', '', 8);
$detail_row_height = 5;
$item_counter = 1;
$total_detalle_cuentas = 0; // Variable para el total del detalle de cuentas

// Datos para las filas de detalle de cuentas (se agregan si los valores son > 0)
$detalle_cuentas_data = array();

 $detalle_cuentas_data[] = array($cuenta_mano_obra, 'Mano de Obra', $mano_obra_prd);
 $detalle_cuentas_data[] = array($cuenta_repuestos, 'Repuestos', $repuestos);
 $detalle_cuentas_data[] = array($cuenta_kilometraje, 'Kilometraje', $kilometraje);


if (!empty($detalle_cuentas_data)) {
    foreach ($detalle_cuentas_data as $cuenta_data) {
        $numero_cuenta = $cuenta_data[0];
        $descripcion_cuenta = $cuenta_data[1];
        $subtotal_cuenta_valor = $cuenta_data[2];

        // Verificar si se necesita un salto de página antes de dibujar esta fila
        if ($pdf->GetY() + $detail_row_height > ($pdf->GetPageHeight() - $pdf->GetBMargin())) {
            $pdf->AddPage();
            // Redibujar el encabezado si es una nueva página
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetX(5);
            $pdf->Cell(0, 8, utf8_decode('DETALLE DE CUENTAS (Continuación)'), 0, 1, 'L');
            $pdf->SetY($pdf->GetY() + 1);

            $pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());
            $pdf->SetY($pdf->GetY());
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetX(5);
            $pdf->Cell($item_width, $detail_header_height, utf8_decode($item), 0, 0, 'L');
            $pdf->Cell($cuenta_width, $detail_header_height, utf8_decode($cuenta), 0, 0, 'L');
            $pdf->Cell($ceco_width, $detail_header_height, utf8_decode($centro_costo), 0, 0, 'L');
            $pdf->Cell($detalle_width, $detail_header_height, utf8_decode($detalle), 0, 0, 'L');
            $pdf->Cell($subtotal_width, $detail_header_height, utf8_decode($subtotal_label), 0, 1, 'R');
            $pdf->SetY($pdf->GetY());
            $pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());
            $pdf->SetY($pdf->GetY() + 1);
            $pdf->SetFont('Arial', '', 8); // Vuelve a la fuente normal para los datos
        }


        $pdf->SetX(5);
        $pdf->Cell($item_width, $detail_row_height, $item_counter, 0, 0, 'L');
        $pdf->Cell($cuenta_width, $detail_row_height, utf8_decode($numero_cuenta), 0, 0, 'L');
        $pdf->Cell($ceco_width, $detail_row_height, utf8_decode($ceco_valor), 0, 0, 'L');
        $pdf->Cell($detalle_width, $detail_row_height, utf8_decode($descripcion_cuenta), 0, 0, 'L');
        // Imprimir el valor del subtotal para esta fila
        $pdf->Cell($subtotal_width, $detail_row_height, utf8_decode(number_format((float)$subtotal_cuenta_valor, 2, '.', ',')), 0, 1, 'R');

        $total_detalle_cuentas += $subtotal_cuenta_valor; // Sumar al total
        $item_counter++;
    }
} else {
    $pdf->SetX(5);
    $pdf->Cell($total_width, $detail_row_height, utf8_decode('No se encontraron detalles de cuentas con montos para esta liquidación.'), 0, 1, 'C');
}

// --- Total para el Detalle de Cuentas ---
$pdf->SetY($pdf->GetY() + 2);
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetX(5);
$pdf->Cell($total_width - $subtotal_width - 10, $line_height + 2, utf8_decode('Sub - Total:'), 0, 0, 'R');
$pdf->Cell($subtotal_width + 7, $line_height + 2, utf8_decode(number_format((float)$total_detalle_cuentas, 2, '.', ',')), 0, 1, 'R');

// Línea divisoria después del total de Detalle de Cuentas
$pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());
$pdf->SetY($pdf->GetY() + 5); // Espacio después de la línea divisoria


// --- INICIO MODIFICACIÓN: SECCIÓN DETALLE DE OTROS GASTOS ---
$total_otros_gastos = 0; // Variable para el total de otros gastos

if (!empty({dts_otros_gastos})) { // Solo si hay datos de otros gastos
    // Título para "Detalle de Otros Gastos"
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX(5);
    $pdf->Cell(0, 8, utf8_decode('Otros Gastos'), 0, 1, 'L');
    $pdf->SetY($pdf->GetY() + 1);

    // Dibuja la línea superior para el encabezado de "Otros Gastos"
    $pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());

    $pdf->SetY($pdf->GetY());

    $pdf->SetFont('Arial', 'B', 9);
    $detail_header_height = 7;
    // Usar los mismos anchos de columna que el detalle de cuentas
    // $item_width, $cuenta_width, $ceco_width, $detalle_width, $subtotal_width

    // Imprimir las celdas de la cabecera del detalle de otros gastos
    $pdf->SetX(5);
    $pdf->Cell($item_width, $detail_header_height, utf8_decode($item), 0, 0, 'L');
    $pdf->Cell($cuenta_width, $detail_header_height, utf8_decode($cuenta), 0, 0, 'L');
    $pdf->Cell($ceco_width, $detail_header_height, utf8_decode($centro_costo), 0, 0, 'L');
    $pdf->Cell($detalle_width, $detail_header_height, utf8_decode($detalle), 0, 0, 'L');
    $pdf->Cell($subtotal_width, $detail_header_height, utf8_decode($subtotal_label), 0, 1, 'R');

    $pdf->SetY($pdf->GetY());

    // Dibuja la línea inferior para el encabezado de "Otros Gastos"
    $pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());

    $pdf->SetY($pdf->GetY() + 1);

    // Filas de datos para "Otros Gastos"
    $pdf->SetFont('Arial', '', 8);
    $otros_gastos_item_counter = 1;
    foreach ({dts_otros_gastos} as $og_record) {
        $og_ceco = $og_record[0];
        $og_cuenta = $og_record[1];
        $og_concepto = $og_record[2];
        $og_subtotal = $og_record[3];

        // Verificar si se necesita un salto de página antes de dibujar esta fila
        if ($pdf->GetY() + $detail_row_height > ($pdf->GetPageHeight() - $pdf->GetBMargin())) {
            $pdf->AddPage();
            // Redibujar el encabezado si es una nueva página
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetX(5);
            $pdf->Cell(0, 8, utf8_decode('DETALLE DE OTROS GASTOS (Continuación)'), 0, 1, 'L');
            $pdf->SetY($pdf->GetY() + 1);

            $pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());
            $pdf->SetY($pdf->GetY());
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetX(5);
            $pdf->Cell($item_width, $detail_header_height, utf8_decode($item), 0, 0, 'L');
            $pdf->Cell($cuenta_width, $detail_header_height, utf8_decode($cuenta), 0, 0, 'L');
            $pdf->Cell($ceco_width, $detail_header_height, utf8_decode($centro_costo), 0, 0, 'L');
            $pdf->Cell($detalle_width, $detail_header_height, utf8_decode($detalle), 0, 0, 'L');
            $pdf->Cell($subtotal_width, $detail_header_height, utf8_decode($subtotal_label), 0, 1, 'R');
            $pdf->SetY($pdf->GetY());
            $pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());
            $pdf->SetY($pdf->GetY() + 1);
            $pdf->SetFont('Arial', '', 8); // Vuelve a la fuente normal para los datos
        }

        $pdf->SetX(5);
        $pdf->Cell($item_width, $detail_row_height, $otros_gastos_item_counter, 0, 0, 'L');
        $pdf->Cell($cuenta_width, $detail_row_height, utf8_decode($og_cuenta), 0, 0, 'L');
        $pdf->Cell($ceco_width, $detail_row_height, utf8_decode($og_ceco), 0, 0, 'L');
        $pdf->Cell($detalle_width, $detail_row_height, utf8_decode($og_concepto), 0, 0, 'L');
        $pdf->Cell($subtotal_width, $detail_row_height, utf8_decode(number_format((float)$og_subtotal, 2, '.', ',')), 0, 1, 'R');
        $total_otros_gastos += $og_subtotal; // Sumar al total
        $otros_gastos_item_counter++;
    }

    // --- Total para el Detalle de Otros Gastos ---
    $pdf->SetY($pdf->GetY() + 2);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetX(5);
    $pdf->Cell($total_width - $subtotal_width - 10, $line_height + 2, utf8_decode('Sub - Total:'), 0, 0, 'R');
    $pdf->Cell($subtotal_width + 7, $line_height + 2, utf8_decode(number_format((float)$total_otros_gastos, 2, '.', ',')), 0, 1, 'R');

    // Línea divisoria después del total de Otros Gastos
    $pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());
    $pdf->SetY($pdf->GetY() + 5); // Espacio después de la línea divisoria

}
// --- FIN MODIFICACIÓN: SECCIÓN DETALLE DE OTROS GASTOS ---


// --- SECCIÓN TOTALES FINALES ---
$pdf->SetY($pdf->GetY() + 1);

$pdf->SetFont('Arial', 'B', 9);
$pdf->SetX(5);

$total_label_width = $total_width * 0.7;
$total_value_width = $total_width * 0.3;

$pdf->SetX(5);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($total_width - $subtotal_width - 10, $line_height + 2, utf8_decode('Total:'), 0, 0, 'R');
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell($total_value_width - 17, $line_height + 2, utf8_decode(number_format((float)$total_liquidacion + $total_otros_gastos, 2, '.', ',')), 0, 1, 'R'); // Sumar ambos totales

// Dibuja la línea inferior ESTA ES LA ULTIMA LINEA DE TOTALES
//$pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());


// Salto de página para el detalle de casos
// Agrega una nueva página para que el detalle de casos comience en una hoja nueva
$pdf->AddPage();


// --- TÍTULO DETALLE DE CASOS (Después de la última línea de totales) ---
$pdf->SetY($pdf->GetY() + 1);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetX(5);
$pdf->Cell(0, 10, utf8_decode('Detalle Ordenes de Servicio'), 0, 1, 'L');
$pdf->SetY($pdf->GetY() + 1);


// --- SECCIÓN DETALLE DE CASOS (Cabecera y Filas) ---

$pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());

$pdf->SetY($pdf->GetY() + 2);

$pdf->SetFont('Arial', 'B', 8);
$case_header_height = 7;
$total_case_detail_width = 216 - 10; // Ancho total disponible para las columnas de casos

// Factor de escala para ajustar los anchos al ancho total disponible
$scaling_factor = $total_case_detail_width / 214; // 214 es la suma de los anchos base

// Definir anchos para las nuevas columnas del detalle de casos (escalados para ajustarse a la página)
$orden_width = 12 * $scaling_factor;
$modelo_width = 25 * $scaling_factor;
$tipo_reclamo_width = 16 * $scaling_factor;
$revision_width = 18 * $scaling_factor;
$movilizacion_width = 22 * $scaling_factor;
$reparacion_width = 16 * $scaling_factor;
$refaccion_width = 23 * $scaling_factor;
$cambio_pro_width = 18 * $scaling_factor;
$carga_gas_width = 20 * $scaling_factor;
$otros_gastos_width = 20 * $scaling_factor;
$despiece_width = 19 * $scaling_factor;


// Imprimir las celdas de la cabecera del detalle de casos (Actualizado)
$pdf->SetX(5);
$pdf->Cell($orden_width, $case_header_height, utf8_decode($orden), 0, 0, 'L');
$pdf->Cell($modelo_width, $case_header_height, utf8_decode($modelo), 0, 0, 'L');
$pdf->Cell($tipo_reclamo_width, $case_header_height, utf8_decode($tipo_reclamo), 0, 0, 'L');
$pdf->Cell($revision_width, $case_header_height, utf8_decode($revision), 0, 0, 'C');
$pdf->Cell($movilizacion_width, $case_header_height, utf8_decode($Movilizacion), 0, 0, 'C');
$pdf->Cell($reparacion_width, $case_header_height, utf8_decode($reparacion), 0, 0, 'C');
$pdf->Cell($refaccion_width, $case_header_height, utf8_decode($refaccion), 0, 0, 'C');
$pdf->Cell($cambio_pro_width, $case_header_height, utf8_decode($cambio_pro), 0, 0, 'C');
$pdf->Cell($carga_gas_width, $case_header_height, utf8_decode($carga_gas), 0, 0, 'C');
$pdf->Cell($otros_gastos_width, $case_header_height, utf8_decode($otros_gastos_col_label), 0, 0, 'C');
$pdf->Cell($despiece_width, $case_header_height, utf8_decode($despiece), 0, 1, 'C');


// --- FILAS DE DATOS DEL DETALLE DE CASOS ---

$pdf->SetFont('Arial', '', 7);
$case_row_height = 5; // Altura de línea base para MultiCell

if (!empty({dts_casos})) {
    foreach ({dts_casos} as $caso_record) {
        $ods_id = $caso_record[0];
        $tik_modelo = $caso_record[1];
        $tic_descripcion = $caso_record[2];
        $liq_revision = $caso_record[3];
        $liq_movilizacion = $caso_record[4];
        $liq_reparacion = $caso_record[5];
        $liq_refaccion = $caso_record[6];
        $liq_cambio_producto = $caso_record[7];
        $liq_carga_gas = $caso_record[8];
        $liq_otros_gastos = $caso_record[9];
        $liq_despiece = $caso_record[10];

        // Calcular la altura efectiva para la fila actual basada en el MultiCell
        $text_modelo_decoded = utf8_decode($tik_modelo);
        $nb_lines_modelo = $pdf->NbLines($modelo_width, $text_modelo_decoded);
        $current_row_effective_height = $nb_lines_modelo * $case_row_height;
        // Asegurar que la altura mínima sea la de una línea
        if ($current_row_effective_height < $case_row_height) {
            $current_row_effective_height = $case_row_height;
        }

        // Verificar si se necesita un salto de página antes de dibujar esta fila
        if ($pdf->GetY() + $current_row_effective_height > ($pdf->GetPageHeight() - $pdf->GetBMargin())) {
            $pdf->AddPage();
            // Redibujar la cabecera en la nueva página
            $pdf->SetY($pdf->GetY() + 1);
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetX(5);
            $pdf->Cell(0, 10, utf8_decode('DETALLE DE CASOS (Continuación)'), 0, 1, 'L');
            $pdf->SetY($pdf->GetY() + 1);

            $pdf->Line(5, $pdf->GetY(), 216 - 5, $pdf->GetY());
            $pdf->SetY($pdf->GetY() + 2);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetX(5);
            $pdf->Cell($orden_width, $case_header_height, utf8_decode($orden), 0, 0, 'L');
            $pdf->Cell($modelo_width, $case_header_height, utf8_decode($modelo), 0, 0, 'L');
            $pdf->Cell($tipo_reclamo_width, $case_header_height, utf8_decode($tipo_reclamo), 0, 0, 'L');
            $pdf->Cell($revision_width, $case_header_height, utf8_decode($revision), 0, 0, 'C');
            $pdf->Cell($movilizacion_width, $case_header_height, utf8_decode($Movilizacion), 0, 0, 'C');
            $pdf->Cell($reparacion_width, $case_header_height, utf8_decode($reparacion), 0, 0, 'C');
            $pdf->Cell($refaccion_width, $case_header_height, utf8_decode($refaccion), 0, 0, 'C');
            $pdf->Cell($cambio_pro_width, $case_header_height, utf8_decode($cambio_pro), 0, 0, 'C');
            $pdf->Cell($carga_gas_width, $case_header_height, utf8_decode($carga_gas), 0, 0, 'C');
            $pdf->Cell($otros_gastos_width, $case_header_height, utf8_decode($otros_gastos_col_label), 0, 0, 'C');
            $pdf->Cell($despiece_width, $case_header_height, utf8_decode($despiece), 0, 1, 'C');
            $pdf->SetFont('Arial', '', 7); // Vuelve a la fuente normal para los datos
        }


        // Guardar la posición Y inicial de la fila en la página actual (o nueva)
        $start_y_row = $pdf->GetY();
        $current_x_pos = 5; // Iniciar en el margen izquierdo

        // Columna: Orden
        $pdf->SetXY($current_x_pos, $start_y_row);
        $pdf->Cell($orden_width, $current_row_effective_height, utf8_decode($ods_id), 0, 0, 'L');
        $current_x_pos += $orden_width;

        // Columna: Modelo (AHORA CON MULTICELL)
        $pdf->SetXY($current_x_pos, $start_y_row);
        $pdf->MultiCell($modelo_width, $case_row_height, $text_modelo_decoded, 0, 'L', 0);
        $y_after_modelo_multicell = $pdf->GetY(); // Obtener la posición Y después de que MultiCell haya terminado

        // Dibujar el resto de las celdas, posicionándolas explícitamente en la Y de inicio de la fila
        // y luego avanzando la posición X.
        $current_x_pos_for_rest_of_cells = $current_x_pos + $modelo_width; // Posición X para la siguiente celda después de Modelo

        // Columna: Tipo Reclamo
        $pdf->SetXY($current_x_pos_for_rest_of_cells, $start_y_row);
        $pdf->Cell($tipo_reclamo_width, $current_row_effective_height, utf8_decode($tic_descripcion), 0, 0, 'L');
        $current_x_pos_for_rest_of_cells += $tipo_reclamo_width;

        // Columna: Revision
        $pdf->SetXY($current_x_pos_for_rest_of_cells, $start_y_row);
        $pdf->Cell($revision_width, $current_row_effective_height, utf8_decode(number_format((float)$liq_revision, 2, '.', ',')), 0, 0, 'R');
        $current_x_pos_for_rest_of_cells += $revision_width;

        // Columna: Movilizacion
        $pdf->SetXY($current_x_pos_for_rest_of_cells, $start_y_row);
        $pdf->Cell($movilizacion_width, $current_row_effective_height, utf8_decode(number_format((float)$liq_movilizacion, 2, '.', ',')), 0, 0, 'R');
        $current_x_pos_for_rest_of_cells += $movilizacion_width;

        // Columna: Reparacion
        $pdf->SetXY($current_x_pos_for_rest_of_cells, $start_y_row);
        $pdf->Cell($reparacion_width, $current_row_effective_height, utf8_decode(number_format((float)$liq_reparacion, 2, '.', ',')), 0, 0, 'R');
        $current_x_pos_for_rest_of_cells += $reparacion_width;

        // Columna: Refaccion
        $pdf->SetXY($current_x_pos_for_rest_of_cells, $start_y_row);
        $pdf->Cell($refaccion_width, $current_row_effective_height, utf8_decode(number_format((float)$liq_refaccion, 2, '.', ',')), 0, 0, 'R');
        $current_x_pos_for_rest_of_cells += $refaccion_width;

        // Columna: Cambio Producto
        $pdf->SetXY($current_x_pos_for_rest_of_cells, $start_y_row);
        $pdf->Cell($cambio_pro_width, $current_row_effective_height, utf8_decode(number_format((float)$liq_cambio_producto, 2, '.', ',')), 0, 0, 'R');
        $current_x_pos_for_rest_of_cells += $cambio_pro_width;

        // Columna: Carga Gas
        $pdf->SetXY($current_x_pos_for_rest_of_cells, $start_y_row);
        $pdf->Cell($carga_gas_width, $current_row_effective_height, utf8_decode(number_format((float)$liq_carga_gas, 2, '.', ',')), 0, 0, 'R');
        $current_x_pos_for_rest_of_cells += $carga_gas_width;

        // Columna: Otros Gastos
        $pdf->SetXY($current_x_pos_for_rest_of_cells, $start_y_row);
        $pdf->Cell($otros_gastos_width, $current_row_effective_height, utf8_decode(number_format((float)$liq_otros_gastos, 2, '.', ',')), 0, 0, 'R');
        $current_x_pos_for_rest_of_cells += $otros_gastos_width;

        // Columna: Despiece (Última celda en la fila, mueve el cursor a la siguiente línea)
        $pdf->SetXY($current_x_pos_for_rest_of_cells, $start_y_row);
        $pdf->Cell($despiece_width, $current_row_effective_height, utf8_decode(number_format((float)$liq_despiece, 2, '.', ',')), 0, 1, 'R');

        // Asegurar que la próxima fila comience después de la celda más alta de la fila actual
        $pdf->SetY(max($y_after_modelo_multicell, $pdf->GetY()));
    }
} else {
    $pdf->SetX(5);
    $pdf->Cell($total_case_detail_width, $case_row_height, utf8_decode('No se encontraron casos para esta liquidación.'), 0, 1, 'C');
}

// --- FIN: SECCIÓN DETALLE DE CASOS (Cabecera y Filas) ---


// La sección del pie de página ahora se maneja en el método Footer() de la clase PDF


// Generación del archivo PDF
$pdf_filename = 'reporte_liquidacion_' . $liq_id . '.pdf';
$pdf_filepath = sys_get_temp_dir() . '/' . $pdf_filename;

$pdf->Output($pdf_filepath, 'F');

return $pdf_filepath;
?>