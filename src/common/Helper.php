<?php
class Helper
{
  static public $fakeHeaders = array(
    'Accept'=> 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Charset'=> 'UTF-8,*;q=0.5',
    'Accept-Encoding'=> 'gzip,deflate,sdch',
    'Accept-Language'=> 'en-US,en;q=0.8',
    'User-Agent'=> 'Mozilla/5.0 (X11; Linux x86_64; rv:13.0) Gecko/20100101 Firefox/13.0'
  );

  static public $fileType=array(
        'video/3gpp'=> '3gp',
        'video/f4v'=> 'flv',
        'video/mp4'=> 'mp4',
        'video/MP2T'=> 'ts',
        'video/quicktime'=> 'mov',
        'video/webm'=> 'webm',
        'video/x-flv'=> 'flv',
        'video/x-ms-asf'=> 'asf',
        'audio/mpeg'=> 'mp3'
  );

  static public function initGuzzleClient($url,$faker=false)
  {
      if($faker)
      {
        $client = new \Guzzle\Http\Client($url,array(
          'header'=>self::$fakeHeaders,
        ));
      }else{
        $client = new \Guzzle\Http\Client($url);
      }
      return $client;
  }

  static public function urlInfo($url,$faker=false)
  {
      $client=self::initGuzzleClient($url,$faker);
      $request=$client->get();
      $response =$request->send();
      $contentType=$response->getContentType();
      if(isset(self::$fileType[$contentType]))
      {
        $ext=self::$fileType[$contentType];
      }else{
        $ext=null;
        if($disposition=$response->getHeader('content-disposition'))
        {
          if(preg_match('filename="?([^"]+)"?',$disposition,$match))
          {
            $fileName=urldecode($match[1]);
            $temp=explode('.',$fileName);
            if(count($temp)>1)
            {
              $ext=array_pop($temp);
            }
          }
        }
      }
      $transfer=$response->getHeader('transfer-encoding');
      if($transfer!='chunked')
        $size=int($response->getHeader('content-length'));
      else
        $size=null;
      return array($contentType,$ext,$size);
  }

  static public function getResponse($url,$faker=false)
  {
      $client=self::initGuzzleClient($url,$faker);
      $request=$client->get();
      $response =$request->send();
      $body=$response->getBody();
      $contentEncoding=$response->getContentEncoding();
      if($contentEncoding=='gzip' || $contentEncoding=='deflate')
      {
        $body->uncompress();
      }
      $body=(string)$body;
      return $body;
  }

  static public function getHtml($url,$faker=false)
  {
      return self::getResponse($url,$faker);
  }

}

