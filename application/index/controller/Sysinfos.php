<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Session;
use app\index\model\Sysinfo;
use app\index\model\Field;
use lib\Form;


/**
 * 用户管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Sysinfos extends Controller
{
	public $title='爱臣同乡管理系统';
	
	
	public function _initialize()
	{
		check();
	}
	
	/**
	 * 系统设置
	 * @param unknown $id
	 * @return \think\mixed
	 */
	public function index() {

		$sysinfo = Sysinfo::get(1);
		
		//判断系统配置是否存在
		if(empty($sysinfo))
		{
			$this->error('要修改的系统配置不存在');
		}
		
		//是否为提交表单
		if (Request::instance()->isPost())
		{
			
			$sysinfo->webname    = Request::instance()->post('webname');
			$sysinfo->site    	 = Request::instance()->post('site');
			$sysinfo->title      = Request::instance()->post('title');
			$sysinfo->keywords   = Request::instance()->post('keywords');
			$sysinfo->description= Request::instance()->post('description');
			$sysinfo->withdrawals= Request::instance()->post('withdrawals');
			$sysinfo->appid      = Request::instance()->post('appid');
			$sysinfo->appsecret  = Request::instance()->post('appsecret');
			$sysinfo->mchid      = Request::instance()->post('mchid');
			$sysinfo->apikey     = Request::instance()->post('apikey');
            $sysinfo->everyprice     = Request::instance()->post('everyprice');
            $sysinfo->stickprice     = Request::instance()->post('stickprice');
            $sysinfo->qcode     = Request::instance()->post('qcode');
            $sysinfo->er     = Request::instance()->post('er');
			$sysinfo->save();
			$this->success('修改成功！');
			
		}

        $form = new Form();

        $field = Field::get(['mid'=>1,'fieldname'=>'qcode']);
        $field['vdefault'] = $sysinfo['qcode'];
        $html['qcode'] = $form->fieldToForm($field,'form-control','qcode');


        $fielder = Field::get(['mid'=>1,'fieldname'=>'er']);
        $fielder['vdefault'] = $sysinfo['er'];
        $html['er'] = $form->fieldToForm($fielder,'form-control','er');



        $this->assign('html',$html);



        $this->assign('temp',$sysinfo);

		$this->assign('title','修改系统配置-'.$this->title);
		$request = Request::instance();
		$this->assign('act', $request->controller());
		return $this->fetch('edit');
	}

}