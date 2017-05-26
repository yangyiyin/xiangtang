<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class ItemUsertypePricesService extends BaseService{

    public function add_one($data) {
        $NfItemUsertypePrices = D('NfItemUsertypePrices');
        if (!$NfItemUsertypePrices->create($data)) {
            return result(FALSE, $NfItemUsertypePrices->getError());
        }

        if ($NfItemUsertypePrices->add($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfItemUsertypePrices->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfItemUsertypePrices = D('NfItemUsertypePrices');
        return $NfItemUsertypePrices->where('id = ' . $id)->find();
    }

    public function get_by_iids($iids) {
        $NfItemUsertypePrices = D('NfItemUsertypePrices');
        return $NfItemUsertypePrices->where('iid in (' . join(',', $iids) . ')')->select();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfItemUsertypePrices = D('NfItemUsertypePrices');

        if (!$NfItemUsertypePrices->create($data)) {
            return result(FALSE, $NfItemUsertypePrices->getError());
        }

        if ($NfItemUsertypePrices->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfItemUsertypePrices->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfItemUsertypePrices = D('NfItemUsertypePrices');
        return $NfItemUsertypePrices->where('id=' . $id)->delete();
    }

    public function get_prices_map($data) {
        $map = result_to_map($data, 'user_type');
        //var_dump($map);
        $prices_map = [];

        foreach (\Common\Model\NfUserModel::$type_map as $key=>$value) {
            if (isset($map[$key])) {
                $prices_map[$value] = $map[$key]['price'];
            }
        }
        //var_dump($prices_map);die();
        return $prices_map;

    }

    public function get_price_by_type($type, $prices) {
        $map = result_to_map($prices, 'user_type');
        if (isset($map[$type]['price'])) {
            return $map[$type]['price'];
        }
        return NULL;
    }

    public function add_by_iid_price($iid, $pirce) {
        $NfItemUsertypePrices = D('NfItemUsertypePrices');

        //查询记录是否存在
        if ($NfItemUsertypePrices->where('iid = ' . $iid)->select()) {
            return result(FALSE, '已经存在商品不同价格的记录');
        }

        $data_insert = [];
        foreach (\Common\Model\NfUserModel::$type_map as $key=>$value) {
            $data = [];
            $data['iid'] = $iid;
            $data['price'] = $pirce;
            $data['user_type'] = $key;
            $data_insert[] = $data;
        }
       // var_dump($data_insert);die();
        $ret = $NfItemUsertypePrices->addALL($data_insert);
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, '插入price失败');
        }

    }

    public function add_by_pids($pids) {
        $NfItemUsertypePrices = D('NfItemUsertypePrices');
        $itemService = \Common\Service\ItemService::get_instance();
        $items = $itemService->get_by_pids($pids);
        if (!$items) {
            return result(FALSE, '没有对应的商品');
        }

        $iids = result_to_array($items, 'id');
        //查询记录是否存在
        if ($item_prices = $NfItemUsertypePrices->where('iid in (' . join(',', $iids) . ')')->select()) {
            $exists_iids = array_unique(result_to_array($item_prices, 'iid'));
        }
        $data_insert = [];
        foreach ($items as $_item) {
            foreach (\Common\Model\NfUserModel::$type_map as $key=>$value) {
                if (isset($exists_iids) && $exists_iids && in_array($_item['id'], $exists_iids)) {
                    continue;
                }
                $data = [];
                $data['iid'] = $_item['id'];
                $data['price'] = $_item['price'];
                $data['user_type'] = $key;
                $data_insert[] = $data;
            }
        }

        $ret = $NfItemUsertypePrices->addALL($data_insert);
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, '插入price失败');
        }
    }

    public function update_by_iid_prices($iid, $prices) {
        $NfItemUsertypePrices = D('NfItemUsertypePrices');
        foreach (\Common\Model\NfUserModel::$type_map as $key=>$value) {
            if (isset($prices[$value])) {
                $NfItemUsertypePrices->where('iid = '. $iid . ' and user_type = ' . $key)->save(['price'=>intval($prices[$value] * 100)]);
            }
        }
        return result(TRUE);
    }

}