<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Adminapi\Lib;
use Common\Service;
require APP_PATH . 'Common/Lib/phpspider/spider.php';
class SpiderLaugh{

    public function init() {
    }

    public function excute() {

        $data = [];
//        require APP_PATH . '/Common/Lib/spider/spider.php';
        $Spider = new \Common\Lib\Spider();
        $data['content'] = $Spider->get_content();
        //var_dump($data['content']);
        if (!$data['content']) {
            return result_json(false, '没有找到内容'.date('Y-m-d H:i:s'));
        }
        $service = Service\ArticleService::get_instance();
        //文字
        if (strpos($data['content'], 'kkkkkk') !== false) {
            $contents = explode('kkkkkk', $data['content']);
            foreach ($contents as $content) {
                $content = iconv('GBK','UTF-8',$content);
               // var_dump($content);
                $data['content'] = $content;
                $data['from'] = \Common\Model\NfArticleModel::FROM_ADMIN;
                $data['status'] = \Common\Model\NfArticleModel::STATUS_OK;
                $data['type'] = \Common\Model\NfArticleModel::TYPE_LAUGH;
                $data['publish_time'] = date('Y-m-d H:i:s');
                $ret = $service->add_one($data);
                if (!$ret->success) {
                    return result_json(false, $ret->message.date('Y-m-d H:i:s'));
                }
            }
        } else {
            $data['from'] = \Common\Model\NfArticleModel::FROM_ADMIN;
            $data['status'] = \Common\Model\NfArticleModel::STATUS_OK;
            $data['type'] = \Common\Model\NfArticleModel::TYPE_LAUGH;
            $data['publish_time'] = date('Y-m-d H:i:s');
            $ret = $service->add_one($data);
            if (!$ret->success) {
                return result_json(false, $ret->message.date('Y-m-d H:i:s'));
            }
        }


        return result_json(true, '操作成功'.date('Y-m-d H:i:s'));
    }



}