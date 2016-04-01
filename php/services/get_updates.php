<?php
	$json = file_get_contents('https://api.telegram.org/bot212364285:AAEiC9Ww4LCGvyNz0Us04nO_KBHEXjdy_zU/getupdates'); # Get Json array from telegram bot API with botNumber/command
	$json = json_decode($json, true); # Decode Json and parse into array
	require '../connect.php'; # Requiere conexión con base de datos
	foreach ($json['result'] as $update) { # por cada resultado en el arreglo JSON
		if ($command = substr($update['message']['text'], 0, strpos($update['message']['text'], ' '))) { # get only the command stem from text
			$args = substr($update['message']['text'], strpos($update['message']['text'], ' ')+1); # get only arguments for command 
			if (isCommand($command) && $args = validArgs($command, $args)) { # if both pass regex validations
				# $sql = "SELECT id, estatus FROM requests WHERE id = ".$update['update_id']; # Testing and debugging
				if ($command == '/pre' || $command == '/solicitar') $sql = "SELECT id, estatus, requester FROM requests WHERE id = ".$update['update_id'];
				elseif ($command == '/aceptar' || $command == '/rechazar') $sql = "SELECT id, estatus, requester FROM requests WHERE id = ".$args[0];
				$res = $con->query($sql);
				$row = $res->fetch_assoc();
				if (($command == '/pre' || $command == '/solicitar') && $res->num_rows == 0) {
					$sql = queryBuilder($command, $args, $res, $update);
					sendMessage($command, $args, $update['update_id'], $update['message']['from']['id']);
				} 
				elseif (($command == '/aceptar' || $command == '/rechazar') && $res->num_rows == 1 && $row['estatus'] != 'DONE') {
					$sql = queryBuilder($command, $args, $res, $update);
					sendMessage($command, $args, $update['update_id'], $row['requester']);
				}
				# var_dump($command, $args, $sql);
				$con->query($sql);
			}
		}
	}
	function isCommand ($command) { # regex validation function 
		if (preg_match('%^\/[a-z]+%', $command)) return true;
		else return false;
	}
	function validArgs ($command, $args) { # regex validation function 
		switch ($command) {
			case '/solicitar':
			case '/pre':
				# dd:mm:yy, empresaTrabajo, Concepto, Trabajador, hh:mm-hh:mm
				if (preg_match('%^((0*|[1-2])[0-9]|3[0-1]):(0*[0-9]|1[0-2]):1[6-9],\s*[a-z\s]+,\s*[a-z\s]+,\s*[a-z\s]+,\s*((0*|1)[0-9]|2[0-3]):[0-6][0-9]-((0*|1)[0-9]|2[0-3]):[0-6][0-9](,\s*[a-z\s]+)*%i', $args)) {
					$temp = arrayMaker($args);
					return $temp;
				}
				else return false;
				break;
			case '/aceptar':
			case '/rechazar':
				# id, Mensaje*
				if (preg_match('%\d{9}(,\s*[a-z\s])*%i', $args)) {
					$temp = arrayMaker($args);
					return $temp;
				}
				else return false;
				break;
		}
	}
	function arrayMaker ($args) { # forms arrays out of argument strings 
		$i = 0;
		while (strpos($args, ',')) {
			$temp[$i] = trim(substr($args, 0, strpos($args, ',')));
			$args = trim(substr($args, strpos($args, ',')+1));
			$i++;
		}
		$temp[$i] = trim($args);
		return $temp;
	}
	function queryBuilder ($command, $args, $res, $update) { # forms queries for each command 
		$id = $update['update_id'];
		$sender = $update['message']['from']['id'];
		switch ($command) {
			case '/solicitar':
				$start = trim(substr($args[4], 0, strpos($args[4], '-')));
				$finish = trim(substr($args[4], strpos($args[4], '-')+1));
				return $sql = "INSERT INTO requests (`id`, `requester`, `employee`, `lugar`, `concepto`, `fecha`, `hora_inicio`, `hora_final`, `creado`) VALUES ('$id', '$sender', '$args[3]', '$args[1]', '$args[2]', '$args[0]', '$start', '$finish', CURRENT_TIMESTAMP)";
				break;
			case '/pre':
				# add validation to avoid workers pre-authorizing EH
				return $sql = "INSERT INTO requests (`id`, `requester`, `employee`, `lugar`, `concepto`, `fecha`, `hora_inicio`, `hora_final`, `autorizado`, `creado`) VALUES ('$id', '$sender', '$args[3]', '$args[1]', '$args[2]', '$args[0]', '$start', '$finish', 'TRUE' CURRENT_TIMESTAMP)";
				break;
			case '/aceptar':
			case '/rechazar':
				if ($command == '/aceptar') return $sql = "UPDATE requests SET `autorizado` = TRUE, `last_update` = CURRENT_TIMESTAMP, `estatus` = 'DONE' WHERE `id` = '$args[0]'";
				else return $sql = "UPDATE requests SET `autorizado` = FALSE, `last_update` = CURRENT_TIMESTAMP, `estatus` = 'DONE' WHERE `id` = '$args[0]'";
				break;
		}
	}
	function sendMessage ($command, $args, $id, $id_user) {
		switch ($command) {
			case '/solicitar':
			case '/pre':
				$json = file_get_contents('https://api.telegram.org/bot212364285:AAEiC9Ww4LCGvyNz0Us04nO_KBHEXjdy_zU/sendmessage?chat_id=88998729&text=Una nueva solicitud de horas extra fue recibida con id = '.$id.', usa /aceptar o /rechazar id, mensaje*'); # Get Json array from telegram bot API with botNumber/command?args
				$json = json_decode($json, true); # Decode Json and parse into array
				break;
			case '/aceptar':
				$json = file_get_contents('https://api.telegram.org/bot212364285:AAEiC9Ww4LCGvyNz0Us04nO_KBHEXjdy_zU/sendmessage?chat_id='.$id_user.'&text=Tu solicitud de horas extra fue aceptada con el siguiente mensaje: '.$args[1]); # Get Json array from telegram bot API with botNumber/command?args
				$json = json_decode($json, true); # Decode Json and parse into array
				break;
			case '/rechazar':
				$json = file_get_contents('https://api.telegram.org/bot212364285:AAEiC9Ww4LCGvyNz0Us04nO_KBHEXjdy_zU/sendmessage?chat_id='.$id_user.'&text=Tu solicitud de horas extra fue rechazada con el siguiente mensaje: '.$args[1]); # Get Json array from telegram bot API with botNumber/command?args
				$json = json_decode($json, true); # Decode Json and parse into array
				break;
			if ($json['ok']) return true;
			else return false;
		}
	}
?>