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
class ImgUpload extends BaseSapi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {
        $img = '';
        $return = $this->uploadPicture();
        if ($return) {

            $img = $return[0];
            $img = item_img($img);
        }


        if ($img) {
            result_json(TRUE, '上传成功!', $img);
        } else {
            result_json(false, '网络繁忙,请重试!');
        }
    }

    public function uploadPicture(){

        /* 返回标准数据 */
//        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');
//
//        $Upload = new Upload();
//        $return = $Upload->upload();
//        return $return;

        $files = [];
        if ($_FILES) {
            foreach ($_FILES as $key => $file) {
                $files[$key] = new \CURLFile(realpath($file['tmp_name']));
            }
        }
        $ret = curl_post_form('http://api.88plus.net/index.php/waibao/common/qiniu_upload?bucket=onepixel-pub', $files);
        $ret = json_decode($ret, true);
        if ($ret && isset($ret['code']) && $ret['code'] == 100) {
            return $ret['data'];
        }

        return false;

    }
}