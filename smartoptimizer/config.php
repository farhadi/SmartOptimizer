<?php
/*
 * SmartOptimizer Configuration File
 */


//base dir
$settings['baseDir'] = '../';

//Encoding of your js and css files. (utf-8 or iso-8859-1)
$settings['charSet'] = 'utf-8'; 

//Show error messages if any error occurs (true or false)
$settings['debug'] = true;

//use it to set gzip compression On or Off
$settings['gzip'] = true;

//these types of files will not be gzipped nor minified
$settings['gzipExceptions'] = array('gif','jpeg','jpg','png','swf'); 

//use it to set Minifier On or Off
$settings['minify'] = true;

//use it to set file concatenation On or Off
$settings['concatenate'] = true;

//separator for files to be concatenated
$settings['separator'] = ',';

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
?>
