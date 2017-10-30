<?php
/**
 * Created by newModule.
 * Time: 2017-08-03 12:47:42
 */
namespace Common\Service;
class LoanService extends BaseService{
    public static $name = 'Loan';

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


  public function get_by_month_year($year, $month, $all_name) {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['year'] = ['EQ', $year];
        $where['month'] = ['EQ', $month];
        $where['all_name'] = ['EQ', $all_name];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->find();
    }

    public function get_by_where_all($where) {
        $FinancialModel = D('Financial' . static::$name);
        $data = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }

    public function get_this_year_data($year, $month, $all_name) {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['year'] = ['EQ', $year];
        $where['month'] = ['elt', $month];
        $where['all_name'] = ['EQ', $all_name];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }


    public function get_all_history_data($year, $month, $all_name) {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['year'] = ['EQ', $year];
        $where['month'] = ['elt', $month];
        $where['all_name'] = ['EQ', $all_name];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        $a = $FinancialModel->where($where)->select();
        $where = [];
        $where['year'] = ['lt', $year];
        $where['all_name'] = ['EQ', $all_name];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        $b = $FinancialModel->where($where)->select();
        if (!$a) {
            return $b;
        }

        if (!$b) {
            return $a;
        }

        return array_merge($a, $b);
    }


}