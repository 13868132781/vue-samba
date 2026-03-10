<?php

namespace app\sdLdap;
use appsys\sysEnvir\sysEnvir;

class adUser extends \table {
	public $pageName="域账户";
	public $TN = "";
	public $colKey = "id";
	public $colOrder = "";
	public $colFid = "";
	public $colName = "loginname";
	public $orderDesc = true;
	public $POST = [];
	public $zdyBackend=true;
	
	public $fenyeNum = 20;
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'loginname','name'=>'登录名'],
				['col'=>'displayname','name'=>'显示名'],
				['col'=>'mail','name'=>'邮箱'],
				['col'=>'phone','name'=>'手机'],
				['col'=>'mfa',
				'name'=>'双因素白名单',
				'type'=>'html',
				'align'=>'center',
				'goto'=>'modMfa',
              'autoSave' => true,				
				'modify' => function ($text, $row) {
    $val = $row['mfa'] ?? '0';
    $checked = $val == '1' ? 'checked' : '';
    $id = $row['id']; // 用户DN

    return [
        'value' => "
            <label class='inline-check'>
                <input type='checkbox' 
                       data-col='mfa' 
                       name='mfa' 
                       $checked 
                       data-id='$id'>
                <span class='check-label'></span>
            </label>",
        'type' => 'html'
    ];
}
				
				],
				['col'=>'userpolicy','name'=>'控制',
					'type'=>'edit',
					'popTitle'=>'账户和密码策略',
					'goto'=>'adUserCtrl',
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center'
				],
				/*
				['col'=>'gp_organ','name'=>'属性',
					'type'=>'dialog',
					'popTitle'=>'属性详细信息',
					'router'=>'/sdLdap/adUserAttr',
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center',
				],
				*/
			],
			
			'rowOper'=>function($row){
				if($row['loginname']=='Administrator'){
					//$row['_operModEnable_']=false;
					$row['_operDelEnable_']=false;
				}
				return $row;
			},
			
			
			'operExpands'=>[ //form page execute batch html link list
				[
					'name'=>'属性列表',
					'type'=>'dialog',
					'popTitle'=>'属性详细信息',
					'router'=>'/sdLdap/adUserAttr',
					'popWidth'=>'80%',
					'popHeight'=>'80%',
					'align'=>'center',
				],
				[
					'name'=>'所属的组',
					'type'=>'dialog',
					'router'=>'/sdLdap/adUserGroup',
				],
				[
					'name'=>'登录测试',
					'type'=>'fetch',
					'zdyCom'=>'loginTest',
					'goto'=>'loginTest',
					'popTitle'=>'登陆测试',
					'popWidth'=>'400px',
					'popHeight'=>'420px',
				],
				[
					'name'=>'二维码',
					'type'=>'fetch',
					'zdyCom'=>'qrcodeShow',
					'goto'=>'qrcode',
					'popTitle'=>'二维码',
					'popWidth'=>'400px',
					'popHeight'=>'420px',
				]
			],
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			//'toolDeleteEnable'=>true,
			'toolImportEnable'=>true,
			'operDelEnable'=> true,
			'fenyeEnable'=> true,
			'fenyeNum'=> $this->fenyeNum ,
			
			'toolSearchColumn'=>[
				'name'=>'like',	
			],
			
		];
		return $gridSet;
	}
	
	public function api_ModMfa() {
    $post = &$this->POST;

    // 获取用户 DN 和 MFA 值
    $userDn = $post['key'];
    $val = $post['formVal']['mfa'] ?? '0';

    // 提取用户名
    preg_match('/CN=([^,]+)/', $userDn, $matches);
    $username = $matches[1] ?? 'unknown';

    // 连接数据库
    $mysqli = new \mysqli("127.0.0.1", "root", "jbgsn!2716888", "radius");
    if ($mysqli->connect_error) {
        return $this->out(1, '', "数据库连接失败: " . $mysqli->connect_error);
    }

    // 更新 mfa 字段
    $stmt = $mysqli->prepare("UPDATE radius.rad_user SET mfa = ? WHERE UserName = ?");
    if (!$stmt) {
        return $this->out(2, '', "SQL 准备失败");
    }

    $stmt->bind_param("ss", $val, $username);
    if (!$stmt->execute()) {
        return $this->out(3, '', "数据库更新失败: " . $stmt->error);
    }

    $stmt->close();
    $mysqli->close();

    return $this->out(0, '', '修改成功');
}
	public function apiModMfa() {
    return $this->editSaveBefore_modMfa();
}
	public function zdySource($inopt=[]){
		//$inopt['byid']='123';
		//$inopt['count']=true;
		//$inopt['where']=[];
		//sdAlert(phpversion());
		$mysql = new \mysqli("127.0.0.1", "root", "jbgsn!2716888", "radius");
		$data=[];
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$cookie = '';//空或1，1表示后面还有数据
		$jishu=0;
		do{
			$data=[];
			
			$controls=[];
			$controls[] = [
				'oid'=>LDAP_CONTROL_PAGEDRESULTS,//分页
				'critical' => true, 
				'value' => ['size' => $this->fenyeNum, 'cookie' => $cookie]];
				
			$controls[] = [
				'oid'=>LDAP_CONTROL_SORTREQUEST ,//排序
				'critical' => true, 
				'value' => [['attr' =>'name']]];
				
			/* 这个属性不靠谱，而且和分页属性冲突
			$controls[] = [
				'oid'=>LDAP_CONTROL_VLVREQUEST ,
				'critical' => true, 
				'value' => ['attrvalue'=>'(name=miao9090)','before'=>0,'after'=>3]];
			*/
			$unitId = isset($this->POST['unitId'])?$this->POST['unitId']:'';
			// 搜索LDAP
			$mySearch='';
			//属性必须是全小写
			$attrs= ['name','mail','telephonenumber','displayname','useraccountcontrol','pwdlastset','accountexpires','userworkstations','telexnumber', 'postofficebox'];
			if(isset($inopt['byid'])){//单独一个条目
				$result = ldap_read($ldapconn, $inopt['byid'],"(objectclass=*)",$attrs);
				
			}else{
				$mySearch = "";
				if(isset($this->POST['search']) and $this->POST['search']!=''){//全局搜索
					$mySearch='(name=*'.$this->POST['search'].'*)';
				}
				
				if($unitId){//列表，不查找子节点
					$objectCategory="CN=Person,CN=Schema,CN=Configuration,".$ldap_dn;
					$filter = '(&(objectCategory='.$objectCategory.')'.$mySearch.')';
					$result = ldap_list($ldapconn, $unitId, $filter, $attrs,0,0,0,0,$controls);
					
				}else{//没有OU信息，就全局搜索
					$objectCategory="CN=Person,CN=Schema,CN=Configuration,".$ldap_dn;
					$filter = '(&(objectCategory='.$objectCategory.')'.$mySearch.')';
					$result = ldap_search($ldapconn, $ldap_dn, $filter, $attrs,0,0,0,0,$controls);
				}
			}
			// 获取搜索结果
			if (!$result) {
				break;
			}
			$entries = ldap_get_entries($ldapconn, $result);
			
			//sdAlert($entries);
			for ($i = 0; $i < $entries['count']; $i++) {
				$one = $entries[$i];
				
				$displayname = '';
				if(isset($one['displayname'])){
					$displayname = $one['displayname'][0];
				}
				//从数据库获取信息
				$username = $one['name'][0];
				
    $stmt = $mysql->prepare("SELECT mfa  FROM rad_user WHERE UserName = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($whiteListFromDb);
    $stmt->fetch();
    $stmt->close();

    $whiteList = $whiteListFromDb ?? '0'; // 默认 0
				
				//$postofficebox_val = adUtils::getVal($one, 'postofficebox');
				$datao=[
					"id" => $entries[$i]['dn'],
					"displayname" => $displayname,
					"loginname" => $one['name'][0],
					"mail" => adUtils::getVal($one,'mail'),
					"phone" => adUtils::getVal($one,'telephonenumber'),
					"dn" => $entries[$i]['dn'],
					"fadn" => explode(",",$entries[$i]['dn'],2)[1],
					"useraccountcontrol" => adUtils::getVal($one,'useraccountcontrol'),
					"pwdlastset"=> adUtils::getVal($one,'pwdlastset'),
					"accountexpires"=> adUtils::getVal($one,'accountexpires'),
					"userworkstations" => adUtils::getVal($one,'userworkstations'),
					"passval"=>'1',
					"optseed"=> adUtils::getVal($one,'telexnumber'),
					//"postofficebox" => adUtils::getVal($one,'postofficebox'),
	              "mfa" => $whiteListFromDb ?? '0',
				];
				
				$data[] = $this->userpolicy_disply($datao);
			}
			
			
			//获取分页返回信息
			$errcode = $dn = $errmsg = $refs = $rcontrols= null;
			$pres = ldap_parse_result($ldapconn, $result,$errcode,$dn,$errmsg,$refs,$rcontrols);
			//sdAlert($rcontrols);
			if(isset($rcontrols[LDAP_CONTROL_PAGEDRESULTS]) and $rcontrols[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie']){
				$cookie = $rcontrols[LDAP_CONTROL_PAGEDRESULTS]['value']['cookie'];
			}else{
				$cookie="";
			}
			
			if(isset($inopt['count'])){
				//若不够一页的话，size了为空，此时，就得看entries的计数了
				if(isset($rcontrols[LDAP_CONTROL_PAGEDRESULTS]) and $rcontrols[LDAP_CONTROL_PAGEDRESULTS]['value']['size']){
					return $rcontrols[LDAP_CONTROL_PAGEDRESULTS]['value']['size'];
				}else{
					return $entries['count'];
				}
			}
			
			
			
			$jishu++;
			$fenyenum = 0;
			if(isset($this->POST['fenye']['now'])){
				$fenyenum = $this->POST['fenye']['now'];
			}
			if($jishu>=$fenyenum){
				break;
			}
		
		}while(strlen($cookie) > 0);
		
		ldap_close($ldapconn);
		
		//usort($data, function($a,$b){
		//	return strcasecmp($a['loginname'], $b['loginname']);
		//});
				//echo "<pre>";
//print_r($data);
//echo "</pre>";
		return $data;

		
	}
	
	
	
	public function crudAddSet(){
		$post=&$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"登录名",
				"col"=>"loginname",
				"ask"=>true,
				"import"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"显示名",
				"col"=>"displayname",
				"import"=>true,
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"邮箱",
				"col"=>"mail",
				"import"=>true,
				"type"=>'text',
				'valid'=>[
					'type'=>'email',
				]
		];
		$back[]=[
				"name"=>"手机号",
				"col"=>"phone",
				"import"=>true,
				"type"=>'text',
				'valid'=>[
					'type'=>'phone',
				]
		];
		$cxty = sysEnvir::eGet("user_pass_cxty");
		$back[]=[
			"name"=>"密码",
			"col"=>"passval",
			"type"=>'password',
			"import"=>true,
			"importValue"=>'qqq000,,,',
			"ask"=>true, 
			'valid'=>[
				'type'=>'password',
				'cxty'=>$cxty,
			]
		];
		$back[]=[
			"name"=>"密码确认",
			"col"=>"passval1",
			'ignore'=>true,
			"type"=>'password',
			"importValue"=>'qqq000,,,',
			"ask"=>true, 
			'valid'=>[
				'type'=>'same',
				'as' =>'passval',
					
			]
		];
		$back[]=[
				"name"=>"隶属",
				"col"=>"fadn",
				"ask"=>true,
				"import"=>true,
				"type"=>'select',
				"options"=>adOrunit::options([],[],'','dnfy'),
		];
		return $back;
	}
	
	public function crudAddBefore(){
		$post=&$this->POST;
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		$ldap_dnd = $ldapobj[2];
		
		$displayname = $post['formVal']['displayname'];
		$loginname = $post['formVal']['loginname'];
		$mail = $post['formVal']['mail'];
		$phone = $post['formVal']['phone'];
		
		$passwd = $post['formVal']['passval'];
		
		$mydn = 'CN='.$loginname.','.$post['formVal']['fadn'];	 
		$pwdtxt = $passwd;
		$newPassword = "\"" . $pwdtxt . "\"";
		$len = strlen($newPassword);
		$newPassw = "";

		for($i=0;$i<$len;$i++) {
			$newPassw .= $newPassword[$i]."\000";
		}
	 
		
		$ldaprecord['objectclass'][0] = "top";
		$ldaprecord['objectclass'][1] = "person";
		$ldaprecord['objectclass'][2] = "organizationalPerson";
		$ldaprecord['objectclass'][3] = "user";
		if($mail!=''){
			$ldaprecord['mail'] = $mail;
		}
		if($phone!=''){
			$ldaprecord['telephoneNumber'] = $phone;
		}
		
		if($post['formVal']['passval']!=''){
			$ldaprecord["unicodepwd"] = $newPassw;
		}
		
		//属性框里账户标签下的登录名（windows 2000以前的版本）
		$ldaprecord["sAMAccountName"] = $loginname;//登录名
		
		//属性框里常规下的显示名
		$ldaprecord["displayName"] = $displayname;// 显示名
		
		//属性框里账户标签下的登录名
		$ldaprecord['userPrincipalName'] = $loginname.'@'.$ldap_dnd;
		
		$ldaprecord["UserAccountControl"] = "512";   //权限
		
		$r = ldap_add($ldapconn, $mydn, $ldaprecord);

		ldap_close($ldapconn);
   
      // 数据库连接配置
      $mysql_host = "127.0.0.1";      // MySQL 服务器地址
      $mysql_user = "root";           // MySQL 用户名
      $mysql_pass = "jbgsn!2716888";  // MySQL 密码
      $mysql_db = "radius";           // MySQL 数据库名称
      $rad_user_table = "rad_user";   // MySQL 表名称
      
      $mysqli = new \mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
      
      $stmt = $mysqli->prepare("INSERT INTO radius.rad_user (UserName, mobile_nmb, user_email) VALUES (?, ?, ?) ");
                               
      if (!$stmt) {
          error_log("准备 SQL 语句失败: (" . $mysqli->errno . ") " . $mysqli->error);
          echo "准备 SQL 语句失败: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
          ldap_close($ldap_conn);
          return;
      }
  
      // 绑定参数
      
      if (!$stmt->bind_param("sss", $loginname, $phone, $mail)) {
          error_log("绑定参数失败: (" . $stmt->errno . ") " . $stmt->error);
          echo "绑定参数失败: (" . $stmt->errno . ") " . $stmt->error . "\n";
          $stmt->close();
          ldap_close($ldap_conn);
          return;
      }
      
      // 检查连接
      if ($mysqli->connect_error) {
          die("MySQL 连接失败: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
      }
      
      // 设置字符集为 UTF-8
      if (!$mysqli->set_charset("utf8")) {
          printf("加载字符集 utf8 失败: %s\n", $mysqli->error);
      }
      
      if (!$stmt->execute()) {
          error_log("同步用户 $loginname 失败: (" . $stmt->errno . ") " . $stmt->error);
          echo "  同步用户 $loginname 失败: (" . $stmt->errno . ") " . $stmt->error . "\n";
      } 
      
      $stmt->close();
      $mysqli->close();
		
		return $this->out(0,'','添加成功');	
	}
	
	
	
	
	
	public function crudModSet(){
		$post=&$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"登录名",//ad上登录名改不了的
				"col"=>"loginname",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"显示名",
				"col"=>"displayname",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"邮箱",
				"col"=>"mail",
				"type"=>'text',
				'valid'=>[
					'type'=>'email',
				]
		];
		$back[]=[
				"name"=>"手机号",
				"col"=>"phone",
				"type"=>'text',
				'valid'=>[
					'type'=>'phone',
				]
		];
		$cxty = sysEnvir::eGet("user_pass_cxty");
		$back[]=[
			"name"=>"密码",
			"col"=>"passval",
			"type"=>'password',
			"importValue"=>'qqq000,,,', 
			'valid'=>[
				'type'=>'password',
				'cxty'=>$cxty,
			]
		];
		$back[]=[
			"name"=>"密码确认",
			"col"=>"passval1",
			'ignore'=>true,
			"type"=>'password',
			'valid'=>[
				'type'=>'same',
				'as' =>'passval',
					
			]
		];
		$back[]=[
				"name"=>"隶属",
				"col"=>"fadn",
				"ask"=>true,
				"type"=>'select',
				"options"=>adOrunit::options([],[],'','dnfy'),
		];
		return $back;
	}
	
	public function crudModBefore(){
		$post=&$this->POST;
		
		$key = $post['key'];
		$row = $this->currentRow;
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		$ldap_dnd = $ldapobj[2];
		
		$displayname = $post['formVal']['displayname'];
		$loginname = $post['formVal']['loginname'];
		$mail = $post['formVal']['mail'];
		$phone = $post['formVal']['phone'];
		
		$passwd = $post['formVal']['passval'];
		
		$mydn = $this->POST['key'];	 
		$pwdtxt = $passwd;
		$newPassword = "\"" . $pwdtxt . "\"";
		$len = strlen($newPassword);
		$newPassw = "";

		for($i=0;$i<$len;$i++) {
			$newPassw .= $newPassword[$i]."\000";
		}
	 
		
		$ldaprecord['objectclass'][0] = "top";
		$ldaprecord['objectclass'][1] = "person";
		$ldaprecord['objectclass'][2] = "organizationalPerson";
		$ldaprecord['objectclass'][3] = "user";
		if($mail!=''){
			$ldaprecord['mail'] = $mail;
		}
		if($phone!=''){
			$ldaprecord['telephoneNumber'] = $phone;
		}
		if($post['formVal']['passval']!=''){
			$ldaprecord["unicodepwd"] = $newPassw;
		}
		
		//属性框里账户标签下的登录名（windows 2000以前的版本）
		$ldaprecord["sAMAccountName"] = $loginname;//登录名
		
		//属性框里常规下的显示名
		$ldaprecord["displayName"] = $displayname;// 显示名
		
		//属性框里账户标签下的登录名
		$ldaprecord['userPrincipalName'] = $loginname.'@'.$ldap_dnd;
		
		//$ldaprecord["UserAccountControl"] = "512";   //权限
		
		$r = ldap_modify($ldapconn, $mydn, $ldaprecord);
		$err = ldap_error($ldapconn);
		if(!$r){
			ldap_close($ldapconn);
			return $this->out(1,'',$err);	
		}
		
		$newdn = 'CN='.$loginname.','.$post['formVal']['fadn'];	
		if($newdn != $mydn){
			$newdnx = 'CN='.$loginname;
			$fadnx = $post['formVal']['fadn'];
			$r = ldap_rename($ldapconn, $mydn, $newdnx, $fadnx , true);
			$err = ldap_error($ldapconn);
			if(!$r){
				ldap_close($ldapconn);
				return $this->out(1,'',$err);	
			}
		}
		
		ldap_close($ldapconn);
		return $this->out(0,'','修改成功');	
	}
	
	public function crudDelBefore(){
		$post=&$this->POST;
		
		$key = $post['key'];
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$r = ldap_delete($ldapconn, $key);

		ldap_close($ldapconn);
		
		return $this->out(0,'','删除成功');	
		
	}
	
	
	public function fetch_loginTest(){
		$post=&$this->POST;
		$key = $post['key'];
		$row = $this->getById($key);
		
		$user= $row['loginname'];
		$pass = $post['pass'];
		
		$err=(new adServer)->ldapLoginTest($user,$pass);
		$code=1;
		if(!$err){
			$err = '登录成功'; 
			$code=0;
		}
		return $this->out($code, $err);
	}
	
	
	
	public function editSet_adUserCtrl(){
		$post=&$this->POST;
		$row=$this->currentRow;
		
		
		$back=[];
		$back[]=[
				"name"=>"登录名",//ad上登录名改不了的
				"col"=>"loginname",
				"ask"=>true,
				"type"=>'show',
		];
		$back[]=[
				"name"=>"显示名",
				"col"=>"displayname",
				"ask"=>true,
				"type"=>'show',
		];
		$back[]=[
				"name"=>"密码策略",
				"col"=>"sd_passpolicy",
				"type"=>'selectms',
				"options"=>[
					'sd_pp_loginModPass'=>'用户下次登录时，需要修改密码',
					'sd_pp_cantModPass'=>'用户不能更改密码',
					'sd_pp_passnoexp'=>'密码永不过期',
				],
		];
		$back[]=[
				"name"=>"账户策略",
				"col"=>"sd_accountplicy",
				"type"=>'selectms',
				"options"=>[
					'sd_ap_disable'=>'禁用账户（手动禁用或账户过期时间到了也会禁用）',
					'sd_ap_lockout'=>'锁定账户（多次登录失败时自动锁定，锁定时间到了会自动解锁。不可手动锁定，但可手动解锁）',
				],
		];
		$back[]=[
				"name"=>"账户过期",
				"col"=>"sd_accountexpdate",
				"hintMore"=>"为空表示永不过期，过期后会禁用账户",
				"type"=>'datePick',
				"dateType"=>0,
		];
		$back[]=[
				"name"=>"登录工作站",
				"col"=>"sd_userworkstations",
				"hintMore"=>"dc1,dc2,dc3    为空表示可登录所有计算机",
				"type"=>'text',
		];
		/*
		$back[]=[
				"name"=>"账户过期时间",
				"col"=>"accountexptime2",
				//"hintMore"=>"0或9223372036854775807表示永不过期",
				"type"=>'datePickm',
		];
		*/
		return $back;
	}
	
	
	public $policyList=[
			'sd_passpolicy'=>'',
			'sd_pp_loginModPass'=>'',//下次登录时修改密码
			'sd_pp_cantModPass'=>'',
			'sd_pp_passnoexp'=>'',
			
			'sd_accountplicy'=>'',
			'sd_ap_disable'=>'',
			'sd_ap_lockout'=>'',
			
			'sd_accountexpdate'=>'',
	];
	
	public function editSaveBefore_adUserCtrl(){
		$post = &$this->POST;
		$form = $post['formVal'];
		$back = $this->policyList;
		foreach($form as $k=>$v){
			$back[$k]=$v;
		}
		$passpolicys = explode('&',$back['sd_passpolicy']);
		foreach($passpolicys as $po){
			$back[$po] = 'yes';
		}

		$accountplicy = explode('&',$back['sd_accountplicy']);
		foreach($accountplicy as $po){
			$back[$po] = 'yes';
		}
		
		$res=[];
		
		if($back['sd_pp_loginModPass'] and $back['sd_pp_passnoexp']){
			$res['sd_passpolicy']="‘下次登录时修改密码’和‘密码永不过期’不可同时设置";
			return $this->out(1,$res);
		}
		if($back['sd_pp_loginModPass'] and $back['sd_pp_cantModPass']){
			$res['sd_passpolicy']="‘下次登录时修改密码’和‘用户不能修改密码’不可同时设置";
			return $this->out(1,$res);
		}
		
		if($back['sd_ap_lockout']){
			$res['sd_accountplicy']="不可手动锁定账户";
			return $this->out(1,$res);
		}
		
		$key = $post['key'];
		$row = $this->currentRow;
		
		$ldapobj = (new adServer)->ldapGetConn();
		$ldapconn = $ldapobj[0];
		$ldap_dn = $ldapobj[1];
		
		$saveArr=[];
		
		//可登录的工作站
		if($back['sd_userworkstations']){
			$saveArr['userworkstations'] = $back['sd_userworkstations'];
		}
		//账户过期时间
		if($back['sd_accountexpdate']){
			$toffset = strtotime('1970-01-01 00:00:00')-strtotime('1601-01-01 00:00:00');
			$timestamp = strtotime($back['sd_accountexpdate'].' 23:59:59');
			$saveArr['accountexpires'] = ($timestamp+$toffset)*10000000;
		}else{
			$saveArr['accountexpires'] = '9223372036854775807';//或为0 或小于0，好像都行
		}
		
		$bits = adUtils::displayBit($row['useraccountcontrol']);
		
		//下次登录时修改密码
		if($back['sd_pp_loginModPass']){
			$saveArr['pwdlastset']='0';
			$bits[65536]=0; //那密码就不能时永不过期
		}else{
			$saveArr['pwdlastset']='-1';
		}
		
		//设置为密码永不过期
		if($back['sd_pp_passnoexp']){
			$bits[65536]=1;
			$saveArr['pwdlastset']='-1';
		}else{
			$bits[65536]=0;
		}
		
		//用户不能修改密码
		if($back['sd_pp_cantModPass']){
		}else{
		}
		
		//禁用账户
		if($back['sd_ap_disable']){
			$bits[2]=1;
		}else{
			$bits[2]=0;
		}
		
		//锁定账户
		if($back['sd_ap_lockout']){
			//下面设置时无效的，不能手动锁定账户
			//$bits[16]=1;
			//$saveArr['lockouttime']='-1';
		}else{
			$bits[16]=0;
			$saveArr['lockouttime']='0';
			$saveArr['badPwdCount']='0';
		}
		
		$bitn = 0;
		foreach($bits as $k => $v){
			if($v){
				$bitn += $k;
			}
		}
		$saveArr['useraccountcontrol']=$bitn;
		
		$r = ldap_modify($ldapconn, $key, $saveArr);
		
		return $this->out(0,'','修改成功');
	}
	
	
	public function userpolicy_disply($input){
		$back=$this->policyList;
		
		$bits = adUtils::displayBit($input['useraccountcontrol']);
		if($bits[64]){//用户不能修改密码  好像也不是这个参数
			$back['sd_passpolicy'].='&sd_pp_cantModPass';
			$back['sd_pp_cantModPass']='yes';
		}
		if($bits[65536]){
			$back['sd_passpolicy'].='&sd_pp_passnoexp';
			$back['sd_pp_passnoexp']='yes';
		}
		if($input['pwdlastset']=='0' and !$back['sd_pp_passnoexp']){
			$back['sd_passpolicy'].='&sd_pp_loginModPass';
			$back['sd_pp_loginModPass']='yes';
		}
		
		
		if($bits[2]){
			$back['sd_accountplicy'].='&sd_ap_disable';
			$back['sd_ap_disable']='yes';
		}
		if($bits[16]){
			$back['sd_accountplicy'].='&sd_ap_lockout';
			$back['sd_ap_lockout']='yes';
		}
		
		$back['sd_passpolicy']=trim($back['sd_passpolicy'],'&');
		$back['sd_accountplicy']=trim($back['sd_accountplicy'],'&');
		
		if($input['accountexpires'] and $input['accountexpires']!='9223372036854775807'){ 
			$toffset = strtotime('1970-01-01 00:00:00')-strtotime('1601-01-01 00:00:00');
			$unixTimestamp = (intval($input['accountexpires'])/10000000)-$toffset;
			$back['sd_accountexpdate'] = date('Y-m-d', $unixTimestamp);
		}
		
		
		$back['sd_userworkstations']=$input['userworkstations'];
		
		foreach($back as $k=>$v){
			$input[$k]=$v;
		}
		
		return $input;
	}
	public function userpolicy_save($input){
		
		
	}
	
	
	public function fetch_qrcode(){
		$post=$this->POST;
		$key = $post['key'];
		$row = $this->getById($key);
		
		$user= $row['loginname'];
		
		$period = 60;
		
		$seed = $row['optseed'];
		if(!$seed or (isset($post['refresh'])and $post['refresh'])){
			$seed = sdOtpSeedGet();
			
			$ldapobj = (new adServer)->ldapGetConn();
			$ldapconn = $ldapobj[0];
			$ldap_dn = $ldapobj[1];
			
			$ldaprecord['telexNumber'] = $seed;
			
			$r = ldap_modify($ldapconn, $key, $ldaprecord);

			ldap_close($ldapconn);
      
      // 数据库连接配置
      $mysql_host = "127.0.0.1";      // MySQL 服务器地址
      $mysql_user = "root";           // MySQL 用户名
      $mysql_pass = "jbgsn!2716888";  // MySQL 密码
      $mysql_db = "radius";           // MySQL 数据库名称
      $rad_user_table = "rad_user";   // MySQL 表名称
      
      $mysqli = new \mysqli($mysql_host, $mysql_user, $mysql_pass, $mysql_db);
      
      $stmt = $mysqli->prepare("INSERT INTO radius.rad_user (UserName, seed) VALUES (?, ?) 
                               ON DUPLICATE KEY UPDATE seed = ?");
                               
      if (!$stmt) {
          error_log("准备 SQL 语句失败: (" . $mysqli->errno . ") " . $mysqli->error);
          echo "准备 SQL 语句失败: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
          ldap_close($ldap_conn);
          return;
      }
  
      // 绑定参数
      
      if (!$stmt->bind_param("sss", $user, $seed, $seed)) {
          error_log("绑定参数失败: (" . $stmt->errno . ") " . $stmt->error);
          echo "绑定参数失败: (" . $stmt->errno . ") " . $stmt->error . "\n";
          $stmt->close();
          ldap_close($ldap_conn);
          return;
      }
      
      // 检查连接
      if ($mysqli->connect_error) {
          die("MySQL 连接失败: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);
      }
      
      // 设置字符集为 UTF-8
      if (!$mysqli->set_charset("utf8")) {
          printf("加载字符集 utf8 失败: %s\n", $mysqli->error);
      }
      
      if (!$stmt->execute()) {
          error_log("同步用户 $user 失败: (" . $stmt->errno . ") " . $stmt->error);
          echo "  同步用户 $user 失败: (" . $stmt->errno . ") " . $stmt->error . "\n";
      } 
      
      $stmt->close();
      $mysqli->close();

		}
		
		$outstr = sdOtpQrcodeGet($user.'(adotp)',$seed,$period);
		
		return $this->out(0, $outstr);
	}
	
	


}
/*
说明：
一.下次登录时修改密码：
	pwdLastSet表示最后一次修改密码的时间，为0表示没有修改过密码	
	选定：
		1.请将 pwdLastSet 属性设置0。 
		2.且UserAccountControl里的ADS_UF_DONT_EXPIRE_PASSWD(密码永不过期)设置0
	取消：
		1.pwdLastSet设置为-1,系统会将pwdLastSet设置为当前时间戳
		
		
二.用户不能更改密码
	选定：
	取消：

三.密码永不过期
	选定：
		1.UserAccountControl里的ADS_UF_DONT_EXPIRE_PASSWD(密码永不过期)设置1
	取消：
		1.UserAccountControl里的ADS_UF_DONT_EXPIRE_PASSWD(密码永不过期)设置0
	
	
	
四.禁用账户（手动禁用或账户过期时间到了也会禁用）
	选定：
		1.UserAccountControl里的ADS_UF_ACCOUNTDISABLE设置1
	取消：
		1.UserAccountControl里的ADS_UF_ACCOUNTDISABLE设置0
		
		
五.锁定账户（通常是多次登录失败时自动锁定，锁定时间到了也会自动解锁）
lockoutTime: 账户被锁定的时间
lockoutThreshold: 无效登录尝试次数阈值
lockoutDuration: 账户锁定间隔
lockOutObservationWindow: 重置无效登录尝试计数器的时间
badPwdCount: 无效登录尝试次数
	选定：
		1.UserAccountControl里的ADS_UF_LOCKOUT设置1
		2.lockouttime为 当前
	取消：
		1.UserAccountControl里的ADS_UF_LOCKOUT设置0
		2.lockouttime为 0
		3.badPwdCount为 0
		
*/



//pt-v1XtIlvNwTphpgsbQbYCwjVe_f35f5c3a-753d-47ae-9a89-97dab2692e0e

?>

