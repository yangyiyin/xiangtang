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
        $item_ids = $this->post_data['item_ids'];
        if (!$item_ids) {
            return result_json(FALSE, '参数错误');
        }
        $iids_arr = explode('_', $item_ids);
        $ret = $this->CartService->del_by_uid_iids($this->uid, $iids_arr);
        if (!$ret->success) {
            result_json(FALSE, $ret->message);
        }
        result_json(TRUE, '删除成功');
    }
}