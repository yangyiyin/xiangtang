<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午5:17
 */
namespace Adminapi\Controller;
class ImgController extends BaseController {
    public function upload() {
        $this->excute_api('Adminapi\Lib\ImgUpload');
    }





}