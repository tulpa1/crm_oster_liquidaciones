<?php
$solicitud_texto = "Liquidacion";

$tipo_usuario = 5; 	//rol de especiallist

$query_obtener_usuarios = "select ar.login, ar.ces_id, au.type from agentes_responsables ar   
							inner join app_users au on au.login = ar.login
							where ar.ces_id = $p_ces_id and au.active = 'Y' and au.type in(4,5)
							union all
							select login, null, null from app_users where type = 6 and active = 'Y'";

sc_select(data, $query_obtener_usuarios);

$mensaje = "Se Aprobo la liquidacion " . str_pad($p_liq_id, 8, "0", STR_PAD_LEFT);


foreach({data} as $row_usuario){

	try{
    $usuario = $row_usuario["login"];
		
	$insert_table  = 'notif_notifications';      // Table name
	$insert_fields = array(   // Field list, add as many as needed
		 'notif_title' => "'" . $solicitud_texto . "'",
		 'notif_message' => "'" . $mensaje . "'",
		 'notif_dtcreated' => "CURRENT_TIMESTAMP()",
		 'notif_ontop' => "0",
		 'notif_dtexpire' => "DATE_ADD(CURRENT_TIMESTAMP(), INTERVAL 1 DAY)",
		 'notif_categ' => "'DEFAULT'",
		 'notif_login_sender' => "'" . $usuario . "'"
	 );

	// Insert record
	$insert_sql = 'INSERT INTO ' . $insert_table
		. ' ('   . implode(', ', array_keys($insert_fields))   . ')'
		. ' VALUES ('    . implode(', ', array_values($insert_fields)) . ')';

	sc_exec_sql($insert_sql);

	$check_sql = "SELECT LAST_INSERT_ID()";

	sc_lookup(rs, $check_sql);

	if (isset({rs[0][0]}))     // Row found
	{
		$id_notif = {rs[0][0]};
	}else
	{
		$id_notif = 0;
	}

	if($id_notif > 0)
	{
		
		send_to_inbox_users($id_notif, $usuario);
	}

	} catch (Exception $e) {
		// Rollback transaction in case of error
		sc_rollback_trans();
		sc_alert("Error: " . $e->getMessage());
	}
}
?>