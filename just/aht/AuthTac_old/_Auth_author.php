<?php
namespace App\SdAaa\Inc\func\AuthTac;
use App\SdAaa\Inc\func\SdRandom;

class Auth_author{
	protected $args=[];
	
	protected $code = 0;// -1 0 1
	protected $msg = '';
	
	protected $row = [];
	protected $authway = '';
	
	protected $backlist = [];
	
	public function __construct($opt){
		$this->args=$opt;
	}	
	
	public function run(){
		if(!$this->query()){
			return $this->outArray();
		}
		
		if($this->authForCmd()){
			return $this->outArray();
		}
		
		$this->writeForReply();//写用户自定义返回属性
		
		$this->writeForAttr();//写角色属性
		
		
		return $this->outArray();
		
	}
	
	
	protected function query(){ 
		global $sdmysql;
		$permsql ="select * from ((select * from sdaaa.raduser where us_name='".$this->args['user']."') as a ,(select na_ip,na_organ,na_type from sdaaa.nas where na_ip='".$this->args['nas']."') as b) left join sdaaa.ag_perm c on a.us_gpid=c.gpid left join sdaaa.ag_perm_organ d on b.na_organ=d.gpo_onid and c.gpid=d.gpo_gpid left join sdaaa.ag_perm_nas e on b.na_ip=e.gpn_naip and c.gpid=e.gpn_gpid";
		$permobj = $sdmysql->query($permsql);
		$permrow = $permobj->fetch_assoc();
		if(!$permrow){
			$this->msg ='unknown user or NAS';
			$this->code = 1;
			return false;	
		}
		$this->row = $permrow;

		$filter="aw_enable='1'";
		if($this->row['us_tfa']){
			$filter="awid='".$this->row['us_tfa']."'";
		}
		$sql = "select aw_key from sdaaa.ab_authway where ".$filter;
		$authwayobj = $sdmysql->query($sql);
		$authwayrow = $authwayobj->fetch_assoc();
		
		if(!$authwayrow){
			$this->msg ='can not find auth way';
			$this->code = 1;
			return false;
		}
		$this->authway = $authwayrow['aw_key']?:'PAP';
		
		return true;
	}
	
	protected function authForCmd(){
		//返回true，表示这是条cmd授权，false表示非cmd授权
		//授权成功与否，由code设定
		$inargs = $this->args['args'];
		$inargsa = explode('-|-|-',$inargs);
		$more = [];
		foreach($inargsa as $co){
			$coo=explode('=',$co,2);
			$k = $coo[0];
			$v = $coo[1];
			if(array_key_exists($k,$more)){
				$more[$k].=" ";
			}else{
				$more[$k] = "";
			}
			$more[$k].=$v;
		}
		if(array_key_exists('cmd',$more)){//表示这是条cmd授权请求
			//cmd=show  cmd-arg=running-config
			//授权命令
			//$this->code=0;return true;
			$mycmd = trim($more['cmd'].' '.$more['cmd-arg']);
			if(substr($mycmd,-4)=='<cr>'){//去除末尾的<cr>
				$mycmd = trim(substr($mycmd,0,-4));
			}
			$cmdid = $this->realValue('cmd');
			if($cmdid=='' or $cmdid=='0'){//该用户没有设置命令授权
				goto getout;
			}
			global $sdmysql;
			$obj=$sdmysql->query("select gc_dflt from sdaaa.ag_cmd where gcid='".$cmdid."'");
			$row=$obj->fetch_assoc();
			$dflt = $row['gc_dflt'];
			$this->code = ($dflt=='0'?'1':'0');//默认授权，数据库里0表示拒绝，得返回1
			$obj=$sdmysql->query("select * from sdaaa.ag_cmd_list where gcl_gcid='".$cmdid."'");
			while($row=$obj->fetch_assoc()){
				$pattern = $row['gcl_cmd'];
				if(substr($pattern,0,1)!='/'){//如果两边没有斜杆
					$pattern = '/'.$pattern.'/';//就把斜杠加上去
				}
				if(preg_match($pattern , $mycmd)){
					$this->code = ($row['gcl_perm']=='0'?'1':'0') ;
					break;
				}
			}
			getout:
			if($this->code!='0'){//记录授权失败的命令
				$sql = "insert into sdaaa_log.rad_oper(logdate, logtime, username, NAS_name,  NAC_address, cmd,result) values(now(),now(),'".$this->args['user']."','".$this->args['nas']."','".$this->args['nac']."','".$mycmd."','1')";
				$sdmysql->query($sql);
			}
			return true;
		}
		return false;
	}
	
	
	protected function writeForReply(){
		if($this->row['us_rad_reply']==''){return;}
		$replys = json_decode($this->row['us_rad_reply'],true);
		foreach($replys as $reps){
			if($reps['key']=='' or $reps['val']==''){continue;}
			if(!strstr($reps['key'],'tac:')){//需以tac:开头
				continue;
			}
			$reps['key'] = str_replace('tac:','',$reps['key']);
			$this->backlist['reply'][$reps['key']]=$reps['val'];
		}
		
	}
	
	protected function writeForAttr(){
		$attrid = $this->realValue('attr');
		
		if($attrid=='' or $attrid=='0'){
			return [];
		}
		$ret=[];
		$vid = $this->row['na_type'];
		global $sdmysql;
		$obj=$sdmysql->query("select * from sdaaa.ag_attr_tac where gat_gaid='".$attrid."' and (gat_vid='0' or gat_vid='".$vid."')");
		while($row=$obj->fetch_assoc()){ 
			$this->backlist['reply'][$row['gat_attr']]=$row['gat_val'];
		}
	}

	protected function realValue($hz,$dep=3){
		$row = $this->row;
		$qzs=['gpn','gpo','gp'];
		if($dep==2){$qzs=['gpo','gp'];}
		$res = '0';
		foreach($qzs as $qz){
			$col = $qz."_".$hz;
			if(strlen($row[$col])>0 and $row[$col]!='-1'){
				$res = $row[$col];
				break;
			}
		}
		return $res;
	}
	
	protected function outArray(){
		
		return [
			'code'=>$this->code,
			'msg'=>$this->msg,
			'vars'=>$this->backlist
		];
	}
	
	
	
}