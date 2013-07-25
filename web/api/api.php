<?php 
include("utils.php");

$CAM_CACHE = 'rpi_cam_image';
$CAM_CACHE_TTL = 1; //Seconds

if (isset($_GET["how"])) {
 $how = [
  "ex" => ["auto","night","nightpreview","backlight",
           "spotlight","sports","snow","beach","verylong",
           "fixedfps","antishake","fireworks","off"],
  "awb" => ["auto","sun","cloud","shade","tungsten",
            "fluorescent","incandescent","flash","horizon","off"],
  "ifx" => ["none","negative","solarise","sketch","denoise",
               "emboss","oilpaint","hatch","gpen","pastel",
               "watercolour","film","blur","saturation",
               "colourswap","washedout","posterise","colourpoint",
               "colourbalance","cartoon"],
  "mm" => ["average","spot","backlit","matrix"],
  "rot" => ["0","90","180","270"],
  "w" => ["200","300","480","576","720","768","1024","1080","1280","1920","1944","2952"],
  "h" => ["200","300","480","576","720","768","1024","1080","1280","1920","1944","2952"],
  "q" => ["100", "90", "80", "70", "60", "50"]
 ];

 json_response($how);
}

if ($_GET["operation"] == "cameras") {
 $cameras = [["ip" => "127.0.0.1"]];
 json_response($cameras);
}

if ($_GET["operation"] == "camera") {
 if (isset($_GET["snapshot"])) {
  $sem = getCameraSem();
  acquireCamera($sem);
  if (!apc_exists($CAM_CACHE)) {
   $params = " ".getParam("ex","");
   $params = $params." ".getParam("awb","");
   $params = $params." ".getParam("ifx","");
   $params = $params." ".getParam("mm","");
   $params = $params." ".getParam("rot","");
   $params = $params." ".getParam("w","");
   $params = $params." ".getParam("h","");
   $params = $params." ".getParam("q","");
   $command = escapeshellcmd("/opt/vc/bin/raspistill -t 0 ".$params." -o -");
   //print $command;
   ob_start();
   system($command);
   $image = ob_get_clean();
   apc_store($CAM_CACHE, $image, $CAM_CACHE_TTL);
  }
  releaseCamera($sem);
  header('Content-Type: image/jpeg');
  print apc_fetch('rpi_cam_image');
  exit(0);
 }
}

if ($_GET["operation"] == "motion") {
 if ($_GET["action"] == "store") {
  $jsonOptions = json_encode($_POST,JSON_PRETTY_PRINT);
  file_put_contents(dirname(__FILE__)."/motion.json",$jsonOptions);
  
  ob_start();
  $data = $_POST;
  include("motion.conf.template.php");
  $motionConf = ob_get_clean(); 
  file_put_contents(dirname(__FILE__)."/motion.conf",$motionConf);
 
  json_response($jsonOptions);
 }

 if ($_GET["action"] == "get") {
  $jsonOptions = file_get_contents(dirname(__FILE__)."/motion.json");
  json_response($jsonOptions);
  exit(0);
 }
}

header('HTTP/1.1 500 Internal Server Error');
print("Did not understand input parameters.<br/>");
print($_SERVER['QUERY_STRING']);
?>
