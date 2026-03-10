<?php


function tacSection_author($args){
	$obj = new Auth_author($args);
	$obj->run();
}



class Auth_author{
	protected $args=[];
	
	protected $code = 0;// -1 0 1
	protected $msg = '';
	
	protected $rowUser = [];
	protected $rowNas = [];
	protected $rowPerm = [];
	
	protected $backlist = [];
	
	public function __construct($opt){
		$this->args=$opt;
	}	
	
	public function run(){
		$this->queryForUser();//查询user信息
		$this->queryForNas();//查询nas信息
		$this->queryForPerm();//查询角色信息
		
		$this->authForCmd();
		
		$this->writeForReply();//写用户自定义返回属性
		$this->writeForAttr();//写角色属性
		
		$this->outArray(0);
		
	}
	
	
	protected function queryForUser(){
		$permrow = \DB::table("sdaaa.raduser")
		->where('us_name',$this->args['user'])
		->first();
		if(!$permrow){
			$this->errmsg ='unknown username';
			$this->code = 1;
			$this->outArray(1);	
		}
		$this->rowUser = $permrow;
	}
	
	protected function queryForNas(){
		$permrow = \DB::table("sdaaa.nas")
		->where('na_ip',$this->args['nas'])
		->first();
		if(!$permrow){
			$this->errmsg ='unknown nas';
			$this->code = 1;
			$this->outArray(1);	
		}
		$this->rowNas = $permrow;
	}
	
	protected function queryForPerm(){
		global $sdmysql;
		
		$filter="gp_enable='1'";
		if($this->rowUser['us_gpid']){
			$filter="gpid='".$this->rowUser['us_gpid']."'";
		}
		
		$permrows = \DB::select("select * from (select * from sdaaa.ag_perm where ".$filter.")c left join (select * from sdaaa.ag_perm_organ where  gpo_onid='".$this->rowNas['na_organ']."')d on c.gpid=d.gpo_gpid left join (select * from sdaaa.ag_perm_nas where gpn_naip='".$this->rowNas['na_ip']."')e on c.gpid=e.gpn_gpid");
		
		if(!$permrows or count($permrows)==0){
			$this->errmsg ='unknown permgroup';
			$this->code = 1;
			$this->outArray(1);	
		}
		$this->rowPerm = $permrows[0];	
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
				$this->args['cmd'] = $mycmd;//用于审计
			}
			$cmdid = $this->realValue('cmd');
			if($cmdid=='' or $cmdid=='0'){//该用户没有设置命令授权
				$this->outArray(2);
			}
			
			$row= \DB::table("sdaaa.ag_cmd")
			->where('gcid',$cmdid)
			->first();
			
			$dflt = $row['gc_dflt'];
			$this->code = ($dflt=='0'?'1':'0');//默认授权，数据库里0表示拒绝，得返回1
			$rows= \DB::table("sdaaa.ag_cmd_list")
			->where('gcl_gcid',$cmdid)
			->get();
			
			foreach($rows as $row){
				$pattern = $row['gcl_cmd'];
				if(substr($pattern,0,1)!='/'){//如果两边没有斜杆
					$pattern = '/'.$pattern.'/';//就把斜杠加上去
				}
				if(preg_match($pattern , $mycmd)){
					$this->code = ($row['gcl_perm']=='0'?'1':'0') ;
					break;
				}
			}
			$this->outArray(2);
		}
	}
	
	
	protected function writeForReply(){
		if($this->rowUser['us_rad_reply']==''){return;}
		$replys = json_decode($this->rowUser['us_rad_reply'],true);
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
		$vid = $this->rowNas['na_type'];
		
		$rows= \DB::table("sdaaa.ag_attr_tac")
			->where('gat_gaid',$attrid)
			->where(function($mydb){
				$mydb->where("gat_vid","0")->orWhere("gat_vid",$vid);
			})
			->get();
			
		foreach($rows as $row){ 
			$this->backlist['reply'][$row['gat_attr']]=$row['gat_val'];
		}
	}

	protected function realValue($hz,$dep=3){
		$row = $this->rowPerm;
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
	
	protected function outArray($logtype){
		echoOut($this->code,$this->msg,$this->backlist,$logtype,$this->args);
	}
	
	
	
}

?>