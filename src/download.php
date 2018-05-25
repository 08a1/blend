<?php

require_once 'src/config.php';
require_once 'src/apng_decode.php';

//download a file encoded in the pixels in a PNG image on i.stack.imgur.com
function download($id)
{
	$first_hash = '';
	$blend = '';
	$ids = explode('-', $id);
	foreach ($ids as $id) {
		//download the file and decode the fie as an animated PNG
		$file = file_get_contents(DOWNLOAD_URL . $id . FILE_EXTENSION);
		if ($file === FALSE) {
			throw new Exception('File not found');
		}
		$apng = apng_decode($file);
		$pixels = $apng['pixels'];
		$offset = 0;

		//read blend file information encoded in the pixels
		$hash = read_bytes($pixels, $offset, 64);
		$length = read_int($pixels, $offset);
		$name = read_bytes($pixels, $offset, $length);
		$length = read_int($pixels, $offset);
		$data = read_bytes($pixels, $offset, $length);

		if ($first_hash === '') {
			$first_hash = $hash;
		}

		//verify the hash stored in each file match
		if ($hash !== $first_hash) {
			throw new Exception('Invalid file parts');
		}

		//concatenate the data from each image to reconstruct the original file
		$blend .= $data;
	}

	//verify the hash associated with the file corresponds to the file
	if ($first_hash !== hash('whirlpool', $blend, true)) {
		throw new Exception('Invalid file');
	}

	return [
		'blend' => $blend,
		'name' => $name
	];
}