<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/25
 * Time: 下午8:25
 */
namespace Api\Lib;
use Common\Model;
use Common\Service;
class ArticleInfo extends BaseSapi{
    const block_type_about = 'about';
    const block_type_contact = 'contact';
    const block_type_public = 'public';
    const block_type_volunteer_agree = 'volunteer_agree';
    const block_type_disabled_help_agree = 'disabled_help_agree';
    protected $method = parent::API_METHOD_GET;
    private $ArticleService;
    public function init() {
        $this->ArticleService = Service\ArticleService::get_instance();
    }

    public function excute() {
        $id = I('get.id');
        $block = I('get.block'); //暂时不做处理
        if (!$id && !$block) {
            result_json(FALSE, '缺少参数');
        }
        $result = new \stdClass();
        $result->content = '';
        if ($id) {
            $info = $this->ArticleService->get_info_by_id($id);
            if (!$info) {
                result_json(FALSE, '没有找到文章~');
            }
            list($pre, $next) = $this->ArticleService->get_pre_next($id, $info['type']);
            $result->pre = convert_obj($pre, 'id,title,create_time');
            $result->next = convert_obj($next, 'id,title,create_time');
        } elseif ($block == self::block_type_about) {
            $info = $this->ArticleService->get_about();
        } elseif ($block == self::block_type_contact) {
            $info = $this->ArticleService->get_contact();
        } elseif ($block == self::block_type_public) {
            $info = $this->ArticleService->get_public($this->from);
        } elseif ($block == self::block_type_volunteer_agree) {
            $info = $this->ArticleService->get_volunteer_agree();
        } elseif ($block == self::block_type_disabled_help_agree) {
            $info = $this->ArticleService->get_disabled_help_agree();
        }

        if ($info) {
            $result->id = $info['id'];
            $result->title = $info['title'];
            $result->content = $info['content'];
            $result->create_time = $info['create_time'];
        }

        return result_json(TRUE, '', $result);
    }

}