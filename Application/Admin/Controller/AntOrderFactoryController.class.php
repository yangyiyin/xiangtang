<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class AntOrderFactoryController extends AntOrderController  {
    protected $type;
    protected function _initialize() {
        parent::_initialize();
        $this->type = $this->OrderService->get_factory_type();
    }





   

}