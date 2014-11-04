<?php
class Bilibili implements Extractor
{
  static public $appkey='85eb6835b0a1034e';
  static public $secretkey='2ad42749773c441109bdc0191257a664';
  static public $header=array(
    'Accept'=>'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Charset'=> 'UTF-8,*;q=0.5',
    'Accept-Encoding'=> 'gzip,deflate,sdch',
    'Accept-Language'=> 'en-US,en;q=0.8',
    'User-Agent'=>'Biligrab /0.8 (cnbeining@gmail.com)'
  );

  public function extractUrl($url)
  {
     $html=Helper::getHtml($url);
     $title=null;
     if(preg_match('%<h2[^>]*>([^<>]+)<\/h2>%',$html,$match))
     {
        $title=$match[1];
     }
     if(preg_match('%(cid=\d+)%',$html,$match))
     {
       $res=$match[1];
     } else if(preg_match('%flashvars="([^"]+)"%',$html,$match)){
       $res=$match[1];
     }else if(preg_match('%"https://[a-z]+\.bilibili\.com/secure,(cid=\d+)(?:&aid=\d+)?"%',$html,$match)){
       $res=$match[1];
     }
     list($temp,$cid)=explode('=',$res);
     if($cid)
     {
       return $this->extractById($cid,$title);
     }
     return false;
  }

  public function extractById($vid,$title=null)
  {
     $sign=md5('appkey='.self::$appkey.'&cid='.$vid.self::$secretkey);
     $url = 'http://interface.bilibili.com/playurl?appkey=' .self::$appkey. '&cid=' .$vid. '&sign=' .$sign;
     $client = new \Guzzle\Http\Client($url,array(
       'header'=>self::$header,
     ));
     $request=$client->get();
     $response =$request->send();
     $body=$response->getBody();
     $xml=(string)$body;
     $obj=json_decode(json_encode(simplexml_load_string($xml,null,LIBXML_NOCDATA)),true);
     $size=0;
     $files = array();
     foreach($obj['durl'] as $v)
     {
        $file = array('filesize'=>0, 'miliseconds'=>0, 'url'=>$v['url']);
        if (isset($v['length']))
        {
            $file['miliseconds'] = $v['length'];
        }
        if (isset($v['size']))
        {
          $size+=$v['size'];
        }
        $files[] = $file;
     }
     $data=array('id'=>$vid,'title'=>$title,'time'=>$obj['timelength'], 'size'=>$size,'files'=>$files, 'url'=>$obj['durl'],'type'=>'bilibili');
     return json_encode($data,true);
  }
}
