<?php

//return a designated number of bytes at a given offset
//and increase the offset by the number of bytes read
function read_bytes(&$data, &$offset, $length)
{
	$bytes = substr($data, $offset, $length);

	if (strlen($bytes) < $length) {
		throw new Exception('Unexpected end of data');
	}
	
	$offset += $length;

	return $bytes;
}

//read an unsigned 32 bit integer at a offset in a byte array
function read_int(&$data, &$offset)
{
	return unpack('N', read_bytes($data, $offset, 4))[1];
}