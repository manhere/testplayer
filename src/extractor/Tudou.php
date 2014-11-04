<?php
class Tudou implements Extractor
{
  /*
   *Tudou useragent strict
   */
  public function extractUrl($url)
  {
     if(preg_match('%v/([^/]+)/%',$url,$match))
     {
       $id=$match[1];
       return $this->extractById($id);
     }
     $html=Helper::getHtml($url);
     if(preg_match('%kw\s*[:=]\s*[\'\"]([^\']+?)[\'\"]%',$html,$match))
       $title=$match[1];
     if(preg_match('%vcode\s*[:=]\s*\'([^\']+)\'%',$html,$match))
     {
       $vcode=$match[1];
       if($vcode)
       {
         $youku=new Youku();
         $res=$youku->extractById($vcode);
         if($res)
         {
           return $res;
         }
       }
     }
     if(preg_match('%iid\s*[:=]\s*(\d+)%',$html,$match))
       return $this->extractById($match[1],$title);
  }

  public function extractById($id,$title='')
  {
     $obj=json_decode(Helper::getHtml("http://www.tudou.com/outplay/goto/getItemSegs.action?iid={$id}"),true);
     $data=array_shift($obj);
     $size=0;
     $time=0;
     $url=array();
     $files = array();
     foreach($data as $v)
     {
       $size+=$v['size'];
       $time+=$v['seconds'];
       $videourl = $this->_parseByVid($v['k']);
       $url[]=$videourl;

       $file = array('filesize'=>$v['size'], 'miliseconds'=>$v['seconds'], 'url'=>$videourl);
       $files[] = $file;
     }
     $data=array('id'=>$id,'title'=>$title,'time'=>$time, 'size'=>$size, 'files'=>$files, 'url'=>$url,'type'=>'tudou');
     return json_encode($data,true);
  }


  private function _parseByVid($id)
  {
      $ctx=stream_context_create(array('http'=>array('user_agent'=>$_SERVER['HTTP_USER_AGENT'])));
      $xml=file_get_contents("http://ct.v2.tudou.com/f?id={$id}",false,$ctx);
      $obj=json_decode(json_encode(simplexml_load_string($xml,null,LIBXML_NOCDATA)),true);
      return $obj[0];
  }
}
