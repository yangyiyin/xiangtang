<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Apimanagerrecommend\Lib;
use Common\Service;
class LaughSysNews extends BaseApi{
    protected $method = parent::API_METHOD_POST;

    public function init() {

    }

    public function excute() {
        $SysNewsUidService = Service\SysNewsUidService::get_instance();
        $newsuid = $SysNewsUidService->getAll(['uid'=>$this->uid,'is_read'=>Service\SysNewsUidService::IS_READ_YES]);
//        if (!$newsuid) {
//            return result_json(FALSE, '');
//        }
        $news_ids = result_to_array($newsuid, 'news_id');
        $SysNewsService = Service\SysNewsService::get_instance();
        $where = [];
        if ($news_ids) {
            $where['id'] = ['not in', $news_ids];
        }
        $news = $SysNewsService->getAll($where);
        if (!$news) {
            return result_json(FALSE, '');
        }
        return result_json(TRUE, '', $news[0]);

    }

}