<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class ItemBrands extends BaseSapi{
    protected $method = parent::API_METHOD_GET;
    private $BrandService;
    public function init() {
        $this->BrandService = Service\BrandService::get_instance();
    }

    public function excute() {

        $data = $this->BrandService->get_all();
        $data = convert_objs($data, 'id,name');
        return result_json(TRUE, '', $data);
    }
}