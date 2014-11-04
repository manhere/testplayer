<?php
class Letv implements Extractor
{
private function getKey($arg1)
{
  $loc4=0;
  $loc1=array('uu','vu','ver','format','cf','ran');
  sort($loc1);
  $loc2='';
  $loc3=count($loc1);
  while($loc4<$loc3)
  {
     $key=$loc1[$loc4];
     $loc2.=$key.$arg1[$key];
     $loc4++;
  }
  return md5($loc2."bie^#@(%27eib58");
}

private function getUrl($arg1)
{
  $loc3=null;
  $loc4=null;
  if($arg1==null)
    return null;
  if(!isset($arg1['uu']))
    return null;
  if(!isset($arg1['uu']))
    return null;
  $arg1["ver"] = "2.1";
  $arg1["cf"] = "flash";
  $arg1["source"] = "letv";
  $arg1["format"] = "xml";
  $arg1["qr"] = "2";
  $arg1["ran"] =mt_rand() / mt_getrandmax();
  $arg1["sign"] =$this->getKey($arg1);
  $selfUrl="http://yuntv.letv.com/bcloud.html?uu={$arg1['uu']}&vu={$arg1['vu']}";
  $loc1='http://api.letvcloud.com/gpc.php?page_url='.urlencode($selfUrl);
  $loc5=0;
  foreach($arg1 as $k=>$v)
  {
    $loc1 .="&" . $k . "=" . urlencode($v);
  }
  return $loc1;
}

  public function extractUrl($url)
  {
    if(preg_match('%vu=(\w+)%',$url,$match))
    {
      $vu=$match[1];
      $uu='';
      if(preg_match('%vu=(\w+)%',$url,$match))
      {
         $uu=$match[1];
      }
      return $this->extractById($vu);
    }
    return false;
  }

  public function extractById($vu,$uu=null)
  {
     if(!$uu)
     {
       $uu='de3100d2ac';
     }
     $arg1=array('uu'=>$uu,'vu'=>$vu);
     $url=$this->getUrl($arg1);

     $xml= Helper::getResponse($url,true);
     if(preg_match('%<v_code>(.*?)<\/v_code>%',$xml,$match))
     {
       $respon=base64_decode($match[1]);
       if(preg_match('%<playurl><!\[CDATA\[(.*?)\]\]><\/playurl>%',$respon,$match))
       {
          $obj=json_decode($match[1],true);
          $lastNode=array_pop($obj['dispatch']);
          $url=$lastNode[0];
          $time=$obj['duration']*1000;//ms
          $files = array();
          $file = array('filesize'=>0, 'miliseconds'=>$time, 'url'=>$url);
          $files[] = $file;
          $data=array('id'=>$vu,'title'=>$obj['title'],'time'=>$time,'size'=>0,'files'=>$files,'url'=>array($url),'type'=>'letv');
          return json_encode($data,true);
       }
     }
     return false;
  }

}
