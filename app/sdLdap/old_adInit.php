<?php

namespace app\sdLdap;

class adInit extends \table {
	public $pageName="系统网络";
	public $TN = "sdsamba.adinit";
	public $colKey = "aiid";
	public $colOrder = "ai_order";
	public $colFid = "";
	public $colName = "ai_name";
	public $orderDesc = false;
	public $POST = [];
	
	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'ai_name','name'=>'名称'],
				['col'=>'ai_key','name'=>'键'],
				['col'=>'ai_val','name'=>'值',
					'modify'=>function($text,$row){
						if($text and stristr($row['ai_key'],'pass')){
							return str_repeat('*',strlen($text));
						}
						return $text;
					}
				],
				['col'=>'ai_mark','name'=>'说明'],
				['col'=>'ai_order','name'=>'排序',
					'type'=>'order',
					'align'=>'center',
					'width'=>'50px',
				],
			],
			
			'toolEnable' => true,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'toolFilterEnable'=> false,
			'operDelEnable'=> true,
			'fenyeEnable'=> false,
			
			'toolExpands'=>[
				[
					'name'=>'执行初始化',
					'type'=>'fetch',
					'goto'=>'execInit',
				]
			],
			
			
		];
		
		return $gridSet;
	}
	
	
	
	public function crudAddSet(){
		$post=&$this->POST;
		
		$back=[];
		$back[]=[
				"name"=>"名称",
				"col"=>"ai_name",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"键",
				"col"=>"ai_key",
				"ask"=>true,
				"type"=>'text',
		];
		$back[]=[
				"name"=>"值",
				"col"=>"ai_val",
				"type"=>'text',
		];
		$back[]=[
				"name"=>"说明",
				"col"=>"ai_mark",
				"type"=>'text',
		];
		return $back;
	}
	
	
	
	
	public function crudModSet(){
		$post=&$this->POST;
		
		$back=$this->crudAddSet();
		
		return $back;
	}
	
	public function fetch_execInit(){
		
		
		return $this->out(0,'xxxxxxxxxxxxx');
		
	}	
	
}









/*

1 清理历史数据
  关闭samba 服务 确定没有 samba 在运行
  systemctl stop samba
  ps -ef|grep samba| grep -v grep  确定没有相关的进程
  
  删除配置文件 
  /etc/samba/smb.conf
  /etc/krb5.conf
  
  删除可能存在的原AD的数据
       cd /var/lock/samba
       rm -rf *.tdb
       rm -rf *.ldb
       cd /var/lib/samba
       rm -rf *.tdb && rm -rf *.ldb
       cd /var/cache/samba
       rm -rf *.tdb && rm -rf *.ldb
       cd /var/lib/samba/private
       rm -rf *.tdb && rm -rf *.ldb
	   
2  设置初始数据
    
   页面需要提供
   本机名称    建议是AD1    AD2 这样的名称
   域名        如softdomain.com
   本机IP      如192.168.0.61
   管理员密码

   /etc/hostname    要包含正确的本机名称
   /etc/resolve.conf    这个文件修改完，要设置只读属性，因为其他的服务会自动修改这个文件
   [root@AD1 etc]# more resolv.conf
search ibm.com
nameserver 192.168.0.61
设置完成后，要设置为只读
sudo chattr +i /etc/resolv.conf

/etc/hosts   这个文件不要包含其他信息，解析为本机主机名，本机主机名.域名
[root@AD1 etc]# more hosts
127.0.0.1   localhost
192.168.0.61 AD1.ibm.com AD1
[root@AD1 etc]#


3  创建AD
   samba-tool domain provision --server-role=dc --use-rfc2307 --dns-backend=SAMBA_INTERNAL --realm=IBM.COM --domain=IBM
 --adminpass=qqq000,,,

   要能显示最终结果，确保不要报错
   
   
4  复制 krb5.conf    这个文件是安装程序生成的，需要cp到etc目录下
   cp /var/lib/samba/private/krb5.conf /etc/
   
5   启动systemctl start samba
6   验证
[root@AD1 etc]# kinit
Password for administrator@IBM.COM:
Warning: Your password will expire in 38 days on 2025年01月13日 星期一 10时42分11秒
[root@AD1 etc]#

验证成功即可

7  增加DNS反向代理（两个）
root@DC1:/etc/samba# samba-tool dns zonecreate 192.168.0.227  0.168.192.in-addr.arpa -U Administrator
Password for [SD\Administrator]:
Zone 0.168.192.in-addr.arpa created successfully
root@DC1:/etc/samba#

samba-tool dns add 192.168.0.227    0.168.192.in-addr.arpa 227  PTR dc1.sd.com -U Administrator 
注意里面这个227指的是IP地址最后这个值，在这个反向查找区域里，IP的最后一位与整个反向区域构成了完整的IP地址，

8  验证安装  包含三个部分

8.1  smbclient -L localhost -N
8.2  验证DNS   注意ip和域名要用用户的设置
     host -t SRV _ldap._tcp.samdom.example.com.   后面是域名
	 host -t SRV _kerberos._udp.samdom.example.com.
	 host -t A dc1.samdom.example.com.
	 host -t P
*/

?>