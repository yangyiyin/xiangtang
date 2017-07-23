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
        $sku_id = $this->post_data['sku_id'];

        if (!$iid || !$sku_id || !$num) {
            return result_json(FALSE, '参数错误~');
        }

        //检测库存
        $ItemService = Service\ItemService::get_instance();
        $item = $ItemService->get_info_by_id($iid);
        $ret_item_status = $ItemService->check_status([$iid], [$item]);
        if (!$ret_item_status->success) {
            return result_json(FALSE, $ret_item_status->message);
        }
        $ProductSkuService = Service\ProductSkuService::get_instance();
        $sku = $ProductSkuService->get_info_by_id($sku_id);
        if (!$sku) {
            return result_json(FALSE, '没有该商品~');
        }
        $sku['buy_num'] = $num;
        $sku['item'] = $item;
        $ret = $ProductSkuService->check_stock([$sku]);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }

        $ret = $this->CartService->add_one($this->uid, $iid, $num, $sku_id);
        if (!$ret->success) {
            return result_json(FALSE, $ret->message);
        }
        return result_json(TRUE, '操作成功');
    }
}