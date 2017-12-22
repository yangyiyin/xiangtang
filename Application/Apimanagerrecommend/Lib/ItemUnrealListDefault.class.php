<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class ItemUnrealListDefault extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $ItemService;
    public function init() {
        $this->ItemService = Service\ItemService::get_instance();
    }

    public function excute() {
        $keyword = I('get.keyword');
        $cid = I('get.cid');
        $page = I('get.p', 1);
        $where = [];
        if ($cid) {
            //获取cid下的所有cids
            $CategoryService = Service\CategoryService::get_instance();
            $cids = $CategoryService->get_cids_by_cid($cid);

            $where['cid'] = ['IN', $cids];
        }

        if ($keyword) {
            $where['keyword'] = ['LIKE', '%'.$keyword.'%'];
        }
        $where['is_real'] = 0;
        $where['status'] = ['EQ', \Common\Model\NfItemModel::STATUS_NORAML];
        list($data, $count) = $this->ItemService->get_by_where($where, 'sort asc, id desc', $page);
        $list = $this->convert_data($data);
        $result = new \stdClass();
        $result->list = $list;
        $result->has_more = has_more($count, $page, Service\ItemService::$page_size);
        return result_json(TRUE, '', $result);
    }

    private function convert_data($data) {
        $list = [];
        if ($data) {

            $itemUsertypePricesService = \Common\Service\ItemUsertypePricesService::get_instance();
            $iids = result_to_array($data);
            $prices = $itemUsertypePricesService->get_by_iids($iids);
            $prices_map = result_to_complex_map($prices, 'iid');
            foreach ($data as $key => $_item) {
                $_item['img'] = item_img(get_cover($_item['img'], 'path'));//todo 这种方式后期改掉
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