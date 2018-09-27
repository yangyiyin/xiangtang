<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
require APP_PATH . '/Common/Lib/redis_simple.php';
class VipExtend extends BaseApi{
    protected $method = parent::API_METHOD_POST;
    private $VipService;
    public function init() {
        $this->VipService = Service\VipService::get_instance();
    }

    public function excute() {
        $redis_simple = new \redis_simple();
        $lock_key = 'managerrecommend_' . 'vip_pay_lock' . $this->uid;
        if (!$redis_simple->lock($lock_key,2)) {
            return result_json(FALSE, '您的操作频繁');
        }
        $pay_no = isset($this->post_data['pay_no']) ? $this->post_data['pay_no'] : '';
        $newer = I('newer',0);
//        var_dump($newer);
        if ($pay_no) {
            $ActivityPayService = \Common\Service\ActivityPayService::get_instance();
            $pay_info = $ActivityPayService->get_by_pay_no($pay_no);
            if (!$pay_info || $pay_info['status'] != 1) {
                $redis_simple->unlock($lock_key);
                return result_json(false, '支付中。。。');
            }

        } else {
            if (!$newer) {
                return result_json(FALSE, '未支付,操作失败');
            }

        }

        $id = $this->post_data['id'];

        if ($newer) {
            if ($this->user_info['newer']) {
                return result_json(FALSE, '您已领取新人礼,不能再次领取');
            }
            $days = 7;

            $UserService = Service\UserService::get_instance();
            $UserService->update_by_id($this->uid, ['newer'=>1]);

            $news_id = I('news_id',0);
            if ($news_id) {
                $SysNewsUidService = Service\SysNewsUidService::get_instance();
                $SysNewsUidService->add_one(['news_id'=>$news_id, 'uid'=>$this->uid, 'is_read'=>Service\SysNewsUidService::IS_READ_YES]);
            }

        } else {
            if (!$id) {
                return result_json(FALSE, '请选择vip时长');
            }

            $months = 0;
            foreach (Service\VipService::$price_info_list as $_price) {
                if ($_price['id'] == $id) {
                    $months = $_price['months'];
                }
            }

            if (!$months) {
                return result_json(FALSE, '请选择vip时长');
            }
            $days = $months * 30;

        }

        //默认开通vip
        $VipService = \Common\Service\VipService::get_instance();
        $VipService->extend_days($this->uid, $days);

        result_json(TRUE, '');
    }
}