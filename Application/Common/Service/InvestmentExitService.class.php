<?php
/**
 * Created by newModule.
 * Time: 2017-08-02 11:12:29
 */
namespace Common\Service;
class InvestmentExitService extends BaseService{
    public static $name = 'InvestmentExit';

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

    public function get_by_names_time($all_names, $year, $month) {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['year'] = ['EQ', $year];
        $where['month'] = ['EQ', $month];
        $where['all_name'] = ['in', $all_names];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }


    public function get_exit_method_options($infos) {

        if ($infos) {
            foreach ($infos as $k => $info) {
                $options = '';
                foreach (\Common\Model\FinancialInvestmentExitModel::$EXISTS_METHOD_MAP as $key => $name) {
                    if ($info['ExitMethod'] == $key) {
                        $options .= '<option selected="selected" value="'.$key.'">'.$name.'</option>';
                    } else {
                        $options .= '<option value="'.$key.'">'.$name.'</option>';
                    }
                }

                $infos[$k]['exit_method_options'] = $options;

            }
            return $infos;
        } else {
            $options = '';
            foreach (\Common\Model\FinancialInvestmentExitModel::$EXISTS_METHOD_MAP as $key => $name) {
                $options .= '<option value="'.$key.'">'.$name.'</option>';
            }
            return $options;
        }


    }

}