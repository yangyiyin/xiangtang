<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class PayService extends BaseService{

    const AlipayPubKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAh5MJ/avdo5JVj5GJIa+hQ93QvxhBIKu0hWHeM/8J7GpdsTWhok/S77MDKTEUQAbNBSVOTK1eFzJaHYJRWl+JdqQPy6uoNq0iCg6CrorfUH6Zj5z2FQMy6VSN1s7DMy2sqo18mXyHyUZRmM4kRwD/Vubsr/4Q4k0ZW7DTbRZpAgCF5TPI2EpccCXArLZsaDmDiYJtusbt8hiArhwyTTNo5BttDnoL7BYxYqfRIsybLGvrgr7aFAxYSSb+ZejNW19JLPfQNZI0tLi0F0M3CQikd/8jQNFHbVKEjGWrDvWt8UAtvctaPVz6wvsl3z+71Bcq3cjyIRxzHxUHQxQxGtm+kwIDAQAB';
    const AlipayPriKey = 'MIIEvwIBADANBgkqhkiG9w0BAQEFAASCBKkwggSlAgEAAoIBAQCLDqJjukluhuI/BsU40zj2jNw42jl9LZPSnD9KfMNb3bBL051kns1x6nuxave8On+D6Q2OONWvC497DqRWey/NCRV/+dmHGIe9Wq8hw/Xd4MaxxAcpmw6Uk3mTE/9IzRCzugc+wVIE0wo5zx3zz8wOBjoEbQWFLs9bptS7EMv9U2IPqySPQtzopYCJXZATHC97ovmIu3vYJrA/rQnXxllc1TOx6d6VWeRtx1ZRUpxJeOy11uV5LmdTpsaz0ghFLALuvxe9IL/6bs43F1OaoAE5fJDcsc19DjXml7ImrOdY7ziQV91YwyfxC5EU7oGhLCaS5faojHQgxZqaLYKivey3AgMBAAECggEAW27f79sJdZdTJEX7YAXiqpqsIuW6b0iMrir2oq+udLUrunAGSabxRzn64wmGo0mDluSieSV9u39KdIuIGyUcpSCX9nH+SgojFqqOBRGolJ+7hh9y5jSCPcdKZR78+I19se9b3DOZDnsFekVpWGsFrSMC+u4EdzH0PjtQHUBKIOK9pt+ByaLWtdCuLZiTey/Nsbez6Q036t7xLUDWoCbBlD295Y7lw8e84oGkR8VI00W7ufnOgp1t4/PlG+h+tYiZVxXypj23F4aFrAhlBdVyVRFv4qWir6HySg8fwmABm7u/ooJhChuTK8WyDy/ar21F35EmBWUjylXaMKJkLxc2oQKBgQDO45oalvTxsKSaXPn47qCKOD0x/D8H9F9tAs9NY73sefUdjV0KgSaqBNPo/ZPsM8KF/wbwCH8EZuotJy5MqI3Psb1hdQKhkqmsjmSaPTLTXV0KK7PzcOOEHQorMJgaj9zl9VeO4IfsVcLayRkfZtF8BnSQiFZuZmkcSNkKzPU1lQKBgQCsEPhABjO61UuQFwJ203qSLCYh2Bf5/AYFT1eF1vlrqBDP2wvMe4l15kM1AAq562ANUarclNpY3WAJQsxOARevEdtrWwHVLw/JvnoE3vHM0fQzSEhFF8JpGI6Jx7iHIyZDgKLNJd1ifKXymgziGf0yuuHD51nTixxIShQpBx6uGwKBgQCtHgiaTUzjXLMvs7M8GLlfT1XtmKxJhLPA7QP4Nwj6csanlt2O56kpxWZo5J89m8YfB9qPShy220MCy3FTlgssCwd0IEw2VRoDmRcXdTQtZ0duNkma4BCRQRd8Mmpkd5MZHWXJ2ZoDKXQmTPXnr8qE5IXvVxYzxSAZGd/7yKs6zQKBgQCdBbN2wfItOv8FFGp0Q9OkV1PJKOnggMSBzgEiK6dcPnhxJwWias5r4GFOmaYwOoNiRDf3qoOD+ynr6aCGQur5IgOp+dg3UwZGZmP10/q3npYKwpjpLKCGxhk11SQpdsMxcM+hBT/946CRP1Iod+0fgXoMDDFmIpoBwlCZjFzMJwKBgQDHPf0fbaB1Nc56Kz9U88cefDFpv3y11rmMNXRQv0QJRjX9pxt6EoIX861TxKw3JKnjS57huBcmzswp8E6IMStNKyscEtgzpQc9mejHF6+r8Vz1dDDndTANWV7757iFjv5kuNl1SHelx96eCn086JlR79Vq24Wiw3yP2Qjy+1Rfng==';
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

    public function del_by_id_real($id) {
        if (!check_num_ids([$id])) {
            return result(FALSE, 'id不合法~');
        }
        $NfPay = D('NfPay');
        $ret = $NfPay->where('id=' . $id)->delete();
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

    public function create_by_orders($orders, $pay_agent=false) {
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
            //删除这个记录
            $this->del_by_id_real($pay['id']);//重新创建一个支付记录的原因是因为,有可能修改了订单总价
            //return result(TRUE, '订单已创建支付', $pay);
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


        $data = [];
        $data['pay_no'] = $this->get_pay_no($orders[0]['uid']);
        $data['pay_agent'] = $pay_agent ? $pay_agent : \Common\Model\NfPayModel::PAY_AGENT_ALIPAY;
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