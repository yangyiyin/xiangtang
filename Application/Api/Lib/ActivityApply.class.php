<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class ActivityApply extends BaseSapi{
    protected $method = parent::API_METHOD_POST;
    private $ActivityApplyService;
    public function init() {
        $this->ActivityApplyService = Service\ActivityApplyService::get_instance();
    }

    public function excute() {

        $activity_id = $this->post_data['activity_id'];
        if (!$activity_id) {
            return result_json(false, '参数错误');
        }

        //判断是否为志愿者
        $VolunteerService = \Common\Service\VolunteerService::get_instance();
        if (!$VolunteerService->is_volunteer($this->uid)) {
            return result_json(false, '您还不是志愿者,暂无法报名该活动!');
        }

        $data['activity_id'] = $activity_id;
        $data['uid'] = $this->uid;
        $info = $this->ActivityApplyService->get_info_by_uid_activity_id($this->uid,$activity_id);
        if ($info && $info['status'] != \Common\Model\NfActivityApplyModel::STATUS_REJECT) {
            return result_json(false, '您已报名该活动,无法再次报名');
        } elseif($info && $info['status'] == \Common\Model\NfActivityApplyModel::STATUS_REJECT) {
            $ret = $this->ActivityApplyService->update_by_id($info['id'], ['status'=>\Common\Model\NfActivityApplyModel::STATUS_SUBMIT]);
        } else {
            $ret = $this->ActivityApplyService->add_one($data);
            if (!$ret->success) {
                return result_json(false, $ret->message);
            }
        }


        return result_json(TRUE, '报名成功!');
        
    }



}