<?php
namespace app\web\controller;
use app\index\model\Book;
use app\index\model\Guestbook;
use think\Controller;
use think\Request;
use think\Route;
use think\Cookie;
use app\index\model\Area;
use app\index\model\Agent;
use app\index\model\Sort;
use app\index\model\Sorttype;
use app\index\model\Article;
use app\index\model\Sysinfo;
use app\index\model\Member;
use app\index\model\Comment;
use app\index\model\Resume;
use app\index\model\Headart;
use app\index\model\Headsort;
use app\index\model\Category;
use app\index\model\Cateart;
use app\index\model\Field;
use app\index\model\Mould;
use app\index\model\Complaint;
use app\index\model\MoneyLog;
use app\index\model\Message;
use lib\Form;



use Wechat\WechatOauth;

class Index extends Controller
{
	public $title='爱臣同镇';
	public $size =10;//每页数量
    public $aid;
	public function _initialize()
	{

	}

    public function checkCookie()
    {
        $this->aid = Cookie::get('aid');
        if (empty($this->aid))
        {
            header('location:' . url('/web/index/select'));exit;
        }
    }

	public function index($sid=0)
    {

        $aid = Request::instance()->param('aid');
        if (empty($aid))
        {
            $this->checkCookie();
            $aid = $this->aid;
        }

        Cookie::set('aid',$aid);
        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        $headsort = Headsort::all();
        $this->assign('headsort', $headsort);
        $this->assign('sid', $sid);

        //系统配置
        $sysinfo = Sysinfo::get(1);
        $this->assign('sysinfo', $sysinfo);

        if($sid==0)
        {
            $headart = Headart::whereOr('aid','-1')->whereOr('aid',$aid)->order('update','desc')->limit($this->size)->select();
        }else{
            $headart = Headart::whereOr('aid','-1')->whereOr('aid',$aid)->where('sid', $sid)->order('update','desc')->limit($this->size)->select();
        }

        foreach ($headart as $k=>$item) {
            $headart[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }

            $headart[$k]['imgs'] = $match[1];
            $headart[$k]['imgs_num'] = count($match[1]);
        }
        $this->assign('headart', $headart);


        //置顶信息
        $h = new Headart;

        if($sid==0)
        {
            $headartzd = $h->whereOr('aid','-1')->whereOr('aid',$aid)->where('recommend',1)->order('update','desc')->limit($this->size)->select();
        }else{
            $headartzd = $h->whereOr('aid','-1')->whereOr('aid',$aid)->where('sid', $sid)->where('recommend',1)->order('update','desc')->limit($this->size)->select();
        }



        foreach ($headartzd as $k=>$item) {
            $headartzd[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }

            $headartzd[$k]['imgs'] = $match[1];
            $headartzd[$k]['imgs_num'] = count($match[1]);
        }
        $this->assign('headartzd', $headartzd);


        $request = Request::instance();
        $this->assign('act', $request->controller());

        $this->assign('title','系统首页-'.$this->title);

        return view('index');
    }
    public function getindexAjax($page)
    {
        $this->checkCookie();
        $aid = $this->aid;
        $data = array();
        $headart = Headart::whereOr('aid','-1')->whereOr('aid',$aid)->order('update','desc')->limit($page*$this->size,$this->size)->select();
        foreach ($headart as $k=>$item) {
            $data[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }
            $data[$k]['imgs'] = $match[1];
            $data[$k]['imgs_num'] = count($match[1]);
            $data[$k]['title'] = $item['title'];
            $data[$k]['click'] = $item['click'];
            $data[$k]['id'] = $item['id'];
        }

        echo json_encode($data);
    }

    public function searchs()
    {
        $this->checkCookie();
        $aid = $this->aid;
        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        //代理二维码
        $agent = Agent::get(['aid' => $aid]);
        $this->assign('agent', $agent);

        $keys = trim(Request::instance()->param('keys'));
        $this->assign('keys', $keys);


        //信息列表
        $cateart = Cateart::whereOr('aid','-1')->whereOr('aid', $aid)->where('title','like','%'.$keys.'%')->order('update','desc')->limit(10)->select();

        $data = getCateArtList($cateart);
        $this->assign('cateart', $data);

        return view('searchs');
    }

    //加载搜索信息
    public function searchsAjax($pid)
    {
        $this->checkCookie();
        $aid = $this->aid;

        $keys = trim(Request::instance()->param('keys'));
        $this->assign('keys', $keys);

        $cateart =  Cateart::where('title','like','%'.$keys.'%')->where('aid', $aid)->order('update','desc')->limit($pid*$this->size, 10)->select();

        $data = getCateArtList($cateart);

        echo json_encode($data);
    }

	/**
	 * 系统首页
	 * @return \think\response\View
	 */
    public function index1($aid=0)
    {

		$aid = Request::instance()->param('aid');
        if (empty($aid))
        {
            $this->checkCookie();
            $aid = $this->aid;
        }

		Cookie::set('aid',$aid);

        //系统配置
        $sysinfo = Sysinfo::get(1);
        $this->assign('sysinfo', $sysinfo);

        //进入幸福门人数
        $act =  Request::instance()->param('act');
        if (!empty($act))
        {
            $sysinfo->p_number++;
            $sysinfo->save();
        }

		//处理地区
		$area = Area::get($aid);
		$this->assign('area', $area);



        //头条
        $headart = Headart::order('update','desc')->limit(6)->select();
        $this->assign('headart', $headart);

		//代理二维码
		$agent = Agent::get(['aid' => $aid]);
		$this->assign('agent', $agent);

		//顶级栏目排序
		$sort = Sort::where('parent_id', 0)->order('rank', 'asc')->select();
		$this->assign('sort', $sort);

		//文章数量
		$count = Article::where('aid','=',$aid)->count();
		$this->assign('count', $count);

		//初始显示文章
		$article = Article::where('aid','=',$aid)->order('addtime', 'DESC')->limit($this->size)->select();
		//时间预处理
		foreach($article as $key=>$val)
		{
			$article[$key]['addtime'] = time_tran($val['addtime']);
			if($val['picjson'] <> '')
			{
				$arr = explode(",",$val['picjson']);
				$arr = array_filter($arr);
				$article[$key]['img']=$arr[1];
			}else{
				$article[$key]['img']='';
			}
		}

		$this->assign('article', $article);

    	$request = Request::instance();
    	$this->assign('act', $request->controller());

    	$this->assign('title','系统首页-'.$this->title);

    	return view('index1');
    }

    public function index2()
    {
        $this->checkCookie();
        $aid = $this->aid;
        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        //代理二维码
        $agent = Agent::get(['aid' => $aid]);
        $this->assign('agent', $agent);

        //顶级类目
        $category = Category::all(['pid'=>0]);
        $this->assign('category', $category);

        //头条
        $message = Message::order('update','desc')->limit(6)->select();
        foreach ($message as $key=>$val)
        {
            $message[$key]['title'] = $val['aid'] .' '. $val['name'] .' '. $val['pro'];
        }
        $this->assign('message', $message);

        //类目信息
        $cateart1 =  Cateart::order('update','desc')->limit(10)->select();
        $cateart = array();
        foreach ($cateart1 as $k=>$item) {
            $cateart[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }
            $cateart[$k]['cid'] = $item['cid'];
            $cateart[$k]['imgs'] = $match[1];
            $cateart[$k]['imgs_num'] = count($match[1]);
            $cateart[$k]['title'] = $item['title'];
            $cateart[$k]['click'] = $item['click'];
            $cateart[$k]['id'] = $item['id'];
            $cateart[$k]['url'] = '/web/index/hartdetail/id/'.$item['id'];
        }
        $this->assign('cateart', $cateart);

        $request = Request::instance();
        $this->assign('act', $request->controller());

        $this->assign('title','系统首页-'.$this->title);

        return view('index2');
    }

    public function cartList($cid =0,$level=1)
    {
        $this->checkCookie();
        $aid = $this->aid;

        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        //代理二维码
        $agent = Agent::get(['aid' => $aid]);
        $this->assign('agent', $agent);

        $temp = Category::get($cid);
        //判断类目是否存在
        if(empty($temp))
        {
            $this->error('要查看的类目不存在');
        }
        $temp['level'] = $level;
        $this->assign('temp', $temp);

        $catelist = Category::all(['pid'=>$cid]);
        $this->assign('catelist', $catelist);

        $cat = new Category();
        $ids = $cat->getAllChildcateIds($cid);

        //信息列表
        $cateart = Cateart::whereIn('cid',$ids)->order('update','desc')->limit(6)->select();
        foreach ($cateart as $k=>$item) {
            $cateart[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }
            $cateart[$k]['imgs'] = $match[1];
            $cateart[$k]['imgs_num'] = count($match[1]);
        }
        $this->assign('cateart', $cateart);

        //置顶信息
        $cateartzd = Cateart::whereIn('cid',$ids)->where('recommend',1)->order('update','desc')->limit(6)->select();
        foreach ($cateartzd as $k=>$item) {
            $cateartzd[$k]['update'] = time_tran($item['update']);

            //初始化图片
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }
            $cateartzd[$k]['imgs'] = $match[1];
            $cateartzd[$k]['imgs_num'] = count($match[1]);

            //判断用户余额不足时清除记录
            $member =  Member::get($item->getData('mid'));
            $sysinfo = Sysinfo::get(1);
            if($member['money'] <= $sysinfo['stickprice'] )//用户余额小于置顶金额
            {
                //清除记录
                unset($cateartzd[$k]);
            }
        }

        $this->assign('cateartzd', $cateartzd);

        return view('catlist');
    }
    //加载类目信息
    public function cartListAjax($cid, $pid)
    {
        $this->checkCookie();
        $aid = $this->aid;
        if($cid == 0) {
            $cateart =  Cateart::order('update','desc')->limit($pid*$this->size,$this->size)->select();
        }else{
            $cat = new Category();
            $ids = $cat->getAllChildcateIds($cid);

            $cateart =  Cateart::whereIn('cid',$ids)->order('update','desc')->limit($pid*$this->size, $this->size)->select();
        }

        $data = array();
        foreach ($cateart as $k=>$item) {
            $data[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }
            $data[$k]['cid'] = $item['cid'];
            $data[$k]['imgs'] = $match[1];
            $data[$k]['imgs_num'] = count($match[1]);
            $data[$k]['title'] = $item['title'];
            $data[$k]['click'] = $item['click'];
            $data[$k]['id'] = $item['id'];
            $data[$k]['url'] = '/web/index/cartdetail/id/'.$item['id'];
        }
        echo json_encode($data);
    }

    public function cartdetail($id=0,$type=0)
    {
        $this->checkCookie();
        $aid = $this->aid;

        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        //代理二维码
        $agent = Agent::get(['aid' => $aid]);
        $this->assign('agent', $agent);

        $temp = Cateart::get($id);
        //判断类目是否存在
        if(empty($temp))
        {
            $this->error('要查看的类目文章不存在');
        }

        //增加点击次数
        $temp->click ++;
        $temp->save();

        $member =  Member::get($temp->getData('mid'));

        //判断发布信息的企业是否存在
        if(empty($member))
        {
            $this->error('发布信息的企业不存在');
        }

        if($member['status'] == 0)
        {
            $temp['status'] = 0;
        }else{
            $temp['status'] = $member['status'];
            $temp['zj'] = $member['zj'];
        }




        if($type==0)
        {
            //检查用户余额并扣除浏览单价浏览单价
            $this->delMoneyByCateart($temp);
        }elseif($type==1)
        {
            //检查用户余额并扣除置顶单价浏览单价
            $this->delMoneyByCateartTop($temp);
        }else{
            $this->error('要查看的类目文章不存在');
        }

        $temp['update'] = time_tran($temp['update']);
        $this->assign('temp', $temp);



        //更多信息
        $cateart = Cateart::where('cid',$temp->getData('cid'))->order('update','desc')->limit(6)->select();
        foreach ($cateart as $k=>$item) {
            $cateart[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }
            $cateart[$k]['imgs'] = $match[1];
            $cateart[$k]['imgs_num'] = count($match[1]);
        }
        $this->assign('cateart', $cateart);


        return view('');
    }


    //头条列表页
    public function hartlist($sid=0)
    {
        $this->checkCookie();
        $aid = $this->aid;

        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);


        $headsort = Headsort::all();
        $this->assign('headsort', $headsort);
        $this->assign('sid', $sid);

        if($sid==0)
        {
            $headart = Headart::whereOr('aid','-1')->whereOr('aid',$aid)->order('update','desc')->limit($this->size)->select();
        }else{
            $headart = Headart::whereOr('aid','-1')->whereOr('aid',$aid)->where('sid', $sid)->order('update','desc')->limit($this->size)->select();
        }


        foreach ($headart as $k=>$item) {
            $headart[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }
            $headart[$k]['imgs'] = $match[1];
            $headart[$k]['imgs_num'] = count($match[1]);
        }

        $this->assign('headart', $headart);
        return view('hartlist');
    }

    //头条列表页
    public function hartlistajax($hid, $pid)
    {
        $this->checkCookie();
        $aid = $this->aid;


        $headart = Headart::whereOr('aid','-1')->whereOr('aid',$aid)->where('sid', $hid)->order('update','desc')->limit($pid*$this->size, 10)->select();
        $data = array();
        foreach ($headart as $k=>$item) {
            $data[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }
            $data[$k]['imgs'] = $match[1];
            $data[$k]['imgs_num'] = count($match[1]);
            $data[$k]['title'] = $item['title'];
            $data[$k]['click'] = $item['click'];
            $data[$k]['id'] = $item['id'];
            $data[$k]['url'] = '/web/index/hartdetail/id/'.$item['id'];
        }
        //echo $hid.'   '.$pid ;
        echo json_encode($data);
    }

    //头条详情页
    public function hartdetail($id=0)
    {
        $this->checkCookie();
        $agent = Agent::get(['aid'=>$this->aid]);
        $this->assign('agent', $agent);
        $headart = Headart::get(['id' => $id]);


        //判断模型是否存在
        if(empty($headart))
        {
            $this->error('要查看的头条的不存在');
        }
        $headart->click ++;
        $headart->save();

        $headart['update'] = time_tran($headart['update']);
        $this->assign('temp', $headart);


        return view('hartdetail');
    }

    //周边留言
    public function message()
    {
        //预定义模块
        $mould= Mould::get(['table'=>'message']);
        $field = Field::where(['mid'=>$mould->id])->order('rank')->select();


        //处理select
        $category = array();
        $psort = new Category();
        $le = 3;
        $psort->getTreeLevel(0,$category, '  ',$le);
        $this->assign('category',$category);

        //是否为提交表单
        if (Request::instance()->isPost())
        {
            $message           = new Message();
            foreach ($field as $val)
            {
                $message->$val['fieldname'] = Request::instance()->post($val['fieldname']);
            }
            $message->mid = 1;
            $message->update = time();
            $message->save();
            $this->success('添加成功！');
        }


        //初始化表单
        $form = new Form();
        $formhtml = array();
        foreach ($field as $val)
        {

            if($val['fieldname'] == 'aid')
            {
                $name = $val['fieldname'];
                $val['fieldname'] = '';
                $temp['aid'] = 370829104;//370829104疃里镇
                $arr=array();
                $area = new Area;
                $area->getAreaTypeArr($arr,$temp['aid']);

                //地区
                //省
                $area1 = Area::all(['level'=>1,'parent_id'=>0]);
                $areadb1 = array();
                foreach ($area1 as $v)
                {
                    $areadb1[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb1;
                $ahtml1 = $form->fieldToForm($val,'form-control','area1',$arr[1]);

                //市
                $area2 = Area::all(['level'=>2,'parent_id'=>$arr[1]]);
                $areadb2 = array();
                foreach ($area2 as $v)
                {
                    $areadb2[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb2;
                $ahtml2 = $form->fieldToForm($val,'form-control','area2',$arr[2]);

                //县
                $area3 = Area::all(['level'=>3,'parent_id'=>$arr[2]]);
                $areadb3 = array();
                foreach ($area3 as $v)
                {
                    $areadb3[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb3;
                $ahtml3 = $form->fieldToForm($val,'form-control','area3',$arr[3]);

                //镇
                $area4 = Area::all(['level'=>4,'parent_id'=>$arr[3]]);
                $areadb4 = array();
                foreach ($area4 as $v)
                {
                    $areadb4[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb4;
                $ahtml4 = $form->fieldToForm($val,'form-control','area4',$arr[4]);


                $arr['html'] ='<div class="col-sm-3">';
                $arr['html'] .= $ahtml1;
                $arr['html'] .= '</div>';

                $arr['html'] .='<div class="col-sm-3">';
                $arr['html'] .= $ahtml2;
                $arr['html'] .= '</div>';

                $arr['html'] .='<div class="col-sm-3">';
                $arr['html'] .= $ahtml3;
                $arr['html'] .= '</div>';

                $arr['html'] .='<div class="col-sm-3">';
                $arr['html'] .= $ahtml4;
                $arr['html'] .= '</div>';

                $arr['html'] .= '<input type="hidden" name="'.$name.'" value="'.$arr[4].'" id="area">';

            }elseif ($val['fieldname'] == 'cid' || $val['fieldname'] == 'mid'){
                continue;
            } else {
                $arr['html'] = $form->fieldToForm($val,'form-control');
            }

            $arr['itemname'] = $val['itemname'];
            $arr['fieldname'] = $val['fieldname'];

            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);
        return view('message');
    }

    //代理留言
    public function guestbook()
    {
        //预定义模块
        $mould= Mould::get(['table'=>'guestbook']);
        $field = Field::where(['mid'=>$mould->id])->order('rank')->select();

        //处理select
        $category = array();
        $psort = new Category();
        $le = 3;
        $psort->getTreeLevel(0,$category, '  ',$le);
        $this->assign('category',$category);

        //是否为提交表单
        if (Request::instance()->isPost())
        {
            $guestbook     = new Guestbook();
            foreach ($field as $val)
            {
                $guestbook->$val['fieldname'] = Request::instance()->post($val['fieldname']);
            }
            $guestbook->update = time();
            $guestbook->save();
            $this->success('添加成功！');
        }


        //初始化表单
        $form = new Form();
        $formhtml = array();
        foreach ($field as $val)
        {

            if($val['fieldname'] == 'aid')
            {
                $name = $val['fieldname'];
                $val['fieldname'] = '';
                $temp['aid'] = 370829104;//370829104疃里镇
                $arr=array();
                $area = new Area;
                $area->getAreaTypeArr($arr,$temp['aid']);

                //地区
                //省
                $area1 = Area::all(['level'=>1,'parent_id'=>0]);
                $areadb1 = array();
                foreach ($area1 as $v)
                {
                    $areadb1[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb1;
                $ahtml1 = $form->fieldToForm($val,'form-control','area1',$arr[1]);

                //市
                $area2 = Area::all(['level'=>2,'parent_id'=>$arr[1]]);
                $areadb2 = array();
                foreach ($area2 as $v)
                {
                    $areadb2[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb2;
                $ahtml2 = $form->fieldToForm($val,'form-control','area2',$arr[2]);

                //县
                $area3 = Area::all(['level'=>3,'parent_id'=>$arr[2]]);
                $areadb3 = array();
                foreach ($area3 as $v)
                {
                    $areadb3[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb3;
                $ahtml3 = $form->fieldToForm($val,'form-control','area3',$arr[3]);

                //镇
                $area4 = Area::all(['level'=>4,'parent_id'=>$arr[3]]);
                $areadb4 = array();
                foreach ($area4 as $v)
                {
                    $areadb4[$v['id']] = $v['name'];
                }
                $val['vdefault'] = $areadb4;
                $ahtml4 = $form->fieldToForm($val,'form-control','area4',$arr[4]);


                $arr['html'] ='<div class="col-sm-3">';
                $arr['html'] .= $ahtml1;
                $arr['html'] .= '</div>';

                $arr['html'] .='<div class="col-sm-3">';
                $arr['html'] .= $ahtml2;
                $arr['html'] .= '</div>';

                $arr['html'] .='<div class="col-sm-3">';
                $arr['html'] .= $ahtml3;
                $arr['html'] .= '</div>';

                $arr['html'] .='<div class="col-sm-3">';
                $arr['html'] .= $ahtml4;
                $arr['html'] .= '</div>';

                $arr['html'] .= '<input type="hidden" name="'.$name.'" value="'.$arr[4].'" id="area">';

            }elseif ($val['fieldname'] == 'cid' || $val['fieldname'] == 'mid'){
                continue;
            } else {
                $arr['html'] = $form->fieldToForm($val,'form-control');
            }

            $arr['itemname'] = $val['itemname'];
            $arr['fieldname'] = $val['fieldname'];

            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);
        return view('');
    }


    //商家通讯录
    public function bookslist($type=-1)
    {
        $this->assign('type', $type);

        $this->checkCookie();
        $aid = $this->aid;

        //获取会员信息
        $mid = $this->getMidByOpenid();
        $member = Member::get(['id' => $mid]);

        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        //代理二维码
        $agent = Agent::get(['aid' => $aid]);
        $this->assign('agent', $agent);

        //商家类型
        $mould= Mould::get(['table'=>'book']);
        $field = Field::where(['mid'=>$mould->id])->where(['fieldname'=>'type'])->order('rank')->find();
        $typelist = explode(',',$field['vdefault']);
        $this->assign('typelist', $typelist);

        if($type < 0)
        {
            $books = Book::where('cid',$member['cid'])->order('update','desc')->limit($this->size)->select();
        }else{
            $books = Book::where('cid',$member['cid'])->where('type',$type)->order('update','desc')->limit($this->size)->select();
        }

        foreach ($books as $k=>$val)
        {
            $books[$k]['update'] = time_tran($val['update']);
        }

        $this->assign('list', $books);
        return view('bookslist');
    }

    //加载商家通讯录
    public function bookslistAjax($type,$pid)
    {
        $this->checkCookie();

        //获取会员信息
        $mid = $this->getMidByOpenid();
        $member = Member::get(['id' => $mid]);

        if($type < 0)
        {
            //$books = Book::where('cid',$member['cid'])->order('update','desc')->limit($this->size)->select();
            $books = Book::where('cid',$member['cid'])->order('update','desc')->limit($pid*$this->size,$this->size)->select();
        }else{
            $books = Book::where('cid',$member['cid'])->where('type',$type)->order('update','desc')->limit($pid*$this->size,$this->size)->select();
        }

        $data = array();
        foreach ($books as $k=>$val)
        {
            $data[$k]['update'] = time_tran($val['update']);
            $data[$k]['merchant'] = $val['merchant'];
            $data[$k]['id'] = $val['id'];
            $data[$k]['cid'] = $val['cid'];
            $data[$k]['shopimg'] = $val['shopimg'];
        }

        echo json_encode($data);
    }

    //商家通讯录详情
    public function booksdetail($id=0)
    {
        $this->checkCookie();
        $aid = $this->aid;

        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        //代理二维码
        $agent = Agent::get(['aid' => $aid]);
        $this->assign('agent', $agent);

        $temp = Book::get($id);
        //判断类目是否存在
        if(empty($temp))
        {
            $this->error('要查看的商家通讯录不存在');
        }

        //获取会员信息
        $mid = $this->getMidByOpenid();


        //检查用户余额并扣除商家通讯录单价
        $this->delMoneyByBooks($temp, $mid);

        $temp['update'] = time_tran($temp['update']);
        $this->assign('temp', $temp);


        //更多信息
        $cateart = Cateart::where('aid', $aid)->order('update','desc')->limit(6)->select();
        foreach ($cateart as $k=>$item) {
            $cateart[$k]['update'] = time_tran($item['update']);
            $match = array();
            preg_match_all('/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png|jpeg))\"?.+>/isU',$item['body'],$match);
            foreach ($match[1] as $key=>$val)
            {
                $match[1][$key] = str_replace('"',"",$val);
            }
            $cateart[$k]['imgs'] = $match[1];
            $cateart[$k]['imgs_num'] = count($match[1]);
        }
        $this->assign('cateart', $cateart);


        return view('booksdetail');
    }

    //周边留言
    public function messageslist()
    {

        $this->checkCookie();
        $aid = $this->aid;

        //获取会员信息
        $mid = $this->getMidByOpenid();
        $member = Member::get(['id' => $mid]);

        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        //代理二维码
        $agent = Agent::get(['aid' => $aid]);
        $this->assign('agent', $agent);

        $message = Message::where('cid',$member['cid'])->order('update','desc')->limit($this->size)->select();

        foreach ($message as $k=>$val)
        {
            $message[$k]['update'] = time_tran($val['update']);
        }

        $this->assign('list', $message);
        return view('messageslist');
    }

    //加载周边留言
    public function messageslistAjax($pid)
    {
        $this->checkCookie();

        //获取会员信息
        $mid = $this->getMidByOpenid();
        $member = Member::get(['id' => $mid]);

        $message = Message::where('cid',$member['cid'])->order('update','desc')->limit($pid*$this->size,$this->size)->select();

        $data = array();
        foreach ($message as $k=>$val)
        {
            $data[$k]['update'] = time_tran($val['update']);
            $data[$k]['id'] = $val['id'];
            $data[$k]['name'] = $val['name'];
            $data[$k]['aid'] = $val['aid'];
            $data[$k]['pro'] = $val['pro'];
        }
        echo json_encode($data);
    }

    //商家通讯录详情
    public function messagesdetail($id=0)
    {
        $this->checkCookie();
        $aid = $this->aid;

        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        //代理二维码
        $agent = Agent::get(['aid' => $aid]);
        $this->assign('agent', $agent);

        $temp = Message::get($id);
        //判断类目是否存在
        if(empty($temp))
        {
            $this->error('要查看的周边留言不存在');
        }

        //获取会员信息
        $mid = $this->getMidByOpenid();

        //检查用户余额是否为空
        $member = Member::get(['id' => $mid]);
        if($member['money'] <= 0)
        {
            $this->error('您的余额不足，请充值后再试！');
        }

        $temp['update'] = time_tran($temp['update']);
        $this->assign('temp', $temp);

        return view('messagesdetail');
    }

    //代理留言
    public function guestbooklist()
    {

        $this->checkCookie();
        $aid = $this->aid;

        //获取会员信息
        $mid = $this->getMidByOpenid();
        $member = Member::get(['id' => $mid]);

        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        //代理二维码
        $agent = Agent::get(['aid' => $aid]);
        $this->assign('agent', $agent);

        $guestbook = Guestbook::where('cid',$member['cid'])->order('update','desc')->limit($this->size)->select();

        foreach ($guestbook as $k=>$val)
        {
            $guestbook[$k]['update'] = time_tran($val['update']);
        }

        $this->assign('list', $guestbook);
        return view('');
    }

    //加载周边留言
    public function guestbookAjax($pid)
    {
        $this->checkCookie();

        //获取会员信息
        $mid = $this->getMidByOpenid();
        $member = Member::get(['id' => $mid]);

        $guestbook = Guestbook::where('cid',$member['cid'])->order('update','desc')->limit($pid*$this->size,$this->size)->select();

        $data = array();
        foreach ($guestbook as $k=>$val)
        {
            $data[$k]['update'] = time_tran($val['update']);
            $data[$k]['id'] = $val['id'];
            $data[$k]['name'] = $val['name'];
            $data[$k]['aid'] = $val['aid'];
            $data[$k]['description'] = $val['description'];
        }
        echo json_encode($data);
    }

    //商家通讯录详情
    public function guestbookdetail($id=0)
    {
        $this->checkCookie();
        $aid = $this->aid;

        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        //代理二维码
        $agent = Agent::get(['aid' => $aid]);
        $this->assign('agent', $agent);

        $temp = Guestbook::get($id);
        //判断类目是否存在
        if(empty($temp))
        {
            $this->error('要查看的周边留言不存在');
        }

        //获取会员信息
        $mid = $this->getMidByOpenid();

        //检查用户余额是否为空
        $member = Member::get(['id' => $mid]);
        if($member['money'] <= 0)
        {
            $this->error('您的余额不足，请充值后再试！');
        }

        $temp['update'] = time_tran($temp['update']);
        $this->assign('temp', $temp);

        return view('');
    }


    //认证
    public function auth()
    {

        //获取会员信息
        $mid = $this->getMidByOpenid();
        $member = Member::get(['id' => $mid]);
        if($member['status'] == 1)
        {
            $this->error('会员信息已认证！');
        }

        //初始化表单
        $form = new Form();
        $formhtml = array();
        $val = array(
            'itemname'=>'营业执照',
            'fieldname'=>'zj',
            'dtype'=> 'img',
            'vdefault'=> '',
            'url'=>'/web/index/addimg/f/upzj.html'
        );

        $arr['html'] = $form->fieldToForm($val,'form-control',$val['fieldname']);

        $arr['itemname'] = $val['itemname'];
        $arr['fieldname'] = $val['fieldname'];

        $formhtml[] = $arr;

        $this->assign('formhtml',$formhtml);




        //是否为提交表单
        if (Request::instance()->isPost())
        {
            $member['zj'] = Request::instance()->post($val['zj']);
            $member->update = time();
            $member->save();
            $this->success('添加成功！');
        }

        $this->assign('formhtml',$formhtml);
        return view('');
    }

    //头条文章
    public function headart()
    {
        //获取会员信息
        $mid = $this->getMidByOpenid();
        $member = Member::get(['id' => $mid]);
        if(empty($member['hid']))
        {
            $this->error('头条栏目没有授权，请联系站长开通！');
        }

        if($member['money'] <= 0)
        {
            $this->error('您的余额不足，请联系站长充值！');
        }

        //预定义模块
        $mould= Mould::get(['table'=>'headart']);
        $field = Field::where(['mid'=>$mould->id])->order('rank')->select();


        //是否为提交表单
        if (Request::instance()->isPost())
        {
            $headart           = new Headart();
            foreach ($field as $val)
            {

                if ($val['fieldname'] == 'sid' || $val['fieldname'] == 'mid' || $val['fieldname'] == 'aid' || $val['fieldname'] == 'click' || $val['fieldname'] == 'rank' || $val['fieldname'] == 'recommend'){
                    continue;
                }else{
                    $headart->$val['fieldname'] = Request::instance()->post($val['fieldname']);
                }
            }
            $headart->sid = $member['hid'];
            $headart->mid = $member['id'];
            $headart->aid = $member['aid'];
            $headart->click = 0;
            $headart->rank = 0;
            $headart->recommend = 0;
            $headart->update = time();
            $headart->save();
            $this->success('添加成功！');
        }

        //初始化表单
        $form = new Form();
        $formhtml = array();
        foreach ($field as $val)
        {

           if ($val['fieldname'] == 'sid' || $val['fieldname'] == 'mid' || $val['fieldname'] == 'aid' || $val['fieldname'] == 'click' || $val['fieldname'] == 'rank' || $val['fieldname'] == 'recommend'){
                continue;
            }elseif($val['fieldname'] == 'body')
            {
                $val ['url']='/web/index/addimg/f/img.html';
                $val['islink'] = $member['islink'];
                $arr['html'] = $form->fieldToForm($val,'form-control');
            }else {
                $arr['html'] = $form->fieldToForm($val,'form-control');
            }

            $arr['itemname'] = $val['itemname'];
            $arr['fieldname'] = $val['fieldname'];

            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);
        return view('');
    }

    //发布类目信息
    public function cateart()
    {
        //获取会员信息
        $mid = $this->getMidByOpenid();
        $member = Member::get(['id' => $mid]);
        if(empty($member['hid']))
        {
            $this->error('类目信息没有授权，请联系站长开通！');
        }

        if($member['money'] <= 0)
        {
            $this->error('您的余额不足，请联系站长充值！');
        }

        //预定义模块
        $mould= Mould::get(['table'=>'cateart']);
        $field = Field::where(['mid'=>$mould->id])->order('rank')->select();


        //是否为提交表单
        if (Request::instance()->isPost())
        {
            $cateart           = new Cateart();
            foreach ($field as $val)
            {
                if ($val['fieldname'] == 'cid' || $val['fieldname'] == 'mid' || $val['fieldname'] == 'aid' || $val['fieldname'] == 'click' || $val['fieldname'] == 'rank' || $val['fieldname'] == 'recommend'){
                    continue;
                }elseif($val['fieldname'] == 'keywords')
                {
                    $lsv = Request::instance()->post($val['fieldname']);
                    $cateart->$val['fieldname'] = str_replace("，",",",$lsv);
                }else{
                    $cateart->$val['fieldname'] = Request::instance()->post($val['fieldname']);
                }
            }
            $cateart->cid = $member['cid'];
            $cateart->mid = $member['id'];
            $cateart->aid = $member['aid'];
            $cateart->click = 0;
            $cateart->rank = 0;
            $cateart->recommend = 0;
            $cateart->update = time();
            $cateart->save();
            $this->success('添加成功！');
        }

        //初始化表单
        $form = new Form();
        $formhtml = array();
        foreach ($field as $val)
        {

            if ($val['fieldname'] == 'cid' || $val['fieldname'] == 'mid' || $val['fieldname'] == 'aid' || $val['fieldname'] == 'click' || $val['fieldname'] == 'rank' || $val['fieldname'] == 'recommend'){
                continue;
            }elseif($val['fieldname'] == 'body')
            {
                $val['url']='/web/index/addimg/f/img.html';
                $val['islink'] = $member['islink'];
                $arr['html'] = $form->fieldToForm($val,'form-control');
            }else {
                $arr['html'] = $form->fieldToForm($val,'form-control');
            }

            $arr['itemname'] = $val['itemname'];
            $arr['fieldname'] = $val['fieldname'];

            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);
        return view('');
    }

    //投诉
    public function complaint()
    {
        //预定义模块
        $mould= Mould::get(['table'=>'complaint']);
        $field = Field::where(['mid'=>$mould->id])->order('rank')->select();

        //是否为提交表单
        if (Request::instance()->isPost())
        {
            $complaint           = new Complaint();
            foreach ($field as $val)
            {
                $complaint->$val['fieldname'] = Request::instance()->post($val['fieldname']);
            }
            $complaint->update = time();
            $complaint->save();
            $this->success('添加成功！');
        }

        //初始化表单
        $form = new Form();
        $formhtml = array();
        foreach ($field as $val)
        {
            if($val['ishide'] ==1)//隐藏时跳过本次
            {
                continue;
            }
            $arr['html'] = $form->fieldToForm($val,'form-control');
            $arr['itemname'] = $val['itemname'];
            $arr['fieldname'] = $val['fieldname'];

            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);
        return view('complaint');
    }

    //免责声明
    public function disclaimer()
    {
        return view('disclaimer');
    }

    //用户中心
    public function member()
    {
        $mid  = $this->getMidByOpenid();
        $member = Member::get(['id' => $mid]);

        $member['browse'] = MoneyLog::where('mid',$mid)->where('money','<',"0")->count();
        $member['sun'] = MoneyLog::where('mid',$mid)->where('money','<',"0")->sum('money');

        $this->assign('member', $member);


        $this->checkCookie();
        $aid = $this->aid;
        //处理地区
        $area = Area::get($aid);
        $this->assign('area', $area);

        //代理二维码
        $agent = Agent::get(['aid' => $aid]);
        $this->assign('agent', $agent);

        return view('member');
    }




    //类目
    public function category($id=0, $level=0)
    {

        $psort = new Category();
        if($id==0)
        {
            $list = $psort->getListByPid($id);
        }else{
            $list = Category::all($id);
        }
        foreach ($list as $key=>$val)
        {
            $list[$key]['suplist'] = $psort->getListByPid($val['id']);
        }
        $this->assign('list', $list);
        $this->assign('level', $level);

        return view('category');
    }



	public function select()
	{

		if(Cookie::get('aid'))
		{

			echo $aid = Cookie::get('aid');
			//$this->redirect('/web/index/index/aid/'.$aid);
		}elseif (!empty(Request::instance()->param('aid')))
        {
            $aid = Request::instance()->param('aid');
        }


		if(!empty($aid))
		{
			$are =  Area::get($aid);
			$this->assign('temp',$are);
		}

		$area = Area::all(['parent_id' => 0]);
		$this->assign('list',$area);
		return view('select');
	}

	public function getArea($id)
	{
		$area = Area::all(['parent_id' => $id]);
		$data = array();
		foreach($area as $key=>$val)
		{
			$data[$key]['name'] = $val->name;
			$data[$key]['id'] = $val->id;
		}
		echo json_encode($data);
	}

	public function selectArea()
	{
		$name = Request::instance()->get('name');
		$area = Area::where('name','like','%'.$name.'%')->select();
		$data = array();
		$i=0;
		foreach($area as $key=>$val)
		{
			if($i<51)
			{
				$data[$key]['name'] = $val->name;
				$data[$key]['id'] = $val->id;
				$arr = array();
				$apar = new Area;
				$apar->getParentArr($arr,$val->id);
				$data[$key]['par'] = $arr;
			}
			$i++;
		}
		echo json_encode($data);
	}

	//列表页
	public function getlist()
    {

		$aid = Request::instance()->param('aid');
		//处理地区
		$area = Area::get($aid);
		$this->assign('area', $area);

		//代理二维码
		$agent = Agent::get(['aid' => $aid]);
		$this->assign('agent', $agent);

		$sid = Request::instance()->param('sid');
		$temp= Sort::get($sid);
		$this->assign('temp', $temp);
		$this->assign('sid', $sid);



		$sort= new Sort();
		$ids = ''.$sid;
		$sort->getSupIds($sid, $ids);

		//初始显示文章
		$article = Article::where('aid','=',$aid)->where('sid','in',$ids)->order('addtime', 'DESC')->limit($this->size)->select();
		//时间预处理
		foreach($article as $key=>$val)
		{
			$article[$key]['addtime'] = time_tran($val['addtime']);
			if($val['picjson'] <> '')
			{
				$arr = explode(",",$val['picjson']);
				$arr = array_filter($arr);
				$article[$key]['img']=$arr[1];
			}else{
				$article[$key]['img']='';
			}
		}
		$this->assign('article', $article);

		//顶级栏目排序
		$sort = Sort::where('parent_id', $sid)->order('rank', 'asc')->select();
		$this->assign('sort', $sort);

		$sort= new Sort();
		$ids = ''.$sid;


		return view('list');
    }

	//详请页
	public function detail()
	{
		$aid = Request::instance()->param('aid');
		//处理地区
		$area = Area::get($aid);
		$this->assign('area', $area);

		//代理二维码
		$agent = Agent::get(['aid' => $aid]);
		$this->assign('agent', $agent);

		$id = Request::instance()->param('id');
		$article = Article::get($id);
		$article['addtime'] = time_tran($article['addtime']);

		$imgarr = explode(",",$article['picjson']);
		$arr = array_filter($imgarr);

		$article['img']=$arr;

		$this->assign('article', $article);

		//顶级栏目排序
		$sort = Sort::where('parent_id', 0)->order('rank', 'asc')->select();
		$this->assign('sort', $sort);


		//初始显示更多文章
		$list = Article::where('aid','=',$aid)->where('sid','=',$article->sid)->order('addtime', 'DESC')->limit($this->size)->select();
		//时间预处理
		foreach($list as $key=>$val)
		{
			$list[$key]['addtime'] = time_tran($val['addtime']);
			if($val['picjson'] <> '')
			{
				$arr = explode(",",$val['picjson']);
				$arr = array_filter($arr);
				$list[$key]['img']=$arr;
			}else{
				$list[$key]['img']='';
			}
		}

		$this->assign('list', $list);


		//获取openid
        $mid = $this->getMidByOpenid();
        $member = Member::get($mid);

        $this->assign('member', $member);



		//是否为提交表单
		if (Request::instance()->isPost())
		{

			$comment = new Comment;

			$comment->aid    	= $id;
			$comment->mid		= $mid;
			$comment->comment	= Request::instance()->post('comment');
			$comment->addtime   = time();;
			$comment->save();
		}

		$comments = Comment::where('aid','=',$id)->order('addtime desc')->select();
		foreach($comments as $key=>$val)
		{
			$comments[$key]['addtime'] =time_tran($comments[$key]['addtime']);
			$my = Member::get($val->mid);
			$comments[$key]['headimgurl'] = $my->headimgurl;
			$comments[$key]['nickname'] = $my->nickname;
		}

		$this->assign('comments', $comments);
		return view('detail');
	}

	public function resume()
	{

		$aid = Request::instance()->param('aid');
		$id = Request::instance()->param('id');
		$this->assign('aid', $aid);
		$this->assign('id', $id);

		//获取openid
        $mid = $this->getMidByOpenid();

		$res = Resume::get(['mid'=>$mid]);

		if(!empty($res))
		{
			$this->assign('res', $res);
		}



		//是否为提交表单
		if (Request::instance()->isPost())
		{

			$resume = new Resume;
			$resume->mid		= $mid;
			$resume->name		= Request::instance()->post('name');
			$resume->sex		= Request::instance()->post('sex');
			$resume->birth		= strtotime(Request::instance()->post('birth'));
			$resume->position	= Request::instance()->post('position');
			$resume->address	= Request::instance()->post('address');
			$resume->phone		= Request::instance()->post('phone');
			$resume->content	= Request::instance()->post('content');
			$resume->addtime   = time();;
			$resume->save();
			$this->success('登记成功！', url('web/index/detail',['aid'=>$aid,'id'=>$id]));
		}
		return view('resume');
	}

	//支付回调函数
	public function notify()
	{
		/*
		1  接收数据
		2  保存到数据库
		3  返回给微信
		*/
	}


	public function townpostcate()
	{
		$aid = Request::instance()->param('aid');
		//处理地区
		$area = Area::get($aid);
		$this->assign('area', $area);

		//顶级栏目排序
		$sort = Sort::where('parent_id', 0)->order('rank', 'asc')->select();
		foreach($sort as $k=>$v)
		{
			$sup = Sort::all(['parent_id'=>$v->id]);
			if(!empty($sup))
			{
				$sort[$k]['sup'] = $sup;
			}
		}

		$this->assign('sort', $sort);

		//代理二维码
		$agent = Agent::get(['aid' => $aid]);
		$this->assign('agent', $agent);

		//获取openid


		$wechatOauth = new WechatOauth();
        $wechat = $wechatOauth->getOpenid();
		if(is_array($wechat)){
			$openid = $wechat['openid'];
		}else{
			$openid = $wechat;
		}

		//没有记录openid时记录到数据库
		$meb = Member::get(['openid' => $openid]);
		if(empty($meb))
		{
			$member = new member;
			$member->nickname	= $wechat['nickname'];
			$member->openid		= $wechat['openid'];
			$member->headimgurl	= $wechat['head_pic'];
			$member->addtime	= time();
			$member->save();
			$mid = $member->id;
			$this->assign('member', $member);
		}else{
			$mid = $meb->id;
			$this->assign('member', $meb);

			$info = json_decode($meb->info_rules, true);
			$meb['rules'] = $info;
			//$aid = 370829104;
			if ($meb['rules']<>'' && array_key_exists($aid,$meb['rules']))
			{
				$sup = $meb['rules'][$aid];
			}
			else
			{
			  	$area = Area::get($aid);
				$sup['name']=$area->name;
			}

			foreach($sup as $key=>$val)
			{
				if(is_array($val))
				{
					if(count(array_filter($val))>0)
					{
						$sup[$key]['charge']=1;
					}
				}
			}
			$this->assign('sup',$sup);
			$this->assign('var',$aid);

		}



		return view('townpostcate');
	}

	public function edit()
	{
		$aid = Request::instance()->param('aid');
		$sid = Request::instance()->param('sid');


		//获取openid
		$mid = $this->getMidByOpenid();
        $member = Member::get($mid);

		//是否为提交表单
		if (Request::instance()->isPost())
		{

			//处理信息条数
			$info = json_decode($member->info_rules, true);
			$info = is_array($info) ? $info:array();
			$sort = Sort::get($sid);
			if(!isset($info[$aid][$sort->parent_id][$sort->id]))
			{
				$num = 0;
			}else{
				$num = $info[$aid][$sort->parent_id][$sort->id];
			}
			if($sort->charge ==1)
			{
				$num =100;
			}

			if($num > 0)
			{
				$article = new Article;
				$article->aid    	= $aid;
				$article->sid    	= $sid;
				$article->mid		= $mid;
				$article->phone		= Request::instance()->post('phone');
				$article->picjson	= Request::instance()->post('picjson');
				$article->wechat	= Request::instance()->post('wechat');
				$article->address	= Request::instance()->post('address');
				$article->content	= Request::instance()->post('content');
				$article->status	= 0;
				$article->addtime   = time();;
				$article->save();

				if($sort->charge ==0)
				{
					//减去条数
					$info[$aid][$sort->parent_id][$sort->id] = $num - 1;
					$member->info_rules = json_encode($info);
					$member->save();
				}

				$this->success('添加成功！');

			}else{

				$this->error('您购买的信息条数已发完，请联系站长');
			}

		}

		$sort = Sort::get($sid);
		$sorttype = Sorttype::get($sort->typeid);

		$field = json_decode($sorttype,true);
		$this->assign('field', json_decode($field['field'],true));


		//初始化乡镇
		$temp['aid'] = $aid;//370829104疃里镇

		$arr=array();
		$area = new Area;
		$area->getAreaTypeArr($arr,$temp['aid']);

		$this->assign('area',$arr);
		//地区
		//省
		$area1 = Area::all(['level'=>1,'parent_id'=>0]);
		$this->assign('area1',$area1);

		//市
		$area2 = Area::all(['level'=>2,'parent_id'=>$arr[1]]);
		$this->assign('area2',$area2);

		//县
		$area3 = Area::all(['level'=>3,'parent_id'=>$arr[2]]);
		$this->assign('area3',$area3);

		//镇
		if(isset($arr[2]))
		{
			$area4 = Area::all(['level'=>4,'parent_id'=>$arr[3]]);

		}else{
			$area3=array();
		}
		$this->assign('area4',$area4);


		//添加栏目
		$sort = array();
		$psort = new Sort();
		$psort->getTree(0,$sort);
		$this->assign('psort',$sort);

		//添加状态
		$temp['status'] = 0;
		//添加时间
		$temp['addtime'] = date('m/d/Y');
		$this->assign('temp',$temp);


		return view('edit');

	}

	public function getData()
	{

		$page = empty(Request::instance()->param('page')) ? 2:Request::instance()->param('page');

		$start = ($page-1)*$this->size;

		$sid = Request::instance()->param('sid');
		$aid = Request::instance()->param('aid');

		$sort= new Sort();
		$ids = ''.$sid;
		$sort->getSupIds($sid, $ids);

		$article = Article::where('aid','=',$aid)->where('sid','in',$ids)->order('addtime', 'DESC')->limit($start.",".$this->size)->select();
		//时间预处理
		foreach($article as $key=>$val)
		{
			$article[$key]['addtime'] = time_tran($val['addtime']);
			if($val['picjson'] <> '')
			{
				$arr = explode(",",$val['picjson']);
				$arr = array_filter($arr);
				$article[$key]['img']=$arr[1];
			}else{
				$article[$key]['img']='';
			}
			$data['id'] =$article[$key]['id'];
			$data['sortname'] =$article[$key]->sort->name;
			$data['content'] =$article[$key]['content'];
			$data['img'] =$article[$key]['img'];
			$data['addtime'] =$article[$key]['addtime'];
			$re[] =$data;
		}
		if(isset($re))
		{
			echo json_encode($re);
		}else{
			echo '';
		}
	}

	public function addimg($f) {

		if(!empty(request() -> file('upqcode')))
		{
			$file = request() -> file('upqcode');
		}

		if(!empty(request() -> file('uper')))
		{
			$file = request() -> file('uper');
		}

        if(!empty(request() -> file($f)))
        {
            $file = request() -> file($f);
        }


        // 移动到框架应用根目录/public/uploads/ 目录下
		$file->validate(['size'=>1024*1024*16,'ext'=>'jpg,png,gif']);
		$info = $file->rule('md5')->move(ROOT_PATH . 'public' . DS . 'uploads'. DS .'images');

		if($info){
			$re =array(
				'code'=> 0,
				'message'=> '上传成功',
				'data'=>DS ."public" . DS . 'uploads'. DS .'images' . DS .$info->getSaveName()
			);
			echo json_encode($re);
		}else{
			// 上传失败获取错误信息
			echo "{\"code\":-1, \"error\":\"Invalid file format\"}";
		}
	}

	public function delimg() {
		Request::instance()->post('imgpath');
		$re =array(
			'success'=> true,
			'message'=> '',
			'data'=>Request::instance()->post('imgpath')
		);
		echo json_encode($re);
	}

    //检查用户余额并扣除指定金额浏览单价
    public function delMoneyByCateart($temp)
    {
        $member =  Member::get($temp->getData('mid'));
        $sysinfo = Sysinfo::get(1);
        if($member['money'] <= $sysinfo['everyprice'] )
        {
            $this->error('发布信息用户余额不足');
        }else{
            //减去指定
            $member->money = $member->money - $sysinfo['everyprice'];
            $member->save();

            $moneylog = new MoneyLog();
            $moneylog->update = time();
            $moneylog->mid = $temp->getData('mid');
            $moneylog->money = - $sysinfo['everyprice'];
            $moneylog->msg = '浏览'.$temp['title']."减少余额";
            $moneylog->save();
        }
    }


    //检查用户余额并扣除指定金额置顶单价
    public function delMoneyByCateartTop($temp)
    {
        $member =  Member::get($temp->getData('mid'));
        $sysinfo = Sysinfo::get(1);
        if($member['money'] <= $sysinfo['stickprice'] )
        {
            $this->error('发布信息用户余额不足');
        }else{
            //减去指定
            $member->money = $member->money - $sysinfo['stickprice'];
            $member->save();

            $moneylog = new MoneyLog();
            $moneylog->update = time();
            $moneylog->mid = $temp->getData('mid');
            $moneylog->money = - $sysinfo['stickprice'];
            $moneylog->msg = '浏览置顶'.$temp['title']."减少余额";
            $moneylog->save();
        }
    }


    //检查用户余额并扣除指定金额商家通讯录单价
    public function delMoneyByBooks($temp,$mid)
    {
        $member =  Member::get($mid);
        $sysinfo = Sysinfo::get(1);
        if($member['money'] <= $sysinfo['shopprice'] )
        {
            $this->error('发布用户余额不足');
        }else{
            //减去指定
            $member->money = $member->money - $sysinfo['shopprice'];
            $member->save();

            $moneylog = new MoneyLog();
            $moneylog->update = time();
            $moneylog->mid = $mid;
            $moneylog->money = - $sysinfo['stickprice'];
            $moneylog->msg = '浏览商家通讯录'.$temp['name']."减少余额";
            $moneylog->save();
        }
    }

    //获取地区数据
    public function getajaxarea($type=1,$supid=0)
    {
        $area = Area::all(['level'=>$type,'parent_id'=>$supid]);

        $data=array();
        foreach ($area as $val)
        {
            $ls=array();
            $ls['name']	=  $val->name;
            $ls['id']	=  $val->id;
            $data[] =$ls;
        }

        return $data;
    }

    //获取openid
    public function getMidByOpenid()
    {
        //获取openid
        $wechatOauth = new WechatOauth();
        $wechat = $wechatOauth->getOpenid();
        if(is_array($wechat)){
            $openid = $wechat['openid'];
        }else{
            $openid = $wechat;
        }

        //没有记录openid时记录到数据库
        $meb = Member::get(['openid' => $openid]);
        if(empty($meb))
        {
            $member = new member;
            $member->nickname	= $wechat['nickname'];
            $member->openid		= $wechat['openid'];
            $member->headimgurl	= $wechat['head_pic'];
            $member->addtime	= time();
            $member->save();
            $mid = $member->id;
        }else{
            $mid = $meb->id;
        }
        return $mid;
    }
}
