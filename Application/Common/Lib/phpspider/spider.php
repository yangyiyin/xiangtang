<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 18/1/31
 * Time: 上午8:54
 */
//http://www.jokeji.cn/list.htm
namespace Common\Lib;
class Spider {


    public $url = 'http://www.jokeji.cn/list.htm';
    public $content_arr = [];
    public $regex = '';
    public function __construct() {

    }

    public function get_content() {
        $content = file_get_contents($this->url);
        $regex = '/\/jokehtml\/[a-z]*\/[0-9]*\.htm/';

        $matches = array();

        if(preg_match_all($regex, $content, $matches)){
            if (isset($matches[0])){
                $matches[0] = array_slice($matches[0],0,2);
                foreach ($matches[0] as $k => $link) {
                    $content_detail = file_get_contents('http://www.jokeji.cn'.$link);
                   // var_dump($content_detail);
                    $regex_detail = '/<span id="text110">(<p>[.\s\S]*<\/p>)<\/span>[.\s\S]*www\.jokeji\.cn<\/font>/i';
                    $matches_detail = [];
                    preg_match_all($regex_detail, $content_detail, $matches_detail);
                    if (isset($matches_detail[1][0])) {
                        $regex_detail_2 = '/<p>(.*)<\/p>/i';
                        $matches_detail_2 =[];
                        preg_match_all($regex_detail_2, $matches_detail[1][0], $matches_detail_2);
                        if (isset($matches_detail_2[1])) {
                            $this->content_arr = array_merge($this->content_arr,$matches_detail_2[1]);
//                            $file_content = join('kkkkkk',$matches_detail_2[1]);
//                            $file_content = str_replace('<BR>','',$file_content);
//                            file_put_contents('./log/'.$k.time(), $file_content);
                        }
                    }

                 //   break;
                }
            }
        //    var_dump($links);
        }
       // var_dump($content);$matches[1]
        foreach ($this->content_arr as $k => $v) {
            $this->content_arr[$k] = preg_replace('/^\d../','',$v);
            $this->content_arr[$k] = str_replace('<BR>','',$this->content_arr[$k]);
        }

        return  join('kkkkkk',$this->content_arr);
    }



}

//$Spider = new Spider();
//$Spider->get_content();
//var_dump($Spider->content_arr);
//$file_content = join('kkkkkk',$Spider->content_arr);
//
//file_put_contents('./log/'.time(), $file_content);