<?php
function json_response($data) {
 header('Content-Type: text/javascript; charset=utf8');
 print(json_encode($data,JSON_PRETTY_PRINT));
 exit(0);
}

function image_response($text) {
 header('Content-Type: image/jpeg');
 readfile("not_available.jpg");
 exit(0);
}

function getCameraSem() {
 return sem_get(8484839393948);
}

function isCameraUsedBySystem() {
 if (is_dir("/tmp/cameralock"))
   return true;
 return false;
}

function acquireCamera($sem) {
 sem_acquire($sem);
}

function releaseCamera($sem) {
 sem_release($sem);
}

function renderAndStore($template, $data, $file) {
 ob_start();
 $data = $data;
 include($template);
 $rendered = ob_get_clean();
 file_put_contents($file,$rendered);
}

function parseUrl($url) {
 $path = "";
 if (key_exists("path",parse_url($url))) {
  $path = parse_url($url)['path'];
 }
 $query = "";
 if (key_exists("query",parse_url($url))) {
  $query = parse_url($url)['query'];
 }
 $noUserPass = parse_url($url)['scheme']."://".parse_url($url)['host'].$path."?".$query;
 return [
  "url" => $noUserPass,
  "host" => parse_url($url, PHP_URL_HOST),
  "user" => parse_url($url, PHP_URL_USER),
  "pass" => parse_url($url, PHP_URL_PASS)
 ];
}

function getMotionConfig() {
 return file_get_contents(dirname(__FILE__)."/motion.json");
}

function getRoot() {
 return dirname(__FILE__)."/../..";
}

function isMotionRunning() {
  ob_start();
  system('ps aux | grep monitor.sh');
  return count(split("\n",ob_get_clean())) == 4;
}
?>
