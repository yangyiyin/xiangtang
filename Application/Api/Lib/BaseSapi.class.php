<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:31
 */
namespace Api\Lib;
class BaseSapi extends Api{
    public function __construct() {
        parent::__construct();
        $this->init();
    }
    public function init(){}
}