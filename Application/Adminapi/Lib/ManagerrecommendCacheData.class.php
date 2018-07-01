<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;

use Common\Model;
use Common\Service;
use Think\Upload;
class ManagerrecommendCacheData extends BaseSapi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $tmp_data = $this->post_data['tmp_data'];
        if (!$tmp_data) {
            result_json(false, '无数据!');
        }
        $file_name = "pages/tmp/preview.tmp_data";
       // $tmp_data = $this->parse_rpx($tmp_data);
      //  $tmp_data = str_replace("\n",'<br/>',json_encode($tmp_data));
        foreach ($tmp_data['page'] as $k => $_page) {
            if ($_page['type'] == 'text') {
                $tmp_data['page'][$k]['text'] = str_replace("\n","<br/>",$_page['text']);
            }
        }
        file_put_contents($file_name, json_encode($tmp_data));
//        $myfile = fopen($file_name, "w");
//        fwrite($myfile,json_encode($tmp_data));
//        fclose($myfile);

        result_json(TRUE, '上传成功!');
    }


}