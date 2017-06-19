<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Model;
use Common\Service;
class ServicesList extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $ServicesService;
    public function init() {
        $this->ServicesService = Service\ServicesService::get_instance();
    }

    public function excute() {
        $keyword = I('get.keyword');
        $where = [];
        if ($keyword) {
            $where['keyword'] = ['LIKE', '%'. $keyword .'%'];
        }
        list($list, $count) = $this->ServicesService->get_by_where_all($where, 'id desc');
        $result = new \stdClass();
        $result->service_list = $this->convert_data($list);

        return result_json(TRUE, '', $result);
    }

    private function convert_data($list) {
        $data = [];
        if ($list) {
            foreach ($list as $_item) {
                $_item['id'] = (int) $_item['id'];
                $tmp = [];
                $tmp = convert_obj($_item, 'id=service_id,title=service_name');
                $data[] = $tmp;
            }
        }
        return $data;
    }

}