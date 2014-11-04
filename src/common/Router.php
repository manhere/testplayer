<?php
class Router
{
  static public $moduleMap=array(
    'acfun'=>'Acfun',
    'bilibili'=>'Bilibili',
    'catfun'=>'Catfun',
    'cntv'=>'Cntv',
    'iask'=>'Sina',
    'iqiyi'=>'Iqiyi',
    'kankanews'=>' Bilibili',
    'ku6'=>'Ku6',
    'letv'=>'letv',
    'miomio'=>'Miomio',
    'qq'=>'Qq',
    'sina'=>'Sina',
    'smg'=>'Bilibili',
    'sohu'=>'Sohu',
    'tucao'=>'Tucao',
    'tudou'=>'Tudou',
    'youku'=>'Youku',
    'qq'=>'QQ',
  );
   public $url;
   public function __construct($url)
   {
     $this->url=$url;
   }

   public function resolveToModule()
   {
     $host=parse_url($this->url,PHP_URL_HOST);
     list($sub,$root,$top)=explode('.',$host);
     if(isset(self::$moduleMap[$root]))
     {
       return self::$moduleMap[$root];
     }
     return false;
   }
}
