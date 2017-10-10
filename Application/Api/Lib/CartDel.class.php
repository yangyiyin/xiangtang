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
class CartDel extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $CartService;
    public function init() {
        $this->CartService = Service\CartService::get_instance();
    }

    public function excute() {
        //$item_ids = I('post.item_ids');
        $item_ids = $this->post_data['iids'];
        $sku_ids = $this->post_data['sku_ids'];

        $all_del = $this->post_data['all_del'];

        if (!$item_ids && !$all_del) {
            return result_json(FALSE, '参数错误');
        }

        if ($all_del) {//全部删除
            $ret = $this->CartService->del_by_uid($this->uid);
            if (!$ret->success) {
                result_json(FALSE, $ret->message);
            }
            result_json(TRUE, '删除成功');
        }

        $iids_arr = explode('_', $item_ids);
        $sku_ids_arr = explode('_', $sku_ids);
        if (count($iids_arr) != count($sku_ids_arr)) {
            result_json(FALSE, '参数异常');
        }

        $ret = $this->CartService->del_by_uid_iids_skuids($this->uid, $iids_arr, $sku_ids_arr);
        if (!$ret->success) {
            result_json(FALSE, $ret->message);
        }
        result_json(TRUE, '删除成功');
    }
}