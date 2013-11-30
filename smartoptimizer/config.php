<?php
/**
 * SmartOptimizer Configuration File
 **/

//base dir (a relative path to the base directory)
$settings['baseDir'] = '';

//Encoding of your js and css files. (utf-8 or iso-8859-1)
$settings['charSet'] = 'utf-8'; 

//Show error messages if any error occurs (true or false)
$settings['debug'] = true;

//use this to set gzip compression On or Off
$settings['gzip'] = true;

//use this to set gzip compression level (an integer between 1 and 9)
$settings['compressionLevel'] = 9;

//these types of files will not be gzipped nor minified
$settings['gzipExceptions'] = array('gif','jpeg','jpg','png','swf'); 

//use this to set Minifier On or Off
$settings['minify'] = true;

//use this to set file concatenation On or Off
$settings['concatenate'] = true;

//separator for files to be concatenated. Tip: Don't use '.' if you use groups and name them like suggested.
$settings['separator'] = ',';

//specifies whether to emebed files included in css files using the data URI scheme or not 
$settings['embed'] = true;

//The maximum size of an embedded file. (use 0 for unlimited size)
$settings['embedMaxSize'] = 5120; //5KB

//these types of files will not be embedded
$settings['embedExceptions'] = array('htc'); 

//to set server-side cache On or Off
$settings['serverCache'] = true;

//if you change it to false, the files will not be checked for modifications and always cached files will be used (for better performance)
$settings['serverCacheCheck'] = true;

//cache dir
$settings['cacheDir'] = 'cache/';

//prefix for cache files
$settings['cachePrefix'] = 'so_';

//to set client-side cache On or Off
$settings['clientCache'] = true;

//Setting this to false will force the browser to use cached files without checking for changes.
$settings['clientCacheCheck'] = false;

//Minifier to use when parsing js files. Add yours in /minifiers directory, and implement function minify_js($text_to_minify)
$settings['jsMinifier'] = 'packer';

//Minifier to use when parsing css files. Add yours in /minifiers directory, and implement function minify_css($text_to_minify)
$settings['cssMinifier'] = 'css';

//To use groups effectively, include your files with absolute paths. Define constants for easy access. 
define('MY_JS_DIR', $_SERVER['DOCUMENT_ROOT'] . '/tkitt/js/');
define('MY_CSS_DIR', $_SERVER['DOCUMENT_ROOT'] . '/tkitt/css/');

//groups configuration. Call a group by using /path/to/smartoptimizer/?group.group_name.
//Tip: If you use 'test.js' as your group name, you'll get a nifty file name like 'group.test.js' 
$settings['groups'] = array(
	'test.js' => array(MY_JS_DIR.'test.js', MY_JS_DIR.'test2.js'),
	'test.css' => array(MY_JS_DIR.'style.css', MY_JS_DIR.'print.css')
);