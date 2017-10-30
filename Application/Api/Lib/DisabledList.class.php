<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class DisabledList extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $DisabledManService;
    public function init() {
        $this->DisabledManService = Service\DisabledManService::get_instance();
    }

    public function excute() {
        $p = I('p',1);

        $where = [];
        list($data,$count) = $this->DisabledManService->get_by_where($where,'id desc',$p);
        $data = $this->convert($data);
        $data = convert_objs($data,'id,name,tel,address,id_no,directly_tel,directly_name,img');
        $result = [
            'list' => $data,
            'has_more' => has_more($count,$p, Service\DisabledManService::$page_size)
        ];
        return result_json(TRUE, '', $result);
        
    }

    private function convert($data) {
        if ($data) {
            foreach ($data as $key => $value) {
                $data[$key]['img'] = item_img($value['img']);
            }
        }
        return $data;

    }

}