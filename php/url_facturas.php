<?php

//$url = 'http://' . $_SERVER['HTTP_HOST']; 
$root = $_SERVER['DOCUMENT_ROOT'];

$attachment = []; // Inicializar como un array vacío para evitar un primer elemento vacío no deseado.

$sql_documento = "SELECT lio_file_name FROM liquidaciones_documentos WHERE liq_id = " . $liq_id;
sc_lookup(dataset_documento, $sql_documento);

if (!empty({dataset_documento})) { // Asegurarse de que hay resultados antes de iterar
    foreach ({dataset_documento} as $documento) {
        $nombre_archivo = $documento[0];
        $ruta_desarrollo = $root.'/scriptcase/file/doc/documentos/liquidaciones/' . $nombre_archivo;
        $ruta_produccion = $root.'/_lib/file/doc/documentos/liquidaciones/' . $nombre_archivo;
        $ruta_produccion_static = $root.'/_lib/file/doc/documentos/liquidaciones/' . $nombre_archivo;
        $ruta_documento_valida = ''; // Variable para almacenar la ruta válida encontrada

        // Lógica para verificar la existencia del archivo en ambas rutas
        if (file_exists($ruta_desarrollo)) {
            $ruta_documento_valida = $ruta_desarrollo;
        } elseif (file_exists($ruta_produccion)) {
            // Solo comprueba la ruta de producción si no se encontró en desarrollo
            $ruta_documento_valida = $ruta_produccion;
        }else{
			$ruta_documento_valida = $ruta_produccion_static;
		}

        // Solo añade el documento al array si se encontró una ruta válida
        if (!empty($ruta_documento_valida)) {
            $attachment[] = $ruta_documento_valida;
        } 

    }
} 

return $attachment; // Retorna el array con las rutas de los archivos existentes


?>