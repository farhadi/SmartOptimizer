<?php
/*
 * SmartOptimizer CSS Minifier
 */

function minify_css($str) {
	global $settings;
	
	$urlPrefix = '';
	if (strpos($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'].'?') === 0 ||
		strpos($_SERVER['REQUEST_URI'], dirname($_SERVER['SCRIPT_NAME']).'/?') === 0 ) {
		$urlPrefix = '../'.dirname($_SERVER['QUERY_STRING']).'/';
	}
	
	$res = '';
	$i=0;
	$inside_block = false;
	$current_char = '';
	while ($i+1<strlen($str)) {
		if ($str[$i]=='"' || $str[$i]=="'") {//quoted string detected
			$res .= $quote = $str[$i++];
			if (strtolower(substr($res, -5, 4))=='url(') {
				if (!preg_match('@://@', substr($str, $i, strpos($str, ')', $i) - $i))) $res .= $urlPrefix;
			}
			while ($i<strlen($str) && $str[$i]!=$quote) {
				if ($str[$i] == '\\') {
					$res .= $str[$i++];
				}
				$res .= $str[$i++];
			}
			$res .= $str[$i++];
			continue;
		} elseif (strtolower(substr($res, -4))=='url(') {//uri detected
			if ($str[$i] == '\'' || $str[$i] == '"') $res .= $str[$i++];
			if (!preg_match('@://@', substr($str, $i, strpos($str, ')', $i) - $i))) $res .= $urlPrefix;
			do {
				if ($str[$i] == '\\') {
					$res .= $str[$i++];
				}
				$res .= $str[$i++];
			} while ($i<strlen($str) && $str[$i]!=')');
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