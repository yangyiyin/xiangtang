<?php
/**
 * Created by newModule.
 * Time: 2017-06-08 12:20:24
 */
namespace Admin\Controller;

class AntArticleController extends AdminController {
    private $ArticleService;
    protected function _initialize() {
        parent::_initialize();
        $this->ArticleService = \Common\Service\ArticleService::get_instance();
    }

    public function index() {

        $where = [];
        /**
        if (I('get.sex')) {
            $where['sex'] = ['EQ', I('get.sex')];
        }

        if (I('get.name')) {
            $where['name'] = ['LIKE', '%' . I('get.name') . '%'];
        }
        */


        if (I('get.title')) {
            $where['title'] = ['LIKE', '%' . I('get.title') . '%'];
        }
        if (I('get.platform')) {
            $where['platform'] = ['eq', I('get.platform')];
        }
        $where['type'] = ['in', [\Common\Model\NfArticleModel::TYPE_NEWS,\Common\Model\NfArticleModel::TYPE_RULES,\Common\Model\NfArticleModel::TYPE_WORKINFO]];
        $type = I('type');
        if ($type) {
            $where['type'] = ['eq', $type];
        }

        $page = I('get.p', 1);
        list($data, $count) = $this->ArticleService->get_by_where($where, 'id desc', $page);
        $data = $this->convert_data($data);
        $PageInstance = new \Think\Page($count, \Common\Service\ArticleService::$page_size);
        if($total>\Common\Service\ArticleService::$page_size){
            $PageInstance->setConfig('theme','%FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END% %HEADER%');
        }
        $page_html = $PageInstance->show();
        $this->assign('type_options', $this->ArticleService->get_type_options($type));
        $this->assign('list', $data);
        $this->assign('page_html', $page_html);

        $this->display();
    }


    private function convert_data($data) {
        $map = \Common\Model\NfArticleModel::$type_map;
        $UserService = \Common\Service\UserService::get_instance();
        $users = $UserService->get_by_ids(result_to_array($data,'from_uid'));
        $users_map = result_to_map($users);
        foreach ($data as $key => $value) {
            $data[$key]['type_desc'] = isset($map[$value['type']]) ? $map[$value['type']] : '未知';
            $data[$key]['from_user'] = isset($users_map[$value['from_uid']]) ? $users_map[$value['from_uid']] : [];
        }
        return $data;
    }
    public function del() {
        $id = I('get.id');
        $ids = I('post.ids');

        if ($id) {
            $ret = $this->ArticleService->del_by_id($id);
        } else {
            $this->error('id没有');
        }
        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('删除文章');
        $this->success('删除成功！');
    }

    public function add() {
        $type = '';
        if ($id = I('get.id',0)) {
            $info = $this->ArticleService->get_info_by_id($id);
            if ($info) {
                $this->assign('info',$info);
            } else {
                $this->error('没有找到对应的信息~');
            }
            $type = $info['type'];
        }

        $this->assign('type_options', $this->ArticleService->get_type_options($type));
        $this->display();
    }

    public function add_about() {
        $info = $this->ArticleService->get_about(I('get.platform',1));
        $this->assign('info',$info);
        $this->display();
    }

    public function add_contact() {
        $info = $this->ArticleService->get_contact(I('get.platform',1));
        $this->assign('info',$info);
        $this->display();
    }

    public function add_public() {
        $info = $this->ArticleService->get_public(I('get.platform',1));
        $this->assign('info',$info);
        $this->display();
    }

    public function add_help() {
        $info = $this->ArticleService->get_help(I('get.platform',1));
        $this->assign('info',$info);
        $this->display();
    }

    public function add_volunteer_agree() {
        $info = $this->ArticleService->get_volunteer_agree();
        $this->assign('info',$info);
        $this->display();
    }

    public function add_disabled_help_agree() {
        $info = $this->ArticleService->get_disabled_help_agree();
        $this->assign('info',$info);
        $this->display();
    }

    public function update() {
        if (IS_POST) {
            $id = I('get.id');
            $data = I('post.');
            if (!$data['platform']) {
                $this->error('请选择平台');
            }
            $data['platform'] = array_sum($data['platform']);
            if ($id) {
                $ret = $this->ArticleService->update_by_id($id, $data);
                if ($ret->success) {
                    action_user_log('修改文章');
                    if (I('get.current')) {
                        $this->success('修改成功！', '');
                    } else {
                        $this->success('修改成功！', U('index'));
                    }
                } else {
                    $this->error($ret->message);
                }
            } else {
                $ret = $this->ArticleService->add_one($data);
                if ($ret->success) {
                    action_user_log('添加文章');
                    if (I('get.current')) {
                        $this->success('添加成功！', '');
                    } else {
                        $this->success('添加成功！', U('index'));
                    }
                } else {
                    $this->error($ret->message);
                }
            }

        }
    }

    public function change_status() {
        $ids = I('post.ids');
        $id = I('get.id');

        $status = I('get.status');
        $status = $status ? $status : I('post.status');
        $data = ['status'=>$status];
        if ($id) {
            $ret = $this->ArticleService->update_by_ids([$id], $data);
        }

        if ($ids) {
            $ret = $this->ArticleService->update_by_ids($ids, $data);
        }

        if (!$ret->success) {
            $this->error($ret->message);
        }
        action_user_log('求职信息审核操作'.$status);
        $this->success('操作成功！');
    }

}