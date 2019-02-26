<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Cache;
use think\Log;
/**
 * 用户管理控制器
 * @author myeoa
 * @email  6731834@163.com
 * @date 2017年6月15日 上午11:07:56
 */
class Clear extends Controller
{
	public $title='SEOCRM管理系统';
	
	
	public function _initialize()
	{
		check();
	}
	
	
	public function index() {
		Cache::clear(); 
		Log::clear();
		$this->success('清理成功');
	}
	
}