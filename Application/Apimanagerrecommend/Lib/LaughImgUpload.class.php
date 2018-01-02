<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;

use Common\Model;
use Common\Service;
use Think\Upload;
class LaughImgUpload extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {
        $img = '';
        $return = $this->uploadPicture();
        if ($return) {

            $img = '/Uploads/' . $return['img']['savepath'] . $return['img']['savename'];
            $img = item_img($img);
        }


        if ($img) {
            result_json(TRUE, '修改成功!', $img);
        } else {
            result_json(false, '网络繁忙,请重试!');
        }
    }

    public function uploadPicture(){

        /* 返回标准数据 */
        $return  = array('status' => 1, 'info' => '上传成功', 'data' => '');

        $Upload = new Upload();
        $return = $Upload->upload();
        return $return;
    }
}