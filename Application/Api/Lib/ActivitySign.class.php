<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class ActivitySign extends BaseSapi{
    protected $method = parent::API_METHOD_POST;
    private $ActivityApplyService;
    public function init() {
        $this->ActivityApplyService = Service\ActivityApplyService::get_instance();
    }

    public function excute() {

        $activity_id = $this->post_data['activity_id'];
        $code = $this->post_data['code'];
        if (!$activity_id || !$code) {
            return result_json(false, '参数错误');
        }


        //判断是否为志愿者
        $VolunteerService = \Common\Service\VolunteerService::get_instance();
        if (!$VolunteerService->is_volunteer($this->uid)) {
            return result_json(false, '您还不是志愿者,暂无法签到该活动!');
        }
        $ActivityService = \Common\Service\ActivityService::get_instance();
        $activity = $ActivityService->get_info_by_id($activity_id);
        if (!$activity || $activity['extra'] != $code) {
            return result_json(false, '签到码错误!');
        }
        $info = $this->ActivityApplyService->get_info_by_uid_activity_id($this->uid,$activity_id);
        if (!$info || $info['status'] != \Common\Model\NfActivityApplyModel::STATUS_OK) {
            return result_json(false, '签到失败!您已签到或没有报名成功该活动');
        }


        $ret = $this->ActivityApplyService->update_by_id($info['id'],['status'=>\Common\Model\NfActivityApplyModel::STATUS_SIGN]);
        if (!$ret->success) {
            return result_json(false, $ret->message);
        }

        return result_json(TRUE, '签到成功!');
        
    }



}