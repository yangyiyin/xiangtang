<?php
/**
 * Created by newModule.
 * Time: 2017-07-28 19:20:13
 */
namespace Common\Service;
class DepartmentService extends BaseService{
    public static $name = 'Department';

    public function add_one($data, $is_only_create = 0) {
        $FinancialModel = D('Financial' . static::$name);
        $data['gmt_create'] = time();
         if (!$FinancialModel->create($data)) {
            return result(FALSE, $FinancialModel->getError());
         }

         if ($is_only_create) {
             return result(true, 'success');
         }
        if ($FinancialModel->add()) {
            return result(TRUE, '', $FinancialModel->getLastInsID());
        } else {
            echo $FinancialModel->getLastSql();die();
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

    public function get_my_list($uid, $type) {

        $DepartmentUid = D('FinancialDepartmentUid')->where(['uid'=>$uid])->find();
        if ($DepartmentUid) {
            $FinancialModel = D('Financial' . static::$name);
            $where = [];
            $where['id'] = ['EQ', $DepartmentUid['did']];
            $where['type'] = ['EQ', $type];
            $where['deleted'] = ['EQ', static::$NOT_DELETED];
            return $FinancialModel->where($where)->select();
        }
        return [];
    }


    public function get_all_list($type) {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['type'] = ['EQ', $type];
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

    public function get_by_all_names($all_names, $type) {
        if (!$all_names) {
            return [];
        }
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['all_name'] = ['in', $all_names];
        $where['type'] = ['EQ', $type];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }

    public function get_by_id_type($id=0, $type=0) {

        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        if ($id) {
            $where['id'] = ['eq', $id];
        }

        if ($type) {
            $where['type'] = ['EQ', $type];
        }

        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }


    public function get_by_type($type) {
        if (!$type) {
            return [];
        }
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['type'] = ['EQ', $type];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }


    public function get_all() {

        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }


    public function get_sub_type_options($cur_id=0) {
        $options = '';
        foreach (\Common\Model\FinancialDepartmentModel::$SUB_TYPE_MAP as $id => $name) {
            if ($cur_id && $cur_id == $id) {
                $options .= '<option value="'.$id.'" selected="selected">'.$name.'</option>';
            } else {
                $options .= '<option value="'.$id.'">'.$name.'</option>';
            }
        }
        return $options;
    }

    public function sub_type_insurance_property_options($cur_id=0) {
        $options = '';
        foreach (\Common\Model\FinancialDepartmentModel::$SUB_TYPE_insurance_property_MAP as $id => $name) {
            if ($cur_id && $cur_id == $id) {
                $options .= '<option value="'.$id.'" selected="selected">'.$name.'</option>';
            } else {
                $options .= '<option value="'.$id.'">'.$name.'</option>';
            }
        }
        return $options;
    }

}