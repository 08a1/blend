<?php

require_once 'src/upload.php';
require_once 'src/format.php';

error_reporting(0);
set_time_limit(300);

$id = '';
$error = '';

if (isset($_GET['error'])) {
	$error = $_GET['error'];
}

if (isset($_FILES['blend-file'])) {
	$name = $_FILES['blend-file']['name'];

	$file = file_get_contents($_FILES['blend-file']['tmp_name']);

	if ($file === FALSE) {
		$error = 'no file selected';
	} else {
		try {
			$upload = upload_blend($file, $name);
			$id = $upload['id'];
			$size = format_bytes($upload['size']);
		} catch (Exception $e) {
			$error = $e->getMessage();
		}
	}
}

?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset='utf-8'>
		<title>Upload Blend File</title>
		<link rel='stylesheet' href='styles/main.css'>
	</head>
	<h1>Blend file hosting for <a href='http://blender.stackexchange.com'>Blender Stack Exchange</a></h1>
	<?php
	if ($error) {
		$error = htmlspecialchars($error);
		echo "<p><strong>$error</strong></p>";
	}
	?>
	<form method='post' action='upload' enctype='multipart/form-data'>
		<p>
			<label for='blend-file'>Browse or drop blend file here</label>
			<input id='blend-file' name='blend-file' type='file'>
		</p>
		<p>
			<button type='submit'>Upload</button>
		</p>
	</form>
	<?php
	if ($id) {
		$id = urlencode($id);
		$name = htmlspecialchars($name);
		
		$path = substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'], '/'));
		$url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . $path  . '/download/' . $id;

		echo "<p>Blend file available at <a href=\"download/$id\">$name ($size)</a></p><p>Download link in markdown format:</p><samp>[$name ($size)]($url)</samp>";
	}
	?>
	<p><small>Uploaded blend files licensed <a href='https://creativecommons.org/licenses/by-sa/3.0/'>CC BY-SA 3.0</a> and subject to <a href='https://stackoverflow.com/legal/terms-of-service'>Stack Exchange's terms of service</a>.</small></p>
</html>