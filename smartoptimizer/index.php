<?php
/* SmartOptimizer v1.8
 * SmartOptimizer enhances your website performance using techniques
 * such as compression, concatenation, minifying, caching, and embedding on demand.
 *
 * Copyright (c) 2006-2010 Ali Farhadi (http://farhadi.ir/)
 * Released under the terms of the GNU Public License.
 * See the GPL for details (http://www.gnu.org/licenses/gpl.html).
 *
 * Author: Ali Farhadi (a.farhadi@gmail.com)
 * Website: http://farhadi.ir/
 */

//Default settings
$settings = array(
	'baseDir' => '../',
	'charSet' => 'utf-8',
	'debug' => true,
	'gzip' => true,
	'compressionLevel' => 9,
	'gzipExceptions' => array('gif','jpeg','jpg','png','swf','ico'),
	'minify' => true,
	'concatenate' => true,
	'separator' => ',',
	'embed' => true,
	'embedMaxSize' => 5120,
	'embedExceptions' => array('htc'),
	'serverCache' => true,
	'serverCacheCheck' => false,
	'cacheDir' => 'cache/',
	'cachePrefix' => 'so_',
	'clientCache' => true,
	'clientCacheCheck' => false,
);

//mime types
$mimeTypes = array(
	"js"	=> "text/javascript",
	"css"	=> "text/css",
	"htm"	=> "text/html",
	"html"	=> "text/html",
	"xml"	=> "text/xml",
	"txt"	=> "text/plain",
	"jpg"	=> "image/jpeg",
	"jpeg"	=> "image/jpeg",
	"png"	=> "image/png",
	"gif"	=> "image/gif",
	"swf"	=> "application/x-shockwave-flash",
	"ico"	=> "image/x-icon",
);

function headerExit($status) {
	header("HTTP/1.0 $status");
	exit();
}

function headerNoCache() {
	// already expired
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

	// always modified
	header("Last-Modified: " . gmdatestr());

	// HTTP/1.1
	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Cache-Control: max-age=0", false);

	// HTTP/1.0
	header("Pragma: no-cache");

	//generate a unique Etag each time
	header('Etag: '.microtime());
}

function headerNeverExpire(){
	header("Expires: " . gmdatestr(time() + 315360000));
	header("Cache-Control: max-age=315360000");
}

function debugExit($msg){
	global $settings;
	if (!$settings['debug']) {
		headerExit('404 Not Found');
	}
	headerNoCache();
	header('Content-Type: text/html; charset='.$settings['charSet']);
	header("Content-Encoding: none");
	echo "//<script>\n";
	echo "alert('SmartOptimizer Error: ".str_replace("\n", "\\n", addslashes($msg))."');\n";
	echo "//</script>\n";
	exit();
}

function gmdatestr($time = null) {
	if (is_null($time)) $time = time();
	return gmdate("D, d M Y H:i:s", $time) . " GMT";
}

function filesmtime() {
	global $files, $fileType;
	static $filesmtime;
	if ($filesmtime) return $filesmtime;
	$filesmtime = max(@filemtime("minifiers/$fileType.php"), filemtime('index.php'), filemtime('config.php'));
	foreach ($files as $file) {
		if (!file_exists($file)) debugExit("File not found ($file).");
		$filesmtime = max(filemtime($file), $filesmtime);
	}
	return $filesmtime;
}

@include('config.php');

list($query) = explode('?', urldecode($_SERVER['QUERY_STRING']));

if (preg_match('/^\/?(.+\/)?(.+)$/', $query, $matchResult)) {
	$fileNames = $matchResult[2];
	$fileDir = $settings['baseDir'].$matchResult[1];
} else debugExit("Invalid file name ($query)");

if (strpos(realpath($fileDir), realpath($settings['baseDir'])) !== 0) debugExit("File is out of base directory.");

if ($settings['concatenate']) {
	$files = explode($settings['separator'], $fileNames);
	$settings['concatenate'] = count($files) > 1;
} else $files = array($fileNames);

foreach ($files as $key => $file) {
	if (preg_match('/^[^\x00]+\.([a-z0-9]+)$/i', $file, $matchResult)) {
		$fileTypes[] = strtolower($matchResult[1]);
	} else debugExit("Unsupported file ($file)");

	$files[$key] = $fileDir.$file;
}

if ($settings['concatenate']) {
	if (count(array_unique($fileTypes)) > 1) debugExit("Files must be of the same type.");
}

$fileType = $fileTypes[0];

if (!isset($mimeTypes[$fileType])) debugExit("Unsupported file type ($fileType)");
header("Content-Type: {$mimeTypes[$fileType]}; charset=".$settings['charSet']);

$settings['gzip'] =
	($settings['gzip'] &&
	!in_array($fileType, $settings['gzipExceptions']) &&
	in_array('gzip', array_map('trim', explode(',' , @$_SERVER['HTTP_ACCEPT_ENCODING']))) &&
	function_exists('gzencode'));

if ($settings['gzip']) header("Content-Encoding: gzip");

$settings['minify'] = $settings['minify'] && file_exists('minifiers/'.$fileType.'.php');
$settings['embed'] = $settings['embed'] && $fileType == 'css' && (!preg_match('/msie/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/msie 8|opera/i', $_SERVER['HTTP_USER_AGENT']));
$settings['serverCache'] = $settings['serverCache'] && ($settings['minify'] || $settings['gzip'] || $settings['concatenate'] || $settings['embed']);

if ($settings['serverCache']) {
	$cachedFile = $settings['cacheDir'].$settings['cachePrefix'].md5($query.($settings['embed']?'1':'0')).'.'.$fileType.($settings['gzip'] ? '.gz' : '');
}

$generateContent = ((!$settings['serverCache'] && (!$settings['clientCache'] || !$settings['clientCacheCheck'] || !isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || $_SERVER['HTTP_IF_MODIFIED_SINCE'] != gmdatestr(filesmtime()))) ||
	($settings['serverCache'] && (!file_exists($cachedFile) || ($settings['serverCacheCheck'] && filesmtime() > filemtime($cachedFile)))));

if ($settings['clientCache'] && $settings['clientCacheCheck']) {
	if ($settings['serverCache'] && !$generateContent) $mtime = filemtime($cachedFile);
	elseif ($settings['serverCache']) $mtime = time();
	else $mtime = filesmtime();
	$mtimestr = gmdatestr($mtime);
}

if (!$settings['clientCache'] || !$settings['clientCacheCheck'] || !isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || $_SERVER['HTTP_IF_MODIFIED_SINCE'] != $mtimestr) {
	if ($settings['clientCache'] && $settings['clientCacheCheck']) {
		header("Last-Modified: " . $mtimestr);
		header("Cache-Control: must-revalidate");
	} elseif ($settings['clientCache']) {
		headerNeverExpire();
	} else headerNoCache();

	if ($generateContent) {
		if ($settings['minify']) include('minifiers/'.$fileType.'.php');
		$content = array();
		foreach ($files as $file) (($content[] = @file_get_contents($file)) !== false) || debugExit("File not found ($file).");
		$content = implode("\n", $content);
		if ($settings['minify']) $content = call_user_func('minify_' . $fileType, $content);
		if ($settings['gzip']) $content = gzencode($content, $settings['compressionLevel']);
		if ($settings['serverCache']) {
			$handle = @fopen($cachedFile, 'w') or debugExit("Could not create cache file($cachedFile).");
			fwrite($handle, $content);
			fclose($handle);
		}
		header('Content-Length: ' . strlen($content));
		echo $content;
	} else {
		header('Content-Length: ' . filesize($cachedFile));
		readfile($cachedFile);
	}
} else headerExit('304 Not Modified');

?>