<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:12:23
 */
namespace Common\Service;
class BankBaddebtDisposeService extends BaseService{
    public static $name = 'BankBaddebtDispose';

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

    public function add_batch($data) {
        $FinancialModel = D('Financial' . static::$name);

        if (!$FinancialModel->create($data)) {
            return result(FALSE, json_encode($FinancialModel->getError()));
        }


        if ($FinancialModel->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
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


    public function update_by_year_month_all_name($year, $month, $all_name, $data) {

        if (!$year || !$month || !$all_name) {
            return result(FALSE, '参数错误');
        }

        $FinancialModel = D('Financial' . static::$name);

        $where[] = ['year' => $year];
        $where[] = ['month' => $month];
        $where[] = ['all_name' => $all_name];

        if (!$FinancialModel->create($data)) {
            return result(FALSE, $FinancialModel->getError());
        }
        if ($FinancialModel->where($where)->save()) {
            return result(TRUE);
        } else {
            echo $FinancialModel->getLastSql();
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


    public function get_by_where($where, $order = 'id desc', $page = 1, $is_all=false) {
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


  public function get_by_month_year($year, $month, $all_name) {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['year'] = ['EQ', $year];
        $where['month'] = ['EQ', $month];
        $where['all_name'] = ['EQ', $all_name];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }

    public function get_by_month_year_all_names($year, $month, $all_names, $is_baddbet = false, $extra=[]) {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['year'] = ['EQ', $year];
        $where['month'] = ['EQ', $month];
        if ($all_names) {
            $where['all_name'] = ['IN', $all_names];
        }
        if ($is_baddbet) {
            $where['Pattern'] = ['gt', 2];
        }

        if (isset($extra['status'])) {
            $where['status'] = $extra['status'];
        }
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }
    public function del_by_month_year($year, $month, $all_name) {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['year'] = ['EQ', $year];
        $where['month'] = ['EQ', $month];
        $where['all_name'] = ['EQ', $all_name];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->delete();
    }

    public function get_by_where_all($where) {
        $FinancialModel = D('Financial' . static::$name);
        $data = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }

    public function recover_method_options($infos = []){
        $arr = \Common\Model\FinancialBankBaddebtDisposeModel::$RECOVER_METHOD_MAP;
        if ($infos) {
            foreach ($infos as $key => $info) {
                $options = '';
                foreach ($arr as $k => $v) {
                    if ($info['Recover_Method'] == $k) {
                        $options .= '<option selected="selected" value="'.$k.'">'.$v.'</option>';
                    } else {
                        $options .= '<option value="'.$k.'">'.$v.'</option>';
                    }
                }

                $infos[$key]['recover_method_options'] = $options;

            }
            return $infos;
        } else {
            $options = '';
            foreach ($arr as $k => $v) {
                $options .= '<option value="'.$k.'">'.$v.'</option>';
            }
            return $options;
        }

    }

}