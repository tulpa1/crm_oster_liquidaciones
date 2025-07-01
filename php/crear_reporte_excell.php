<?php
require_once(sc_url_library("prj", "PhpSpreadsheet", "vendor/autoload.php"));
try {
	$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setTitle("Reporte de Liquidación");

    // Main view call
    $sql = "SELECT
                ods_id, cli_nombre, cat_denominacion, tik_modelo, ods_fecha_atencion,
                ods_reingreso, ods_garantia_fabrica, tik_numero_serie, tic_descripcion,
                liq_revision, liq_movilizacion, liq_reparacion, liq_refaccion,
                liq_cambio_producto, liq_carga_gas, liq_otros_gastos,
                (cos_subtotal + cos_impuesto) AS cos_total_principal_con_impuesto
            FROM vista_preliquidaciones
            WHERE liq_id = " . {liq_id}.
            " AND ces_id = " . {ces_id} .
            " AND est_id = 4";

    sc_lookup(dataset, $sql);

    // Other expenses data - MODIFIED QUERY HERE!
    $sql_otros_gastos = "SELECT oc.concepto, SUM(og.subtotal) AS total_subtotal_concepto
                        FROM otros_gastos og
                        INNER JOIN otros_gastos_conceptos oc ON oc.idotros_gastos_conceptos = og.id_concepto
                        WHERE og.liq_id = " . {liq_id} .
                        " GROUP BY oc.concepto"; // Group by concept to sum subtotals
    sc_lookup(dts_gastos, $sql_otros_gastos);

    if (!empty({dataset})) {
        // Define styles
        $styleHeader = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '085793']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];

        $styleTotal = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
        ];

        $styleNumericRightBold = [
            'font' => ['bold' => false],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
        ];

        // Style for other expenses concept
        $styleOtrosGastosConceptoNormal = [
            'font' => ['italic' => false, 'color' => ['rgb' => '000000']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT],
        ];

        // Style for final totals (size 15, no background, no borders)
        $styleBigTotalClean = [
            'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 12],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT],
        ];


        // Headers
        $titles = [
            'A1' => 'Caso', 'B1' => 'Nombre', 'C1' => 'Categoria', 'D1' => 'Modelo',
            'E1' => 'Fecha de Cierre', 'F1' => 'Reingreso', 'G1' => 'Garantia Fabrica',
            'H1' => 'Numero Serie', 'I1' => 'Tipo Reclamo',
            'J1' => 'Revision', 'K1' => 'Movilizacion', 'L1' => 'Reparacion',
            'M1' => 'Refaccion', 'N1' => 'Cambio Producto', 'O1' => 'Carga Gas',
            'P1' => 'Otros Gastos',
            'Q1' => 'SubTotal'
        ];

        foreach ($titles as $cell => $title) {
            $sheet->setCellValue($cell, $title);
            $sheet->getStyle($cell)->applyFromArray($styleHeader);
        }

        $row = 2; // Initial row for data
        $totalPrincipalAcumulado = 0;
        $totalOtrosGastosDetalle = 0;

        // Populate data from the main view
        foreach ({dataset} as $record) {
            $sheet->setCellValue('A' . $row, $record[0]);  // ods_id
            $sheet->setCellValue('B' . $row, $record[1]);  // cli_nombre
            $sheet->setCellValue('C' . $row, $record[2]);  // cat_denominacion
            $sheet->setCellValue('D' . $row, $record[3]);  // tik_modelo
            $sheet->setCellValue('E' . $row, $record[4]);  // ods_fecha_atencion
            $sheet->setCellValue('F' . $row, $record[5]);  // ods_reingreso
            $sheet->setCellValue('G' . $row, $record[6]);  // ods_garantia_fabrica
            $sheet->setCellValue('H' . $row, $record[7]);  // tik_numero_serie
            $sheet->setCellValue('I' . $row, $record[8]);  // tic_descripcion

            $sheet->setCellValue('J' . $row, $record[9]);  // liq_revision
            $sheet->setCellValue('K' . $row, $record[10]); // liq_movilizacion
            $sheet->setCellValue('L' . $row, $record[11]); // liq_reparacion
            $sheet->setCellValue('M' . $row, $record[12]); // liq_refaccion
            $sheet->setCellValue('N' . $row, $record[13]); // liq_cambio_producto
            $sheet->setCellValue('O' . $row, $record[14]); // liq_carga_gas
            $sheet->setCellValue('P' . $row, $record[15]); // liq_otros_gastos
            $sheet->setCellValue('Q' . $row, $record[16]); // cos_total_principal_con_impuesto

            // Apply numeric format and bold/right alignment to value columns
            $sheet->getStyle('J' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('K' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('L' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('M' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('N' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('O' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('P' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

            // Apply style to column Q for right alignment and bold!
            $sheet->getStyle('Q' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('Q' . $row)->applyFromArray($styleNumericRightBold);


            // Accumulate the main total
            $totalPrincipalAcumulado += $record[16];

            $row++;
        }

        // --- Start: Final Totals ---
        $row++; // Blank space before final totals

        // Total Principal (includes IVA)
        $startRowTotals = $row;
        $rowTotalPrincipal = $startRowTotals;

        $sheet->setCellValue('P' . $rowTotalPrincipal, "Total Servicios: ");
        $sheet->setCellValue('Q' . $rowTotalPrincipal, $totalPrincipalAcumulado);
        $sheet->getStyle('Q' . $rowTotalPrincipal)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("P$rowTotalPrincipal:Q$rowTotalPrincipal")->applyFromArray($styleBigTotalClean);

        $rowTotalPrincipal++; // Advance row for the next element

        // --- Other Expenses Details (now in between totals) ---
        if (!empty({dts_gastos})) {
            foreach ({dts_gastos} as $gasto) {
                // Concept in column I
                $sheet->setCellValue('I' . $rowTotalPrincipal, $gasto[0]); // Concept
                $sheet->getStyle('I' . $rowTotalPrincipal)->applyFromArray($styleOtrosGastosConceptoNormal);

                // Subtotal in column Q (now it's index 1 after sum)
                $sheet->setCellValue('Q' . $rowTotalPrincipal, $gasto[1]); // Subtotal
                $sheet->getStyle('Q' . $rowTotalPrincipal)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle('Q' . $rowTotalPrincipal)->applyFromArray($styleNumericRightBold);


                $totalOtrosGastosDetalle += $gasto[1]; // Accumulate the subtotal (now it's index 1!)
                $rowTotalPrincipal++;
            }
        }
        // --- End Other Expenses Details ---

        // Subtotal of Other Expenses concepts (below the detail)
        $rowTotalOtrosGastosConceptos = $rowTotalPrincipal;

        $sheet->setCellValue('P' . $rowTotalOtrosGastosConceptos, "Total Otros Gastos: ");
        $sheet->setCellValue('Q' . $rowTotalOtrosGastosConceptos, $totalOtrosGastosDetalle);
        $sheet->getStyle('Q' . $rowTotalOtrosGastosConceptos)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("P$rowTotalOtrosGastosConceptos:Q$rowTotalOtrosGastosConceptos")->applyFromArray($styleBigTotalClean);

        $rowTotalOtrosGastosConceptos++;

        // Grand Total (Main Total + Other Expenses Total)
        $granTotalGeneral = $totalPrincipalAcumulado + $totalOtrosGastosDetalle;
        $rowGranTotal = $rowTotalOtrosGastosConceptos;

        $sheet->setCellValue('P' . $rowGranTotal, "Total: ");
        $sheet->setCellValue('Q' . $rowGranTotal, $granTotalGeneral);
        $sheet->getStyle('Q' . $rowGranTotal)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("P$rowGranTotal:Q$rowGranTotal")->applyFromArray($styleBigTotalClean);
        // --- End: Final Totals ---


        // Auto-adjust column width
        foreach (range('A', 'Q') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        // Guardar el archivo Excel temporalmente en el servidor
        $nombre_archivo = 'Detalle Liquidación-' . {liq_id} . '.xlsx';
        $ruta_archivo = __DIR__ . '/' . $nombre_archivo; // Guarda en el mismo directorio del script
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($ruta_archivo);

            // Retornar la ruta completa del archivo generado
        return $ruta_archivo;
		

	} else {
		return false; // Retornar false si no hay datos para generar
	}
} catch (Exception $e) {
	return false; // Retornar false en caso de error
}

?>