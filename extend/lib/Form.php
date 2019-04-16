<?php

namespace lib;
/**
 * 解析成form表单
 * @author myeoa
 * @email  6731834@163.com
 * @date 2015年4月30日 上午10:10:32
 */
/*
 text,单行文本(varchar)     			文本框		字符型
 int,整数类型									文本框		数字型
 float,小数类型								文本框		小数型
 datetime,时间类型						文本框      数字型(10)  	（时间组件）
 img,图片										文本框   	字符型		（上传组件   ）

 multitext,多行文本						文本域		备注型 	(65535)不用传
 htmltext,HTML文本						文本域		备注型  (65535)不用传       	（在线编辑器	 ）
 select,使用select下拉框				下拉框		数组型
 radio,使用radio选项卡					单选框		数组型
 checkbox,checkbox多选框			多选框		数组型
*/

class Form {
	public $forminc=array(
			'text'		=>'text',
			'multitext'	=>'textarea',
			'htmltext'	=>'htmlarea',
			'int'		=>'text',
			'float'		=>'text',
			'datetime'	=>'datetime',
			'img'		=>'img',
			'select'	=>'select',
			'radio'		=>'radio',
			'checkbox'	=>'checkbox',
			'multiimg'  =>'multiimg'
		);
	/**
	 * 字段解析成form 表单
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return mixed
	 */
	public function fieldToForm($field,$class='',$id='',$key=''){

		$ftype=$this->forminc[$field['dtype']];
		if(method_exists($this, $ftype))
		{
			return $this->$ftype($field,$class,$id,$key);
		}else
		{
			return false;
		}
	}
	/**
	 * 解析成文本框
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public function text($field,$class='',$id='',$key=''){
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$str="<input name='{$field['fieldname']}' placeholder='{$field['itemname']}' type='text' value='{$field['vdefault']}'  {$class}  {$id} />";
		return $str;
	}

	/**
	 * 解析成文本域
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public  function textarea ($field,$class='',$id='',$key=''){
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$str="<textarea name='{$field['fieldname']}' {$class}  {$id}>{$field['vdefault']}</textarea>";
		return $str;
	}

	/**
	 * 解析成下拉框
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public  function select ($field,$class='',$id='',$key=''){
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$str="<select name='{$field['fieldname']}'  {$class}  {$id}>";
		if(is_array($field['vdefault']))
        {
            $arrs = $field['vdefault'];
        }else{
            $arrs=explode(',', $field['vdefault']);
        }

		if(is_array($arrs))
		{
			foreach ($arrs as $k=>$value)
			{
			    if($k == $key)
                {
                    $str.="<option value='$key' selected>$value</option>";
                }else{
                    $str.="<option value='$key'>$value</option>";
                }

			}
		}
		$str.="</select>";
        return $str;
	}

	/**
	 * 解析成单选框
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public function radio ($field,$class='',$id='' ,$key=''){
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$str='';
		$arrs=explode(',', $field['vdefault']);
		if(is_array($arrs))
		{
			foreach($arrs as $key=>$value)
			{
				$str.="<input  type='radio' name='{$field['fieldname']}'  value='{$key}' {$class}  {$id} />{$value}";
			}
		}
		return $str;
	}
	/**
	 * 解析成多选框
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public function checkbox($field,$class='',$id='',$key=''){
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$str='';
		$arrs=explode(',', $field['vdefault']);
		if(is_array($arrs))
		{
			foreach($arrs as $key=>$value)
			{
				//print_r ($value);
				$str.="<input  type='checkbox' name='{$field['fieldname']}[]'  value='{$key}' {$class}  {$id}/>{$value}";
			}
		}
		return $str;
	}
	/**
	 * 解析成上传图片
	 * @param array $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public function img ($field,$class='',$id='qcode',$key=''){

	    if(stripos($field['vdefault'], 'http') !== false)
        {
            $header_array = get_headers($field['vdefault'], true);
            $imagesize = $header_array['Content-Length'];
        }else{
            $imagesize =filesize(getcwd().$field['vdefault']);
        }
        $field['vdefault'] = str_replace('\\','/',$field['vdefault']);

	    if(empty($field['url']))
        {
            $field['url'] = "/categorys/addimg/f/up{$field['fieldname']}.html";
        }


		$html = "<input id='{$id}' name='{$field['fieldname']}' value='{$field['vdefault']}' type='hidden'>
                <input id='file-{$id}' name='up{$id}' value='{$field['vdefault']}' type='file' data-min-file-count='1'>
                <script>
	                $('#file-{$id}').fileinput({
                        language: 'zh',
		                uploadUrl: '{$field['url']}',
                        allowedFileExtensions : ['jpg', 'png','gif'],
		                previewFileType:'any',
		                dropZoneEnabled: false,
				";
	    if($field['vdefault'] <> '')
        {
        $html .= "     initialPreview: [
			            \"<img src='{$field['vdefault']}'  class='kv-preview-data file-preview-image' style='width:auto;height:160px;'/>\",

		                ],
		                initialPreviewConfig:[{caption: \"{$field['vdefault']}\", size: {$imagesize}, width: \"120px\",  key: 1},] ,
		                initialCaption:'{$field['vdefault']}'
                    ";
        }
        $html .=    "}).on('fileuploaded', function (event, data, previewId, index){
                            $('#{$id}').val(data.response.data);
                            $('.file-caption-name').eq(0).attr('title',data.response.data);
                            var html ='<i class=\'glyphicon glyphicon-file kv-caption-icon\'></i>'+data.response.data;
                            $('.file-caption-name').eq(0).html(html);
                        
                    });
                </script>";



		return $html;
	}

	/**
	 * 解析成文本编缉器
	 * @param unknown $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public function htmlarea($field,$class='',$id=''){
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";

        if(empty($field['url']))
        {
            $field['url'] = "/uploads/addimg/f/img.html";
        }

		$html = '<div class="zx-eidtor-container" id="editorContainer"></div>';
        $html .= '<input type="hidden" name="'.$field['fieldname'].'" value=\''.$field['vdefault'].'\' class="zx-eidtor">';
        $html .= '<a href="#" class="submit active" onclick="handleSubmitClick()">完成编辑</a>';
        $html .= "<script>

// 初始化ZX编辑器
var zxEditor = new ZxEditor('#editorContainer', {
            fixed: true,
  // demo有顶部导航栏，高度44
  //top: 44,
  // 编辑框左右边距
  //padding: 13,
  ";
        if(!empty($field['islink']))
        {
            $html .= "
  //是否显示底部工具栏（图片、标签、链接添加等图标）。
  showToolbar: ['pic', 'emoji', 'text', 'link']
})
";
        }else{
            $html .= "
  //是否显示底部工具栏（图片、标签、链接添加等图标）。
  showToolbar: ['pic', 'emoji', 'text']
})
";
        }
        if(!empty($field['vdefault']))
        {
            $html .= "zxEditor.setContent('".str_replace(array("\r\n", "\r", "\n"), "", $field['vdefault'])."')";
        }

        $html .= "
function handleSubmitClick () {
    // 获取文章数据
    var data = getArticleData() || {};
  // 显示loading
  zxEditor.dialog.loading();

  // 上传图片数据
  // 处理正文中的base64图片
  // 获取正文中的base64数据数组
  var base64Images = zxEditor.getBase64Images();
  // 上传base64图片数据
  uploadBase64Images(base64Images, function () {
      // 正文中有base64数据，上传替换成功后再重新获取正文内容
      if (base64Images.length) {
          data.content = zxEditor.getContent();
      }
      // 需要提交的数据
      // 防止提交失败，再保存一次base64图片上传后的文章数据
      zxEditor.storage.set('article', data)
    // 发送至服务器

    // end
    zxEditor.dialog.removeLoading();

    $('.zx-eidtor').val(data.content)

  })
}


//获取文章数据
function getArticleData () {
    var data = {
        // 获取正文内容
        content: zxEditor.getContent()
  }
  return (!data.content || data.content === '')
      ? null
      : data;
}


//数据处理，并提交数据处理

function uploadBase64Images (base64Images, callback) {
    var len = base64Images.length;
    var count = 0;
    if (len === 0) {
        callback()
    return
  }
    for (var i = 0; i < len; i++) {
        _uploadHandler(base64Images[i]);
    }
  function _uploadHandler (data) {
      upload(data.blob, function (url) {
          // 替换正文中的base64图片
          zxEditor.setImageSrc(data.id, url)
      setTimeout(function () {}, 3000)
      // 计算图片是否上传完成
      _handleCount();
    })
  }
  function _handleCount () {
      count++
    if (count === len) {
        callback()
    }
  }
}

// 模拟文件上传
function upload (blob, callback) {
    var formData = new FormData();
    formData.append('img', blob,blob.size+'.jpg');
    $.ajax({
    type : 'POST',
    url : '{$field['url']}',
    data : formData,
    async: false,
    cache: false,
    contentType: false,
    processData: false,
    success : function(msg) {
        if(msg){
            var obj = JSON.parse(msg);
            console.log(obj.data)
            callback(obj.data);
        }
    }
  });
}
</script>";
		return $html;
	}
	/**
	 * 解析成时间插件
	 * @param string $field
	 * @param string $class
	 * @param string $id
	 * @return string
	 */
	public function datetime($field,$class='',$id='')
	{
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$field['vdefault']=$field['vdefault']=='' ?  date('Y-m-d H:m:s'):$field['vdefault'];
		if(stripos($field['vdefault'],'-')===false)
		{
			$field['vdefault']=date('Y-m-d H:m:s',$field['vdefault']);
		}
		$str.='<div class="inline layinput">';
		$str.="<input name='{$field['fieldname']}' type='text' value='{$field['vdefault']}' onclick=\"laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})\"  {$class}  {$id} /> <label class=\"laydate-icon\"></label>";
		$str.='</div>';
		return $str;
	}

	public function multiimg($field,$class='',$id='')
	{
		$class=$class=='' ? $class : "class='$class'";
		$id=$id=='' ? $id : "id='$id'";
		$timestamp = time();
		$token=md5('unique_salt' . $timestamp);
		$html='
		<script src="uploadify/jquery.min.js" type="text/javascript"></script>
		<script src="uploadify/jquery.uploadify.min.js" type="text/javascript"></script>
		<link rel="stylesheet" type="text/css" href="uploadify/uploadify.css">
		
		<input id="file_upload" name="file_upload" type="file" multiple="true">
		<div id="sid"></div>
        <div id="img">';
		if(!$field['vdefault']=='')
		{
			$imgarr=json_decode($field['vdefault'],true);

			if(is_array($imgarr))
			{
				foreach($imgarr['url'] as $key=>$value)
				{
				$html.='<li><div class="fk"><img src="/upload.php?do=display&amp;img='.$imgarr['url'][$key].'&amp;width=200&amp;height=200"></div><p><input type="text" value="'.$imgarr['url'][$key].'" style="width:190px;" name="'.$field['fieldname'].'[url][]"><input type="text" style="width:150px;" value="'.$imgarr['name'][$key].'" name="'.$field['fieldname'].'[name][]"><input name="del" type="button" value="删除" onclick="delli(this)" class="del"></p></li>';
				}
			}
		}
		$html.='</div>';
		$html.="
		<script type=\"text/javascript\">
		$(function() {
			$('#file_upload').uploadify({
				'formData'     : {
					'timestamp' : ' $timestamp',
					'token'     : ' $token'
				},
				'buttonText' : '上传图集',
				'queueID' : 'sid',
				'progressData' : 'speed',
				'onUploadSuccess' : function(file, data, response) {
					var obj = JSON.parse(data);
					if(obj.error == 0){
						$('#img').append('<li><div class=\'fk\'><img src=\"/upload.php?do=display&img='+obj.url+'&width=200&height=200\"></div><p><input type=\'text\' value='+obj.url+' style=\'width:190px;\' name=\'{$field['fieldname']}[url][]\'><input type=\'text\' style=\'width:150px;\' value=\"'+obj.name+'\" name=\'{$field['fieldname']}[name][]\'><input name=\'del\' type=\'button\' value=\'删除\' onClick=\'delli(this)\' class=\'del\'></p></li>');
					}else{
						$('#img').append('<li><div class=\'fk\'>'+obj.name+':'+obj.msg+'<input name=\'msg\' type=\'button\' value=\'删除\' onClick=\'delli(this)\' class=\'del\'></p></li>');
					}
					
        		},
				'swf'      : 'uploadify/uploadify.swf',
				'uploader' : '/upload.php?do=upimg&dir=image'
			});
			
			$('#img li .del').click(function() {
				
			});
		});
		//删除按钮事件
		function delli(s)
		{
			$(s).parents('li').remove();
		}
		
		</script>";

		return $html;
	}
}




