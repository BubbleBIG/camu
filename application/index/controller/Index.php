<?php
// // 获取包含域名的完整URL地址
// $this->assign('domain',$this->request->url(true));
// return $this->fetch('index');
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Session;
class Index extends Common
{
    public function index() {
        $id = Session::get('uid');
        $img = db('img')->order('iid desc')->select();
        $data = db('user')->where('uid',$id)->find();
        $this->assign('data',$data);
        $this->assign('img',$img);
    return view('index');
    }
    public function index1() {
        $a = new Index();
        $a->index();
    return view('index1');
    }
    public function index2() {
        $a = new Index();
        $a->index1();
    return view('index2');
    }
    public function user() {
        $uid = Session::get('uid');
        $u = db('user')->where('uid',$uid)->find();
        $this->assign('user',$u);
        $data=Db::table('user')->where('age','>',1)->select();

        $this->assign('data',$data);
            // dump($data);
            // return $this->fetch('user');
        // return view('user');
    }
    public function boards() {
        $a = new Index();
        $a->user();
        $boards = db('boards')->where('uid',Session::get('uid'))->order('bid desc')->select();
        $imgb = db('img')->where('uid',Session::get('uid'))->order('iid desc')->select();
        foreach($boards as $key=>$board){
            // $imgb = db('img')->where('bid',$board['bid'])->order('iid desc')->limit(6)->select();
            // $this->assign('bimg',$imgb);
            // dump($imgb);
        }
        // $boa = Db::field('bo.name,role.title')
        //         ->table(['boards'=>'bo','img'=>'im'])
        //         ->limit(10)->select();
        // $imgb = db('img')->where('bid',$boards['id'])->order('id desc')->limit(4)->select();
        // $this->assign('bimg',$imgb);
        // $databn = array('name' => $boards['bname'], );
        $this->assign('boards',$boards );
        // dump($user);
        $this->assign('bimg',$imgb);
        // dump($imgb);
        return $this->fetch('boards');
    }
    public function pins() {
        $a = new Index();
        $a->user();
        return $this->fetch('pins');
    }
    public function likes() {
        $a = new Index();
        $a->user();
        return $this->fetch('likes');
    }
    public function settings() {
        $uid = Session::get('uid');
        $u = db('user')->where('uid',$uid)->find();
        $this->assign('u',$u);
        return $this->fetch('settings');
    }
    public function changes() {
        $em = $_POST['em'];
        $eg = $_POST['eg'];
        $name = $_POST['name'];
        $ab =$_POST['ab'];
        $data = array(
            'mail' => $em,
            'gender'  => $eg,
            'name' => $name,
            'about' => $ab,
            );
        $user = db('user');
        $info = $user->where('uid',Session::get('uid'))->update($data);
        if ($info == NULL) {
            return ['status'=>0,'message'=>'update erro'];
        }else {
            return ['status'=>1,'message'=>'successful'];
        }
    }
    public function upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('img');
        // 移动到框架应用根目录/public/uploads/ 目录下
        $info = $file->rule('md5')->move(ROOT_PATH . 'public' . DS . 'uploads');
        // $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
        if($info){
            // 成功上传后 获取上传信息
            // 输出 jpg
            // echo $info->getExtension();
            // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            $url = $info->getSaveName();
            $un = ('uploads'."\\").$url;
            echo $un;
            $uid = Session::get('uid');
            $u = db('user')->where('uid',$uid)->update(['uimg' => $un]);
            // 输出 42a79759f284b767dfcb2a0197904287.jpg
            // echo $info->getFilename();
            // $data = array(
            //     'img' => $u,
            //     // 'name' => $info->getSaveName(),
            //     // 'aa' => $info->getFilename(),
            //     'status'=>0,
            //     );
            $this->redirect('settings');
        }else{
            // 上传失败获取错误信息
            echo $file->getError();
            return $file->getError();
        }
    }
    public function createboard() {
        $uid = Session::get('uid');
        $bname = $_POST['bname'];
        $datab = array(
            'uid' => $uid,
            'bname' => $bname,
            );
        $info = db('boards')->insert($datab);
        if ($info == null) {
            return $info->getError();
        }else {
            $this->redirect('boards');
        }
        dump($datab);
    }
    public function sImg() {
        $id = $_POST['id'];
        $img = db('img')->where('bid',$id)->order('id desc')->select();
        return ['data'=>$img];
    }
    //退出登陆
    public function logout() {
        session::clear();
        // session_unset();
        // session_destroy();
        $this -> redirect('Login/');
    }
    public function test() {
        // $id = $_POST['id'];
        $boards = db('boards')->where('uid',Session::get('uid'))->order('bid desc')->select();
        return ['me'=>$boards];
    }
    public function test2() {
        $id = $_POST['id'];
        $imgb = db('img')->where('uid',$id)->order('iid desc')->select();
        // dump($imgb);
        return ['me2'=>$imgb];
    }
}
?>