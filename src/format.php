<?php

//format a number as GiB, MiB, KiB, or bytes
function format_bytes($n) {
	if ($n >= 1024 * 1024 * 1024) {
		return round($n / (1024 * 1024 * 1024), 1) . 'GiB';
	} elseif ($n >= 1024 * 1024) {
		return round($n / (1024 * 1024), 1) . 'MiB';
	} elseif ($n >= 1024) {
		return round($n / 1024, 1) . 'KiB';
	} else {
		return $n . 'bytes';
	}	
}