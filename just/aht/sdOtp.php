<?php
//获取种子
function sdOtpSeedGet(){
	return HlcOtp3e12e321e32r23::generate_secret_key(32);
}

//根据种子，间隔时间，获取一次口令
function sdOtpOnePassGet($seed,$period){
	return HlcOtp3e12e321e32r23::onepass_get($seed,$period);
}

//根据名称，种子，间隔时间，获取二维码图片字符串
function sdOtpQrcodeGet($name,$seed,$period){
	$length = HlcOtp3e12e321e32r23::length_get();
	//sdQrcodeGet是生成二维码的函数，在sdqrcode库里
	return base64_encode(sdQrcodeGet("otpauth://totp/".$name."?secret=".$seed."&digits=".$length."&period=".$period."&issuer=hlcotp"));
}

//根据间隔时间，获取当前口令已逝时间。返回60:23 ,
function sdOtpTimedGet($period){
	return HlcOtp3e12e321e32r23::get_allready($period);
}


class HlcOtp3e12e321e32r23 {

    private static $otpLength       = 6;// Length of the Token generated

    private static $lut = array(// Lookup needed for Base32 encoding
        "A" => 0,    "B" => 1,
        "C" => 2,    "D" => 3,
        "E" => 4,    "F" => 5,
        "G" => 6,    "H" => 7,
        "I" => 8,    "J" => 9,
        "K" => 10,    "L" => 11,
        "M" => 12,    "N" => 13,
        "O" => 14,    "P" => 15,
        "Q" => 16,    "R" => 17,
        "S" => 18,    "T" => 19,
        "U" => 20,    "V" => 21,
        "W" => 22,    "X" => 23,
        "Y" => 24,    "Z" => 25,
        "2" => 26,    "3" => 27,
        "4" => 28,    "5" => 29,
        "6" => 30,    "7" => 31
    );
	

	public static function length_get(){
		return self::$otpLength;
	}
	public static function onepass_get($seed,$period){
		$TimeStamp = self::get_timestamp($period);
		$secretkey = self::base32_decode($seed);
		$realotp   = self::oath_hotp($secretkey, $TimeStamp);
			
		return $realotp;
	}
	
    /**
     * Generates a 16 digit secret key in base32 format
     * @return string
     **/
    public static function generate_secret_key($length = 16) {
        $b32     = "234567QWERTYUIOPASDFGHJKLZXCVBNM";
        $s     = "";

        for ($i = 0; $i < $length; $i++)
            $s .= $b32[rand(0,31)];

        return $s;
    }

    /**
     * Returns the current Unix Timestamp devided by the 
     * period.
     * @return integer
     **/
    public static function get_timestamp($period) {
       return floor(microtime(true)/$period);
    }
	
	
	
	
	//返回当下经过了几秒，用于显示进度条
	 public static function get_allready($period) {
        return $period.":".time()%int($period);
    }
    /**
     * Decodes a base32 string into a binary string.
     **/
    public static function base32_decode($b32) {

        $b32     = strtoupper($b32);

        if (!preg_match('/^[ABCDEFGHIJKLMNOPQRSTUVWXYZ234567]+$/', $b32, $match))
            throw new Exception('Invalid characters in the base32 string.');

        $l     = strlen($b32);
        $n    = 0;
        $j    = 0;
        $binary = "";

        for ($i = 0; $i < $l; $i++) {

            $n = $n << 5;                 // Move buffer left by 5 to make room
            $n = $n + self::$lut[$b32[$i]];     // Add value into buffer
            $j = $j + 5;                // Keep track of number of bits in buffer

            if ($j >= 8) {
                $j = $j - 8;
                $binary .= chr(($n & (0xFF << $j)) >> $j);
            }
        }

        return $binary;
    }

    /**
     * Takes the secret key and the timestamp and returns the one time
     * password.
     *
     * @param binary $key - Secret key in binary form.
     * @param integer $counter - Timestamp as returned by get_timestamp.
     * @return string
     **/
    public static function oath_hotp($key, $counter)
    {
        if (strlen($key) < 8)
        throw new Exception('Secret key is too short. Must be at least 16 base 32 characters');

        $bin_counter = pack('N*', 0) . pack('N*', $counter);        // Counter must be 64-bit int
        $hash      = hash_hmac ('sha1', $bin_counter, $key, true);

        return str_pad(self::oath_truncate($hash), self::$otpLength, '0', STR_PAD_LEFT);
    }

    /**
     * Verifys a user inputted key against the current timestamp. Checks $window
     * keys either side of the timestamp.
     *
     * @param string $b32seed 32位种子
     * @param string $key - User specified key 用户输入的口令
     * @param integer $window 窗口，即对比前后窗口内的令牌
     * @param boolean $useTimeStamp 是否使用所提供的时间戳，否则用当前时间戳
     * @return boolean
     **/
	 //检查前后4个，共8个令牌里，是否有匹配的
    public static function verify_key($b32seed, $key, $window = 4, $useTimeStamp = true) {

        $timeStamp = self::get_timestamp();

        if ($useTimeStamp !== true) $timeStamp = (int)$useTimeStamp;

        $binarySeed = self::base32_decode($b32seed);

        for ($ts = $timeStamp - $window; $ts <= $timeStamp + $window; $ts++)
            if (self::oath_hotp($binarySeed, $ts) == $key)
                return true;

        return false;

    }

    /**
     * Extracts the OTP from the SHA1 hash.
     * @param binary $hash
     * @return integer
     **/
    public static function oath_truncate($hash)
    {
        $offset = ord($hash[19]) & 0xf;

        return (
            ((ord($hash[$offset+0]) & 0x7f) << 24 ) |
            ((ord($hash[$offset+1]) & 0xff) << 16 ) |
            ((ord($hash[$offset+2]) & 0xff) << 8 ) |
            (ord($hash[$offset+3]) & 0xff)
        ) % pow(10, self::$otpLength);
    }
}

