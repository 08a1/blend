<?php

require_once 'src/config.php';
require_once 'src/apng_encode.php';

//verify a file starts as  blend file, make it a compressed blend file, and upload it
function upload_blend($blend, $name)
{
	if ("\x1f\x8b" === substr($blend, 0, 2)) {
		$blend = gzdecode($blend);
	}
	
	if ('BLENDER' !== substr($blend, 0, 7)) {
		throw new Exception('Not a blend file');
	}

	$data = gzencode($blend);
	
	return upload($data, $name);
}

//encode a file in PNG images and upload the images to t.stack.imgur.com
function upload($data, $name)
{
	$size = 0;
	$ids = [];
	$hash = hash('whirlpool', $data, true);

	//determine how many images to upload and how much to upload per image
	$length = strlen($data);
	$chunk_count = ceil($length / MAX_IMAGE_LENGTH);
	$chunk_length = ceil(($length + $chunk_count * APPROXIMATE_OVERHEAD) / $chunk_count);

	$chunks = str_split($data, $chunk_length);
	foreach ($chunks as $chunk) {
		
		//the format of the encoded data is 64 bytes for a hash, 4 bytes for the length
		//of the file name, the file name, 4 bytes for the length of the data, and the data
		$pixels = $hash;
		$pixels .= pack('N', strlen($name)) . $name;
		$pixels .= pack('N', strlen($chunk)) . $chunk;

		//make a square image 
		$side = ceil(sqrt(strlen($pixels) / 4));
		$pixels = str_pad($pixels, 4 * $side * $side, "\x00");
		$apng = apng_encode($side, $side, $pixels);
		$size += strlen($apng);
		$ids[] = upload_png($apng);
	}
	
	return [
		'id' => implode('-', $ids),
		'size' => $size
	];
}

//upload a PNG image to i.stack.imgur.com and return the id to imgur's to that image
function upload_png($png)
{
	if (strlen($png) >= MAX_IMAGE_LENGTH) {
		throw new Exception('image too large');
	}

	//start the boundary as 10 hyphens and add pseudo-random numbers to it until it is unique
	$boundary = str_repeat('-', 10);
	while (strpos($png, $boundary) !== FALSE) {
		$boundary .= rand();
	}

	//make the body for a multipart request according to RFC 7578 and RFC 2046
	$form_data = implode("\r\n", [
		'--'.$boundary,
		'Content-Disposition: form-data; name="file"; filename="image.png"',
		'Content-Type: image/png',
		'',
		$png,
		'--'.$boundary.'--',
		''
	]);

	//make a HTTP post request
	$context = stream_context_create([
		'http' => [
			'header' => "Content-Type: multipart/form-data; boundary=$boundary\r\n",
			'method' => 'POST',
			'content' => $form_data
		]
	]);
	$response = file_get_contents(UPLOAD_URL, FALSE, $context);	
	if ($response === FALSE) {
		throw new Exception('Upload unsuccessful');
	}

	//check that a URL to the uploaded image was returned
	$start = strpos($response, DOWNLOAD_URL);
	$end = strpos($response, FILE_EXTENSION);
	if ($start === FALSE || $end === FALSE) {
		throw new Exception('id not received');
	}

	//return the file name of the image without the file extension
	$start += strlen(DOWNLOAD_URL);
	return substr($response, $start, $end - $start);
}