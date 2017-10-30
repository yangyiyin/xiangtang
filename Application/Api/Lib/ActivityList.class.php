<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class ActivityList extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $ActivityService;
    public function init() {
        $this->ActivityService = Service\ActivityService::get_instance();
    }

    public function excute() {
        $p = I('p',1);

        $where = [];
        list($data,$count) = $this->ActivityService->get_by_where($where,'id desc',$p);
        $data = $this->convert($data);
        $data = convert_objs($data,'id,title,time_start,time_end,apply_status,apply_status_desc');
        $result = [
            'list' => $data,
            'has_more' => has_more($count,$p, Service\ActivityService::$page_size)
        ];
        return result_json(TRUE, '', $result);
        
    }

    private function convert($data) {
        if ($data) {
            $activity_ids = result_to_array($data);
            $ActivityApplyService = \Common\Service\ActivityApplyService::get_instance();
            $applies = $ActivityApplyService->get_by_activity_ids($activity_ids,$this->uid);
            $applies_map = result_to_map($applies, 'activity_id');
            $status_map = \Common\Model\NfActivityApplyModel::$status_map;
            foreach ($data as $key => $value) {
                $data[$key]['apply_status'] = isset($applies_map[$value['id']]) ? $applies_map[$value['id']]['status'] : 0;
                $data[$key]['apply_status_desc'] = isset($status_map[$data[$key]['apply_status']]) ? $status_map[$data[$key]['apply_status']] : '未报名';
            }
        }
        return $data;

    }

}