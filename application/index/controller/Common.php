<?php
    namespace app\index\controller;
    use think\Controller;
    use think\Session;
    class Common extends Controller {
        public function _initialize() {
            if (Session::get('uid') == null) {
                $this->redirect('Index/Login/');
            }
        }
    }
?>