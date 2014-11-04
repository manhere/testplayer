<?php
class Sina implements Extractor
{

  public function extractUrl($url)
  {
     $html=null;
     if(!preg_match('%\#(\d+)$%',$url,$match))
     {
         $html=Helper::getHtml($url);
         $vid=$hdVid=0;
         if(preg_match('%hd_vid\s*:\s*\'([^\']+)\'%',$html,$match))
         {
           $vid=$hdVid=$match[1];
         }
         if($hdVid=='0')
         {
           preg_match('%[^\w]vid\s*:\s*\'([^\']+)\'%',$html,$match);
           $vids=explode('|',$match[1]);
           $vid=array_pop($vids);
         }
     }else{
       $vid=$match[1];
     }
     if($vid)
     {
       return $this->extractById($vid);
     }
  }

  public function extractById($vid)
  {
      $rand1=rand(10000,10000000);
      $rand2=rand(10000,10000000);
      $rand = "0.{$rand1}{$rand2}";
      $k=$this->_genKey($vid,$rand);
      $url ="http://v.iask.com/v_play.php?vid={$vid}&ran={$rand}&p=i&k={$k}";
      $xml= Helper::getResponse($url,true);
      $obj=json_decode(json_encode(simplexml_load_string($xml,null,LIBXML_NOCDATA)),true);
      $time=$obj['timelength'];
      $title=$obj['vname'];
      $files = array();
      if(isset($obj['durl'][0]))
      {
        $size=0;
        $url=array();
        foreach($obj['durl'] as $v)
        {
          $size+=$v['filesize'];
          $url[]=$v['url'];
          $files[] = array('filesize'=>$v['filesize'], 'miliseconds'=>$v['length'], 'url'=>$v['url']);
        }
      }else{
        $size=$obj['durl']['filesize'];
        $url=$obj['durl']['url'];
        $files[] = array('filesize'=>$v['durl']['filesize'], 'miliseconds'=>$v['durl']['length'], 'url'=>$v['durl']['url']);
      }
      $data=array('id'=>$vid,'title'=>$title,'time'=>$time, 'size'=>$size,'files'=>$files, 'url'=>$url,'type'=>'sina');
      return json_encode($data,true);
  }

  public function _genKey($vid,$rand)
  {
    $binTime=sprintf('%b',time());
    $t=bindec(substr($binTime,0,strlen($binTime)-6));
    return substr(md5($vid.'Z6prk18aWxP278cVAH'.$t.$rand),0,16).$t;
  }

}
