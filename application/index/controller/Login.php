<?php
// namespace app\index\controller;

// // class Index
// // {
// //     public function index()
// //     {
// //         return view('index');
// //         // return 'Hello World!'
// //     }
// // }
// use think\Controller;
// class Index extends Controller
// {
// public function index()
// {
// // 获取包含域名的完整URL地址
// $this->assign('domain',$this->request->url(true));
// return $this->fetch('index');
// }
// }
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Session;
class Login extends Controller
{
    public function index() {
        return $this->fetch('index');
    }
    public function register()
    {
    return view('register');
    }
    public function login() {
        $user = db('user');
        $name = $_POST['name'];
        $pwd = md5($_POST['pwd']);
        $u = $user->where('name',$name)->find();
        if($u == NULL) {
        return ['message'=>'账号不存在','status'=>0];
        }
        if ($u['pwd'] !== $pwd) {
            // return ['message'=>'密码错误','status'=>0];
            $data = array(
                'message'=>'密码错误',
                'status'=>0,
                );
            return ($data);
        }
        else {
            // session('[start]');
            Session::set('uid',$u['uid']);
            // echo Session::get('uid');
            // session('uid',$u['uid']);
            // session('name',$u['name']);
            // dump($_SESSION['uid']);
            // $this->redirect('Index/index');
            // return ['data'=>$u,'message'=>'登陆成功,正在跳转...','status'=>1];
            $data = array(
                'data'=>$u,
                'message'=>'登陆成功,正在跳转...',
                'status'=>1,
                );
            return ($data);
            // dump($u);
            // dump($u['pwd']);
            // dump($pwd);
        }
    }
    public function login1()
    {
        $uname = $_POST['id'];
        $user = db('user')->where('name',$uname)->find();
        // $user = db('user')->where(array('name'=>$uname))->find();
        if (!$user) {
            echo "<script>alert('账号或密码错误') ;window.location.href='login.html';</script>";
        } else {
            $this->redirect('/camU/index/index/user');
            dump($user);
            dump($uname);
        }
            dump($user);
            dump($uname);
            return $this->fetch('login');
        return view('user');
    }
    public function sign() {
        $user = db('user');
        $name = $_POST['name'];
        $u = $user->where('name',$name)->find();
        if(!$u == NULL) {
        return ['message'=>'用户名已被使用','status'=>0];
        }else {
            $data = array(
                'name'  =>$_POST['name'],
                'pwd'   =>md5($_POST['pwd']),
                'join_time' =>time(),
                );
            $user->insert($data);
            return ['message'=>'注册成功,正在跳转...','status'=>1];
        }
    }
    public function signc() {
        $uname = $_POST['id'];
        $pwd = md5($_POST['password']);
        dump($uname);
        dump($pwd);
    }
    public function add(){
    $m=db('message');
    $tit = $_GET['title'];
    $d = db('message')->where('title',$tit)->find();
    if(!$d == NULL) {
        return ['message'=>'shibb','status'=>1];
    }
    // if($m->insert($_GET)){
    //   return ['data'=>$_GET,'status'=>1];
    //   // return ['message'=>'添加信息成功','status'=>1];
    // }else{
    //   $this->ajaxReturn(0,'添加信息失败',0);
    // }
  }
}
?>