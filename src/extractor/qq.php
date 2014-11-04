<?php
#http://www.bobobus.com/tengxunshipinjiexijiekou.html
#http://vv.video.qq.com/geturl?vid=v00149uf4ir&otype=json普通
#http://vv.video.qq.com/getinfo?vids=v00149uf4ir&otype=json&charge=0&defaultfmt=shd高清
class QQ implements Extractor
{
	public function extractUrl($url)
	{
		$vid = $this->_getVid($url);
		//$commonUrl = 'http://vv.video.qq.com/geturl?otype=json&vid=' . $vid;
		$gaoqingUrl = 'http://vv.video.qq.com/getinfo?otype=json&charge=0&defaultfmt=hd&vids=' . $vid;
		$content = Helper::getHtml($gaoqingUrl);
		var_dump($content);
	}

	private function _getVid($url)
	{
		$vid = 0;
		preg_match('%[.]?\?vid=([A-Za-z0-9]+)%', $url, $match);
		if (isset($match[1])) {
			$vid = $match[1];
		} else {
			$html=Helper::getHtml($url);
			preg_match('%rel=\"canonical\" href=\"http://v.qq.com/[\S]*\/([A-Za-z0-9]*)\.html%', $html, $match);
			if (isset($match[1])) {
				$vid = $match[1];	
			}
		}
		return $vid;
	}
}