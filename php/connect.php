<?php
	$con = new mysqli('localhost', 'usnow', '48kgeia8!', 'telebyte_horas');
	if ($con->connect_errno) echo 'SQL error: '.$con->connect_errno;
	else $con->query("SET NAMES 'utf8'");

	function SQLquery ($query, $con) {
		$res = $con->query($query);
		if (!$res) printf("Error: %s\n", $con->sqlstate);
		else { $res = $res->fetch_assoc(); return $res;}
	}

	function validateKey ($key, $con) {
		$key = $con->real_escape_string($key);
		$sql = "SELECT key_valid FROM api_keys WHERE key_hash = '$key'";
		$res = SQLquery($sql, $con);
		# var_dump($res);
		if ($res['key_valid']) return true;
		else return false;
	}
?>