<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class DisabledHelpApplyList extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $DisabledHelpService;
    public function init() {
        $this->DisabledHelpService = Service\DisabledHelpService::get_instance();
    }

    public function excute() {
        $p = I('p',1);

        $where = [];
        $where['uid'] = $this->uid;
        list($data,$count) = $this->DisabledHelpService->get_by_where($where,'id desc',$p);
        $data = $this->convert($data);

        $result = [
            'list' => $data,
            'has_more' => has_more($count,$p, Service\DisabledHelpService::$page_size)
        ];
        return result_json(TRUE, '', $result);
        
    }

    private function convert($data) {
        if ($data) {

            $DisabledHelpCatService = \Common\Service\DisabledHelpCatService::get_instance();
            $all_cats = $DisabledHelpCatService->get_all();
            $all_cats_map = result_to_map($all_cats);
            $status_map = \Common\Model\NfDisabledHelpModel::$status_map;
            foreach ($data as $key => $help) {
                $data[$key]['help_cat_desc'] = isset($all_cats_map[$help['help_cat']]) ? $all_cats_map[$help['help_cat']] : [];
                $data[$key]['status_desc'] = isset($status_map[$help['status']]) ? $status_map[$help['status']] : [];
            }
        }
        return $data;

    }

}