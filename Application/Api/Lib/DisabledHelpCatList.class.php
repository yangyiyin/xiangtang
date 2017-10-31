<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class DisabledHelpCatList extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $DisabledHelpCatService;
    public function init() {
        $this->DisabledHelpCatService = Service\DisabledHelpCatService::get_instance();
    }

    public function excute() {
        $p = I('p',1);


        $data = $this->DisabledHelpCatService->get_all();
        $data = convert_objs($data, 'id,title');

        return result_json(TRUE, '', $data);
        
    }

//    private function convert($data) {
//        if ($data) {
//
//            $DisabledHelpCatService = \Common\Service\DisabledHelpCatService::get_instance();
//            $all_cats = $DisabledHelpCatService->get_all();
//            $all_cats_map = result_to_map($all_cats);
//            $status_map = \Common\Model\NfDisabledHelpModel::$status_map;
//            foreach ($data as $key => $help) {
//                $data[$key]['help_cat_desc'] = isset($all_cats_map[$help['help_cat']]) ? $all_cats_map[$help['help_cat']] : [];
//                $data[$key]['status_desc'] = isset($status_map[$help['status']]) ? $status_map[$help['status']] : [];
//            }
//        }
//        return $data;
//
//    }

}