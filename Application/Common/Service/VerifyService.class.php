<?php
/**
 * Created by newModule.
 * Time: 2017-08-04 09:14:38
 */
namespace Common\Service;
class VerifyService extends BaseService{
    public static $name = 'Verify';

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


  public function get_info($year, $month, $all_name, $type = FALSE) {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];
        $where['year'] = ['EQ', $year];
        $where['month'] = ['EQ', $month];
        $where['all_name'] = ['EQ', $all_name];
        if ($type !== FALSE) {
            $where['type'] = ['EQ', $type];
        }

        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->find();
    }


    public function get_infos($all_name, $type = FALSE) {
        $FinancialModel = D('Financial' . static::$name);
        $where = [];

        $where['all_name'] = ['EQ', $all_name];
        if ($type !== FALSE) {
            $where['type'] = ['EQ', $type];
        }

        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }

    public function get_by_where_all($where) {
        $FinancialModel = D('Financial' . static::$name);
        $data = [];
        $where['deleted'] = ['EQ', static::$NOT_DELETED];
        return $FinancialModel->where($where)->select();
    }

    public function get_type($type) {
        $verify_type = 0;
        switch ($type) {
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceProperty:
                $verify_type = \Common\Model\FinancialVerifyModel::TYPE_FinancialInsuranceProperty;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceLife:
                $verify_type = \Common\Model\FinancialVerifyModel::TYPE_FinancialInsuranceLife;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceMutual:
                $verify_type = \Common\Model\FinancialVerifyModel::TYPE_FinancialInsuranceMutual;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialVouch:
                $verify_type = \Common\Model\FinancialVerifyModel::TYPE_FinancialVouch;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment:
                $verify_type = \Common\Model\FinancialVerifyModel::TYPE_FinancialInvestment;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager:
                $verify_type = \Common\Model\FinancialVerifyModel::TYPE_FinancialInvestmentManager;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialFutures:
                $verify_type = \Common\Model\FinancialVerifyModel::TYPE_FinancialFutures;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialLease:
                $verify_type = \Common\Model\FinancialVerifyModel::TYPE_FinancialLease;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialLoan:
                $verify_type = \Common\Model\FinancialVerifyModel::TYPE_FinancialLoan;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialSecurities:
                $verify_type = \Common\Model\FinancialVerifyModel::TYPE_FinancialSecurities;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialTransferFunds:
                $verify_type = \Common\Model\FinancialVerifyModel::TYPE_FinancialTransferFunds;
                break;

        }
        return $verify_type;
    }

    public function is_ok_direct($type) {
        switch ($type) {
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceProperty:
                return false;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceLife:
                return false;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInsuranceMutual:
                return true;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialVouch:
                return true;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestment:
                return true;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialInvestmentManager:
                return true;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialFutures:
                return true;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialLease:
                return true;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialLoan:
                return true;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialSecurities:
                return true;
                break;
            case \Common\Model\FinancialDepartmentModel::TYPE_FinancialTransferFunds:
                return true;
                break;

        }
        return false;
    }


}