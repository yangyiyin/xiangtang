<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/29
 * Time: 下午1:31
 *f
 *
 * */



 function decode_user_session($user_session) {
    $user_session_str = base64_decode($user_session);
    return explode('|', $user_session_str);

}

var_dump(decode_user_session('MTB8MTUwMDYxNzAzMHw0'));
?>
