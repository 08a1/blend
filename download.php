<?php

require_once 'src/download.php';

set_time_limit(0);

try {
	if (!isset($_GET['id'])) {
		throw new Exception('missing id');
	}
	
	$file = download($_GET['id']);

	$blend = $file['blend'];
	$name = $file['name'];
	$size = strlen($blend);

	header('Content-Length: ' . $size);
	header('Content-Type: application/octet-stream');
	header('Content-Disposition: attachment; filename="' . $name . '"');
	echo $blend;
} catch (Exception $e) {
	$error = $e->getMessage();
	$error = urlencode($error);
	header("Location: ../upload?error=$error");
}
