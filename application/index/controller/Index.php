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
        for($i=0;$i<$count;$i++) { // 遍历每一个查询的结果
            if($boards[$i]['cover']) { // 判断是否有封面cover，有
                $img = db('img')->where('bid',$boards[$i]['bid'])->order('iid desc')->limit(2)->select();
            } else { // 判断是否有封面cover，否
                $img = db('img')->where('bid',$boards[$i]['bid'])->order('iid desc')->limit(6)->select();
            }
            $mapp['uid'] = $boards[$i]['uid'];
            $mapp['bid'] = $boards[$i]['bid'];
            $mapp['status'] = 1;
            $inv = db('invite')->where($mapp)->find();
            if($inv) { // 判断invite表是否存在符合要求的数据
                $user = db('user')->where('uid',$uid)->find();
                $boards[$i]['invited'] = 1;
                $boards[$i]['name'] = $user['wname'];
                $boards[$i]['uimg'] = $user['uimg'];
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
        for($i=0;$i<$count;$i++) { // 遍历每一个查询的结果
            $boards = db('boards')->where('bid',$invis[$i]['bid'])->find();
                // $obj[$i] = $boards[$i]+$img;
            // dump($obj,"<br>");
        // }
        if($boards) { // 如果boards有在invite表中邀请状态status为1的数据项
            $boards['invited'] = 1;
            $user = db('user')->where('uid',$boards['uid'])->find();
            $boards['name'] = $user['wname'];
            $boards['uimg'] = $user['uimg'];
            $obj2[$i] = $boards;
            if($obj2[$i]['cover']) { // 判断是否有封面cover，有
                $img = db('img')->where('bid',$obj2[$i]['bid'])->order('iid desc')->limit(2)->select();
            } else { // 判断是否有封面cover，无
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
        if($_POST['description'] != 'null') { // 如果传入的description不为空才插入数据
            $data['bdescription'] = $_POST['description'];
        }
        if($_POST['category'] != 'null') { // 如果传入的dcategory不为空才插入数据
            $data['category'] = $_POST['category'];
        }
        $checkName = db('boards')->where('bname',$bname)->find();
        if($checkName['bid'] != $bid && $checkName['uid'] == $uid) {
            // 如果没查到符合传入uid等于查询数据的uid的且传入的bid不等于查询的bid数据
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
        // $imgb = db('img')->where(1)->order('iid desc')->select();
        return json($imgb);
    }
    public function getpins1() {
        // $uid = Session::get('uid');
        $uid = $_POST['id'];
        $imgb = db('bo_img')->where('uid','<>',$uid)->order('iid desc')->select();
        // $imgb = db('img')->where(1)->order('iid desc')->select();
        return json($imgb);
    }
    public function getcategorypins() {
        $uid = $_POST['id'];
        $category= $_POST['category'];
        $imgb = db('bo_img')
        // $map['uid']  = ['<>',$uid];
        // $map['category']  = $category;
        ->where('uid','<>',$uid)
        ->where('category',$category)->order('iid desc')->select();
        // $imgb = db('img')->where(1)->order('iid desc')->select();
        return json($imgb);
    }
    public function getpin() {
        $iid = $_POST['iid'];
        $data['iid'] = $iid;
        $data['uid'] = $_POST['id'];
        $check = db('likes')->where($data)->find();
        $imgb = db('img')->where('iid',$iid)->find();
        $board = db('boards')->where('bid',$imgb['bid'])->find();
        if($check) {
            $imgb['pinsave'] = 1;
        }
        $imgb['category'] = $board['category'];
        return json($imgb);
    }
    public function getboardpins() {
        $uname = $_POST['uname'];
        // $uid = $_POST['id'];
        $user = db('user')->where('wname',$uname)->find();
        if($user['uid'] == $_POST['id']) {
        // 如果传入的uid等于传入的wname查询到的uid则判断为当前用户查询自己的相册数据
            $map['uid'] = $_POST['id'];
            $map['bname'] = $_POST['bname'];
            $board = db('boards')->where($map)->find();
            if($board) { // 如果用户存在这样额相册则返回查询数据,否则返回错误信息
                $imgb = db('img')->where('bid',$board['bid'])->order('iid desc')->select();
                $board['name'] = $user['uname'];
                $board['img'] = $user['uimg'];
                return json(['status'=>1,'pins'=>$imgb,'board'=>$board]);
            } else {
                return json(['status'=>0,'pins'=>0,'board'=>0]);
            }
        } else { // 当前用户查询他人的相册数据
            $map['uid'] = $user['uid'];
            $map['bname'] = $_POST['bname'];
            $board = db('boards')->where($map)->find();
            if($board) { // 如果被查询用户存在这样额相册则返回查询数据,否则返回错误信息
                $map2['uid'] = $user['uid'];
                $map2['inviteduid'] = $_POST['id'];
                $map2['bid'] = $board['bid'];
                $inv = db('invite')->where($map2)->find();
                if($inv) { // 如果该相册有当前用户被查询用户邀请记录，返回相应邀请信息
                    // $da['uid'] = $inv['uid'];
                    $da['bid'] = $inv['bid'];
                    $bo = db('boards')->where($da)->find();
                    $imgb = db('img')->where($da)->order('iid desc')->select();
                    if($inv['status'] == 1){ // 如果邀请的接受状态为真
                        $bo['invited'] = '1';
                    }
                    // for($i=0;)
                    $bo['name'] = $user['uname'];
                    $bo['img'] = $user['uimg'];
                    return json(['status'=>2,'pins'=>$imgb,'board'=>$bo]);
                } else { // 当前用户与被查询相册无任何联系的查询数据
                    $da['uid'] = $user['uid'];
                    $da['bname'] = $_POST['bname'];
                    $bo = db('boards')->where($da)->find();
                    $bo['name'] = $user['uname'];
                    $bo['img'] = $user['uimg'];
                    $imgb = db('img')->where($da)->order('iid desc')->select();
                    return json(['status'=>3,'pins'=>$imgb,'board'=>$bo]);
                }
            } else { // 不存在相册名
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
                $data['invitedwname'] = $check['wname'];
                $data['bid'] = $bid;
                $info = db('invite')->where($data)->find();
                if($info) {
                    if($info['status'] == 1) {
                        return json(['status' => 1, 'mess'=>'already']);
                    } else {
                        // $count['count'] = $info['count']+1;
                        // $co = db('invite')->where($data)->update($count);
                        // if($co) {
                            return json(['status' => 1, 'mess'=>'dupe']);
                        // } else {
                        //     json(['status' => 0, 'mess'=>'update error']);
                        // }
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
                        $data2['action'] = 'add';
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
    public function removeinvite() {
        $uid = $_POST['id'];
        $bid = $_POST['bid'];
        $toid = $_POST['toid'];
        $data['inviteduid'] = $uid;
        $data['bid'] = $bid;
        $check = db('invite')->where($data)->delete();
        if($check) {
            $data1['fromuid'] = $toid;
            $data1['touid'] = $uid;
            $data1['news'] = $bid;
            $data1['action'] = 'remove';
            $info = db('invitenews')->insert($data1);
            $data2['touid'] = $uid;
            $data2['news'] = $bid;
            $check2 = db('invitenews')->where($data2)->delete();
            return json(['status' => 1,'mess'=>'success']);
        } else {
            return json(['status' => 0,'mess'=>'erro']);
        }
    }
    public function getnewsnums() {
        $id = $_POST['id'];
        $data['touid'] = $id;
        $data['status'] = 0;
        $info = db('invitenews')->where($data)->select();
        if($info) {
            $nums = count($info);
            return json(['status'=>1,'nums'=>$nums]);
        } else {
            return json(['status'=>0]);
        }
    }
    public function getnews() {
        $id = $_POST['id'];
        $data['touid'] = $id;
        $data['status'] = 0;
        $data['action'] = 'add';
        $info = db('invitenews')->where($data)->order('innewsid desc')->select();
        if($info) { // 如果有未阅读的消息
            $nums = count($info);
            for($i=0;$i<$nums;$i++) { // 遍历查询记录
                $user = db('user')->where('uid',$info[$i]['fromuid'])->find();
                $board = db('boards')->where('bid',$info[$i]['news'])->find();
                if($user && $board) { // 只有两个查询都不为空时才返回查询数据
                    $info[$i]['new'] = $board;
                    $info[$i]['uname'] = $user['uname'];
                    $info[$i]['wname'] = $user['wname'];
                }
            }
            return json(['status'=>1,'mess'=>$info]);
        } else {
            return json(['status'=>0]);
        }
    }
    public function handlenews() {
        $id = $_POST['id'];
        $status = $_POST['status'];
        $da['news'] = $_POST['bid'];
        $da['touid'] = $id;
        $inv = db('invitenews')->where($da)->find();
        if($inv) { // 如果查询信息存在
            $data['inviteruid'] = $inv['fromuid'];
            $data['inviteduid'] = $inv['touid'];
            $data['bid'] =$inv['news'];
            if($status == 1) { // 接受invite
                $info = db('invite')->where($data)->update(['status'=>1]);
                if($info) {
                    $bo = db('boards')->where('bid',$inv['news'])->find();
                    $del= db('invitenews')->where('innewsid',$inv['innewsid'])->delete();
                    return json(['status'=>1,'mess'=>$bo]);
                } else {
                    return json(['status'=>2,'mess'=>21]);
                }
            } else { // 拒绝invite
                $info = db('invite')->where($data)->delete();
                if($info) {
                    $del= db('invitenews')->where('innewsid',$inv['innewsid'])->delete();
                    return json(['status'=>3,'mess'=>3]);
                } else {
                    return json(['status'=>2,'mess'=>22]);
                }
            }
        } else {
            return json(['status'=>0,'mess'=>0]);
        }
    }
    public function checkuser() {
        $name = $_POST['name'];
        $info = db('user')->where('wname',$name)->find();
        if($info) {
            return json(['status'=>1,'uname'=>$info['uname'],'name'=>$name,'id'=>$info['uid']]);
        } else {
            return json(['status'=>0]);
        }
    }
    public function getuname() {
        $name = $_POST['name'];
        $info = db('user')->where('wname',$name)->find();
        if($info) {
            return json(['status'=>1,'name'=>$info['uname']]);
        } else {
            return json(['status'=>0]);
        }
    }
    public function getsearch() {
        if($_POST['word']) {
            $word = '%'.$_POST['word'].'%';
            $user = db('userview')->where('uname','like',$word)->limit(5)->select();
            if($user) {
                $u = $user;
            } else {
                $u = '';
            }
            $board = db('userboards')->where('bname','like',$word)->limit(5)->select();
            if($board) {
                $b = $board;
            } else {
                $b = '';
            }
            return json(['user'=>$u,'board'=>$b]);
        } else {
            return json(['user'=>'','board'=>'']);
        }
    }
    public function getsearchboards() {
        $uid = $_POST['id'];
        $word = '%'.$_POST['word'].'%';
        $boards = db('boards')->where('bname','like',$word)->select();        
        $obj = array();
        $obj2 = array();
        $count = count($boards);
        for($i=0;$i<$count;$i++) { // 遍历每一个查询的结果
            if($boards[$i]['cover']) { // 判断是否有封面cover，有
                $img = db('img')->where('bid',$boards[$i]['bid'])->order('iid desc')->limit(2)->select();
            } else { // 判断是否有封面cover，否
                $img = db('img')->where('bid',$boards[$i]['bid'])->order('iid desc')->limit(6)->select();
            }
            $mapp['uid'] = $boards[$i]['uid'];
            $mapp['bid'] = $boards[$i]['bid'];
            $mapp['status'] = 1;
            $inv = db('invite')->where($mapp)->find();
            $user = db('user')->where('uid',$boards[$i]['uid'])->find();
            $boards[$i]['name'] = $user['wname'];
            $boards[$i]['uimg'] = $user['uimg'];
            if($inv) { // 判断invite表是否存在符合要求的数据
                // $user = db('user')->where('uid',$boards[$i]['uid'])->find();
                $boards[$i]['invited'] = 1;
                // $boards[$i]['name'] = $user['wname'];
                // $boards[$i]['uimg'] = $user['uimg'];
            }
            $boards[$i]['img'] = $img;
            $obj[$i] = $boards[$i];
                // $obj[$i] = $boards[$i]+$img;
            // dump($obj,"<br>");
        }
        // $map['inviteduid'] = $uid;
        // $map['status'] = 1;
        // $invis = db('invite')->where($map)->order('invid desc')->select();
        // $count = count($invis);
        // for($i=0;$i<$count;$i++) { // 遍历每一个查询的结果
        //     $boards = db('boards')->where('bid',$invis[$i]['bid'])->find();
        //         // $obj[$i] = $boards[$i]+$img;
        //     // dump($obj,"<br>");
        // // }
        // if($boards) { // 如果boards有在invite表中邀请状态status为1的数据项
        //     $boards['invited'] = 1;
        //     $user = db('user')->where('uid',$boards['uid'])->find();
        //     $boards['name'] = $user['wname'];
        //     $boards['uimg'] = $user['uimg'];
        //     $obj2[$i] = $boards;
        //     if($obj2[$i]['cover']) { // 判断是否有封面cover，有
        //         $img = db('img')->where('bid',$obj2[$i]['bid'])->order('iid desc')->limit(2)->select();
        //     } else { // 判断是否有封面cover，无
        //         $img = db('img')->where('bid',$obj2[$i]['bid'])->order('iid desc')->limit(6)->select();
        //     }
        //     $obj2[$i]['img'] = $img;
        //     $j = count($obj);
        //     $obj[$j+$i+1] = $obj2[$i];
        // }
        //         // $obj[$i] = $boards[$i]+$img;
        //     // dump($obj,"<br>");
        // }
        return json($obj);
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