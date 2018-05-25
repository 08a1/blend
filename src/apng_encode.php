<?php

//make a single frame animated PNG with R, G, B, A components for pixels in which each component is 8 bits
function apng_encode($width, $height, $pixels)
{
	$data = "\x00" . implode("\x00", str_split($pixels, 4 * $width));
	$png = "\x89PNG\r\n\x1a\n";
	$png .= png_encode_chunk('IHDR', pack('N2C5', $width, $height, 8, 6, 0, 0, 0));
	$png .= png_encode_chunk('acTL', pack('N2', 1, 1));
	$png .= png_encode_chunk('fcTL', pack('N5n2C2', 0, $width, $height, 0, 0, 0, 0, 0, 1));
	$png .= png_encode_chunk('IDAT', zlib_encode($data, ZLIB_ENCODING_DEFLATE));
	$png .= png_encode_chunk('IEND', '');
	return $png;
}

//a PNG chunk consists of 4 bytes for a length of the chunk data, 4 bytes
//for the chunk type, the chunk data, and 4 bytes for a check sum
function png_encode_chunk($type, $data)
{
	return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type.$data));
}