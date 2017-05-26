<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:31
 */
namespace Api\Lib;
class Api{
    const API_METHOD_GET = 'get';
    const API_METHOD_POST= 'post';
    protected $method = 'get';
    protected $callback = '';
    protected $post_data = [];
    public function __construct() {
        header('Content-Type:application/json; charset=utf-8');
        switch ($this->method) {
            case self::API_METHOD_GET:
                if (!IS_GET) {
                    result_json(FALSE, '非法请求方式');
                }
                break;
            case self::API_METHOD_POST:
                if (!IS_POST) {
                    result_json(FALSE, '非法请求方式');
                }
                break;
            default:
                result_json(FALSE, '非法请求方式');
                break;
        }
        if (I('get.callback')) {
            $this->callback = I('get.callback');
        }

        $this->post_data = json_decode(file_get_contents('php://input'), true);
        //echo_json_die($this->post_data);
    }
}