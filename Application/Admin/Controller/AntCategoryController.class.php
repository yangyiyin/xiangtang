<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/26
 * Time: 下午1:31
 */
namespace Admin\Controller;

class AntCategoryController extends AdminController {
    private $CategoryService;
    protected function _initialize() {
        parent::_initialize();
        $this->CategoryService = \Common\Service\CategoryService::get_instance();
    }
    public function index() {
        $cat_tree = $this->CategoryService->get_all_tree();
        $this->meta_title = '分类管理';
        $this->assign("category", $cat_tree);
        $this->display();
    }

    public function add() {
        if (IS_POST) {
            $data = [];
            $data['name'] = I('post.name');
            $data['parent_id'] = I('post.parent_id', 0);
            $data['icon'] = I('post.icon', '');
            $data['sort'] = I('post.sort', 0);
            if (!$data['name']) {
                $this->error('分类名称不可以为空哦~');
            }
            if ($this->CategoryService->add_one($data)) {
                action_user_log('新增分类');
                $this->success('新增成功！', U('index'));
            } else {
                $this->error('新增失败,请联系技术');
            }
        } else {
            $pid = I('get.pid');
            $cate = array();
            if($pid){
                /* 获取上级分类信息 */
                $cate = $this->CategoryService->get_info_by_id($pid);

                if(!$cate){
                    $this->error('指定的上级分类不存在或被禁用！');
                }
            }

            $catetree = $this->get_all_tree_option($pid);
            //var_dump($catetree);die();
            /* 获取分类信息 */
            $this->assign('catetree',$catetree);
            $this->assign('category', $cate);
            $this->meta_title = '新增分类';
            $this->display('edit');
        }
    }

    public function edit() {
        if (IS_POST) {
            $id = I('post.id');
            $data = [];
            $data['name'] = I('post.name');
            $data['parent_id'] = I('post.parent_id', 0);
            $data['icon'] = I('post.icon', '');
            $data['sort'] = I('post.sort', 0);
            if (!$data['name']) {
                $this->error('分类名称不可以为空哦~');
            }
            if ($this->CategoryService->update_info_by_id($id, $data)) {
                action_user_log('修改分类');
                $this->success('修改成功！', U('index'));
            } else {
                $this->error('修改失败,请联系技术');
            }
        } else {
            $id = I('id');
            if (!$id) {
                $this->error('请传入分类id!');
            }
            $cate = $this->CategoryService->get_info_by_id($id);
            $catetree = $this->get_all_tree_option($cate['parent_id']);
            /* 获取分类信息 */
            $this->assign('catetree',$catetree);
            $this->assign('info', $cate);
            $this->display();
        }

    }

    public function del() {
        $id = I('id');
        if (!$id) {
            $this->error('请传入id!');
        }
        if ($this->CategoryService->del_by_id($id)) {
            action_user_log('删除分类');
            $this->success('删除成功！', U('index'));
        } else {
            $this->error('删除失败,请联系技术');
        }
    }

    private function get_all_tree_option($id = '') {
        return $this->CategoryService->get_all_tree_option($id);
    }


}