<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
	'__alias__' =>  [
        'index'  		=>  'index/index',
		'account'  		=>  'index/account',
		'areas'			=>	'index/areas',
		'magents' 		=>  'index/magents',
		'sysinfos' 		=>  'index/sysinfos',
		'clear' 		=>  'index/clear',
		'withdraws' 	=>  'index/withdraws',
		'sorts' 		=>  'index/sorts',
		'members' 		=>  'index/members',
		'resumes' 		=>  'index/resumes',
		'articles' 		=>  'index/articles',
		'comments' 		=>  'index/comments',
        'moulds' 		=>  'index/moulds',
        'fields' 		=>  'index/fields',
        'headsorts'     =>  'index/headsorts',
        'headarts'      =>  'index/headarts',
        'categorys'     =>  'index/categorys',
        'catearts'      =>  'index/catearts',
        'money_logs'      =>  'index/money_logs',
        'messages'      =>  'index/messages',
        'guestbooks'    => 'index/guestbooks',
        'books'    => 'index/books',
        'mails'    => 'index/mails',
        'uploads'   => 'index/uploads'


    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],
	'[index]'     => [
        ':id'   => ['areas/index', ['method' => 'get'], ['id' => '\d+']],
    ],
    '[edit]'  =>  [
    	':id'   => ['account/edit', ['method' => 'get'], ['id' => '\d+']],
    ],
    '[del]'  =>  [
    	':id'   => ['account/del', ['method' => 'get'], ['id' => '\d+']],
    ],

];
