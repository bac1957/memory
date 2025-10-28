<?php
/**
 * Определение версии ОС WEB-сервера
 */
$UrlOS = array(
    "Ubuntu"   => "https://ubuntu.com",
    "Mint" => "https://linuxmint.com",
    "Debian" => "https://www.debian.org",
    "CentOS" => "https://www.centos.org",
    "Windows" => "http://www.microsoft.com"
    );
$debianFile = "/etc/issue.net";
$redHatFile = "/etc/redhat-release";
$serverOS_Version = "";
$serverOS_URL = "";
if (file_exists($debianFile)) {
    $f = fopen("$debianFile", "r");
    $serverOS_Version = fgets($f);
    fclose($f);
} elseif (file_exists($redHatFile)) {
    $f = fopen("$redHatFile", "r");
    $serverOS_Version = fgets($f);
    fclose($f);
} else {
    $serverOS_Version = php_uname("s") . " " . php_uname("r") . " " . php_uname("v");
}
foreach ($UrlOS as $key => $URL) {
    $pos = strpos($serverOS_Version, $key);
    if ($pos === false) {continue;}
    $serverOS_URL = $URL;
    break;
}
