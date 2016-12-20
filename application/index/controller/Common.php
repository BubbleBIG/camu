<?php
    namespace app\index\controller;
    use think\Controller;
    use think\Session;
    use think\Cookie;
    class Common extends Controller {
        public function _initialize() {
            if (Session::get('uid') == null) {
                // $refer = Cookie::get('refer');
                $this->redirect('Index/Login/');
            }
        }
    }
?>