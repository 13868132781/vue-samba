<?php

namespace app\sdLdap;

class adGPOShow extends \table {
	public $pageName="组策略详情";
	public $TN = "";
	public $colKey = "id";
	public $colOrder = "";
	public $colFid = "fid";
	public $colName = "loginname";
	public $orderDesc = true;
	public $POST = [];
	public $zdyBackend=true;

	public function gridSet(){
		$gridSet=[
			'columns'=>[
				['col'=>'namestr','name'=>'名称'],
				['col'=>'valuestr','name'=>'数据'],
			],
			
			'toolEnable' => false,
			'toolAddEnable' => true,
			'toolExportEnable' => false,
			'toolRefreshEnable'=> true,
			'operEnable' => false,
			'operModEnable' => true,
			'operDelEnable'=> true,
			'fenyeEnable'=> false,
		];
		return $gridSet;
	}
	
	public function zdySource($inopt=[]){
		
		$post=&$this->POST;
		$fakey = $post['keyList'][0];
		$fakeyname = explode(',',explode('=',$fakey)[1])[0];
		
		$asArgs = (new adServer)->ldapConnArgs();
		
		$cmd ="samba-tool gpo show '".$fakeyname."' -H ldap://".$asArgs['ip']." ".$asArgs['stAuth'];
		
		exec('sudo '.$cmd.' 2>&1',$res,$code);
		if($code){
			sdError([$cmd,$res]);
		}
		
		$data=[];
		$collist=[
			'GPO'=>'名称',
			'display name'=>'显示名',
			'dn'=>'dn',
			'version'=>'版本',
			'flags' => 'flags',
			'ACL' => 'ACL',
			'Policies' => '策略',
			];
		$startPolicy = false;
		$policyStr = '';
		foreach($res as $line){
			$line = trim($line);
			if($startPolicy){
				$policyStr .= $line;
			}
			foreach($collist as $clk=>$clv){
				if(substr($line, 0, strlen($clk)) === $clk){
					if($clk == 'Policies'){
						$startPolicy=true;
						continue;
					}
					$clval = explode(':',$line,2)[1];
					if($clk == 'ACL'){
						$clval = ['type'=>'table','value'=>$this->decodeACL($clval)];
					}
					$data[]=[
						'namestr'=> $clv,
						"valuestr"=> $clval,
					];
				}
			}
			
		}
		
		$policyArr = json_decode($policyStr,true);
		foreach($policyArr as $k=>$v){
			if(is_array($v['data'])){
				$policyArr[$k]['data'] = json_encode($v['data']);
			}
		}
		
		if(count($policyArr)>0){
			array_unshift($policyArr,['键名','值名','类','类型','数据']);
		}
		$data[]=[
			'namestr'=> '策略',
			"valuestr"=> [
				'type'=>'table',
				'value'=>$policyArr,
			]
		];
		
		/*
		$data[]=[
			'namestr'=> '原始数据',
			"valuestr"=> [
				'type'=>'html',
				'value'=>join('<br/>',$res),
			],
		];
		*/
		
		return $data;
	}
	
	
	public function decodeACL($val){
		$klist = ['O'=>'所有者','G'=>'主组','D'=>'DACL','S'=>'SACL'];
		$valList=[];
		foreach($klist as $kl=>$vl){
			$valO = '';
			$spit = $kl.":";
			if(stristr($val,$spit)){
				$valq = explode( ':', explode( $spit, $val)[1]);
				$valO = $valq[0];
				if(count($valq)>1){
					$valO = substr( $valO, 0, -1);
				}
			}
			if($kl=='D' or $kl=='S'){
				$valOO = explode('(',$valO);
				foreach($valOO as $valOOk=>$valOOv){
					if($valOOk==0){
						$flagsType=[
							'P'=>'保护',
							'AI'=>'自动继承',
							'AR' => '请求自动继承',
							'NO_ACCESS_CONTROL'=>'无',
						];
						$valList[] = [$vl.'-flags',$flagsType[$valOOv].'('.$valOOv.')'];
					}else{
						//$valOOv是ACE，包含6个字段，格式在页面底部
						$valOOv = rtrim($valOOv,')');
						$valOOval = join('<br/>', $this->decodeACE($valOOv));
						$valList[] = [
							['type'=>'html','value'=>'&nbsp;&nbsp;&nbsp;'.$vl.'-'.$valOOk],
							['type'=>'html','value'=>$valOOval],
						];
					}
				}
			}else{
				$valList[] = [$vl,$valO];
			}
		}
		return $valList;
	}
	
	
	//https://learn.microsoft.com/zh-cn/windows/win32/secauthz/ace-strings
	public function decodeACE($val){
		$valArr = explode(';',$val);
		$valOOval=[];
		foreach($valArr as $valArrk=>$valArrv){
			if(!$valArrv){
				continue;
			}
			$aceColList=['类型','标志','权限','对象类型','属性类型','SID'];
			$aceColMap=[
				'col0' => [
					'A'=>'允许',
					'D'=>'拒绝',
					'OA'=>'对象特定的允许',
					'OD'=>'对象特定的拒绝',
					'AU'=>'审核',
					'AL'=>'报警',
					'OU'=>'对象审计',
					'OL'=>'对象报警',
				],
				'col1' => [
					'CI'=>'容器继承',
					'OI'=>'对象继承',
					'NP'=>'不传播',
					'IO'=>'仅继承',
					'ID'=>'继承的',
					'SA'=>'成功审计',
					'FA'=>'失败审计',
				],
				'col2' => [
					'CC'=>'创建子对象',
					'DC'=>'删除子对象',
					'LC'=>'列出子对象',
					'SW'=>'写属性',
					'RP'=>'读属性',
					'WP'=>'写属性',
					'DT'=>'删除树',
					'LO'=>'列出对象',
					'SD'=>'读取安全描述符',
					'WD'=>'写入安全描述符',
					'WO'=>'写入所有权',
					'GA'=>'完全控制',
					'GR'=>'读取',
					'GW'=>'写入',
					'GX'=>'执行',
					'CR'=>'创建子对象',
				],
				//https://learn.microsoft.com/zh-cn/windows/win32/secauthz/sid-strings
				'col5' => [
					'CO' => '创建者所有者',
					'SY' => '本地系统',
					'AU' => '经过身份验证的用户',
					'ED' => '企业域控制器',
					'WD' => '所有人',
				]
			];
			if($valArrk==0 or $valArrk==5){
				$qqq='';
				if(isset($aceColMap['col'.$valArrk][$valArrv])){
					$qqq = $aceColMap['col'.$valArrk][$valArrv];
				}else{
					$qqq="未知";
				}
				$valArrv = $qqq.'('.$valArrv.')';
			}else if($valArrk==1 or $valArrk==2){
				$valArrvArr = str_split($valArrv,2);
				$qqq='';
				foreach($valArrvArr as $www){
					if(isset($aceColMap['col'.$valArrk][$www])){
						$qqq.=",".$aceColMap['col'.$valArrk][$www];
					}else{
						$qqq.=",未知";
					}
				}
				$valArrv = trim($qqq,',').'('.$valArrv.')';
			}
			$valOOval[] = $aceColList[$valArrk].'：'.$valArrv;
		}
		return $valOOval;
	}
	
}


/*

flagsType //参考https://learn.microsoft.com/zh-cn/windows/win32/secauthz/security-descriptor-string-format



ACE（访问控制条目）是ACL（访问控制列表）的基本组成部分，用于定义特定用户或组对资源的访问权限。ACE通常包含六个字段，每个字段都有特定的含义。以下是这六个字段的详细说明：

1. 类型 (Type)
作用：指定ACE的类型，例如允许（Allow）或拒绝（Deny）。
常见值：
A：允许（Allow）
D：拒绝（Deny）
OA：对象特定的允许（Object-Specific Allow）
OD：对象特定的拒绝（Object-Specific Deny）
AU：审核（Audit）
AL：报警（Alarm）
2. 标志 (Flags)
作用：指定ACE的继承和其他标志。
常见值：
CI：容器继承（Container Inherit）
OI：对象继承（Object Inherit）
NP：不传播（No Propagate）
IO：仅继承（Inherit Only）
ID：继承的（Inherited）
SA：成功审计（Success Audit）
FA：失败审计（Failure Audit）
3. 权限掩码 (Permissions Mask)
作用：指定具体的权限。
常见值：
CC：创建子对象（Create Child）
DC：删除子对象（Delete Child）
LC：列出子对象（List Child）
SW：写属性（Write Attribute）
RP：读属性（Read Property）
WP：写属性（Write Property）
DT：删除树（Delete Tree）
LO：列出对象（List Object）
SD：读取安全描述符（Read Security Descriptor）
WD：写入安全描述符（Write Security Descriptor）
WO：写入所有权（Write Owner）
GA：完全控制（Generic All）
GR：读取（Generic Read）
GW：写入（Generic Write）
GX：执行（Generic Execute）
CR：创建子对象（Create Child）
4. 对象类型 (Object Type)
作用：指定受此ACE影响的对象类型（可选）。
格式：<GUID>
示例：edacfd8f-ffb3-11d1-b41d-00a0c968f939
5. 属性类型 (Inherited Object Type)
作用：指定受此ACE影响的继承对象类型（可选）。
格式：<GUID>
示例：bf967aa5-0de6-11d0-a285-00aa003049e2
6. SID (Security Identifier)
作用：指定用户或组的安全标识符。
格式：<SID>
示例：S-1-5-21-293015046-2454718589-1543939404-512

*/


?>