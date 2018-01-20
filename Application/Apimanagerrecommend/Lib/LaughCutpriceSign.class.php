<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughCutpriceSign extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];

        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);
        if ($page_info['tmp_data']) {
            $page_info['tmp_data'] = json_decode($page_info['tmp_data'], true);
        }

        if (isset($page_info['tmp_data']['time_limit_end'])) {
            if (time() > strtotime($page_info['tmp_data']['time_limit_end'])) {
                return result_json(false, '报名已结束');
            }
        }
        $price = 0;
        foreach ($page_info['tmp_data']['page'] as $_page) {
            if ($_page['type'] == 'cutprice_price') {
                $price = $_page['cutprice_price'];
            }
        }
        if (!$price) {
            return result_json(false, '活动信息异常');
        }

        //存入
        $PageCutpriceService = \Common\Service\PageCutpriceService::get_instance();

        $data = [];
        $data['uid'] = $this->uid;
        $data['page_id'] = $id;
        if ($PageCutpriceService->get_by_uid_page_id($data['uid'], $data['page_id'])) {
            return result_json(false, '您已报名');
        }

        $data['price'] = $price * 100;
        $ret = $PageCutpriceService->add_one($data);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }

        return result_json(TRUE, '报名成功!');
    }

}