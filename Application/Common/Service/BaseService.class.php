<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class BaseService {
    protected static $instance_map = NULL;
    public static $page_size = 10;
    public static $NOT_DELETED = 0;
    public static $DELETED = 1;

    // 不允许实例化，子类禁止覆盖
    final protected function __construct() {
    }

    final protected function __clone() {
    }

    protected function init() {

    }

    /**
     * 获取Service的实例
     *
     * @return static
     */
    public static function get_instance() {
        $class = get_called_class();
        if (!isset(self::$instance_map[$class])) {
            $instance = new $class();
            $instance->init();
            self::$instance_map[$class] = $instance;
            return $instance;
        }
        return self::$instance_map[$class];
    }

}