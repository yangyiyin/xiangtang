<?php
/**
 *  Redis锁操作类
 *
 *  Func:
 *  public  lock    获取锁
 *  public  unlock  释放锁
 *  private connect 连接
 */
class  redis_simple{ // class start

    private $_config;
    private $_redis;

    public static $_default_config = array(
        'socket_type' => 'tcp',
        'host' => '127.0.0.1',
        'password' => NULL,
        'port' => 6379,
        'timeout' => 0
    );
    /**
     * 初始化
     * @param Array $config redis连接设定
     */
    public function __construct($config=array()){
        if ($config) {
            $this->_config = $config;
        } else {
            $this->_config = self::$_default_config;
        }

        $this->_redis = $this->connect();
    }

    /**
     * 获取锁
     * @param  String  $key    锁标识
     * @param  Int     $expire 锁过期时间
     * @return Boolean
     */
    public function lock($key, $expire=5){
        $is_lock = $this->_redis->setnx($key, time()+$expire);

        // 不能获取锁
        if(!$is_lock){

            // 判断锁是否过期
            $lock_time = $this->_redis->get($key);

            // 锁已过期，删除锁，重新获取
            if(time()>$lock_time){
                $this->unlock($key);
                $is_lock = $this->_redis->setnx($key, time()+$expire);
            }
        }

        return $is_lock? true : false;
    }

    /**
     * 释放锁
     * @param  String  $key 锁标识
     * @return Boolean
     */
    public function unlock($key){
        return $this->_redis->del($key);
    }

    public function set( $key, $value ) {
        return $this->_redis->set($key, $value);
//        return $this->setex($key, $value, 86400*180);//默认半年
    }

    public function setex( $key, $value, $ttl ) {
        return $this->_redis->setex($key, $ttl, $value);
    }
    public function get( $key ) {
        return $this->_redis->get($key);
    }

    public function del( $key ) {
        return $this->_redis->del($key);
    }
    /**
     * 创建redis连接
     * @return Link
     */
    private function connect(){
        try{
            $redis = new \Redis();
            $redis->connect($this->_config['host'],$this->_config['port'],$this->_config['timeout'],null,100);
            if(!empty($this->_config['auth'])){
                $redis->auth($this->_config['auth']);
            }
            $redis->select(0);
        }catch(\RedisException $e){
            throw new \Exception($e->getMessage());
            return false;
        }
        return $redis;
    }

} // class end

?>