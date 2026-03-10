<?php

function hlcphp_encode($string = '', $skey = 'hlckey') {
    $strArr = str_split(base64_encode($string));
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value)
        $key < $strCount && $strArr[$key].=$value;
    return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
}
function hlcphp_decode($string = '', $skey = 'hlckey') {
    $strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value)
        $key <= $strCount  && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
    return base64_decode(join('', $strArr));
}



function hlcphp_encrypt($input, $key="hlckey"){
        $input = str_replace("\n", "", $input);
        $input = str_replace("\t", "", $input);
        $input = str_replace("\r", "", $input);
        $key = substr(md5($key), 0, 24);
        $td = mcrypt_module_open('tripledes', '', 'ecb', '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $encrypted_data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return trim(chop(base64_encode($encrypted_data)));
}
function hlcphp_decrypt($input, $key="hlckey"){
        $input = str_replace("\n", "", $input);
        $input = str_replace("\t", "", $input);
        $input = str_replace("\r", "", $input);
        $input = trim(chop(base64_decode($input)));
        $td = mcrypt_module_open('tripledes', '', 'ecb', '');
        $key = substr(md5($key), 0, 24);
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $key, $iv);
        $decrypted_data = mdecrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return trim(chop($decrypted_data));
}
?>