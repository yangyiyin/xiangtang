<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class ItemServicePricesService extends BaseService{

    public function add_one($data) {
        $NfItemServicePrices = D('NfItemServicePrices');
        if (!$NfItemServicePrices->create($data)) {
            return result(FALSE, $NfItemServicePrices->getError());
        }

        if ($NfItemServicePrices->add($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfItemServicePrices->getError());
        }
    }

    public function get_info_by_id($id) {
        $NfItemServicePrices = D('NfItemServicePrices');
        return $NfItemServicePrices->where('id = ' . $id)->find();
    }

    public function get_by_iids($iids) {
        $NfItemServicePrices = D('NfItemServicePrices');
        return $NfItemServicePrices->where('iid in (' . join(',', $iids) . ')')->select();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfItemServicePrices = D('NfItemServicePrices');

        if (!$NfItemServicePrices->create($data)) {
            return result(FALSE, $NfItemServicePrices->getError());
        }

        if ($NfItemServicePrices->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfItemServicePrices->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $NfItemServicePrices = D('NfItemServicePrices');
        return $NfItemServicePrices->where('id=' . $id)->delete();
    }

    public function get_prices_map($data) {
        $map = result_to_map($data, 'service_id');
        //var_dump($map);
        $prices_map = [];
        $ServicesService = ServicesService::get_instance();
        list($services, $count) = $ServicesService->get_by_where_all([]);
        foreach ($services as $value) {
            if (isset($map[$value['id']])) {
                $prices_map[$value['id']] = $map[$value['id']]['price'];
            }
        }
        //var_dump($prices_map);die();
        return $prices_map;

    }

    public function get_price_by_service_id($service_id, $prices) {
        $map = result_to_map($prices, 'service_id');
        if (isset($map[$service_id]['price'])) {
            return $map[$service_id]['price'];
        }
        return NULL;
    }

//    public function add_by_iid_price($iid, $pirce) {
//        $NfItemServicePrices = D('NfItemServicePrices');
//
//        //查询记录是否存在
//        if ($NfItemServicePrices->where('iid = ' . $iid)->select()) {
//            return result(FALSE, '已经存在商品不同价格的记录');
//        }
//
//        $data_insert = [];
//        foreach (\Common\Model\NfUserModel::$type_map as $key=>$value) {
//            $data = [];
//            $data['iid'] = $iid;
//            $data['price'] = $pirce;
//            $data['user_type'] = $key;
//            $data_insert[] = $data;
//        }
//       // var_dump($data_insert);die();
//        $ret = $NfItemServicePrices->addALL($data_insert);
//        if ($ret) {
//            return result(TRUE);
//        } else {
//            return result(FALSE, '插入price失败');
//        }
//
//    }

//    public function add_by_pids($pids) {
//        $NfItemServicePrices = D('NfItemServicePrices');
//        $itemService = \Common\Service\ItemService::get_instance();
//        $items = $itemService->get_by_pids($pids);
//        if (!$items) {
//            return result(FALSE, '没有对应的商品');
//        }
//
//        $iids = result_to_array($items, 'id');
//        //查询记录是否存在
//        if ($item_prices = $NfItemServicePrices->where('iid in (' . join(',', $iids) . ')')->select()) {
//            $exists_iids = array_unique(result_to_array($item_prices, 'iid'));
//        }
//        $data_insert = [];
//        foreach ($items as $_item) {
//            foreach (\Common\Model\NfUserModel::$type_map as $key=>$value) {
//                if (isset($exists_iids) && $exists_iids && in_array($_item['id'], $exists_iids)) {
//                    continue;
//                }
//                $data = [];
//                $data['iid'] = $_item['id'];
//                $data['price'] = $_item['price'];
//                $data['user_type'] = $key;
//                $data_insert[] = $data;
//            }
//        }
//
//        $ret = $NfItemServicePrices->addALL($data_insert);
//        if ($ret) {
//            return result(TRUE);
//        } else {
//            return result(FALSE, '插入price失败');
//        }
//    }

    public function update_by_iid_prices($iid, $prices) {
        $NfItemServicePrices = D('NfItemServicePrices');
        foreach ($prices as $key=>$value) {
            $service_id = intval(str_replace('price', '', $key));
            if ($service_id <= 0) {
                continue;
            }
            $count = $NfItemServicePrices->where('iid = '. $iid . ' and service_id = ' . $service_id)->count();
            if ($count) {
                $NfItemServicePrices->where('iid = '. $iid . ' and service_id = ' . $service_id)->save(['price'=>intval($value * 100)]);
            } else {
                $data = [
                    'iid' => $iid,
                    'service_id' => $service_id,
                    'price' => intval($value * 100)
                ];
                $NfItemServicePrices->add($data);
            }

        }
        return result(TRUE);
    }

}