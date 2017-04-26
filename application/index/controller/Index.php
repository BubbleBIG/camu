<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
class Index
{
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
            return ['status'=>0,'message'=>'update error'];
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
            $url = str_replace('\\','/',$info->getSaveName());
            $un = ('uploads'."/").$url;
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
        // $uid = Session::get('uid');
        $uid = $_POST['id'];
        $bname = $_POST['bname'];
        $secret = $_POST['secret'];
        $map['bname'] = $bname ;
        $map['uid'] = $uid;
        $bNameFind = Db::name('boards')->where($map)->find();
        // if($bNameFind == NULL && $bname != NULL && $bname != 'boards' && $bname != 'pins' && $bname != 'likes') {
            if($bNameFind == NULL) {
            $datab = array(
                'uid' => $uid,
                'bname' => $bname,
                'secret' => $secret
                );
            // $info = db('boards')->insert($datab);
            $info = Db::name('boards')->insertGetId($datab); //insertGetId 方法添加数据成功返回添加数据的自增主键
            if ($info == null) {
                return json(['status' => 0, 'mess' => 'error']);
            }else {
                return json(['status' => 1, 'bname' => $bname, 'count' => 0, 'bid' => $info]);
            }
        } else {
            return json(['status' => 0, 'mess' => 'error1']);
        }
        // dump($datab);
    }
    public function changesboard() {
        $uid = Session::get('uid');
        $bid = $_POST['bid'];
        $bname = $_POST['bname'];
        $description = $_POST['description'];
        $datab = array(
            'bdescription' => $description,
            'bname' => $bname,
            );
        $map['bid'] = $bid;
        $map['uid'] = $uid;
        // dump($description);
        $info = db('boards')->where($map)->update($datab);
        if ($info == null) {
            return $info->getError();
        }else {
            $this->redirect('boards');
        }
        // dump($datab);
    }
    public function changecover() {
        $uid = $_POST['id'];
        $bid = $_POST['bid'];
        $delb['bid'] = $bid;
        $delb['uid'] = $uid;
        $imgforcover = db('img')->where($delb)->order('iid asc')->find();
        if ($imgforcover) {
            return json(
                ['iid' => $imgforcover['iid'],
                'bid' => $imgforcover['bid'],
                'url' => $imgforcover['url'],
                'status' => 1]
            );
        }else {
            return json(['mess'=>'error','status'=>0 ]);
        }
    }
    public function coverimg() {
        $uid = $_POST['id'];
        $iid = $_POST['iid'];
        $bid = $_POST['bid'];
        $e = $_POST['status'];
        if($e == 1) {
            $delb['iid'] = ['>',$iid];
            $delb['uid'] = $uid;
            $delb['bid'] = $bid;
            $imgforcover = db('img')->where($delb)->order('iid asc')->find();
            $del['iid'] = ['>',$imgforcover['iid']];
            $del['uid'] = $uid;
            $del['bid'] = $bid;
            $tag = db('img')->where($del)->order('iid asc')->find();
            if($tag) {
                $status = 2;
            } else { 
                $status = 3;
            }
        } else {
            $delb['iid'] = ['<',$iid];
            $delb['uid'] = $uid;
            $delb['bid'] = $bid;
            $imgforcover = db('img')->where($delb)->order('iid desc')->find();
            $del['iid'] = ['<',$imgforcover['iid']];
            $del['uid'] = $uid;
            $del['bid'] = $bid;
            $tag = db('img')->where($del)->order('iid desc')->find();
            if($tag) {
                $status = 2;
            } else {
                $status = 1;
            }
        }
        if ($imgforcover) {
            return json(
                ['iid' => $imgforcover['iid'],
                'bid' => $imgforcover['bid'],
                'url' => $imgforcover['url'],
                'status' => $status]
            );
        }else {
            return json(['mess'=>'error','status'=>0 ]);
        }
    }
    public function savecover() {
        $uid = $_POST['id'];
        $bid = $_POST['bid'];
        $url = $_POST['url'];
        $delb['bid'] = $bid;
        $delb['uid'] = $uid;
        $tag = db('boards')->where($delb)->update(['cover' => $url]);
        if ($tag) {
            return json(['status' => 1]);
        }else {
            return json(['mess'=>'error','status'=>0 ]);
        }
    }
    public function delboard() {
        $uid = $_POST['id'];
        $bid = $_POST['bid'];
        $delb['bid'] = $bid;
        $delb['uid'] = $uid;
        // return json(['mess'=>'successful!','status'=>1]);die;
        if($bid > 10) {
            $delboard = db('boards')->where($bid)->find();
            $delboardpins = db('img')->where($delb)->select();
            foreach($delboardpins as $url ) {
                if($url['iswebsite'] == 0) {
                    $file = ROOT_PATH .$url['url'];
                    unlink($file);
                    $delboardpin = db('img')->where('url',$url['url'])->delete();
                    // db('boards')->where('bid',$bid)->setDec('count',1);
                } else {
                    $delboardpin = db('img')->where('url',$url['url'])->delete();
                    // db('boards')->where('bid',$bid)->setDec('count',1);
                }
            }
            $delboard = db('boards')->where($delb)->delete();
            if ($delboard) {
            return json(['mess'=>'successful!','status'=>1]);
            }else {
                return json(['mess'=>'error!','status'=>0]);
            }
        } else {
            return json(['mess'=>'Warning! It is forbidden to del this board',
            'status'=>0]);
        }
    }
    public function test() {
        $text = $_POST['text'];
        $data = array('text' => $text);
        $boards = db('test')->insert($data);
        if ($boards) {
            return json(['insert'=>$text,'status'=>1]);
        }else {
            return json(['status'=>0]);
        }

    }
    public function test1() {
        $boards = db('test')->order('tid asc')->select();
        return json(['me'=>$boards]);
    }
    public function test2() {
        $boards = db('boards')->order('bid desc')->select();
        $count = count($boards);
        $obj = array();
        for($i=0;$i<$count;$i++) {
            if($boards[$i]['cover']>0) {
                $img = db('img')->where('bid',$boards[$i]['bid'])->order('bid desc')->limit(2)->select();
            } else {
                $img = db('img')->where('bid',$boards[$i]['bid'])->order('bid desc')->limit(6)->select();
            }
            // for($i=0;$i<$count;$i++) {
                $boards[$i]['aa'] = $img;
                $obj[$i] = $boards[$i];
            // }
            // dump($obj,"<br>");
        }
        return json($obj);
    }
    public function test4() {
        $b = uniqid();
        $n = mt_rand(0,9).mt_rand(0,9).mt_rand(0,9).mt_rand(0,9);
        $a = 'name'.$n;
        for($i=0;$i<10;$i++) {
            $data = mt_rand(0,9);
            // $data = 1000;
            $data1='';
            if($data !=5) {
                return json(['a'=>$data,'b'=>$i]);
            }
        }
        // return json($a);
    }
    public function test3($url,$save_dir='',$filename='',$type=0){
        if(trim($url)==''){
            return array('file_name'=>'','save_path'=>'','error'=>1);
        }
        if(trim($save_dir)==''){
            $save_dir='./';
        }
        if(trim($filename)==''){//保存文件名
            $ext=strrchr($url,'.');
            if($ext!='.gif'&&$ext!='.jpg'){
                return array('file_name'=>'','save_path'=>'','error'=>3);
            }
            $filename=time().$ext;
        }
        if(0!==strrpos($save_dir,'/')){
            $save_dir.='/';
        }
        //创建保存目录
        if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
            return array('file_name'=>'','save_path'=>'','error'=>5);
        }
        //获取远程文件所采用的方法 
        if($type){
            $ch=curl_init();
            $timeout=5;
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
            $img=curl_exec($ch);
            curl_close($ch);
        }else{
            ob_start(); 
            readfile($url);
            $img=ob_get_contents(); 
            ob_end_clean(); 
        }
        //$size=strlen($img);
        //文件大小 
        $fp2=@fopen($save_dir.$filename,'a');
        fwrite($fp2,$img);
        fclose($fp2);
        unset($img,$url);
        return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
    }
    public function getboards() {
        // $uid = Session::get('uid');
        $uid = $_POST['id'];
        $boards = db('boards')->where('uid',$uid)->order('bid desc')->select();
        $obj = array();
        $obj2 = array();
        $count = count($boards);
        for($i=0;$i<$count;$i++) {
            if($boards[$i]['cover']) {
                $img = db('img')->where('bid',$boards[$i]['bid'])->order('iid desc')->limit(2)->select();
            } else {
                $img = db('img')->where('bid',$boards[$i]['bid'])->order('iid desc')->limit(6)->select();
            }
            $boards[$i]['img'] = $img;
            $obj[$i] = $boards[$i];
                // $obj[$i] = $boards[$i]+$img;
            // dump($obj,"<br>");
        }
        $map['inviteduid'] = $uid;
        $map['status'] = 1;
        $invis = db('invite')->where($map)->order('invid desc')->select();
        $count = count($invis);
        for($i=0;$i<$count;$i++) {
            $boards = db('boards')->where('bid',$invis[$i]['bid'])->find();
                // $obj[$i] = $boards[$i]+$img;
            // dump($obj,"<br>");
        // }
        if($boards['secret']==='false') {
            $boards['invited'] = 1;
            $user = db('user')->where('uid',$boards['uid'])->find();
            $boards['name'] = $user['wname'];
            $boards['uimg'] = $user['uimg'];
            $obj2[$i] = $boards;
            if($obj2[$i]['cover']) {
                $img = db('img')->where('bid',$obj2[$i]['bid'])->order('iid desc')->limit(2)->select();
            } else {
                $img = db('img')->where('bid',$obj2[$i]['bid'])->order('iid desc')->limit(6)->select();
            }
            $obj2[$i]['img'] = $img;
            $j = count($obj);
            $obj[$j+$i+1] = $obj2[$i];
        }
                // $obj[$i] = $boards[$i]+$img;
            // dump($obj,"<br>");
        }
        return json($obj);
    }
    public function getboard() {
        // $uid = Session::get('uid');
        $bid = $_POST['bid'];
        $boards = db('boards')->where('bid',$bid)->find();
        // dump($boards);
        return json($boards);
    }
    public function getsave() {
        // $uid = Session::get('uid');
        $uid = $_POST['id'];
        $iid = $_POST['iid'];
        $data['uid'] = $uid;
        $data['oiid'] = $iid;
        $save= db('save')->where($data)->find();
        if($save) {
            return json($save);
        } else {
            return json(['oiid'=>0]);
        }
    }
    public function getcategory() {
        $category = db('category')->where(1)->select();
        return json($category);
    }
    public function saveboardchange() {
        $uid = $_POST['id'];
        $bid = $_POST['bid'];
        $bname = $_POST['bname'];
        $data1['uid'] = $uid;
        $data1['bid'] = $bid;
        $data = array(
            'bname' => $_POST['bname'],
            // 'category' => $_POST['category'],
            // 'bdescription' => $_POST['description'],
            'secret' => $_POST['secret']
        );
        if($_POST['description'] != 'null') {
            $data['bdescription'] = $_POST['description'];
        }
        if($_POST['category'] != 'null') {
            $data['category'] = $_POST['category'];
        }
        $checkName = db('boards')->where('bname',$bname)->find();
        if($checkName['bid'] != $bid && $checkName['uid'] == $uid) {
            return json(['status' => 0]);
        } else {
            $info = db('boards')->where($data1)->update($data);
            return json(['status' => 1,'info' => $info]);
        }

    }
    public function getpins() {
        // $uid = Session::get('uid');
        $uid = $_POST['id'];
        $imgb = db('img')->where('uid',$uid)->order('iid desc')->select();
        $imgb = db('img')->where(1)->order('iid desc')->select();
        return json($imgb);
    }
    public function getpin() {
        // $uid = Session::get('uid');
        $iid = $_POST['iid'];
        $imgb = db('img')->where('iid',$iid)->find();
        return json($imgb);
    }
    public function getboardpins() {
        $uname = $_POST['uname'];
        // $uid = $_POST['id'];
        $user = db('user')->where('wname',$uname)->find();
        if($user['uid'] == $_POST['id']) {
            $map['uid'] = $_POST['id'];
            $map['bname'] = $_POST['bname'];
            $board = db('boards')->where($map)->find();
            if($board) {
                $imgb = db('img')->where($map)->order('iid desc')->select();
                $board['name'] = $user['uname'];
                $board['img'] = $user['uimg'];
                return json(['status'=>1,'pins'=>$imgb,'board'=>$board]);
            } else {
                return json(['status'=>0,'pins'=>0,'board'=>0]);
                // $bname = db('boards')->where('bname',$_POST['bname'])->order('bid desc')->select();
                // $obj = array();
                // $count = count($bname);
                // $map2['inviteduid'] = $_POST['id'];
                // for($i=0;$i<$count;$i++){
                //     $map2['bid'] = $bname[$i]['bid'];
                //     $inv = db('invite')->where($map2)->find();
                // }
                // if($inv['status'] == 1) {
                //     $da['uid'] = $inv['uid'];
                //     $da['bid'] = $inv['bid'];
                //     $bo = db('boards')->where($da)->find();
                //     return json(['status'=>2,'pins'=>1,'board'=>1]);
                // } else {
                //     return json(['status'=>0,'pins'=>0,'board'=>0]);
                // }
            }
        } else {
            $map['uid'] = $user['uid'];
            $map['bname'] = $_POST['bname'];
            $board = db('boards')->where($map)->find();
            if($board) {
                $map2['uid'] = $user['uid'];
                $map2['inviteduid'] = $_POST['id'];
                $map2['bid'] = $board['bid'];
                $inv = db('invite')->where($map2)->find();
                if($inv) {
                    $da['uid'] = $inv['uid'];
                    $da['bid'] = $inv['bid'];
                    $bo = db('boards')->where($da)->find();
                    $imgb = db('img')->where($da)->order('iid desc')->select();
                    if($inv['status'] == 1){
                        $bo['invited'] = '1';
                    }
                    $bo['name'] = $user['uname'];
                    $bo['img'] = $user['uimg'];
                    return json(['status'=>2,'pins'=>$imgb,'board'=>$bo]);
                } else {
                    $da['uid'] = $user['uid'];
                    $da['bname'] = $_POST['bname'];
                    $bo = db('boards')->where($da)->find();
                    $imgb = db('img')->where($da)->order('iid desc')->select();
                    return json(['status'=>3,'pins'=>$imgb,'board'=>$bo]);
                }
            } else {
                return json(['status'=>0,'pins'=>2,'board'=>2]);
            }
        }
    }
    public function uploadPins1() {
        // $uid = Session::get('uid');
        // $name = isset($_POST['name'])? $_POST['name'] : '';
        // $gender = isset($_POST['gender'])? $_POST['gender'] : '';
        $filename = time().substr($_FILES['photo']['name'], strrpos($_FILES['photo']['name'],'.'));

        $response = array();

        if(move_uploaded_file($_FILES['photo']['tmp_name'], ROOT_PATH .'/public/tmp/'.$filename)){
            $response['isSuccess'] = true;
            $response['photo'] = $filename;
        }else{
            $response['isSuccess'] = false;
        }

        // echo json_encode($response);
        // return json($response);
    }
    public function uploadpintmp(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('photo');
        if($file !== NULL) {
        $info = $file->rule('uniqid')->move(ROOT_PATH . 'public/tmp/');
        if($info){
            $url = str_replace('\\','/',$info->getSaveName());
            $un = ('tmp'."/").$url;
            $unn = ('/public/tmp'."/").$info->getSaveName();
            return json(['status' => 1, 'url' => $unn]);
        }else{
            return json(['status' => 0]);
        }
        }
    }
    public function uploadpin(){
        $uid = $_POST['id'];
        $bid = $_POST['bid'];
        $bname = $_POST['bname'];
        $url = $_POST['url'];
        $height = $_POST['height'];
        $iswebsite = $_POST['iswebsite'];
        $idescription = $_POST['idescription'];
        $data = array(
            'uid' => $uid,
            'bid' => $bid,
            'bname' => $bname,
            'url' => $url,
            'height' => $height,
            'iswebsite' => $iswebsite,
            'idescription' => $idescription
            );
        if($data !== NULL) {
            if($url !== NULL && $iswebsite == 0) {
                $newurl = str_replace('tmp','temp',$url);
                $data['url'] = $newurl;
                // return json([$data]);
                $file = ROOT_PATH .$url; //旧目录
                $newFile = ROOT_PATH .$newurl; //新目录
                copy($file,$newFile); //拷贝到新目录
                unlink($file); //删除旧目录下的文件
                $info = db('img')->insertGetId($data); //获取自增id
                if($info) {
                    $count = db('boards')
                    ->where('bid',$bid)
                    ->setInc('count',1); //count自增1
                    if($count) {
                        return json(['status' => 1, 'mess' => 'success!', 'iid' => $info]);
                    } else {
                        return json(['tag' => 3,'status' => 0]);
                    }
                } else {
                    return json(['tag' => 1, 'status' => 0, 'mess' => 'error']);
                }
            } else {
                $info = db('img')->insertGetId($data);
                if($info) {
                    $count = db('boards')
                    ->where('bid',$bid)
                    ->setInc('count',1); //count自增1
                    return json(['status' => 1, 'mess' => 'success!']);
                } else {
                    return json(['tag' => 2, 'status' => 0, 'mess' => 'error']);
                }
            }
        } else {
            return json(['tag' => 0, 'mess' => 'error']);
        }
    }
    public function uploadpin2(){
        $uid = $_POST['id'];
        $bid = $_POST['bid'];
        $bname = $_POST['bname'];
        $url = $_POST['url'];
        $height = $_POST['height'];
        $iswebsite = $_POST['iswebsite'];
        $idescription = $_POST['idescription'];
        $data = array(
            'uid' => $uid,
            'bid' => $bid,
            'bname' => $bname,
            'url' => $url,
            'height' => $height,
            'iswebsite' => $iswebsite,
            'idescription' => $idescription
        );
        $data2 = array(
            'uid' => $uid,
            'oiid' => $_POST['iid']
        );
            // die;
        if($data !== NULL) {
            if($url !== NULL && $iswebsite == 0) {
                $url = str_replace('http://localhost/camu','',$url);
                $data['url'] = '/public/temp/'.uniqid().'.jpg';
                // return json([$data]);
                $file = ROOT_PATH .$url; //旧目录
                $newFile = ROOT_PATH .$data['url']; //新目录
                // return json(['a'=>$data['url'],'q'=>$file,'d'=>$newFile]);
                copy($file,$newFile); //拷贝到新目录
                $info = db('img')->insertGetId($data); //获取自增id
                if($info) {
                    $count = db('boards')
                    ->where('bid',$bid)
                    ->setInc('count',1); //count自增1
                    if($count) {
                        $checksave = db('save')->where($data2)->find();
                        if($checksave) {
                            return json(['status' => 1, 'mess' => 'success!', 'iid' => $info]);
                        } else {
                            $data2['bid'] = $bid;
                            $data2['bname'] = $bname;
                            $save = db('save')->insert($data2);
                            return json(['status' => 1, 'mess' => 'success!', 'iid' => $info]);
                        }
                    } else {
                        return json(['tag' => 3,'status' => 0]);
                    }
                } else {
                    return json(['tag' => 1, 'status' => 0, 'mess' => 'error']);
                }
            } else {
                $info = db('img')->insertGetId($data);
                if($info) {
                    $count = db('boards')
                    ->where('bid',$bid)
                    ->setInc('count',1); //count自增1
                    return json(['status' => 1, 'mess' => 'success!']);
                } else {
                    return json(['tag' => 2, 'status' => 0, 'mess' => 'error']);
                }
            }
        } else {
            return json(['tag' => 0, 'mess' => 'error']);
        }
    }
    public function getuserinfo() {
        $uid = $_POST['id'];
        $info = db('user')->where('uid',$uid)->find();
        return json($info);
        if($info) {
            return json(['email'=>$info]);
        }
    }
    public function checkuname() {
        $uid = $_POST['id'];
        $uname = $_POST['name'];
        // $map['uid'] = $uid;
        // $map['uname'] = $uname;
        $user = db('user')->where('uname',$uname)->find();
        if($user) {
            if($user['uid'] != $uid) {
                return json(['status'=>0]);
            } else {
                return json(['status'=>1]);
            }
        } else {
            return json(['status'=>1]);
        }
    }
    public function checkwname() {
        $uid = $_POST['id'];
        $wname = $_POST['name'];
        // $map['uid'] = $uid;
        // $map['uname'] = $uname;
        $user = db('user')->where('wname',$wname)->find();
        if($user && $wname != 'user') {
            if($user['uid'] != $uid) {
                return json(['status'=>0]);
            } else {
                return json(['status'=>1]);
            }
        } else {
            return json(['status'=>1]);
        }
    }
    public function updateinfo() {
        $uid = $_POST['id'];
        $data = array(
            'uname' => $_POST['uname'],
            'wname' => $_POST['wname'],
            'mail' => $_POST['mail'],
            'gender' => $_POST['gender'],
            'about' => $_POST['about']
        );
        $info = db('user')->where('uid',$uid)->update($data);
        if($info) {
            return json(['status'=>1,'name'=>$data['wname']]);
        } else {
            return json(['status'=>0]);
        }
    }
    public function changepw() {
        $uid = $_POST['id'];
        $pw = md5($_POST['pw']);
        $newpw = md5($_POST['newpw']);
        $data = array('pwd' => $newpw);
        $user = db('user')->where('uid',$uid)->find();
        if($pw == $user['pwd']) {
            $info = db('user')->where('uid',$uid)->update($data);
            return json(['status'=>1]);
        } else {
            return json(['status'=>0]);
        }
    }
    public function changeuserimg() {
        $uid = $_POST['id'];
        $uimg = $_POST['img'];
        $newurl = str_replace('tmp','temp/img',$uimg);
        $map['uimg'] = $newurl;
        $file = ROOT_PATH .$uimg; //旧目录
        $newFile = ROOT_PATH .$newurl; //新目录
        copy($file,$newFile); //拷贝到新目录
        unlink($file);
        $info = db('user')->where('uid',$uid)->update($map);
        if($info) {
            return json(['status'=>1,'url'=>$uimg]);
        } else {
            return json(['status'=>0]);
        }
    }
    public function like() {
        $uid = $_POST['id'];
        $iid = $_POST['iid'];
        $data['uid'] = $uid;
        $data['iid'] = $iid;
        $check = db('likes')->where($data)->find();
        if($check) {
            $info = db('likes')->where($data)->delete();
            if($info) {
                return json(['status' => 1]);
            } else {
                return json(['status' => 0, 'mess'=>'del erro']);
            }
        } else {
            $info = db('likes')->insert($data);
            if($info) {
                return json(['status' => 1]);
            } else {
                return json(['status' => 0, 'mess'=>'insert erro']);
            }
        }
    }
    public function likes() {
        $uid = $_POST['id'];
        // $iid = $_POST['iid'];
        $data['uid'] = $uid;
        // $data['iid'] = $iid;
        $check = db('likes')->where($data)->select();
        if($check) {
            return json(['status' => 1, 'mess'=>$check]);
        } else {
            return json(['status' => 0, 'mess'=>'erro']);
        }
    }
    public function getinvite() {
        $uid = $_POST['id'];
        $bid = $_POST['bid'];
        $wname = $_POST['uname'];
        $checkid = db('user')->where('wname',$wname)->find();
        $data['uid'] = $checkid['uid'];
        $data['bid'] = $bid;
        $check = db('invite')->where($data)->order('invid desc')->select();
        if($check) {
            $data['status'] = 1;
            $check2 = db('invite')->where($data)->order('invid desc')->select();
            $count2 = count($check);
            for($i=0;$i<$count2;$i++) {
                $img = db('user')->where('uid',$check[$i]['inviteduid'])->find();
                if($img) {
                    $check[$i]['img'] = $img['uimg'];
                }
            }
            $count = count($check2);
            return json(['status' => 1,'count' => $count,'mess'=>$check]);
        } else {
            return json(['status' => 0, 'count' => 0,'mess'=>'erro']);
        }
    }
    public function invite() {
        $uid = $_POST['id'];
        $bid = $_POST['bid'];
        $name = $_POST['name'];
        $check = db('user')->where('uname',$name)->find();
        $bcheck= db('boards')->where('bid',$bid)->find();
        if($check['uid'] == $bcheck['uid']) {
            return json(['status' => 2, 'mess'=>'error']);
        } else {
            if($check) {
                $data['uid'] = $bcheck['uid'];
                $data['inviteduid'] = $check['uid'];
                $data['inviteduname'] = $check['uname'];
                $data['bid'] = $bid;
                $info = db('invite')->where($data)->find();
                if($info) {
                    if($info['status'] == 1) {
                        return json(['status' => 1, 'mess'=>'already']);
                    } else {
                        $count['count'] = $info['count']+1;
                        // dump ($count);
                        $co = db('invite')->where($data)->update($count);
                        if($co) {
                            return json(['status' => 1, 'mess'=>'success']);
                        } else {
                            json(['status' => 0, 'mess'=>'update error']);
                        }
                    }
                } else {
                    $ncheck = db('user')->where('uid',$uid)->find();
                    $data['inviteruid'] = $uid;
                    $data['inviteruname'] = $ncheck['uname'];
                    $insert = db('invite')->insert($data);
                    if($insert) {
                        $data2['fromuid'] = $uid;
                        $data2['touid'] = $check['uid'];
                        $data2['news'] = $bid;
                        $new = db('invitenews')->insert($data2);
                        return json(['status' => 1, 'mess'=>'success']);
                    } else {
                        return json(['status' => 0, 'mess'=>'insert error']);
                    }
                }
            } else {
                return json(['status' => 0, 'mess'=>'error']);
            }
        }
    }
    public function checkuser() {
        $name = $_POST['name'];
        $info = db('user')->where('wname',$name)->find();
        if($info) {
            return json(['status'=>1,'name'=>$name,'id'=>$info['uid']]);
        } else {
            return json(['status'=>0]);
        }
    }
    public function unlinkDir() {
        $dir = ROOT_PATH .'/public/uploads/a0';
         //先删除目录下的文件：
      $dh=opendir($dir);
      while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
          $fullpath=$dir."/".$file;
          $file_time=@stat($dh);
          if(!is_dir($fullpath)) {
              unlink($fullpath);
          } else {
              deldir($fullpath);
          }
        }
      }    
      closedir($dh);
      //删除当前文件夹：
    //   if(rmdir($dir)) {
    //     echo '删除成功';
    //   } else {
    //     echo '删除失败';;
    //   }
    }
    public function delfile() {
        $dir = ROOT_PATH .'/public/tmp';
        $n = 60*60*24*20;
        //delfile("upload",10);
        if(is_dir($dir)) {
            if($dh=opendir($dir)) {
            while (false !== ($file = readdir($dh))) {
                if($file!="." && $file!="..") {
                    $fullpath=$dir."/".$file;
                    if(!is_dir($fullpath)) { 
                        //$filedate=date("Y-m-d", filemtime($fullpath));     
                        $filedate=date("Y-m-d h:i:s", filemtime($fullpath)); 
                //$d1=strtotime(date("Y-m-d")); 
                        $d1=strtotime(date("Y-m-d h:i:s"));
                        $d2=strtotime($filedate);
                        //$Days=round(($d1-$d2)/3600/24); 
                        $Days=round($d1-$d2);   
                        if($Days>$n)
                        unlink($fullpath);  ////删除文件

                        }
                    }      
                }
            }
        closedir($dh); 
        }
    }
    public function movefile() {
        $file = ROOT_PATH .'/public/tmp/12.jpg'; //旧目录
        $newFile = ROOT_PATH .'/public/tmp/1/2.jpg'; //新目录
        copy($file,$newFile); //拷贝到新目录
        unlink($file); //删除旧目录下的文件
    }

}
?>