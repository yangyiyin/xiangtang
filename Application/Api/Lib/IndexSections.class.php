<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class IndexSections extends BaseSapi{
    protected $method = parent::API_METHOD_GET;

    public function init() {

    }

    public function excute() {
        $list = [
            ['type' => 'mall', 'name'=>'商城', 'img'=>item_img('/Uploads/Picture/12.png')],
            ['type' => 'volunteer_apply', 'name'=>'志愿者申请', 'img'=>item_img('/Uploads/Picture/13.png')],
            ['type' => 'aid_apply', 'name'=>'残疾人救助申请', 'img'=>item_img('/Uploads/Picture/14.png')],
            ['type' => 'regulation', 'name'=>'公益活动', 'img'=>item_img('/Uploads/Picture/15.png')],
            ['type' => 'disable_family_info', 'name'=>'残疾人家庭状况', 'img'=>item_img('/Uploads/Picture/16.png')],
            ['type' => 'infomation_center', 'name'=>'信息中心', 'img'=>item_img('/Uploads/Picture/17.png')],
        ];
        return result_json(TRUE, '', $list);

    }

}