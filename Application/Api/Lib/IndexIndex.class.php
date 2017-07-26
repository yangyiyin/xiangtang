<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class IndexIndex extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $ItemService;
    public function init() {
        $this->ItemService = Service\ItemService::get_instance();
    }

    public function excute() {

        $result = new \stdClass();
        //实物商品
        $where = [];
        $where['is_real'] = 1;
        $where['status'] = ['EQ', \Common\Model\NfItemModel::STATUS_NORAML];
        list($data1, $count) = $this->ItemService->get_by_where($where, 'sort asc, id desc');
        $result->list[] = ['items' => $this->convert_data($data1), 'title'=>'商城'];
        //农家乐
        $where = [];
        $where['is_real'] = 0;
        $where['cid'] = 19;
        $where['status'] = ['EQ', \Common\Model\NfItemModel::STATUS_NORAML];
        list($data2, $count) = $this->ItemService->get_by_where($where, 'sort asc, id desc');
        $result->list[] = ['items' => $this->convert_data($data2), 'title'=>'农家乐'];
        //旅游
        $where = [];
        $where['is_real'] = 0;
        $where['cid'] = 2;
        $where['status'] = ['EQ', \Common\Model\NfItemModel::STATUS_NORAML];
        list($data3, $count) = $this->ItemService->get_by_where($where, 'sort asc, id desc');
        $result->list[] = ['items' => $this->convert_data($data2), 'title'=>'旅游'];

        return result_json(TRUE, '', $result);
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
                $_item['sold_num'] = (int) $_item['sold_num'];
                $list[] = convert_obj($_item, 'id=item_id,pid,title,img,desc,unit_desc,price,sold_num');
            }

        }
        return $list;
    }

}