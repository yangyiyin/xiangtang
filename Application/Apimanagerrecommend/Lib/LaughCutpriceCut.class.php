<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughCutpriceCut extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {

        $id = $this->post_data['id'];
        $extra_uid = $this->post_data['extra_uid'];
        $PageService = \Common\Service\PageService::get_instance();
        $page_info = $PageService->get_info_by_id($id);
        if ($page_info['tmp_data']) {
            $page_info['tmp_data'] = json_decode($page_info['tmp_data'], true);
        }

        if (isset($page_info['tmp_data']['time_limit_end'])) {
            if (time() > strtotime($page_info['tmp_data']['time_limit_end'])) {

                return result_json(false, '报名已结束!');
            }
        }
        $price = $max_minus_price = $average_price = 0;
        foreach ($page_info['tmp_data']['page'] as $_page) {
            if ($_page['type'] == 'cutprice_price') {
                $price = $_page['cutprice_price'];
                $max_minus_price = $_page['cutprice_max_minus_price'];
                $average_price = $_page['cutprice_average_price'];
            }
        }
        if (!$price || !$max_minus_price || !$average_price) {

            return result_json(false, '活动信息异常!');
        }

        //存入
        $PageCutpriceService = \Common\Service\PageCutpriceService::get_instance();
        $data = [];
        $data['uid'] = $this->uid;
        $data['page_id'] = $id;
        $data['pid'] = $extra_uid;
        if ($PageCutpriceService->get_by_uid_page_id($data['uid'], $data['page_id'], $data['pid'])) {

            return result_json(false, '您已砍价!');

        }
        $cut_info = $PageCutpriceService->get_by_uid_page_id($extra_uid, $data['page_id']);
        if (!$cut_info) {

            return result_json(false, '砍价信息异常!');
        }

        $left_price = $max_minus_price * 100 - ($price * 100 - $cut_info['price']);
        if ($left_price <= 0) {

            return result_json(false, '该砍价已经到上限!');
        }
        $can_cut_price = $left_price > ($average_price * 1.4 * 100) ? ($average_price * 1.4 * 100) : $left_price;

        $data['cutprice'] = mt_rand($can_cut_price * 0.4, $can_cut_price);
        $ret = $PageCutpriceService->add_one($data);
        if (!$ret->success) {

            return result_json(false, $ret->message);

        }

        $data_up = [];
        $data_up['price'] = $cut_info['price'] - $data['cutprice'];
        $PageCutpriceService->update_by_id($cut_info['id'], $data_up);

        return result_json(TRUE, '成功砍价!');
    }

}