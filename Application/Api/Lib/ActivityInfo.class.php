<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class ActivityInfo extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $ActivityService;
    public function init() {
        $this->ActivityService = Service\ActivityService::get_instance();
    }

    public function excute() {
        $activity_id = I('activity_id');
        if (!$activity_id) {
            return result_json(false, '参数错误');
        }


        $data = $this->ActivityService->get_info_by_id($activity_id);
        if (!$data) {
            return result_json(false, '没有该活动信息');
        }
        $ActivityApplyService = \Common\Service\ActivityApplyService::get_instance();
        $applies = $ActivityApplyService->get_info_by_uid_activity_id($this->uid, $activity_id);
        $applies_map = result_to_map($applies, 'activity_id');
        $status_map = \Common\Model\NfActivityApplyModel::$status_map;
        $data['apply_status'] = isset($applies_map[$data['id']]) ? $applies_map[$value['id']]['status'] : 0;
        $data['apply_status_desc'] = isset($status_map[$data['apply_status']]) ? $status_map[$data['apply_status']] : '未报名';

        $data = convert_obj($data,'id,title,time_start,time_end,apply_status,apply_status_desc');

        return result_json(TRUE, '', $data);
        
    }



}