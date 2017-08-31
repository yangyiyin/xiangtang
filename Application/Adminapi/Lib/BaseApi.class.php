<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:31
 */
namespace Adminapi\Lib;
use Common\Service;
class BaseApi extends Api {
    public function __construct() {
        parent::__construct();

//        var_dump($_SESSION);
        // 获取当前用户ID
        define('UID',is_login());
//        var_dump(UID);
        if( !UID ){// 还没登录 跳转到登录页面
            result_json(FALSE, '您还没登录', NULL, ERROR_CODE_SESSION_ERROR);
        }
        /* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $config =   api('Config/lists');
            S('DB_CONFIG_DATA',$config);
        }
        C($config); //添加配置

        // 是否是超级管理员
        define('IS_ROOT',   is_administrator());
        if(!IS_ROOT && C('ADMIN_ALLOW_IP')){
            // 检查IP地址访问
            if(!in_array(get_client_ip(),explode(',',C('ADMIN_ALLOW_IP')))){
                result_json(FALSE, '403:禁止访问');
            }
        }
        // 检测访问权限
        $access =   $this->accessControl();

        if ( $access === false ) {
            result_json(FALSE, '403:禁止访问');
        }elseif( $access === null ){
            $dynamic        =   $this->checkDynamic();//检测分类栏目有关的各项动态权限
            if( $dynamic === false ){
                //检测非动态权限
                $rule  = strtolower(MODULE_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME);
                if ( !$this->checkRule($rule,array('in','1,2')) ){
                    result_json(FALSE, '未授权访问');
                }
            }
        }

        //执行init
        $this->init();
    }
    public function init() {
        //子类实现
    }


    final protected function accessControl(){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
        $allow = C('ALLOW_VISIT');
        $deny  = C('DENY_VISIT');

        $check = strtolower(CONTROLLER_NAME.'/'.ACTION_NAME);

        if ( !empty($deny)  && in_array_case($check,$deny) ) {
            return false;//非超管禁止访问deny中的方法
        }
        if ( !empty($allow) && in_array_case($check,$allow) ) {
            return true;
        }
        return null;//需要检测节点权限
    }

    protected function checkDynamic(){
        if(IS_ROOT){
            return true;//管理员允许访问任何页面
        }
        return false;//不明,需checkRule
    }

}