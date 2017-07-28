<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class FinancialInsurancePropertyController extends AdminController {
    protected $OrderService;
    protected function _initialize() {
        parent::_initialize();
        $this->OrderService = \Common\Service\OrderService::get_instance();
    }




}