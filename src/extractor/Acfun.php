<?php
class Acfun implements Extractor
{

  public function extractUrl($url)
  {
     $index=0;
     if(preg_match('%ac\d+_(\d+)%',$url,$match))
     {
       $index=$match[1]-1;
     }
     $html=Helper::getHtml($url);
     $title=null;
     if(preg_match('%<h1 id="txt-title-view">([^<>]+)<%',$html,$match))
       $title=$match[1];
     $data=array();
     if(preg_match_all('%data-vid="(\d+)"\s*data-from="(\w+)"\s*(data-did="\w*")?\s*data-sid="(\w*)"%',$html,$match))
     {
       foreach($match[1] as $k=>$vid)
       {
         $data[]=array('vid'=>$vid,'type'=>$match[2][$k],'id'=>$match[4][$k]);
       }
     }
     if($data)
     {
       $value=$data[$index];
       $vid=$value['vid'];
       $type=$value['type'];
       $id=$value['id'];
       switch($type)
       {
         case 'youku':
           $extractor=new Youku;
           return $extractor->extractById($id);
         case 'letv':
           $extractor=new Letv;
           return $extractor->extractById($id);
         case 'tudou':
           $extractor=new Tudou;
           return $extractor->extractById($id);
         case 'sina':
           return $this->extractSina($vid,$title);
       }
     }
     return $this->extractAgain($html);
     return false;
  }

  private function extractAgain($html)
  {
    $data = array();
    if(preg_match_all('%data-vid="(\d+)"\s*data-sid="(\w*)"\s*data-from="(\w+)" class="btn active primary">%',$html,$match))
    {
      $vid = $match[1][0];
      $content = Helper::getHtml("http://jiexi.acfun.info/index.php?vid=" . $vid);
      $videoInfo = json_decode($content, true);
      $result = $videoInfo['result'];
      if (isset($result['C20'])) {
        return $this->extractAcfun($result['C20'], $vid);
      } else if (isset($result['C10'])) {
        return  $this->extractAcfun($result['C10'], $vid);
      } else if (isset($result['C00'])) {
        return $this->extractAcfun($result['C00'], $vid);
      }
    }
    return false;
  }

  private function extractAcfun($videoInfo, $vid)
  {
    $time = $videoInfo['totalseconds'];
    $size = $videoInfo['totalbytes'];
    $url = array();
    $files = array();
    $files = $videoInfo['files'];
    foreach ($files as $k => $v) {
       $url[] = $v['url'];
       $files[] = array('filesize'=>$v['bytes'], 'miliseconds'=>$v['seconds']*1000, 'url'=>$v['url']);
    }
    $data=array('id'=>$vid,'title'=>'','time'=>$time*1000, 'size'=>$size, 'files'=>$files, 'url'=>$url,'type'=>'ac');
    return json_encode($data,true);
  }

  public function extractById($vid)
  {
     if(preg_match('%ac(\d+)%',$vid,$match))
     {
        return $this->extractUrl('http://www.acfun.tv/v/'.$vid);
     }else if(preg_match('%(\d+)%',$vid,$match)){
        return $this->extractUrl('http://www.acfun.tv/v/ac'.$vid);
     }
     return false;
  }

  public function extractSina($vid,$title)
  {
    $url="http://jiexi.acfun.info/index.php?vid=".$vid;
    $html=Helper::getHtml($url);
    $json=json_decode($html,true);
    $files=array();
    $firstObj=array_shift($json['result']);
    $url=$files=array();
    $time=$size=0;
    foreach($firstObj['files'] as $v)
    {
      $url[]=$v['url'];
      $second=$v['seconds']*1000;
      $time+=$second;
      $size+=$v['bytes'];
      $files[] = array('filesize'=>$v['bytes'], 'miliseconds'=>$second, 'url'=>$v['url']);
    }
    $data=array('id'=>$vid,'title'=>$title,'time'=>$time, 'size'=>$size,'files'=>$files, 'url'=>$url,'type'=>'acfun');
    return json_encode($data);
  }
}
