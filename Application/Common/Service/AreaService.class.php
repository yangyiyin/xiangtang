<?php
/**
 * Created by newModule.
 * Time: 2017-08-02 13:05:15
 */
namespace Common\Service;
class AreaService extends BaseService{
    public static $name = 'Area';

    public function add_one($data, $is_only_create = 0) {
        $FinancialModel = D('Financial' . static::$name);
         if (!$FinancialModel->create($data)) {
            return result(FALSE, $FinancialModel->getError());
         }

         if ($is_only_create) {
            return result(true, 'success');
         }

        if ($FinancialModel->add()) {
            return result(TRUE, '', $FinancialModel->getLastInsID());
        } else {

            return result(FALSE, '网络繁忙~');
        }
    }

    public function get_info_by_id($id) {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['id'] = ['EQ', $id];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->find();
    }


    public function get_all() {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $FinancialModel = D('Financial' . static::$name);

        $where = ['id' => $id];

         if (!$FinancialModel->create($data)) {
            return result(FALSE, $FinancialModel->getError());
         }
        if ($FinancialModel->where($where)->save()) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }


    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return false;
        }
        $FinancialModel = D('Financial' . static::$name);
        $where = ['id' => $id];
        $ret = $FinancialModel->where($where)->save(['deleted'=>static::$DELETED]);
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }


    public function get_by_where($where, $order = 'id desc', $page = 1) {
        $FinancialModel = D('Financial' . static::$name);
        $data = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        $count = $FinancialModel->where($where)->order($order)->count();
        if ($count > 0) {
            $data = $FinancialModel->where($where)->order($order)->page($page . ',' . static::$page_size)->select();
        }
        return [$data, $count];
    }

    public function set_area_options($infos = []){
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        $areas = $FinancialModel->where($where)->select();
        if ($infos) {
            foreach ($infos as $key => $info) {
                $options = '';
                foreach ($areas as $area) {
                    if ($info['Area'] == $area['id']) {
                        $options .= '<option selected="selected" value="'.$area['id'].'">'.$area['name'].'</option>';
                    } else {
                        $options .= '<option value="'.$area['id'].'">'.$area['name'].'</option>';
                    }
                }

                $infos[$key]['area_options'] = $options;

            }
            return $infos;
        } else {
            $options = '';
            foreach ($areas as $area) {
                $options .= '<option value="'.$area['id'].'">'.$area['name'].'</option>';
            }
            return $options;
        }

    }
}