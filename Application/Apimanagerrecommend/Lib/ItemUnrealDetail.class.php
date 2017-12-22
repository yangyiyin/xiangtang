<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class ItemDetail extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $ItemService;
    public function init() {
        $this->ItemService = Service\ItemService::get_instance();
    }

    public function excute() {
        $item_id = I('get.item_id');
        if (!$item_id) {
            result_json(FALSE, '没有商品id');
        }
        $where = [];
        $where['id'] = ['EQ', $item_id];
        $where['status'] = ['EQ', \Common\Model\NfItemModel::STATUS_NORAML];
        $page = 1;
        list($data, $count) = $this->ItemService->get_by_where($where, 'id desc', $page);
        $list = $this->convert_data($data);
        $data = isset($list[0]) ? $list[0] : [];
        return result_json(TRUE, '', $data);
    }

    private function convert_data($data) {
        $list = [];
        if ($data) {


            $UserService = Service\UserService::get_instance();
            $user_info = $this->user_info;
            foreach ($data as $key => $_item) {
                $_item['img'] = item_img(get_cover($_item['img'], 'path'));//todo 这种方式后期改掉

                if ($UserService->is_dealer($user_info['type'])) {
                    $_item['price'] = (int) $_item['min_dealer_price'];
                } elseif ($UserService->is_normal($user_info['type'])) {
                    $_item['price'] = (int) $_item['min_normal_price'];
                }

                $_item['id'] = (int) $_item['id'];
                $_item['pid'] = (int) $_item['pid'];
                $_item['price'] = (int) $_item['price'];
                $_item['content'] = $_item['content'];
                $_item['tips'] = $_item['tips'];
                $list[] = convert_obj($_item, 'id=item_id,pid,title,img,desc,unit_desc,price,content,tips');
            }

        }
        return $list;
    }
}