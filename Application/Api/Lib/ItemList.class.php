<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class ItemList extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $ItemService;
    public function init() {
        $this->ItemService = Service\ItemService::get_instance();
    }

    public function excute() {
        $keyword = I('get.keyword');
        $cid = I('get.cid');
        $brand_id = I('get.brand_id');
        $page = I('get.p', 1);
        $is_love_product = I('get.is_love_product', 0);
        $where = [];
        if ($cid) {
            //获取cid下的所有cids
            $CategoryService = Service\CategoryService::get_instance();
            $cids = $CategoryService->get_cids_by_cid($cid);

            $where['cid'] = ['IN', $cids];
        }

        if ($brand_id) {
            $where['brand_id'] = ['eq', $brand_id];
        }

        if ($keyword) {
            $where['keyword'] = ['LIKE', '%'.$keyword.'%'];
        }

        if ($this->from == self::FROM_SERVICE) {
            $where['platform'] = ['in', [self::FROM_SERVICE, self::FROM_ALL]];
        } elseif($this->from == self::FROM_RETAIL) {
            $where['platform'] =  ['in', [self::FROM_RETAIL, self::FROM_ALL]];
        } else {
            $where['platform'] =  ['eq', self::FROM_ALL];
        }
        if ($is_love_product) {
            $where['attr'] =  \Common\Model\NfProductModel::ATTR_LOVE;
        } else {
            $where['attr'] =  \Common\Model\NfProductModel::ATTR_NORMAL;
        }
        $where['is_real'] = 1;
        $where['status'] = ['EQ', \Common\Model\NfItemModel::STATUS_NORAML];
        $order_by = 'sort asc, id desc';
        $order = I('get.order');
        $sort = I('get.sort');
        if ($order && $sort) {
            $order_by = $order . ' ' . $sort;
        }
        list($data, $count) = $this->ItemService->get_by_where($where, $order_by, $page);
        $list = $this->convert_data($data);
        $result = new \stdClass();
        $result->list = $list;
        $result->has_more = has_more($count, $page, Service\ItemService::$page_size);
        return result_json(TRUE, '', $result);
    }

    private function convert_data($data) {
        $list = [];
        if ($data) {

            $UserService = Service\UserService::get_instance();
            $user_info = $this->user_info;

            //优惠(限时抢购)
            $iids = result_to_array($data);
            $ItemTimelimitActivityService = \Common\Service\ItemTimelimitActivityService::get_instance();
            $limit_activities = $ItemTimelimitActivityService->get_by_iids($iids);
            $limit_activities_map = result_to_complex_map($limit_activities, 'iid');


            foreach ($data as $key => $_item) {
                $_item['img'] = item_img(get_cover($_item['img'], 'path'));//todo 这种方式后期改掉
                if ($UserService->is_dealer($user_info['type'])) {
                    $_item['price'] = (int) $_item['min_dealer_price'];
                    $_item['show_price'] = (int) $_item['min_normal_price'];
                    $_item['pay_price'] = (int) $_item['min_dealer_price'];
                } elseif ($UserService->is_normal($user_info['type'])) {
                    $_item['price'] = (int) $_item['min_normal_price'];
                    $_item['show_price'] = (int) $_item['min_normal_price'];
                    $_item['pay_price'] = (int) $_item['min_normal_price'];
                }
                //优惠(限时抢购)
                if (isset($limit_activities_map[$_item['id']])) {
                    $price = $ItemTimelimitActivityService->get_price_by_info($limit_activities_map[$_item['id']]);
                    if ($price) {
                        $_item['price'] = $_item['pay_price'] = $_item['show_price'] = $price;
                    }
                }
                $_item['id'] = (int) $_item['id'];
                $_item['pid'] = (int) $_item['pid'];
                $_item['price'] = (int) $_item['price'];
                $_item['sold_num'] = (int) $_item['sold_num'];
//                $_item['normal_price'] = (int) $_item['normal_price'];
//                $_item['dealer_price'] = (int) $_item['dealer_price'];
                $list[] = convert_obj($_item, 'id=item_id,pid,title,img,desc,unit_desc,price,sold_num,show_price,pay_price');
            }

        }
        return $list;
    }
}