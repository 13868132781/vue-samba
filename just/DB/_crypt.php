<?php
namespace just\DB;
/*
加密字段

*/
trait _crypt{
	
	public function cryptField(){
	   $num=func_num_args();
	   $args = func_get_args();
	   $arr=[];
	   foreach($args as $ar){
			$arr[$ar] = true;	
	   }
	   if(count($arr)>0){
		   $this->cryptArr = array_merge($this->cryptArr,$arr);
	   }
	   
	   return $this;
	}
	/*
	没有用mysql的加密解密，
	一则是能在sql语句里看到密钥
	再则，select * 时，不好操作
	public function _encrypt_mysql($col,$val ){
		$key="sd-secret-key";
		if(isset($this->cryptArr[$col])){
			return "TO_BASE64(AES_ENCRYPT('".$val."','".$key."'))";
		}ele{
			return $val;
		}
	}
	
	public function _decrypt_mysql($col){
		$key="sd-secret-key";
		if(isset($this->cryptArr[$col])){
			return "AES_DECRYPT(FROM_BASE64(".$col."),'".$key."')";
		}ele{
			return $val;
		}
	}
	*/
	
	private function _encrypt_php($val ){
		//$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
		
		$key="sd-secret-key";
		$method = "AES-256-CBC";
		$iv = base64_decode("SIm6PCCARjsfnolZ37dd9Q==");
		
		$val = openssl_encrypt($val, $method, $key, OPENSSL_RAW_DATA, $iv); 
		$val = base64_encode($val); 
		
		//加密后最后总是多出两个==，这是base64填充的，可以去掉
		$val = rtrim($val,'=');
		return $val;
	}
	
	private function _decrypt_php($val ){
		$key="sd-secret-key";
		$method = "AES-256-CBC";
		$iv = base64_decode("SIm6PCCARjsfnolZ37dd9Q==");
		
		$val = base64_decode($val);
		$val = openssl_decrypt($val, $method, $key, OPENSSL_RAW_DATA, $iv);
		
	   return $val;
	}
	
   //给外部用的，类似\DB::raw()
   public static function encrypt($val){
	   $th = new static;
	   return $ht->_encrypt_php($val);
   }
   public static function decrypt($val){
	   $th = new static;
	   return $ht->_decrypt_php($val);
   }
	
}


?>