<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class NeedsTypes extends BaseApi{
    protected $method = parent::API_METHOD_GET;
    private $NeedsTypesService;
    public function init() {
        $this->NeedsTypesService = Service\NeedsTypesService::get_instance();
    }

    public function excute() {
        $types = $this->NeedsTypesService->get_all_types();
        $types = convert_objs($types, 'id,title,tips');
        return result_json(TRUE, '', $types);
    }

}