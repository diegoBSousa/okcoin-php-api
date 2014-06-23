<?php
include "class/okcoin.php";

$partner = "";// your partner number here
$secretKey = "";// your secret key here

$okcoin = new okcoin($partner, $secretKey);

$userInfo = $okcoin->userinfo();

print_r($userInfo);
/*
Array userinfo error
(
    [errorCode] => 10006
    [result] => 
)

( userinfo result
    [info] => Array
        (
            [funds] => Array
                (
                    [free] => Array
                        (
                            [btc] => 0
                            [cny] => 0
                            [ltc] => 0
                        )

                    [freezed] => Array
                        (
                            [btc] => 0
                            [cny] => 0
                            [ltc] => 0
                        )

                )

        )

    [result] => 1
)
*/
$trade = $okcoin->trade($symbol = 'btc_cny', $type='buy', $price = 10.001, $amount = 0.01);
echo "\n\n";
print_r($trade);


$cancelorder = $okcoin->cancelorder($order_id = 1, $symbol='ltc_cny');

print_r($cancelorder);


$getorder = $okcoin->getorder($order_id = 1, $symbol='ltc_cny');

print_r($getorder);


$ticker = $okcoin->ticker($symbol='ltc_cny');

print_r($ticker);
/*
Array
(
    [ticker] => Array
        (
            [buy] => 60.99
            [high] => 61.8
            [last] => 60.99
            [low] => 60.63
            [sell] => 61.0
            [vol] => 591482.94899994
        )

)
*/

$depth = $okcoin->depth($symbol='ltc_cny');

print_r($depth);

$trades = $okcoin->trades($symbol='ltc_cny');

print_r($trades);

?>

Error Codes Table 错误代码对照表:

10000 	必选参数不能为空
10001 	用户请求过于频繁
10002 	系统错误
10003 	未在请求限制列表中,稍后请重试
10004 	IP限制不能请求该资源
10005 	密钥不存在
10006 	用户不存在
10007 	签名不匹配
10008 	非法参数
10009 	订单不存在
10010 	余额不足
10011 	买卖的数量小于BTC/LTC最小买卖额度
10012 	当前网站暂时只支持btc_cny ltc_cny
10013 	此接口只支持https请求
10014 	下单价格不得≤0或≥1000000
10015 	下单价格与最新成交价偏差过大

10000 Required parameter can not be null
10001 user requests too frequently
10002 System error
10003 is not restricted list in the request, please try again later
10004 IP restriction does not request the resource
10005 key does not exist
10006 user does not exist
10007 signatures do not match
10008 illegal parameters
10009 Order does not exist
10010 Less than Balance
10011 traded less than the number of BTC / LTC minimum trading amount
10012 current site only temporary support btc_cny ltc_cny
10013 This interface supports only https requests
10014 single price shall ≤ 0 or ≥ 1000000
10015 single price with the latest transaction price deviation is too large
