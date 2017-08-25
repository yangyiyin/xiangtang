<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class PayService extends BaseService{

    const AlipayPubKey = '';
    const AlipayPriKey = 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDXzV3i2FEHM2hwE0SoMTM+I5oCHwkpkS7s3oP4IYZiG74x/M0OyR9n6lvIShhZfhxGB7Cgw1dcGqssoR21//QwVOxOCIlwfLgUSFqtxkdduyeeyq0vWlbepiX2Kunfo5yc6jlDC9ArZIl5dBywpNfRDUcqVR6qf1VEnJEROaGkXOanSNFRhk/BYIY08sX50rPKnOSeTJpwcPvC+hqvxSywAIz9o/1BNoaI7Vt7vWBfSyg0lQuupaDWidA9m+JwwLDLW1fzOJY0GLJulIDPY/dQZtuJGDcpAs07VianaY3AMImvIkSSS88q4cmiuhhdatXYfcPRB5hSxqOePkUYSqlFAgMBAAECggEBALbUw0pBQsT7BOyPJofoxyVKPFy3tqeE3WDJVL2Qia3tG0J2j7SLKRR9Na2HOQH5GjMqqLZLSWQ7I/L3vZhIdbXM/TdPfEHVKI/mjQzVA/mhanvZ8sg/nYGc+mSJDwPuNe2rc0AebguVbfN8MCJoRoBS44w1+Xkx0CgFDdKuJQh+LZkN4btrTzDuEEAVXyNTfxfzDtj/yPmAFw/sUOK17lQEOyQWz82PMxKKJRHvDWB+l8ttGffgWUdMmX5C8KvMvuF8xbFc48U9w5kiK5cFQP3xAno0e5tNzUB2DpTMqGP9aufXFTTb9913HEDG1zZd7nl36YyjvRtuayXExtlHezECgYEA/Dp3obkRLrvP831jhrPsa+MhaMwqTeIJctRjlOPa9yT6jYTbBWEP7vj/4WLwGsg4y7r7Bb55ujlvwMUOFE88cCrkhXBfRxoklVnUa3iUs//D5HIhJkZo7bmYzSlJODlqiL13bct7FcluZOrR9hrBJA3q8GU+NKpchKl+CPXIHkMCgYEA2wd1rhFqu90YnijvuSyx6Voqi5IIw/vbCZrmHdv3HTVdYdxm++A6p4/wwvG9eEWqsdq3MVjlR35eM6RQ4E11YB3DkLLTA88y+umIDUF8eYCwPemVPRx3kUgloLRQVZjk4ajnETiZmKN/e9iEaliyRDDAlg91LEKwIpae8YHPVdcCgYAMlcY+VYOb91cK6Si8IeIcB/s0xWse87ZQ+nP1i+DzD+9IfGaJQCyRWe86ibs9OtxKngvEX5qnRhJjZfGr5cA4QIuidNbsL0u///lvW1bgHFTj4yXwSPcXVXlgUz0KByNfq0R4P/zmO2S8uFK9mtwkNmWQlRyjeBShsetN+yV/DQKBgEMPAJ+fo38LDTt3KxYVsg7Q1U/QETD3zjMdorCnpPvjV8jbcwhQuYSN1FfLLYhCRCWZ4haQsfn2nZ7QAxb5gCNCWZrWtTZoXKJnl4j/cL2+gbci6ddA/PaVETgnnKToX8MbNEuYeaY7AJAJVVk9+K5aAsyuxOAdNtIQLW+hn7oXAoGBAKsNNz2v3JydkmUge3xxWUqEZYI5efPYYGThxPIa5VnInOQ3Av4hRX84W/aCs4S+I85o5eZD4esnkfA9O/cTRZZ5TLqq49awQlurbmofKIC1bQWUrnAImyAzVuhOCZ5pR8m2bMwLX+61ALiwGQddH/U6AcpB27srnACbW9abwZ/X';
    const AlipayAppId = '2017081508202701';
    const AlipayAppIdTest = '2016080600178402';
    const AlipayPubKeyTest = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsiYC6F3Qps+MxnWumT9JvzDiFbwa5d8WrMdwKdcD27TQddZVuxddSpdBf6x7KwbHsD1lxgowkEONqukUPJw6pGjJd40Zsa8XoeORFGLsH4cWSZSO6moWZ+1F+xkuexurtv+4UIYaE5aHdfzzP0f+nOAVVkkzAl+kma8Z5i0c1enblUlq6qclDEFNKj1oJNS3rK/+nUKb+DD2qKz8dv/bMm4uit/eJxH4SsiO06CybUw+B7HHp3yi8pb/RKPtO28rXNbFYzFeNrwfYss6SM5VyI/kbSZ0uM4JLOA9nAWoxgaA40amftjUVBDC0LvLacO8pXeAQceT4finYWFedJC2dQIDAQAB';
    public function add_one($data) {
        $NfPay = D('NfPay');
        if ($NfPay->add($data)) {

            return result(TRUE, '', $NfPay->getLastInsID());
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }

    public function get_info_by_id($id) {
        $NfPay = D('NfPay');
        return $NfPay->where('id = ' . $id)->find();
    }

    public function get_info_by_no($no) {
        $NfPay = D('NfPay');
        return $NfPay->where('pay_no = "' . $no . '"')->find();
    }

    public function get_info_by_oids($oids) {
        $NfPay = D('NfPay');
        $where = [];
        $where['order_ids'] = join(',', $oids);
        return $NfPay->where($where)->find();
    }

    public function update_by_id($id, $data) {

        if (!$id) {
            return result(FALSE, 'id不能为空');
        }

        $NfPay = D('NfPay');

        if ($NfPay->where('id=' . $id)->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfPay->getError());
        }
    }

    public function update_by_ids($ids, $data) {
        if (!check_num_ids($ids)) {
            return result(FALSE, 'ids不能为空');
        }
        $NfPay = D('NfPay');
        $ret = $NfPay->where('id in ('. join(',', $ids) .')')->save($data);

        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, $NfPay->getError());
        }
    }

    public function del_by_id($id) {
        if (!check_num_ids([$id])) {
            return result(FALSE, 'id不合法~');
        }
        $NfPay = D('NfPay');
        $ret = $NfPay->where('id=' . $id)->save(['status'=>\Common\Model\NfPayModel::STATUS_DELETE]);
        if ($ret) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }


    public function add_batch($data) {
        $NfPay = D('NfPay');
        if ($NfPay->addAll($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '批量插入失败');
        }
    }

    public function get_pay_no($uid) {
        return 'T'.$uid.getMillisecond().mt_rand(0,9).mt_rand(0,9);
    }

//    public function create_by_order($order) {
//        if (!$order) {
//            return result(FALSE, '订单不存在~');
//        }
//
//        if ($pay = $this->get_info_by_oid($order['id'])) {
//            if ($pay['status'] != \Common\Model\NfPayModel::STATUS_SUBMIT) {
//                return result(FALSE, '该订单无法支付~');
//            }
//            return result(TRUE, '订单已创建支付', $pay);
//        }
//
//        $data = [];
//        $data['pay_no'] = $this->get_pay_no($order['uid']);
//        $data['pay_agent'] = \Common\Model\NfPayModel::PAY_AGENT_ALIPAY;
//        $data['uid'] = $order['uid'];
//        $data['order_id'] = $order['id'];
//        $data['sum'] = $order['sum'];
//        $data['create_time'] = current_date();
//
//        $ret = $this->add_one($data);
//
//        if (!$ret->success) {
//            return result(FALSE, $ret->message);
//        }
//        $data['id'] = $ret->data;
//
//        return result(TRUE, '创建成功', $data);
//
//    }

    public function create_by_orders($orders) {
        if (!$orders) {
            return result(FALSE, '订单不存在~');
        }

        if (!$this->check_orders_same_uid($orders)) {
            return result(FALSE, '订单异常~');
        }

        $order_ids = result_to_array($orders);
        if ($pay = $this->get_info_by_oids($order_ids)) {
            if ($pay['status'] != \Common\Model\NfPayModel::STATUS_SUBMIT) {
                return result(FALSE, '该订单无法支付~');
            }
            return result(TRUE, '订单已创建支付', $pay);
        }

        $sum = 0;
        foreach ($orders as $order) {
            $sum += $order['sum'];
        }

        //检测优惠
        sort($order_ids);
        $OrderBenefitService = \Common\Service\OrderBenefitService::get_instance();
        $benefit = $OrderBenefitService->get_info_by_oids(join(',', $order_ids));
        if ($benefit) {
            if ($benefit['type'] == \Common\Model\NfOrderBenefitModel::TYPE_ACCOUNT) {

                $AccountService = \Common\Service\AccountService::get_instance();
                $ret = $AccountService->check_is_available($this->uid, $benefit['rule']);
                if (!$ret->success) {
                    return result_json(FALSE, $ret->message);
                }

                $sum -=  $benefit['rule'];
            }
        }
        if ($sum < 0) {
            return result(FALSE, '该订单支付异常~');
        }

        if ($sum == 0) {//完全用账户支付
            $data = [];
            $data['pay_no'] = $this->get_pay_no($orders[0]['uid']);
            $data['pay_agent'] = \Common\Model\NfPayModel::PAY_AGENT_ACCOUNT;
            $data['uid'] = $orders[0]['uid'];
            $data['order_ids'] = join(',', $order_ids);
            $data['sum'] = $benefit['rule'];
            $data['create_time'] = current_date();
            $data['status'] = \Common\Model\NfPayModel::STATUS_COMPLETE;
            $ret = $this->add_one($data);

            if (!$ret->success) {
                return result(FALSE, $ret->message);
            }
            $data['id'] = $ret->data;

            //更新订单
            $AccountLogService = \Common\Service\AccountLogService::get_instance();
            $AccountService = \Common\Service\AccountService::get_instance();
            //更新订单状态
            //获取加盟商的uids
            $MemberService = \Common\Service\MemberService::get_instance();
            $franchisee_uids = $MemberService->get_franchisee_uids();
            $OrderService = \Common\Service\OrderService::get_instance();
            $UserService = \Common\Service\UserService::get_instance();
            foreach ($order_ids as $order_id) {

                $ret = $OrderService->is_available_payed($order_id, $data['uid']);
                if (!$ret->success) {
                    return result(FALSE, '订单不可支付');
                }
                $order = $ret->data;
                $ret = $OrderService->payed($order);
                if (!$ret->success) {
                    return result(FALSE, '订单支付失败');
                }
                //财务记录
                $account_data = [];
                if (in_array($order['seller_uid'], $franchisee_uids)) {
                    $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_FRANCHISEE_ADD;
                } else {
                    $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_PLATFORM_ADD;
                }
                $account_data['sum'] = $order['sum'];
                $account_data['oid'] = $order_id;
                $account_data['uid'] = $order['seller_uid'];
                $account_data['pay_no'] = $data_notify['pay_no'];
                $AccountLogService->add_one($account_data);

                if ($order['inviter_id']) {
                    $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_INVITER_ADD;
                    //$account_data['sum'] = intval($order['sum'] * C('INVITER_RATE'));
                    $account_data['sum'] = $order['dealer_profit'];
                    $account_data['oid'] = $order_id;
                    $account_data['uid'] = $order['inviter_id'];
                    $account_data['pay_no'] = $data_notify['pay_no'];
                    $AccountLogService->add_one($account_data);
                    $AccountService->add_account($order['inviter_id'], $order['dealer_profit']);
                }

                if ($UserService->is_dealer($order['uid'])) {
                    $account_data['type'] = \Common\Model\NfAccountLogModel::TYPE_DEALER_ADD;
                    //$account_data['sum'] = intval($order['sum'] * C('INVITER_RATE'));
                    $account_data['sum'] = $order['dealer_profit'];
                    $account_data['oid'] = $order_id;
                    $account_data['uid'] = $order['uid'];
                    $account_data['pay_no'] = $data_notify['pay_no'];
                    $AccountLogService->add_one($account_data);
                    $AccountService->add_account($order['uid'], $order['dealer_profit']);
                }
            }



            return result(TRUE, 'all_by_account', $data);
        }

        $data = [];
        $data['pay_no'] = $this->get_pay_no($orders[0]['uid']);
        $data['pay_agent'] = \Common\Model\NfPayModel::PAY_AGENT_ALIPAY;
        $data['uid'] = $orders[0]['uid'];
        $data['order_ids'] = join(',', $order_ids);
        $data['sum'] = $sum;
        $data['create_time'] = current_date();

        $ret = $this->add_one($data);

        if (!$ret->success) {
            return result(FALSE, $ret->message);
        }
        $data['id'] = $ret->data;

        return result(TRUE, '创建成功', $data);

    }

    public function is_available_complete($no) {
        $pay = $this->get_info_by_no($no);
        if (!$pay) {
            return FALSE;
        }
        if ($pay['status'] != \Common\Model\NfPayModel::STATUS_SUBMIT) {
            return FALSE;
        }
        return $pay;

    }

    public function complete($no) {
        if (!$no) {
            return result(FALSE, '单号不能为空');
        }

        $NfPay = D('NfPay');
        $data = ['status' => \Common\Model\NfPayModel::STATUS_COMPLETE];
        if ($NfPay->where('pay_no= "'. $no .'"')->save($data)) {
            return result(TRUE);
        } else {
            return result(FALSE, '网络繁忙~');
        }
    }

    public function check_orders_same_uid($orders) {
        foreach ($orders as $order) {
            $is_same = 99;
            foreach ($orders as $_item) {
                if ($is_same == 99) {
                    $is_same = $_item['uid'];
                }

                if ($is_same != 99 && $is_same != $_item['uid']) {
                    return false;
                }

            }
            return true;
        }
    }


}