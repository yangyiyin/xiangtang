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
    const API_METHOD_ALL = 'all';
    const FROM_SERVICE = 1;//服务站
    const FROM_RETAIL = 2;//零售版
    const FROM_ALL = 3;//零售版
    protected $method = 'get';
    protected $callback = '';
    protected $post_data = [];
    protected $from = self::FROM_SERVICE;//默认
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
            case self::API_METHOD_ALL:

                break;
            default:
                result_json(FALSE, '非法请求方式');
                break;
        }
        if (I('get.callback')) {
            $this->callback = I('get.callback');
        }
        if (I('get.from')) {
            $this->from = I('get.from');
        }
        $this->post_data = json_decode(file_get_contents('php://input'), true);

        if (I('get.dev_y') == 'yyy') {
            $this->post_data = $_POST;
        }

        if (API_DEBUG) {
            $LogService = \Common\Service\LogService::get_instance();
            $content = 'action_name::' . ACTION_NAME . '|post::' . json_encode($this->post_data) . '|get::' . json_encode($_GET);
            $LogService->log($content);
        }


        //echo_json_die($this->post_data);
    }
}