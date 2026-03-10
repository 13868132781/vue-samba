<?php
namespace app\radUser;
use app\radPerm\perm;
use app\radAuth\authWay;
use app\radUser\radUserOrgan;
use appsys\sysEnvir\sysEnvir;

class radUser extends \table{
	public $pageName='用户';
	public $TN = "sdaaa.raduser";
	public $colKey = "usid";
	public $colOrder = "";
	public $colFid = "";
	public $colUnit = "us_organ";
	public $colName = "us_name";
	public $colNafy = "us_user";
	public $orderDesc = false;
	public $POST = [];
	public $colCrypt=['us_passval'];//as_pass存储时要进行加密
	
	public function gridSet(){
		
		$dftaw = authWay::DB()->where('aw_default','1')->value('aw_name');
		
		$gridSet=[
			'columns'=>[
				['col'=>'us_name','name'=>'姓名'],
				['col'=>'us_user','name'=>'登录名'],
				['col'=>'us_active','name'=>'启停',
					'type'=>'onoff',
					'goto'=>'active',
					'align'=>'center',
					'width'=>'40px',
					'refresh'=>true,//点击后强制刷新
					'auditOper'=>'启停用户',
				],
				['col'=>'us_status','name'=>'状态',
					'valMap'=>[
						'0'=>'正常',
						'_default_'=>'异常',
					],
					'overShow'=>'us_status_msg',
					'dotMap'=>[
						'正常'=>'#385E0F',
						'异常'=>'#FF9912',
					],
					'align'=>'center',
					'type'=>'fetch',
					'showType'=>'text',
					'popTitle'=>'状态详情',
					'post'=>['goto'=>'statusMsg'],
				],
				[
					'col'=>'us_gpid',
					'name'=>'角色',
					'valMap'=>perm::options(['0'=>'未设置']),
				],
				[
					'col'=>'us_tfa',
					'name'=>'认证',
					'valMap'=>authWay::options(['0'=>'-'.$dftaw.'-']),
					'modify'=>function($val,&$row)use($dftaw){
						if($val=='-'.$dftaw.'-'){
							return [
								'value'=>$dftaw,
								'color'=>'#888'
							];
						}
						return $val;
					}
				],
				/*
				['col'=>'us_seed','name'=>'二维码',
					'jsCtrl'=>[
						'type'=>'fetch',
						'name'=>'二维码',
						'align'=>'center',
						'com'=>'qrcodeShow',
						'goto'=>'qrcode',
						'popWidth'=>'400px',
						'popHeight'=>'420px',
					]
				
				]
				*/
			],
			
			'rowOper'=>function($row){
				if($row['us_user']=='admin'){
					$row['_operDelEnable_']=false;
				}
				return $row;
			},
			
			'toolFilterEnable'=>true,
			'toolSearchColumn'=>[
				'us_user'=>'like',
				'us_name'=>'like',	
			],
			
			//下面两个，也可以移到toolExpands里的某个list类型按钮里
			//'toolDeleteEnable'=>true,//批量删除
			//'toolImportEnable'=>true,//批量导入
			'toolExpands'=>[ //form page execute batch html link list
				[
					'name'=>'检测状态',
					'type'=>'execute',
					'goto'=>'statusCheck',
				],
				[
					'name'=>'批量操作',
					'type'=>'list',
					'toolDeleteEnable'=>true,//批量删除
					'toolImportEnable'=>true,//批量导入
					'listOptions'=>[
						[
							'name'=>'批量修改启停',
							'type'=>'multEdit',
							'goto'=>'active',
							'popWidth'=>'500px',
							'popHeight'=>'200px',
						],
						[
							'name'=>'批量修改角色',
							'type'=>'multEdit',
							'goto'=>'role',
							'popWidth'=>'500px',
							'popHeight'=>'200px',
						]
					]
				],
			],
			
			'operExpands' =>[
				[
					'type'=>'fetch',
					'name'=>'二维码',
					'align'=>'center',
					'zdyCom'=>'qrcodeShow',
					'goto'=>'qrcode',
					'popWidth'=>'400px',
					'popHeight'=>'420px',
				],
			],
			
			
		];
		
		return $gridSet;
	}
	
	public function filterSet(){
		
		$back=[
			[
				"name"=>"姓名",
				"col"=>"us_name",
				"type"=>'text',
			],
			[
				"name"=>"登录名",
				"col"=>"us_user",
				"type"=>'text',
			],
			[
				"name"=>"启停",
				"col"=>"us_active",
				"type"=>'select',
				'options'=>[
					'0'=>'停用',
					'1'=>'启用',
				]
			],
			[
				"name"=>"状态",
				"col"=>"us_status",
				"type"=>'select',
				'options'=>[
					'0'=>'正常',
					'raw:>0'=>'异常',
				]
			],
			[
				"name"=>"角色",
				"col"=>"us_gpid",
				"type"=>'select',
				"options"=>perm::options(['0'=>'未设置']),
			],
			[
				"name"=>"认证",
				"col"=>"us_tfa",
				"type"=>'select',
				'options'=>authWay::options(['0'=>'未设置']),
			]
		];
		return $back;
	}
	
	
	public function crudAddSet(){//获取编辑字段信息
		$post=$this->POST;
		
		$back=[];
		$back[]=[
			"name"=>"姓名",
			"col"=>"us_name",
			"type"=>'text',
			"ask"=>true, 
			"import"=>true,
			'valid'=>[
				'type'=>'text',
				'max'=>15,
				'min'=>2
			],
			'width'=>'100px',
		];
		$back[]=[
			"name"=>"登录名",
			"col"=>"us_user",
			"type"=>'text',
			"import"=>true,
			"ask"=>true, 
			'unique'=>true,
			'valid'=>[
				'type'=>'text',
				'max'=>15,
				'min'=>4
			]
		];
		
		//$cxty = sysEnvir::eGet("user_pass_cxty");
		$cxty = '';
		//根据设定的角色组来确定密码复杂度
		//这里主要用于提交时，php的valid类验证
		if(isset($post['formVal']['us_gpid'])){
			$permRow = $this->getPermRow($post['formVal']['us_gpid']);
			if($permRow and isset($permRow['gl_passcxty'])){
				$cxty = $permRow['gl_passcxty'];
			}
		}
		
		$back[]=[
			"name"=>"密码",
			"col"=>"us_passval",
			"type"=>'password',
			"importValue"=>'qqq000,,,',
			"ask"=>true, 
			'valid'=>[
				'type'=>'password',
				'cxty'=>$cxty,
			]
		];
		$back[]=[
			"name"=>"密码确认",
			"col"=>"us_passval1",
			'ignore'=>true,
			"type"=>'password',
			"importValue"=>'qqq000,,,',
			"ask"=>true, 
			'valid'=>[
				'type'=>'same',
				'as' =>'us_passval',
					
			]
		];
		$back[]=[
			"name"=>"历史密码",
			"col"=>"us_passvaly",
			"type"=>'hidden',
			//hidden是不传到浏览器
			//在添加时，有formVal，用formVal，没有就根据value填
			//在修改时，有formVal，用formVal，没有就忽略
		];
		
		$back[]=[
			"name" => "认证方式",
			"col" => "us_tfa",
			"type" => 'select',
			"value" => authWay::DB()->where('aw_default','1')->value('awid'),
			"options" => authWay::options(),
			"ask"=>true, 
		];
		$back[]=[
			"name"=>"角色",
			"col"=>"us_gpid",
			"type"=>'select',
			"value" => perm::DB()->where('gp_default','1')->value('gpid'),
			"options"=>perm::options(),
			"ask"=>true, 
		];
		
		$ourow=(new radUserOrgan)->getFirst();
		$back[]=[
			"name"=>"机构",
			"col"=>"us_organ",
			"type"=>'treePick',
			'import'=>true,
			"value"=>$ourow?$ourow['ouid']:'',//默认值
			"xsname"=>$ourow?$ourow['ou_name']:'',//默认显示
			"router"=>'/radUser/radUserOrgan',
			"data"=>'',
			"ask"=>true, 
			"hintMore"=>'没有机构可选的话，请先创建机构',
		];
		$back[]=[
			"name"=>"手机号",
			"col"=>"us_phone",
			"type"=>'text',
			'valid'=>[
				'type'=>'phone',	
			],
			'hintMore'=>'发送通知和告警',
		];
		$back[]=[
			"name"=>"邮箱",
			"col"=>"us_email",
			"type"=>'text',
			'valid'=>[
				'type'=>'email',	
			],
			'hintMore'=>'xxx@bbb.com',
			
		];
		$back[]=[
			"name"=>"OTP周期",
			"col"=>"us_seep",
			"type"=>'radio',
			"value"=>'60',
			'options'=>[
				'30' => '口令30秒钟变动',
				'60' => '口令60秒钟变动'
			],
			"ask"=>true, 
			'hintMore'=>'一次口令的变更时间间隔',
		];
		$back[]=[
			"name"=>"种子",
			"col"=>"us_seed",
			"value"=> sdOtpSeedGet(),
			"type"=>'hidden',
			//hidden是不传到浏览器
			//在添加时，有formVal，用formVal，没有就根据value填
			//在修改时，有formVal，用formVal，没有就忽略
		];
		
		$back[]=[
			"name"=>"客户端标识",
			"col"=>"us_rad_clientid",
			"type"=>'text',
			'hintMore'=>'radius客户端标识',
		];
		$back[]=[
				"name"=>"返回属性",
				"col"=>"us_rad_reply",
				"type"=>'table',
				'headers'=>['属性名','属性值'],
				'hintMore'=>'radius返回属性',
		];
		$back[]=[
				"name"=>"额外变量",
				"col"=>"us_exvar",
				"type"=>'table',
				'headers'=>['变量名','变量值'],
				'hintMore'=>'可以在脚本里使用',
		];
		return $back;
	}
	
	
	public function crudModSet(){
		$back= $this->crudAddSet();
		return $back;
	}
	
	public function crudModBefore(){
		$post = &$this->POST;
		$post['formVal']['us_passvaly'] = '';
		$key = $post['key'];
		$row = $this->currentRow;
		$passvaly = $row['us_passvaly'];
		$gpid = $row['us_gpid'];
		$permRow = $this->getPermRow($gpid);
		//print_r($permRow);
		if($post['formVal']['us_passval']!='' 
		and $permRow and isset($permRow['gl_passnum']) 
		and $permRow['gl_passnum']>0){
			$passnum = $permRow['gl_passnum'];
			$passold = json_decode($passvaly,true)?:[];
			$passena = array_slice($passold,0,$passnum);
			$passn = md5($post['formVal']['us_passval']);
			foreach($passena as $pass){
				if($pass == $passn){
					return $this->out(1,[
						'us_passval'=>'与最近'.$passnum.'次旧密码有重复'
					]);
				}
			}
			array_unshift($passena,$passn);
			$passena = array_slice($passena,0,$passnum);
			$post['formVal']['us_passvaly'] = json_encode($passena);
		}
		
		return;
	}
	
	public function crudModAfter(){
		$post = &$this->POST;
		$key = $post['key'];
		if($post['formVal']['us_passval']!=''){
			$this->DB()->where($this->colKey,$key)
				->update([
					'us_limit_passtime'=>\DB::raw('now()')
				]);
		}
	}
	
	
	public function onoffAfter_active(){
		$key = $this->POST['key'];
		if($key){
			$this->DB()->where($this->colKey,$key)
			->update([
				'us_limit_usertime'=>\DB::raw('now()'),
				'us_limit_loginnum'=>'0',
				'us_limit_failnum'=>'0',
				'us_limit_passtime'=>\DB::raw('now()'),
			]);
			exec("php ".__DIR__."/ht/userCheck.php ".$key);
		}
		
	}
	
	public function execute_statusCheck(){
		exec("php ".__DIR__."/ht/userCheck.php ",$res,$code);
		if($code){
			return $this->out(1,'',join('.',$res),false);
		}
		return $this->out(0,'','成功',true);
	}
	
	public function fetch_statusMsg(){
		$post = $this->POST;
		$key = $post['key'];
		$row = $this->getById($key);
		$data = str_replace("+","\n",$row['us_status_msg']?:'正常');
		
		return $this->out(0,$data);
	}
	
	
	public function fetch_qrcode(){
		$post=$this->POST;
		$key = $post['key'];
		$row = $this->getById($key);
		
		if($row['us_seep']=='' or $row['us_seep']=='0'){
			$row['us_seep'] = '60';
			$this->DB()->where($this->colKey,$key)
			->update([
				'us_seep' => $row['us_seep']
			]);
		}
		
		if($row['us_seed']=='' or (isset($post['refresh'])and $post['refresh'])){
			$row['us_seed'] = sdOtpSeedGet();
			$this->DB()->where($this->colKey,$key)
			->update([
				'us_seed' => $row['us_seed']
			]);
		}
		
		
		$outstr=sdOtpQrcodeGet($row['us_user'].'(aaa)', $row['us_seed'], $row['us_seep']);
		
		return $this->out(0, $outstr);
	}
	
	public function getPermRow($gpid){
		$permRow=perm::DB()
			->where('gpid',$gpid)
			->first();
		if($permRow and isset($permRow['gp_limit'])){
			$limitRow = \app\radPerm\permLimit::DB()
				->where('glid',$permRow['gp_limit'])
				->first();
			$permRow = array_merge($permRow, $limitRow?:[]);
		}
		return $permRow;
	}
	
	public function multEditSet_active(){
		$back=[];
		$back[]=[
				"name"=>"启停",
				"width"=>'50px',
				"hintMore"=>'批量设置用户是否启用或停用',
				"col"=>"us_active",
				"type"=>'radio',
				"ask"=>true,
				"value"=>'1',
				'options'=>[
					'0'=>'停用',
					'1'=>'启用',
				]
		];
		return $back;
	}
	
	public function multEditSet_role(){
		$back=[];
		$back[]=[
				"name"=>"角色",
				"col"=>"us_gpid",
				"type"=>'select',
				"value" => perm::DB()->where('gp_default','1')->value('gpid'),
				"options"=>perm::options(),
				"ask"=>true,
		];
		return $back;
	}

}


?>