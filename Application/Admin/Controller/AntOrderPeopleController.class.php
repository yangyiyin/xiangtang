<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class AntOrderPeopleController extends AntOrderController  {
    protected $type;
    protected function _initialize() {
        parent::_initialize();
        $this->type = $this->OrderService->get_people_type();
        $this->assign('type', $this->type);
    }





   

}