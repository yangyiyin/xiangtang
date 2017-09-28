<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class SmsService extends BaseService{
    public static $name = 'Sms';

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


    public function get_by_where($where, $order = 'id desc', $page = 1, $is_all = '') {
        $FinancialModel = D('Financial' . static::$name);
        $data = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        $page_size = static::$page_size;
        $count = $FinancialModel->where($where)->order($order)->count();
        if ($count > 0) {
            if ($is_all) {
                $data = $FinancialModel->where($where)->order($order)->select();

            } else {
                $data = $FinancialModel->where($where)->order($order)->page($page . ',' . $page_size)->select();

            }
        }
        return [$data, $count];
    }


    public function get_by_month_year($year, $month) {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['year'] = ['EQ', $year];
        $where['month'] = ['EQ', $month];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }


    public function get_by_where_all($where) {
        $FinancialModel = D('Financial' . static::$name);
        $data = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }

    public function add_count($year, $month, $phones, $content) {
        if ($phones) {
            $FinancialModel = D('Financial' . static::$name);
            $where = [];
            $where['year'] = ['EQ', $year];
            $where['month'] = ['EQ', $month];
            $where['phone'] = ['in', $phones];
            $where['deleted'] = ['EQ', static::$NOT_DELETED];
            $data = $FinancialModel->where($where)->select();
            $log_phones = result_to_array($data, 'phone');
            $new_phones = array_diff($phones, $log_phones);
            if ($new_phones) {
                $add_data = [];
                foreach ($new_phones as $_phone) {
                    $add_data[] = [
                        'year' => $year,
                        'month' => $month,
                        'phone' => $_phone,
                        'content' => $content
                    ];
                }
                $FinancialModel->addAll($add_data);
            }

           if ($log_phones) {
               $where = [];
               $where['phone'] = ['in', $log_phones];
               $FinancialModel->where($where)->setInc('count',1);
           }

        }
    }

}