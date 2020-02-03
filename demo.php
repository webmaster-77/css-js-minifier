<?php
header("Content-Type: text/plain");
require "jetPacker.php";
$files = array("/css/style.css", "/css/custom.css", "/css/settings.css");
$packer = new jetPacker();
$packer->files = $files;
$out = $packer->init();
echo "/*Work with files*/\n";
echo $out;
echo "\n\n";
$data = file_get_contents("index.html");
$packer->type = "html";
$out = $packer->compress($data);
echo "/*Work with data*/\n";
echo $out;