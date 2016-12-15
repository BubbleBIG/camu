<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Session;
class Public extends Common {
    public function u() {
        $uid = Session::get('uid');
        $u = db('user')->where('uid',$uid)->find();
        $this->assign('user',$u);
        $data=Db::table('user')->where('age','>',1)->select();

        $this->assign('data',$data);
            // dump($data);
            return $this->fetch('u');
        // return view('user');
    }
}
?>