<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\index\model\User;
use think\Route;
use think\Session;

class Index extends Controller
{
	public $title='爱臣同乡管理系统';
	
	public function _initialize()
	{
		
	}
	/**
	 * 系统首页
	 * @return \think\response\View
	 */
    public function index()
    {
		
		check();
		
    	$info = array(
    			'操作系统'=>PHP_OS,
    			'运行环境'=>$_SERVER["SERVER_SOFTWARE"],
    			'PHP运行方式'=>php_sapi_name(),
    			'ThinkPHP版本'=>THINK_VERSION.' [ <a href="http://thinkphp.cn" target="_blank">查看最新版本</a> ]',
    			'上传附件限制'=>ini_get('upload_max_filesize'),
    			'执行时间限制'=>ini_get('max_execution_time').'秒',
    			'服务器时间'=>date("Y年n月j日 H:i:s"),
    			'北京时间'=>gmdate("Y年n月j日 H:i:s",time()+8*3600),
    			'服务器域名/IP'=>$_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
    			'剩余空间'=>round((disk_free_space(".")/(1024*1024)),2).'M',
    			'register_globals'=>get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
    			'magic_quotes_gpc'=>(1===get_magic_quotes_gpc())?'YES':'NO',
    			'magic_quotes_runtime'=>(1===get_magic_quotes_runtime())?'YES':'NO',
    	);
	
    	$this->assign('info',$info);
    	
    	$request = Request::instance();
    	$this->assign('act', $request->controller());
    	
    	$this->assign('title','系统首页-'.$this->title);
	
    	return view('index');
    }
	
    /**
     * 登陆
     * @return \think\response\View
     */
	public function login()
    {
    	
		
		
		
		if (Request::instance()->isPost()) 
		{
			if(!captcha_check(Request::instance()->post('code'))){
				$this->error('验证码不正确');//验证失败
			}else{
				$user = User::get(['user' => Request::instance()->post('user'),'pwd' => md5(Request::instance()->post('pwd'))]);
				
				if(empty($user))
				{
					$this->error('用户名密码错误！');
				}else{
					Session::set('id',$user->id);
					Session::set('user',$user->user);
					Session::set('pwd',$user->pwd);
					$this->success('登陆成功', 'index/index/index');
				}
			}
		}else{
			return view('login');
		}
    }
	
    public function quit() {

    	Session::delete('user');
		Session::delete('did');
    	Session::delete('type');
    	Session::delete('pwd');
    	Session::delete('nickname');
    	$this->success('登陆成功', 'index/index/login');
    }
}
