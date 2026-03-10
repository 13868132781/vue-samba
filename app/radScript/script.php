<?php
namespace app\radScript;
use app\radPerm\perm;
use app\radNas\nasType;

class script extends \table{
	public $pageName='脚本管理';
	public $TN = "sdaaa.script";
	public $colKey = "scid";
	public $colOrder = "sc_order";
	public $colFid = "";
	public $colUnit = "sc_type";
	public $colName = "sc_name";
	public $colNafy	= "";
	public $orderDesc = false;
	public $POST = [];
	public $auditList=[
		'fetch_scriptCodeSave'=>'代码保存',
	];
	
	public $sc_types=[
		0=>'普通脚本',
		1=>'备份脚本',
		2=>'下发脚本',
		3=>'巡检脚本',
	];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'scid','name'=>'编号','jsCtrl'=>['width'=>'30px']],
				['col'=>'sc_name','name'=>'名称'],
				['col'=>'sc_type','name'=>'类型',
					'map'=>$this->sc_types
				],
				['col'=>'sc_mark','name'=>'说明'],
				['col'=>'sv_log','name'=>'代码',
					'type'=>'fetch',
					'popTitle'=>'代码编辑',
					'align'=>'center',
					'zdyCom'=>'scriptCode',
					'goto'=>'scriptCode',
					'popWidth'=>'90%',
					'popHeight'=>'90%',
				],
				
				
			],
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'toolFilterEnable'=>false,
			'toolExpands'=>[ 
				[
					'name'=>'代码语法',
					'type'=>'fetch',
					'goto'=>'readme'
					//'router'=>'/sysAcct/sysAcct', 
				],
			],

			
			'operEnable' => true ,
			'operModEnable'=> true,
			'operDelEnable'=> true,
				
				
			'fenyeEnable'=> true,
			'fenyeNum'=> 20,//默认20 
		];
		return $gridSet;
	}
	
	public function crudAddSet(){
		$back=[];
		$back[]=[
			"name"=>"脚本名",
			"col"=>"sc_name",
			"type"=>'text',
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"说明",
			"col"=>"sc_mark",
			"type"=>'text',
		];
		$back[]=[
			"name"=>"类型",
			"col"=>"sc_type",
			"type"=>'select',
			"options"=>$this->sc_types,
			"ask"=>true,
		];
		
		$back[]=[
			"name"=>"用户名",
			"col"=>"sc_head_user",
			"type"=>'text',
			//"ask"=>true,
			"hintMore"=>'不填时默认用环境变量里设定的备份用户',
		];
		
		//ispass和type=password不同
		//password类型会有一系列处理，如修改时不回显，为空时不跟新
		//但ispass只是把输入文本隐藏，处理流程还是text
		$back[]=[
			"name"=>"密码",
			"col"=>"sc_head_pass",
			"type"=>'text',
			"ispass"=>true,
			//"ask"=>true,
			"hintMore"=>'不填时默认用环境变量里设定的备份密码',
		];
		$back[]=[
			"name"=>"匹配字符",
			"col"=>"sc_head_match",
			"type"=>'text',
		];
		$back[]=[
			"name"=>"提权命令",
			"col"=>"sc_head_sucmd",
			"type"=>'text',
		];
		$back[]=[
			"name"=>"提权密码",
			"col"=>"sc_head_supwd",
			"type"=>'text',
		];
		$back[]=[
			"name"=>"超时时间",
			"col"=>"sc_head_time",
			"type"=>'text',
		];
		$back[]=[
			"name"=>"超时处理",
			"col"=>"sc_head_tdeal",
			"type"=>'select',
			'options'=>[
				'return 1'=>'异常中断脚本',
				'return' =>'正常终端脚本',
				'ctrl+c'=>'仅中断此命令',
			]
		];
		$back[]=[
			"name"=>"错误关键字",
			"col"=>"sc_head_error",
			"type"=>'text',
		];
		$back[]=[
			"name"=>"错误处理",
			"col"=>"sc_head_edeal",
			"type"=>'select',
			'options'=>[
				'return 1'=>'异常中断脚本',
				'return' =>'正常终端脚本',
				'ctrl+c'=>'仅中断此命令',
			]
		];
		
	
		return $back;
	}
	
	public function crudModSet(){
		return $this->crudAddSet();
	}
	
	public function fetch_scriptCodeGet(){
		$post=$this->POST;
		$key = $post['key'];
		$row = $this->getById($key);
		if($row){
			return $this->out(0,$row['sc_script']);
		}
		return $this->out(1,'','未找到数据');
	}
	
	public function fetch_scriptCodeSave(){
		$post=$this->POST;
		$key = $post['key'];
		$data = $post['data'];
		$this->DB()->where($this->colKey,$key)
			->update([
				'sc_script'=>$data,
			]);
		
		return $this->out(0,'','保存成功');
	}
	
	
	public function fetch_readme(){
		$text = <<<'out'
除了以 “>>”开头的语句，其他语句都是标准的php语法
注意 “>>” 只是标识这条语句是一条远程执行的命令，这个 >> 并不是提示符 ，

“>>”语句里可以写变量。如
>>cd /{$folder}
{$folder}就标识了一个变量，其中$folder就是php里的变量，在上文中必须要定义这个变量的值

处理写变量外，本脚本还有8个标识量，全部用 { } 括起来

>>ls {result:lsreturn} {time:30} {tdeal:ctrl+c} {error:^} {edeal:} {match:#|>} {save:/tmp/lsrreturn.txt} {enter:\n}

1.{result:lsreturn} 表示将ls命令的返回信息放在 $lsreturn 这个变量里
	这个变量是个数组
	$lsreturn['text'] ，表示ls命令返回的数据，不包含最后的提示行"root@SD-AAA:~#"
	$lsreturn['prompt']，该命令结束后的提示行， 类似于 "root@SD-AAA:~#"
	$lsreturn['match']，匹配到的字符或串，如果超时，该值为timeout
	$lsreturn['error']，如果设置了{error:}，且匹配到了，该值为匹配到的串

2.{time:30} 表示这条命令的超时时间设定为 30 

3.{tdeal:ctrl+c} ,标识了这条命令如果超时的话，该如何处理 ，
	其值可以为 ctrl+c 、return、return 0、return 1 或 空
	为空，不进行任何处理
	ctrl+c，标识通过 ctrl+c 停止这条命令 ，如linux 下的的ping 命令，本身不会结束，超时之后，加个 ctrl+c 就可以结束这条命令
	return，表示正常结束整个脚本，不再继续执行，返回代码为0
	return 0，表示正常结束整个脚本，返回代码为0，和return相同
	return 1，表示异常结束整个脚本，1表示错误代码，可以为>0的任何数字

4.{error:^} ，标识如果返回数据里存在什么字符串的话，就代表这条命令执行失败
	字符串可以多个，由 | 隔开，也可以是正则表达式  如  aaa|bbb|/[ab]c/
	如果要匹配 “{” “}” “|”，在前面加\ ,如{error:#|\}|\||>}
	正则表达式里不需要这样做
	由于“/”标识的是正则的开始，所以该标识量不能匹配“/”

5.{edeal:}，搜索到错误串之后，如何处理，参考 3.{tdeal:ctrl+c} 

6.{match:#|>} 表示该命令遇到什么字符或字符串时，意味着结束 ，这里写了两个 #|> ，也可以写正则表达式，参考 {error:^} 
	注意：非正则表达式的话，匹配的将是返回数据的最后字符或字符串，如 # 可以匹配到 root@SD-AAA:~# ，但匹配不到root@SD-AAA:~#$，因为#不是最后字符
	这样做，可以尽量避免匹配失误。不过正则表达式没有此限制
	{match:prompt} ，表示采用上次命令执行之后的提示行作为该命令的匹配串，如上一条命令最后返回“root@SD-AAA:~#”，这条命令也匹配这一串

7.{save:/tmp/lsrreturn.txt}，表示将这条命令的返回数据保存到文件 	tmp/lsrreturn.txt 里

8.{enter:\n} 表示这条命令 采用的换号符是\n ,比如windows系统，执行一条命令就必须用 \r\n ，才会换行


在php代码里要想输出信息到返回串里的话，放到变量$returnmsg里
而要想在php代码里结束脚本的话，用"return"语句，后面可以跟数字
如return 1；
例：
>>enable {match:>|#}{result:enres}
if($enres['match']=='>'){//匹配到 > 而不是 #
	$returnmsg="need pivilage 15";//将要输出的信息放到$returnmsg里
	return 1;//中断脚本，并返回错误代码 1 .
}	
		
out;
		
		return $this->out(0,$text);
	}
	
	public static function optionsBackup($prex=[]){
		return self::options($prex,['sc_type'=>'1']);
	}
	
	
}

?>