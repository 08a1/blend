<?php

require_once 'src/read_binary.php';

//return the width, height, and pixel data from a animated PNG with one frame
function apng_decode($apng)
{
	$offset = 0;
	
	if ("\x89PNG\r\n\x1a\n" !== read_bytes($apng, $offset, 8)) {
		throw new Exception('Invalid PNG Signature');
	}

	//verify the chunks are present for a animated PNG
	$chunks = ['IHDR', 'acTL', 'fcTL', 'IDAT', 'IEND'];
	foreach ($chunks as $chunk) {
		//read the chunks components of a PNG chunk
		$length = read_int($apng, $offset);
		$type = read_bytes($apng, $offset, 4);
		$data = read_bytes($apng, $offset, $length);
		$crc = read_int($apng, $offset);

		//verify the check sum matches
		if ($type !== $chunk || $crc !== crc32($type . $data)) {
			throw new Exception('Invalid PNG chunk');
		}
		
		if ($type === 'IHDR') {
			//extract the width and height from the IHDR chunk
			if ($length !== 13) {
				throw new Exception('Invalid PNG header');
			}
			list(, $width, $height) = unpack('N2', $data);
		} elseif ($type === 'IDAT') {
			//extract the pixel data from the IDAT chunk
			$pixels = '';
			$raw_data = zlib_decode($data);
			$scan_lines = str_split($raw_data, 1 + 4 * $width);
			foreach ($scan_lines as $scan_line) {
				$pixels .= substr($scan_line, 1);
			}
		}
	}
	
	return [
		'width' => $width,
		'height' => $height,
		'pixels' => $pixels
	];
}