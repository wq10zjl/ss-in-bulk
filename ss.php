<?php

/**
 * ss.php
 * 批量导入 Shadowsocks 配置
 * by @cjli
 */

# 1. 先获得 Mac OS X 下 Shadowsocks 的配置的数组格式
// $ss_json = file_get_contents( 'test/ss.json' ) ;

// $ss_arr  = json_decode( $ss_json, true ) ;

// print_r( $ss_arr ) ;    // 通过数组查看默认配置规律

# 2. 处理基本配置文件并生成格式化的数组
$servers  = file_get_contents( 'ss.cnf' ) ;
$servers  = explode( "\n", $servers ) ;
$profiles = array() ;
foreach ($servers as $k => $v) {
	if (!preg_match('/^#+/', $v) && !empty($v)) {
		$server     = explode(' ', $v) ;

		# 使用正则表达式检查配置文件每行格式是否正确
		// ...

		/**
		 * !!! 不能直接在此循环中直接一次性添加到 $profiles 数组, 否则会报 illegal offset type error
		 * 错误的写法:
		 *          $profiles[] = array(
		 *          		['password']    => $server[3] ,
		 *          		['method']      => $server[2] ,
		 *          		['server_port'] => $server[1] ,
		 *          		['remarks']     => $server[4] ,
		 *          		['server']      => $server[0] ,
		 *	         ) ;
		 */
		$profiles[] = getFormattedArr($server) ;
	}
}
$server_cnt = count($profiles) ;

$new_arr = array(
	'current'  => 3 ,
	'profiles' => $profiles
	) ;

# 3. 将数组转化为 JSON 后使用 Base64 编码
$ss_conf = base64_encode(json_encode($new_arr)) ;

# 4.1 将生成的 Base64 字符串保存到文件 (手动)
// date_default_timezone_set( 'Asia/Shanghai' ) ;
// file_put_contents( date('Y-m-d-His').'.ss.x'.$server_cnt, $ss_conf ) ;

# 4.2 将生成的 Base64 字符串替换掉 ss.xml 中 <data> 标签中的信息 (自动)
$pattern  = '/<data>\s*[\w|=|\+|\/]*\s*<\/data>/' ;
$subject  = file_get_contents( 'ss.xml' ) ;
$replace  = "<data>\n\t" ;
$replace .= $ss_conf ;
$replace .= "\n\t</data>" ;
$data     = preg_replace($pattern, $replace, $subject) ;

# 5. 替换旧的 ss.xml
file_put_contents( 'ss.xml', $data) ;

/**
 * 重新获得一个格式化的数组
 * by @cjli
 */
function getFormattedArr($server) {
	$arr = array() ;

	$arr['password']    = $server[3] ;
    $arr['method']      = $server[2] ;
    $arr['server_port'] = $server[1] ;
    $arr['remarks']     = $server[4] ;
    $arr['server']      = $server[0] ;

	return $arr ;
}