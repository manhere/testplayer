<?php
class Youku implements Extractor
{
  static  public $streamTypes=array(
    array('id'=> 'hd3', 'container'=> 'flv','type'=>'flv', 'video_profile'=> '1080P'),
    array('id'=> 'hd2', 'container'=> 'flv','type'=>'flv', 'video_profile'=> '超清'),
    array('id'=> 'mp4', 'container'=> 'mp4','type'=>'mp4', 'video_profile'=> '高清'),
    array('id'=> 'flvhd', 'container'=> 'flv','type'=>'flv', 'video_profile'=> '高清'),
    array('id'=> 'flv', 'container'=> 'flv','type'=>'flv', 'video_profile'=> '标清'),
    array('id'=> '3gphd', 'container'=> '3gp','type'=>'mp4', 'video_profile'=> '高清（3GP）'),
    array('id'=> '3gp', 'container'=> '3gp','type'=>'flv', 'video_profile'=> '3GP'),
  );
  private static $sz = '-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,-1,62,-1,-1,-1,63,52,53,54,55,56,57,58,59,60,61,-1,-1,-1,-1,-1,-1,-1,0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,-1,-1,-1,-1,-1,-1,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,-1,-1,-1,-1,-1';
  private static $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

  static public $typeArray = array("flv"=>"flv","mp4"=>"mp4","hd2"=>"flv","3gphd"=>"mp4","3gp"=>"flv","hd3"=>"flv");

  public function extractUrl($url)
  {
     $id=$this->_getVidFromUrl($url);
     return $this->extractById($id);
  }

  public function extractById($id)
  {
     $videoInfo=$this->parseMetaInfo($id);
     if($videoInfo)
     {
       $files = $this->parseFiles($videoInfo);
       $src=$this->parseVideoSrc($videoInfo);

       $data=array('id'=>$id,'title'=>$videoInfo['title'],'time'=>$videoInfo['time']*1000, 'size'=>$videoInfo['size'],'files'=>$files, 'url'=>$src,'type'=>'youku');
       return json_encode($data,true);
     }
     return false;
  }

  private function parseVideoSrc(&$info)
  {
    $urls=array();
    list($sid, $token) = explode('_', self::yk_e('becaf9be', self::yk_na($info['ep'])));
    $types=array();
    $selectType='flv';
    if(in_array('hd2',$info['streamstypes']))
    {
      $selectType='hd2';
    } elseif(in_array('flvhd',$info['streamstypes'])){
      $selectType='flvhd';
    } elseif(in_array('mp4',$info['streamstypes'])){
      $selectType='mp4';
    }
    $info['size']=$info['streamsizes'][$selectType];
    $i=1;
    foreach($info['segs'][$selectType] as $k=>$v)
    {
      $no = strtoupper(dechex($v['no'])); //转换为16进制 大写
      if(strlen($no) == 1){
        $no ="0".$no;  //no 为每段视频序号
      }
      //构建视频地址K值
      $_k = $v['k'];
      if ((!$_k || $_k == '') || $_k == '-1') {
        $_k = $info['bsegs'][$selectType][$k]['k'];
      }
      $fileId =$this->getFileid($info['streamfileids'][$selectType],$info['seed']);
      $fileId = substr($fileId,0,8).$no.substr($fileId,10);
      $newEp = urlencode(iconv('gbk', 'UTF-8', self::yk_d(self::yk_e('bf7e5f01', ((($sid . '_') . $fileId) . '_') . $token))));
      $fileType = self::$typeArray[$selectType];
      $urls[]="http://k.youku.com/player/getFlvPath/sid/{$sid}_0{$i}/st/{$fileType}/fileid/{$fileId}?K={$_k}&hd=1&myp=0&ts={$info['time']}&ypp=0&ctype=12&ev=1&token={$token}&oip={$info['ip']}&ep={$newEp}";
      $i++;
    }
    return $urls;
  }

  private function parseFiles($info)
  {
    $files=array();
    list($sid, $token) = explode('_', self::yk_e('becaf9be', self::yk_na($info['ep'])));
    $types=array();
    $selectType='flv';
    if(in_array('hd2',$info['streamstypes']))
    {
      $selectType='hd2';
    } elseif(in_array('flvhd',$info['streamstypes'])){
      $selectType='flvhd';
    } elseif(in_array('mp4',$info['streamstypes'])){
      $selectType='mp4';
    }
    $info['size']=$info['streamsizes'][$selectType];
    $i=1;
    foreach($info['segs'][$selectType] as $k=>$v)
    {
      $no = strtoupper(dechex($v['no'])); //转换为16进制 大写
      if(strlen($no) == 1){
        $no ="0".$no;  //no 为每段视频序号
      }
      //构建视频地址K值
      $_k = $v['k'];
      if ((!$_k || $_k == '') || $_k == '-1') {
        $_k = $info['bsegs'][$selectType][$k]['k'];
      }
      $fileId =$this->getFileid($info['streamfileids'][$selectType],$info['seed']);
      $fileId = substr($fileId,0,8).$no.substr($fileId,10);
      $newEp = urlencode(iconv('gbk', 'UTF-8', self::yk_d(self::yk_e('bf7e5f01', ((($sid . '_') . $fileId) . '_') . $token))));
      $fileType = self::$typeArray[$selectType];
      $url="http://k.youku.com/player/getFlvPath/sid/{$sid}_0{$i}/st/{$fileType}/fileid/{$fileId}?K={$_k}&hd=1&myp=0&ts={$info['time']}&ypp=0&ctype=12&ev=1&token={$token}&oip={$info['ip']}&ep={$newEp}";
      $files[] = array('filesise'=>$v['size'], 'miliseconds'=>$v['seconds']*1000, 'url'=>$url);

      $i++;
    }
    return $files;
  }

  private function getFileid($fileId,$seed)
  {
    $mixed =$this->getMixString($seed);
    $ids = explode("*",rtrim($fileId,'*')); //去掉末尾的*号分割为数组
    $realId = "";
    for ($i=0;$i<count($ids);$i++){
      $idx = $ids[$i];
      $realId .= substr($mixed,$idx,1);
    }
    return $realId;
  }

  private  function getMixString($seed)
  {
    $mixed = "";
    $source ="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ/\\:._-1234567890";
    $len = strlen($source);
    for($i=0;$i<$len;$i++){
      $seed = ($seed * 211 + 30031)%65536;
      $index = ($seed / 65536 * strlen($source));
      $c = substr($source,$index,1);
      $mixed .= $c;
      $source = str_replace($c,"",$source);
    }
    return $mixed;
  }

  private function parseMetaInfo($id)
  {
    $video=array();
    if($id)
    {
      $video['id']=$id;
        $baseUrl="http://v.youku.com/player/getPlayList/VideoIDS/{$id}";
        $metaUrl="http://v.youku.com/player/getPlayList/VideoIDS/{$id}/Pf/4/ctype/12/ev/1";
        $meta=json_decode(Helper::getHtml($metaUrl),true);
        if(!empty($meta) && $meta['data'])
        {
          $base=json_decode(Helper::getHtml($baseUrl),true);
          $data=$meta['data'][0];
          if(!empty($data['error']))
          {
            return false;
          }
          $baseData=$base['data'][0];
          $video['streamstypes']=$data['streamtypes'];
          $video['streamsizes']=$data['streamsizes'];
          $video['streamfileids']=$data['streamfileids'];
          $video['seed']=$data['seed'];
          $video['segs']=$data['segs'];
          $video['title']=$data['title'];
          $video['ep']=$data['ep'];
          $video['ip']=$data['ip'];
          $video['time']=$data['seconds'];
          $video['bsegs']=$baseData['segs'];
        }
     }
     return $video;
  }

   private  function _getVidFromUrl($url)
   {
     if(preg_match('%youku\.com/v_show/id_([\w=]+)%',$url,$match) ||
        preg_match('%player\.youku\.com/player\.php/sid/([\w=]+)/v\.swf%',$url,$match) ||
        preg_match('%loader\.swf\?VideoIDS=([\w=]+)%',$url,$match)
     )
     {
       return $match[1];
     }
     return false;
   }

   private function _getPlaylistIdFromUrl($url)
   {
     if(preg_match_all('%youku\.com/playlist_show/id_([\w=]+)%',$url,$match))
     {
       return $match[1];
     }
     return false;
   }


   private static function yk_d($a){
     if (!$a) {
       return '';
     }
     $f = strlen($a);
     $b = 0;
     $str = self::$str;
     for ($c = ''; $b < $f;) {
       $e = self::charCodeAt($a, $b++) & 255;
       if ($b == $f) {
         $c .= self::charAt($str, $e >> 2);
         $c .= self::charAt($str, ($e & 3) << 4);
         $c .= '==';
         break;
       }
       $g = self::charCodeAt($a, $b++);
       if ($b == $f) {
         $c .= self::charAt($str, $e >> 2);
         $c .= self::charAt($str, ($e & 3) << 4 | ($g & 240) >> 4);
         $c .= self::charAt($str, ($g & 15) << 2);
         $c .= '=';
         break;
       }
       $h = self::charCodeAt($a, $b++);
       $c .= self::charAt($str, $e >> 2);
       $c .= self::charAt($str, ($e & 3) << 4 | ($g & 240) >> 4);
       $c .= self::charAt($str, ($g & 15) << 2 | ($h & 192) >> 6);
       $c .= self::charAt($str, $h & 63);
     }
     return $c;
   }
   private static function yk_na($a){
     if (!$a) {
       return '';
     }

     $h = explode(',', self::$sz);
     $i = strlen($a);
     $f = 0;
     for ($e = ''; $f < $i;) {
       do {
         $c = $h[self::charCodeAt($a, $f++) & 255];
       } while ($f < $i && -1 == $c);
       if (-1 == $c) {
         break;
       }
       do {
         $b = $h[self::charCodeAt($a, $f++) & 255];
       } while ($f < $i && -1 == $b);
       if (-1 == $b) {
         break;
       }
       $e .= self::fromCharCode($c << 2 | ($b & 48) >> 4);
       do {
         $c = self::charCodeAt($a, $f++) & 255;
         if (61 == $c) {
           return $e;
         }
         $c = $h[$c];
       } while ($f < $i && -1 == $c);
       if (-1 == $c) {
         break;
       }
       $e .= self::fromCharCode(($b & 15) << 4 | ($c & 60) >> 2);
       do {
         $b = self::charCodeAt($a, $f++) & 255;
         if (61 == $b) {
           return $e;
         }
         $b = $h[$b];
       } while ($f < $i && -1 == $b);
       if (-1 == $b) {
         break;
       }
       $e .= self::fromCharCode(($c & 3) << 6 | $b);
     }
     return $e;
   }
   private static function yk_e($a, $c){
     for ($f = 0, $i, $e = '', $h = 0; 256 > $h; $h++) {
       $b[$h] = $h;
     }
     for ($h = 0; 256 > $h; $h++) {
       $f = (($f + $b[$h]) + self::charCodeAt($a, $h % strlen($a))) % 256;
       $i = $b[$h];
       $b[$h] = $b[$f];
       $b[$f] = $i;
     }
     for ($q = ($f = ($h = 0)); $q < strlen($c); $q++) {
       $h = ($h + 1) % 256;
       $f = ($f + $b[$h]) % 256;
       $i = $b[$h];
       $b[$h] = $b[$f];
       $b[$f] = $i;
       $e .= self::fromCharCode(self::charCodeAt($c, $q) ^ $b[($b[$h] + $b[$f]) % 256]);
     }
     return $e;
   }

   private static function fromCharCode($codes){
     if (is_scalar($codes)) {
       $codes = func_get_args();
     }
     $str = '';
     foreach ($codes as $code) {
       $str .= chr($code);
     }
     return $str;
   }
   private static function charCodeAt($str, $index){
     static $charCode = array();
     $key = md5($str);
     $index = $index + 1;
     if (isset($charCode[$key])) {
       return $charCode[$key][$index];
     }
     $charCode[$key] = unpack('C*', $str);
     return $charCode[$key][$index];
   }

   private static function charAt($str, $index = 0){
     return substr($str, $index, 1);
   }

}
