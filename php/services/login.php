<?php
	$data = json_decode(file_get_contents("php://input"));
	if (isset($data->user) && isset($data->pass)) {
		require '../connect.php';
		$data->user = $con->real_escape_string($data->user);
		$data->pass = $con->real_escape_string($data->pass);
		$sql = "SELECT reg_id AS 'id', reg_em AS 'user', reg_pw AS 'pass' FROM users WHERE reg_em = '$data->user'";
		$res = $con->query($sql);
		# printf("Error: %s\n, %s\n", $con->sqlstate, $sql);
		if ($res->num_rows == 1) {
			$res = $res->fetch_array(MYSQLI_ASSOC);
			if (password_verify($data->pass, $res['pass'])) {
				$login = array(
					'id' => $res['id'],
					'time' => time()+900,
					'valid' => true
				);
				echo json_encode($login);
			}
		}
	}
?>