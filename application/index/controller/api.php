<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Session;

class Api
{
	public function user() {
		$uid = Session::get('uid');
        $u = db('user')->where('uid',$uid)->find();
        $this->assign('user',$u);
	}
}