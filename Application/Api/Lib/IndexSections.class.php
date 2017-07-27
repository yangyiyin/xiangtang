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
            ['type' => 'wedding', 'name'=>'婚庆礼仪', 'img'=>item_img('/Uploads/Picture/13.png')],
            ['type' => 'farm_happy', 'name'=>'农家乐', 'img'=>item_img('/Uploads/Picture/14.png')],
            ['type' => 'farm_goods', 'name'=>'农资产品', 'img'=>item_img('/Uploads/Picture/15.png')],
            ['type' => 'travel', 'name'=>'旅游', 'img'=>item_img('/Uploads/Picture/16.png')],
            ['type' => 'needs', 'name'=>'生活服务', 'img'=>item_img('/Uploads/Picture/17.png')],
        ];
        return result_json(TRUE, '', $list);

    }

}