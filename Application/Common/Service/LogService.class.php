<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午9:03
 */
namespace Common\Service;
class LogService extends BaseService{
    public function log($content, $level=1) {
        $NfLog= D('NfLog');
        $data['content'] = $content;
        $data['level'] = $level;
        $data['create_time'] = current_date();
        $NfLog->add($data);
    }

}