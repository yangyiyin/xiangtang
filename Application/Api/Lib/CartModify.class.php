<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Model;
use Common\Service;
class CartModify extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $CartService;
    public function init() {
        $this->CartService = Service\CartService::get_instance();
    }

    public function excute() {
        $iid = $this->post_data['item_id'];
        $num = $this->post_data['num'];

        if (!$iid || !$num) {
            return result_json(FALSE, '参数错误~');
        }

        //检测库存
        $ItemService = Service\ItemService::get_instance();
        $item = $ItemService->get_info_by_id($iid);
        if (!$item) {
            return result_json(FALSE, '没有该商品~');
        }
        $item['num'] = $num;
        $ret = $ItemService->check_status_stock([$item]);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }

        $ret = $this->CartService->add_one($this->uid, $iid, $num);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        return result_json(TRUE, '操作成功');
    }
}