<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Service;
class NeedsBankList extends BaseSapi{
    protected $method = parent::API_METHOD_GET;


    public function excute() {
        $data = ['中国邮储银行'];
        return result_json(TRUE, '发布成功', $data);
    }

}