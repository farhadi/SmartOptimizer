<?php
/*
 * SmartOptimizer CSS Minifier
 */

function convertUrl($url, $count)
{
	global $settings, $mimeTypes, $fileDir;
	
	static $baseUrl = '';
	
	$url = trim($url);
	
	if (preg_match('@^[^/]+:@', $url)) return $url;
	
	$fileType = substr(strrchr($url, '.'), 1);
	if (isset($mimeTypes[$fileType])) $mimeType = $mimeTypes[$fileType];
	elseif (function_exists('mime_content_type')) $mimeType = mime_content_type($url);
	else $mimeType = null;
	
	if (!$settings['embed'] ||
		!file_exists($fileDir.$url) ||
		($settings['embedMaxSize'] > 0 && filesize($fileDir.$url) > $settings['embedMaxSize']) ||
		!$fileType ||
		in_array($fileType, $settings['embedExceptions']) ||
		!$mimeType ||
		$count > 1) {
		if (strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'].'?') === 0 ||
			strpos($_SERVER['REQUEST_URI'], rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/').'/?') === 0) {
			if (!$baseUrl) return $fileDir . $url;
		}
		return $baseUrl . $url;
	}
	
	$contents = file_get_contents($fileDir.$url);
	 
	if ($fileType == 'css') {
		$oldFileDir = $fileDir;
		$fileDir = rtrim(dirname($fileDir.$url), '\/').'/';
		$oldBaseUrl = $baseUrl;
		$baseUrl = 'http'.(@$_SERVER['HTTPS']?'s':'').'://'.$_SERVER['HTTP_HOST'].rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/').'/'.$fileDir;
		$contents = minify_css($contents);		
		$fileDir = $oldFileDir;
		$baseUrl = $oldBaseUrl;
	}
	
	$base64   = base64_encode($contents); 
	return 'data:' . $mimeType . ';base64,' . $base64;
}

function minify_css($str) {
	$res = '';
	$i=0;
	$inside_block = false;
	$current_char = '';
	while ($i+1<strlen($str)) {
		if ($str[$i]=='"' || $str[$i]=="'") {//quoted string detected
			$res .= $quote = $str[$i++];
			$url = '';
			while ($i<strlen($str) && $str[$i]!=$quote) {
				if ($str[$i] == '\\') {
					$url .= $str[$i++];
				}
				$url .= $str[$i++];
			}
			if (strtolower(substr($res, -5, 4))=='url(' || strtolower(substr($res, -9, 8)) == '@import ') {
				$url = convertUrl($url, substr_count($str, $url));
			}
			$res .= $url;
			$res .= $str[$i++];
			continue;
		} elseif (strtolower(substr($res, -4))=='url(') {//url detected
			$url = '';
			do {
				if ($str[$i] == '\\') {
					$url .= $str[$i++];
				}
				$url .= $str[$i++];
			} while ($i<strlen($str) && $str[$i]!=')');
			$url = convertUrl($url, substr_count($str, $url));
			$res .= $url;
			$res .= $str[$i++];
			continue;
		} elseif ($str[$i].$str[$i+1]=='/*') {//css comment detected
			$i+=3;
			while ($i<strlen($str) && $str[$i-1].$str[$i]!='*/') $i++;
			if ($current_char == "\n") $str[$i] = "\n";
			else $str[$i] = ' ';
		}
		
		if (strlen($str) <= $i+1) break;
		
		$current_char = $str[$i];
		
		if ($inside_block && $current_char == '}') {
			$inside_block = false;
		}
		
		if ($current_char == '{') {
			$inside_block = true;
		}
		
		if (preg_match('/[\n\r\t ]/', $current_char)) $current_char = " ";
		
		if ($current_char == " ") {
			$pattern = $inside_block?'/^[^{};,:\n\r\t ]{2}$/':'/^[^{};,>+\n\r\t ]{2}$/';
			if (strlen($res) &&	preg_match($pattern, $res[strlen($res)-1].$str[$i+1]))
				$res .= $current_char;
		} else $res .= $current_char;
		
		$i++;
	}
	if ($i<strlen($str) && preg_match('/[^\n\r\t ]/', $str[$i])) $res .= $str[$i];
	return $res;
}
?>
