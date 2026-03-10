<?php

/*
只需在 left或right里添加特定格式的数组成员，即可在页面显示

数组成员也是个数组，格式如下

[
	'title'=> '标题',
    'head' => [//head有几个成员，页面就显示几列
		['align'=>'right'],
		[],
		[],
	],
    'list' => [//多行数据
		['1','a','A'],
		['2','b','B'],
	],
]
*/


namespace app\main;

class main extends \table{
	public $pageName="首页";
	public $POST = [];
	public $FUNC = '';
	public $zdyBackend=true;
	
	public function gridSet(){
		return [];
	}
	public function fetch_sysStaus(){
		$this->noAudit=true;
		$data=[
			'left' => [
				$this->ADinfo(),
			],
			'right' => [
				$this->sysInfo(),
				$this->sysSrc(),
				$this->sysLogin(),
			],
		];
		return $this->out(0,$data);
	}
	
	public function fetch_sysResouse(){
		$this->noAudit=true;
		$cpuMemDisk = $this->cpuMemDisk();
		return $this->out(0, $cpuMemDisk);
	}
	
	public function sysInfo(){
		$title="系统信息";
		$head=[
			['align'=>'right'],
			[]
		];
		$list=[
			['系统版本','SD6800S0628-101SYSFULL2020'],
			['启动时间',trim(str_replace('system boot ','',exec("who -b")))],
			['序列号码','SNAAArtl062820201001SYSwx'],
			['系统时间',exec('date "+%Y-%m-%d %H:%M:%S"')],
		];
		$vars = [
			'title' => $title,
           'head' => $head,
           'list'    => $list,
        ];
		
		return $vars;
	}
	
	public function ADinfo(){
		$title="AD域信息管理";
		$head=[
			['align'=>'right'],
			['align'=>'left'],
			['align'=>'right'],
			//['align'=>'right'],
		];
		
		//开始显示域信息
		$output = [];
		
		$asArgs = (new \app\sdLdap\adServer)->ldapConnArgs();
		exec('samba-tool domain info '.$asArgs['ip'], $output);

		// 初始化结果数组
		$list = [];

		// 遍历每一行并解析
		foreach ($output as $line) {
			// 使用正则表达式提取键和值
			if (preg_match('/^(.*?)\s*:\s*(.*)$/', $line, $matches)) {
				$key = trim($matches[1]);
				$value = trim($matches[2]);

				// 根据不同的键添加到结果数组中
				switch ($key) {
					case 'Forest':
						$list[] = ['域森林:', 'Forest', $value];
						break;
					case 'Domain':
						$list[] = ['域名:', 'Domain', $value];
						break;
					case 'Netbios domain':
						$list[] = ['NetBIOS 域:', 'Netbios domain', $value];
						break;
					case 'DC name':
						$list[] = ['域控制器名称:', 'DC name', $value];
						break;
					case 'DC netbios name':
						$list[] = ['域控制器 NetBIOS 名称:', 'DC netbios name', $value];
						break;
					case 'Server site':
						$list[] = ['服务器站点:', 'Server site', $value];
						break;
					case 'Client site':
						$list[] = ['客户端站点:', 'Client site', $value];
						break;
				}
			}
		}
		
		$vars = [
			'title' => $title,
           'head' => $head,
           'list'    => $list,
        ];
		
		
		return $vars;
	} 
	
	
	public function sysLogin(){
		$title="系统最近5次登录";
		$head=[
			['align'=>'middle'],
			['align'=>'middle'],
			['align'=>'middle'],
			['align'=>'middle'],
		];
		$sysLogin = new \appsys\auth\sysLogin();
		$data = $sysLogin::DB()->orderBy($sysLogin->colKey,'desc')->limit(5)->get();
		
		$list=[];
		
		foreach($data as $da){
			$list[]=[$da['sl_acctname'],$da['sl_client'],$da['sl_starttime'],$da['sl_status']?'在线':'离线'];
		}
		
		$vars = [
			'title' => $title,
           'head' => $head,
           'list'    => $list,
        ];
		return $vars;
	}
	
	
	public function sysSrc(){
		$title="域控资源";
		$head=[
			['align'=>'right'],
			['align'=>'right'],
			['align'=>'right'],
			['align'=>'right'],
		];

		$countUser = (new \app\sdLdap\adUser)->zdySource(['count'=>true]);
		
		$countComputer = (new \app\sdLdap\adComputer)->zdySource(['count'=>true]);
		
		$countOU = (new \app\sdLdap\adOrunit)->zdySource(['count'=>true])-1;
		
		$countGroup = (new \app\sdLdap\adGroup)->zdySource(['count'=>true]);
		
		$countDns = (new \app\sdLdap\adDns)->zdySource(['count'=>true]);
		
		$countGPO = (new \app\sdLdap\adGPO)->zdySource(['count'=>true]);
		
		
		$list=[
			['用户数:',$countUser.'个','计算机数:',$countComputer.'个'],
			['OU数:',$countOU.'个','DNS数:',$countDns.'个'],
			['组数:',$countGroup.'个','组策略数:',$countGPO.'个'],
		];
		
		$vars = [
			'title' => $title,
           'head' => $head,
           'list'    => $list,
       ];
		return $vars;
	} 
	
	
	public function cpuMemDisk(){
		$cpu=floor(exec("sudo sar -u 1 1  | awk '{printf \"%s \",$8}' | awk '{printf \"%s\",$3}'"));
		$mem=ceil(exec("sudo free -m | grep Mem | awk '{printf\"%d\",($2-$7)/$2*100}'"));
		$disk=ceil(exec("sudo df -m | grep '/$'  | awk '{printf $5}' | awk -F % '{printf $1}'"));
		
		$memtotal = '('.ceil(exec("sudo free -m | grep Mem | awk '{printf $2}'")/1000)."G)";
		$disktotal = '('.ceil(exec("sudo df -m | grep '/$'  | awk '{printf $2}'")/1000)."G)";
		
		$cpu = 100- intval($cpu);
		
		$vars=[
			["CPU",$cpu,'('.$cpu.'%)'],
			["内存",$mem,$memtotal],
			["硬盘",$disk,$disktotal]
		];
		
		return $vars;
	}
	
}


?>