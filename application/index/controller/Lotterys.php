<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\index\model\Lottery;
use app\index\model\LotterySign;
use app\index\model\LotteryLog;
use lib\Form;

/**
 * 抽奖管理
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月16日 上午10:21:27
 */
class Lotterys extends Controller
{
	public $title='SEOCRM管理系统';
    public $field=array();


	public function _initialize()
	{
		check();
        $this->assign('menu', getLeftMenu());


        $this->field = array(
            array(
                'itemname'=>'活动规则',
                'fieldname'=>'rules_title',
                'dtype'=>'text',
                'vdefault'=>''
            ),
            array(
                'itemname'=>'规则内容',
                'fieldname'=>'rules_content',
                'dtype'=>'htmltext',
                'vdefault'=>''
            ),
            array(
                'itemname'=>'兑奖信息',
                'fieldname'=>'accept_title',
                'dtype'=>'text',
                'vdefault'=>''
            ),
            array(
                'itemname'=>'兑奖内容',
                'fieldname'=>'accept_content',
                'dtype'=>'htmltext',
                'vdefault'=>''
            ),
            array(
                'itemname'=>'机构介绍',
                'fieldname'=>'about_title',
                'dtype'=>'text',
                'vdefault'=>''
            ),
            array(
                'itemname'=>'机构内容',
                'fieldname'=>'about_content',
                'dtype'=>'htmltext',
                'vdefault'=>''
            ),
        );

	}

	/**
	 * 抽奖列表
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function  index($id=0){

		if(empty($id))/////////////////////////////
		{

			$list = Lottery::order('addtime')->paginate(10);

		}else {
			$area =Lottery::get($id);
			$level = $area->level+1;
			$list = Area::where('parent_id','=',$id)->order('sort')->select();
			//$list = Area::order('sort','desc')->all(['parent_id'=>$id]);
		}

		// 查询数据集
		// 把数据赋值给模板变量list
		$this->assign('list', $list);


		//获取当当前控制器
		$request = Request::instance();
		$this->assign('act', $request->controller());
		$this->assign('title','抽奖管理-'.$this->title);
		return $this->fetch();
	}

	/**
	 * 修改抽奖
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function edit($id) {
        $temp = Lottery::get($id);
        if(empty($temp))
        {
            $this->error('您要删除的抽奖不存在！');
        }

		//是否为提交表单
		if (Request::instance()->isPost())
		{
            $lottery = $temp;
            $lottery->toppic    	= Request::instance()->post('toppic');
            $lottery->music    	    = Request::instance()->post('music');
            $lottery->title    	    = Request::instance()->post('title');
            $lottery->startime    	= strtotime(Request::instance()->post('startime'));
            $lottery->endtime    	= strtotime(Request::instance()->post('endtime'));
            $lottery->rules_title    	= Request::instance()->post('rules_title');
            $lottery->rules_content    	    = Request::instance()->post('rules_content');
            $lottery->accept_title    	    = Request::instance()->post('accept_title');
            $lottery->accept_content     = Request::instance()->post('accept_content');
            $lottery->about_title    	    = Request::instance()->post('about_title');
            $lottery->about_content     = Request::instance()->post('about_content');
            $lottery->tel     = Request::instance()->post('tel');
            $lottery->save();
            $this->success('添加成功！');
		}


        //处理时间
        $temp['startime']=date('m/d/Y',$temp['startime']);
        $temp['endtime']=date('m/d/Y',$temp['endtime']);


        $temp['toppic'] = str_replace('\\','/',$temp['toppic']);
        if(file_exists(getcwd().$temp['toppic']))
        {
            $temp['imagesize'] = filesize(getcwd().$temp['toppic']);
        }
        else
        {
            $temp['toppic']='/theme/web/static/lottery/images/01.jpg';
            $temp['imagesize'] = 876;

        }

        if(empty($temp['music']))
        {
            unset($temp['music']);
        }else{
            $temp['music'] = str_replace('\\','/',$temp['music']);
            $temp['musicsize'] = filesize(getcwd().$temp['music']);
        }


		$this->assign('temp',$temp);


        $form = new Form();
        $formhtml = array();
        foreach ($this->field as $val)
        {
            //开放链接
            $val['islink'] = 1;
            $val['vdefault'] = $temp[$val['fieldname']];
            $arr['html'] = $form->fieldToForm($val,'form-control',$val['fieldname']);
            $arr['fieldname'] = $val['fieldname'];
            $arr['itemname'] = $val['itemname'];
            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);


		$this->assign('title','修改抽奖-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());


		return $this->fetch();
	}

	/**
	 * 添加抽奖
	 * @param number $supid
	 * @param number $type
	 * @return \think\mixed
	 */
	public function add($parent_id=0,$level=1) {

		//是否为提交表单
		if (Request::instance()->isPost())
		{

				$lottery                = new Lottery();
                $lottery->toppic    	= Request::instance()->post('toppic');
                $lottery->music    	    = Request::instance()->post('music');
                $lottery->title    	    = Request::instance()->post('title');
                $lottery->startime    	= strtotime(Request::instance()->post('startime'));
                $lottery->endtime    	= strtotime(Request::instance()->post('endtime'));
                $lottery->rules_title    	= Request::instance()->post('rules_title');
                $lottery->rules_content    	    = Request::instance()->post('rules_content');
                $lottery->accept_title    	    = Request::instance()->post('accept_title');
                $lottery->accept_content     = Request::instance()->post('accept_content');
                $lottery->about_title    	    = Request::instance()->post('about_title');
                $lottery->about_content     = Request::instance()->post('about_content');
                $lottery->tel     = Request::instance()->post('tel');
                $lottery->click = 1;
                $lottery->addtime = time();
                $lottery->save();
				$this->success('添加成功！');
		}



        $form = new Form();
        $formhtml = array();
        foreach ($this->field as $val)
        {
            //开放链接
            $val['islink'] = 1;
            $arr['html'] = $form->fieldToForm($val,'form-control',$val['fieldname']);
            $arr['fieldname'] = $val['fieldname'];
            $arr['itemname'] = $val['itemname'];
            $formhtml[] = $arr;
        }
        $this->assign('formhtml',$formhtml);



		$this->assign('title','添加抽奖-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());


		//获取上级抽奖信息
        $temp=array();

        $temp['toppic']='/theme/web/static/lottery/images/01.jpg';
        $temp['imagesize'] = 876;

		$this->assign('temp',$temp);

		return $this->fetch('edit');
	}

	/**
	 * 删除抽奖
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function del($id) {
        $temp = Lottery::get($id);
		if(empty($temp))
		{
			$this->error('您要删除的抽奖不存在！');
		}else{
            $temp ->delete();
			$this->success('删除抽奖成功！',url('index/lotterys/index'));
		}
		$this->assign('title','删除抽奖-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch();
	}

	public function log($lid)
    {
        $temp = Lottery::get($lid);
        if(empty($temp))
        {
            $this->error('您要编辑的抽奖不存在！');
        }
        $this->assign('temp',$temp);

        $list = LotteryLog::where(['lid'=>$lid])->paginate(10);

        $this->assign('list', $list);

        $this->assign('title','中奖记录-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return view();
    }

    public function cash($id)
    {
        $temp = LotteryLog::get($id);
        if(empty($temp))
        {
            $this->error('您要编辑的中奖记录不存在！');
        }

        //是否为提交表单
        if (Request::instance()->isPost())
        {

            $temp->status     = Request::instance()->post('status');
            $temp->save();
            $this->success('添加成功！');
        }
        $this->assign('temp', $temp);
        return view();
    }

    public function signlist($lid)
    {
        $list = LotterySign::where('lid',$lid)->order('addtime')->paginate(10);

        // 查询数据集
        // 把数据赋值给模板变量list
        $this->assign('list', $list);

        $this->assign('lid', $lid);
        //获取当当前控制器
        $request = Request::instance();
        $this->assign('act', $request->controller());
        $this->assign('title','报名管理-'.$this->title);
        return $this->fetch();
    }

    public function signedit($id)
    {
        $temp = LotterySign::get($id);
        if(empty($temp))
        {
            $this->error('您要编辑的报名不存在！');
        }
        $this->assign('lid', $temp['lid']);
        $this->assign('temp', $temp);

        $this->assign('title','报名管理-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return view();
    }


    public function signdel($id) {
        $temp = LotterySign::get($id);
        if(empty($temp))
        {
            $this->error('您要删除的报名不存在！');
        }else{
            $lid = $temp['lid'];
            $temp ->delete();
            $this->success('删除报名成功！',url('index/lotterys/signlist',['lid'=>$lid]));
        }
        $this->assign('title','删除报名-'.$this->title);
        $request = Request::instance();
        $this->assign('act', $request->controller());
        return $this->fetch();
    }
}
