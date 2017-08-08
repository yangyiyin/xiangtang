<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/6/1
 * Time: 下午3:37
 */

class newModule {
    private $name = 'test';
    private $desc = 'test';
    private $service_path = '../../Application/Common/Service/';
    private $model_path = '../../Application/Common/Model/';
    private $controller_path = '../../Application/Admin/Controller/';
    private $view_path = '../../Application/Admin/View/';
    public function __construct($name, $desc = ''){
        $this->name = ucfirst($name);
        $this->desc = ucfirst($desc);
    }

    public function gain_model() {
        $content = file_get_contents('model');
        $content = str_replace('___name___', $this->name, $content);
        $content = str_replace('___time___', date('Y-m-d H:i:s'), $content);
        file_put_contents($this->model_path . 'Nf' . $this->name . 'Model.class.php', $content);
    }

    public function gain_service() {
        $content = file_get_contents('service');
        $content = str_replace('___name___', $this->name, $content);
        $content = str_replace('___time___', date('Y-m-d H:i:s'), $content);
        file_put_contents($this->service_path . $this->name . 'Service.class.php', $content);
    }

    public function gain_controller() {
        $content = file_get_contents('controller');
        $content = str_replace('___name___', $this->name, $content);
        $content = str_replace('___desc___', $this->desc, $content);
        $content = str_replace('___time___', date('Y-m-d H:i:s'), $content);
        file_put_contents($this->controller_path . 'Ant' . $this->name . 'Controller.class.php', $content);
    }

    public function gain_view() {
        $content = file_get_contents('view/add.html');
        $content = str_replace('___name___', $this->name, $content);
        $content = str_replace('___desc___', $this->desc, $content);
        $content = str_replace('___time___', date('Y-m-d H:i:s'), $content);
        $path = $this->view_path . 'Ant'.$this->name;
        mkdir($path, 777);
        file_put_contents($path . '/add.html', $content);

        $content = file_get_contents('view/index.html');
        $content = str_replace('___name___', $this->name, $content);
        $content = str_replace('___desc___', $this->desc, $content);
        $content = str_replace('___time___', date('Y-m-d H:i:s'), $content);
        file_put_contents($path . '/index.html', $content);


    }

    public function gain($names) {
        if (isset($names['service'])) {
            $this->gain_service();


        }
        if (isset($names['model'])) {
            $this->gain_model();

        }
        if (isset($names['controller'])) {
            $this->gain_controller();

        }

        if (isset($names['view'])) {
            $this->gain_view();

        }

        echo 'success';
        exit();
    }

    public function gain_power($names) {
        if (isset($names['service'])) {

            //chmod($this->service_path . $this->name . 'Service.class.php', 777);
            echo 'sudo chmod 777 '.'Application/Common/Service/'. $this->name . 'Service.class.php '. "\n";

        }
        if (isset($names['model'])) {

           // chmod($this->model_path . 'Nf' . $this->name . 'Model.class.php', 777);
            echo 'sudo chmod 777 '.'Application/Common/Model/'. 'Nf' . $this->name . 'Model.class.php '. "\n";
        }
        if (isset($names['controller'])) {

            //chmod($this->controller_path . 'Ant' . $this->name . 'Controller.class.php', 777);
            echo 'sudo chmod 777 '.'Application/Admin/Controller/' . 'Ant' . $this->name . 'Controller.class.php '. "\n";
        }

        if (isset($names['view'])) {
            echo 'sudo chmod -R 777 '.'Application/Admin/View/' . 'Ant' . $this->name . "\n";

            //chmod($this->view_path . 'Ant' . $this->name . '/add.html', 777);
           // echo 'sudo chmod 777 '.'Application/Admin/View/' . 'Ant' . $this->name . '/add.html '. "\n";
            //chmod($this->view_path . 'Ant' . $this->name . '/index.html', 777);
            //echo 'sudo chmod 777 '.'Application/Admin/View/' . 'Ant' . $this->name . '/index.html '. "\n";
        }

        echo 'success';
        exit();
    }
}


$newModule = new newModule('account', '品牌');
//['model'=>1, 'controller'=>1, 'service'=>1, 'view'=>1]
//$newModule->gain(['model'=>1, 'service'=>1]);
$newModule->gain_power(['model'=>1, 'service'=>1]);