<?php

namespace SoundNormalizer;

class Utilities
{
	public static function getIP()
	{
		return $_SERVER["REMOTE_ADDR"];
	}

	public static function getYouTubeID($url)
	{
		$url_parts = parse_url($url);
		parse_str($url_parts["query"], $query_parts);
		
		$video_id = "";
		if (strpos($url_parts["host"], "youtube.com") !== false) {
			if (isset($query_parts["v"])) {
				$video_id = $query_parts["v"];
			}
		}

		if ($video_id != "") {
			return $video_id;
		} else {
			return false;
		}
	}

	public static function getRandomHash()
	{
		return bin2hex(openssl_random_pseudo_bytes(16));
	}
}