<?php
/**
 * Created by PhpStorm.
 * User: yyy
 * Date: 17/4/29
 * Time: 下午1:31
 *f
 *
 * */

function encode_big_endian( $_num ) {
    $_str = '0000';
    #echo ($_num>>24) & 0xFF, "\n";
    #echo ($_num>>16) & 0xFF, "\n";
    #echo ($_num>>8 ) & 0xFF, "\n";
    #echo ($_num>>0 ) & 0xFF, "\n";

    //此处需要将得到的数字转换为对应的字符(ascii)
    $_str[0] = chr(($_num >> 24) & 0xFF);
    $_str[1] = chr(($_num >> 16) & 0xFF);
    $_str[2] = chr(($_num >> 8 ) & 0xFF);
    $_str[3] = chr(($_num >> 0 ) & 0xFF);

    return $_str;
}

function decode_big_endian( $_str ) {
    $_ret = 0;
    $_ret = ($_ret << 8) | ord($_str[0]);
    $_ret = ($_ret << 8) | ord($_str[1]);
    $_ret = ($_ret << 8) | ord($_str[2]);
    $_ret = ($_ret << 8) | ord($_str[3]);

    return $_ret;
}

$_num = 0x12345678;			//305419896
echo dechex($_num);
$_str = encode_big_endian($_num);
echo 'encode: ', $_num, '=', $_str, "\n";
echo 'deocde: ', $_str, '=', decode_big_endian($_str), "\n";
