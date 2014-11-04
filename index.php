<?php
$autoload=dirname(__FILE__)."/vendor/autoload.php";
require($autoload);
if(!empty($_GET['url']))
{
  $route=new Router($_GET['url']);
  $extractorName=UcFirst($route->resolveToModule());
  $extractor=new $extractorName;
  $json=$extractor->extractUrl($route->url);
  if(!empty($_GET['callback']))
  {
    echo $_GET['callback'].'('.$json.')';
  }else{
    echo $json;
  }
}else if(!empty($_GET['id']) && !empty($_GET['type'])){
  $extractorName=UcFirst($_GET['type']);
  $extractor=new $extractorName;
  $json=$extractor->extractById($_GET['id']);
  if(!empty($_GET['callback']))
  {
    echo $_GET['callback'].'('.$json.')';
  }else{
    echo $json;
  }
}
