<?php

namespace app\sdLdap;

class adServer extends \table {
	public $pageName="域服务器";
	public $TN = "sdsamba.adserver";
	public $colKey = "asid";
	public $colOrder = "as_order";
	public $colFid = "";
	public $colName = "as_name";
	public $orderDesc = false;
	public $POST = [];
	public $colCrypt=['as_pass'];//as_pass存储时要进行加密
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'as_name','name'=>'名称'],
				['col'=>'as_url','name'=>'IP地址'],
				['col'=>'as_domain','name'=>'域名'],
				['col'=>'as_user','name'=>'用户'],
				['col'=>'as_default','name'=>'默认',
					'type'=>'radio',
					'goto'=>'enable',
					'align'=>'center',
					'width'=>'50px',
				],
				['col'=>'as_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center',
					'width'=>'50px',
				],
			],
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operDelEnable'=> true,
			'fenyeEnable'=> false,
			
			'toolSearchColumn'=>[
				'name'=>'like',	
			],
			
		];
		
		return $gridSet;
	}
	
	
	
	public function crudAddSet(){
		$post=&$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"名称",
				"col"=>"as_name",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"地址",
				"col"=>"as_url",
				"ask"=>true,
				"type"=>'text',
				"hintMore"=>'如：ldaps://1.1.1.1:636',
		];
		$back[]=[
				"name"=>"域名",
				"col"=>"as_domain",
				"ask"=>true,
				"type"=>'text',
				"hintMore"=>'如：test.com',
		];
		$back[]=[
				"name"=>"用户名",
				"col"=>"as_user",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
			"name"=>"密码",
			"col"=>"as_pass",
			"type"=>'password',
			"importValue"=>'qqq000,,,',
			"ask"=>true, 
			'valid'=>[
				'type'=>'password',
				//'cxty'=>$cxty,
			]
		];
		$back[]=[
			"name"=>"密码确认",
			"col"=>"as_pass1",
			'ignore'=>true,
			"type"=>'password',
			"importValue"=>'qqq000,,,',
			"ask"=>true, 
			'valid'=>[
				'type'=>'same',
				'as' =>'as_pass',
					
			]
		];

		return $back;
	}
	
	public function crudModSet(){
		$post=&$this->POST;
		
		$back=$this->crudAddSet();
		
		return $back;
	}
	
	//获得ldap服务器参数
	public function ldapConnArgs(){
		$row = $this->DB()->where('as_default','1')->first();
		
		$ip = explode(':',explode("//",$row['as_url'])[1])[0];
		$dn="DC=".str_replace('.',',DC=',$row['as_domain']);
		
		$rown = [
			'name'=>$row['as_name'],
			'url'=>$row['as_url'],
			'ip'=>$ip,
			'domain'=>$row['as_domain'],
			'dn' => $dn,
			'user'=>$row['as_user'],
			'pass'=>$row['as_pass'],
			'stAuth' => " -U ".$row['as_user']."@".$row['as_domain']." --password='".$row['as_pass']."'",
		];
		return $rown;
	}
	
	//获得ldap连接
	public function ldapGetConn(){
		
		$dftaw = $this->DB()->where('as_default','1')->first();
		
		if(!$dftaw){
			return [null,null,null];
		}
		
		$ldap_host = $dftaw['as_url'];
		$domain = $dftaw['as_domain'];
		// 用户的凭证（如果需要进行验证的话）
		$ldap_username = $dftaw['as_user'].'@'.$domain;
		$ldap_password = $dftaw['as_pass'];
			 
		// 创建一个LDAP连接
		$ldapconn = ldap_connect($ldap_host) or die('无法连接到LDAP服务器');
		$timeout_sec = 15; 
		ldap_set_option($ldapconn, LDAP_OPT_TIMELIMIT, $timeout_sec);
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
		ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7); // 可用于调试
       ldap_set_option($ldapconn, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_NEVER);
		putenv('LDAPTLS_REQCERT=never'); // 跳过 SSL 证书验证（不安全）
				
		$ldapbind = ldap_bind($ldapconn, $ldap_username, $ldap_password);
        //$ldapbind = ldap_bind($ldapconn, "CN=Administrator,CN=Users,DC=IBM,DC=COM", $ldap_password);
        if (!$ldapbind) {
            $error = ldap_error($ldapconn); // Retrieve the LDAP error message
            $errno = ldap_errno($ldapconn); // Retrieve the LDAP error code
            die("LDAP绑定失败。错误码: $errno,错误信息: $error");
        }
		//显示详细错误
		$last_error = error_get_last();
       if ($last_error) {
       echo "PHP 错误: " . $last_error['message'] . " in " . $last_error['file'] . " on line " . $last_error['line'] . "\n";
}
		
		/* 读取支持的control		
		$result = ldap_read($ldapconn, '', '(objectClass=*)', ['supportedControl']);
		$entries = ldap_get_entries($ldapconn, $result);
		if (in_array(LDAP_CONTROL_PAGEDRESULTS, $entries[0]['supportedcontrol'])) {
			sdAlert(LDAP_CONTROL_PAGEDRESULTS);
			sdAlert($entries[0]['supportedcontrol']);
		}
		*/
		
		// 分页control
		//https://blog.csdn.net/zk_jy520/article/details/126073116
		//分页只能本次连接时有效
		
		
		$dn="DC=".str_replace('.',',DC=',$domain);
		return [$ldapconn,$dn,$domain];
	}
		
	//账户登录测试
	public function ldapLoginTest($user,$pass){
		
		$dftaw = $this->DB()->where('as_default','1')->first();
		
		if(!$dftaw){
			return '未找到ad服务器';
		}
		
		$ldap_host = $dftaw['as_url'];
		$domain = $dftaw['as_domain'];
		
		$ldap_username = $user.'@'.$domain;
		$ldap_password = $pass;
			 
		// 创建一个LDAP连接
		$ldapconn = @ldap_connect($ldap_host);
		if(!$ldapconn){
			return '无法连接到LDAP服务器';
		}
		
		// 使用凭证绑定到LDAP服务器
		ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);
		ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7); // 可用于调试
		putenv('LDAPTLS_REQCERT=never'); // 跳过 SSL 证书验证（不安全）
				
		$ldapbind = @ldap_bind($ldapconn, $ldap_username, $ldap_password);
		if (!$ldapbind) {
			$err = ldap_error($ldapconn);
			return "无法绑定 ".$err;
		}
		return;
	}
	
	//下面两个函数用于初始化系统
	//需先验证一下这个密码，再才能执行初始化操作
	public function fetch_adInitReq(){
		$post = &$this->POST;
		
		global $sysDevelopPass;
		if(md5($post['loginPass'])!=$sysDevelopPass){
			return $this->out(1,[["cmd"=>"验证开发密码","msg"=>"开发密码错误"]]);
		}
		return $this->out(0);
	}
	
	public function fetch_adInitExec(){
		$post = &$this->POST;
		
		global $sysDevelopPass;
		if(md5($post['loginPass'])!=$sysDevelopPass){
			return $this->out(1,[["cmd"=>"验证开发密码","msg"=>"开发密码错误"]]);
		}
		
		$myName = $post['myName'];
		$myIp = $post['myIp'];
		$myDomain = $post['myDomain'];
		$myPass = $post['myPass'];
		
		$myDomainPre = explode(".",$myDomain)[0];
		$dns_arr = array_reverse(explode('.',$myIp));
		$dns_zone = join('.',array_slice($dns_arr,1)).".in-addr.arpa";
		$dns_last = $dns_arr[0];
		
		$back=[];
		if(!$myName){
			$back['未设置计算机名'];
		}
		if(!$myIp){
			$back['未设置本机IP'];
		}
		if(!$myDomain){
			$back['未设置域名'];
		}
		if(!$myPass){
			$back['未设置管理员密码'];
		}
		if(count($back)>0){
			return $this->out(1,join('<br/>',$back));
		}
		
		
  
		$initCmd=[
			//停止samba
			['cmd' => 'systemctl stop samba'],
			['cmd' => 'ps -ef|grep samba| grep -v grep','success'=>['line'=>0],'fail'=>[]],
			//删除配置文件
			['cmd' => 'rm /etc/samba/smb.conf'],
			['cmd' => 'rm /etc/krb5.conf'],
			//删除/var/lock/samba下的数据库
			['cmd' => 'rm -rf /var/lock/samba/*.tdb'],
			['cmd' => 'rm -rf /var/lock/samba/*.ldb'],
			//删除/var/cache/samba下的数据库
			['cmd' => 'rm -rf /var/cache/samba/*.tdb'],
			['cmd' => 'rm -rf /var/cache/samba/*.ldb'],
			//删除/var/lib/samba/private下的数据库
			['cmd' => 'rm -rf /var/lib/samba/private/*.tdb'],
			['cmd' => 'rm -rf /var/lib/samba/private/*.ldb'],
			//设置/etc/hostname
			['cmd' => "chmod 777 /etc/hostname"],
			['cmd' => "echo '".$myName."' > /etc/hostname"],
			['cmd' => "chmod 644 /etc/hostname"],
			//设置/etc/resolv.conf
			['cmd' => "chmod 777 /etc/resolv.conf"],
			['cmd' => "echo 'search ".$myDomain."' > /etc/resolv.conf"],
			['cmd' => "echo 'nameserver ".$myIp."' >> /etc/resolv.conf"],
			['cmd' => "chmod 644 /etc/resolv.conf"],
			//设置/etc/hosts
			['cmd' => "chmod 777 /etc/hosts"],
			['cmd' => "echo '127.0.0.1   localhost' > /etc/hosts"],
			['cmd' => "echo '".$myIp." ".$myName.".".$myDomain." ".$myName."' >> /etc/hosts"],
			['cmd' => "chmod 644 /etc/hosts"],
			//临时应用新主机名称，因为上面修改hostname和hosts，只有重启才有效
			['cmd' => "hostname ".$myName],
			//创建AD
			['cmd' => "samba-tool domain provision --server-role=dc --use-rfc2307 --dns-backend=SAMBA_INTERNAL --realm=".$myDomain." --domain=".$myDomainPre." --adminpass='".$myPass."'",'success'=>['text'=>'']],
			//复制 krb5.conf    这个文件是安装程序生成的，需要cp到etc目录下
			['cmd' => "cp /var/lib/samba/private/krb5.conf /etc/"],
			//启动samba
			['cmd' => "systemctl start samba"],
			//增加DNS反向代理
			['cmd' => "samba-tool dns zonecreate ".$myIp." ".$dns_zone." --username=Administrator --password='".$myPass."'"],
			['cmd' => "samba-tool dns add ".$myIp." ".$dns_zone." ".$dns_last." PTR ".$myName.".".$myDomain."  --username=Administrator --password='".$myPass."'"],
		];
		
		$allcode=0;
		foreach($initCmd as $k=>$cmdo){
			$res = [];
			$code = 0;
			exec("sudo ".$cmdo['cmd']." 2>&1",$res,$code);
			$initCmd[$k]['code'] = $code;
			$initCmd[$k]['msg'] = join('<br/>',$res);
			if($code){
				break;
			}
			if(isset($cmdo['success'])){//成功的判断规则
				
			}
			if(isset($cmdo['fail'])){//失败的判断规则
				
			}
		}
		return $this->out($allcode, $initCmd );
	}
	
	
	
}

?>