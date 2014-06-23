<?php
/**
* Author: diego sousa
* e-mail: diego@diegosousa.com.br
* Facebook: www.facebook.com/diego.brito.148553
* Github:  github.com/vadinho
* Pay Me a Beer:
* BTC : 1JkAmtB9cnW8oiGCKmmArk59jEsKhaxqQX
* LTC : LVT3MdQEwJpdwfhvUvrudDWqzErZmfbmhg
*/
class okcoin
  {
    private static $certificate;
    private static $partner;
    private static $secretKey;
    private static $privateApi;
    private static $publicApi;
    private static $baseUrl;

    public function __construct($partner, $secretKey, $haveYourServerCertificate = false)
      {
        $this->partner = $partner;
        $this->secretKey = $secretKey;
        $this->certificate = (bool)$haveYourServerCertificate;
        $this->baseUrl = "https://www.okcoin.cn/api/";
        $this->version = "v. beta 0.1";
        $this->privateApi = Array("userinfo", "trade", "cancelorder", "getorder");
        $this->publicApi = Array("ticker", "depth", "trades");
      }

    private function signMessage($postArray)
      {
        ksort($postArray);//alphabethical ordering
        $signedMessage = strtoupper(md5(http_build_query($postArray, '', '&') . $this->secretKey));
        return $signedMessage;
      }
     
    private function callApi($api, $params = Array())
      {
        foreach($this->privateApi as $value)
          {
            if($api == $value)
              { 
                $params["partner"] = $this->partner;
                $params["sign"]    = $this->signMessage($params);
                return $this->doRequest("POST", $api, $params);
              }
          }
        foreach($this->publicApi as $value)
          {
            if($api == $value)
              {
                $params["method"] = $api;
                return $this->doRequest("GET", $api, $params);
              }
          }
        return false;
      }
    
    private function doRequest($method, $api, $params)
      {
        foreach(array_keys($params) as $key)
          {
            $params[$key] = urlencode($params[$key]);
          }
        $postFields = http_build_query($params);
        $ch = curl_init();
        $options = Array(
                          CURLOPT_HEADER         => false,
                          CURLOPT_USERAGENT      => urlencode('OKCoin PHP Api Module ' . $this->version),
                          CURLOPT_RETURNTRANSFER => true,
                          CURLOPT_SSL_VERIFYPEER => $this->certificate, 
                          CURLOPT_SSL_VERIFYHOST => $this->certificate
                        );
        if($method == "POST")
          {
            $options[CURLOPT_URL]  = $this->baseUrl . $api . ".do";
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $postFields;
          }
        else
          {
            $options[CURLOPT_URL] = $this->baseUrl . $api . ".do?" . $postFields;
          }
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($status == 200)
          {
            return json_decode($response, true);
          }
        else
          {
            /* Debug */
            echo "HTTP STATUS: " . $status;
            echo "\n" . $response;
          }
        return false;
      }
    
    private function removeVoid($params = Array())
      {
        $return = Array();
        foreach($params as $key => $value)
          {
            if($value != "")
              {
                $return[$key] = $value;
              }
          }
        return $return;
      }
    
    /*#########################Private 交易APIs########################################*/
    /**请求方式
     * POST https://www.okcoin.cn/api/userinfo.do
     * 获取用户信息 - Get user information
     * @return array JSON results
     * 返回值 JSON
     *  result: true 返回结果, false 返回错误代码
     *  info: Array 返回结果
     *    funds: Array
     *      free: Array
     *        cny:float
     *        btc:float
     *        ltc: float
     *      freezed: Array
     *        cny:float
     *        btc:float
     *        ltc: float
     *  errorCode: int 返回错误代码 Error Code
     */
    public function userinfo()
      { 
        return $this->callApi(__FUNCTION__);
      }
    
    /**请求方式
     * POST https://www.okcoin.cn/api/trade.do
     * 下单交易 - Single transaction
     * @param string $symbol
     * symbol: 必选 Required 当前货币兑 pair List: (btc_cny,ltc_cny)
     * @param string $type
     * type: 必选 Required 买卖类型 Trading type: 限价单 Limit orders(buy/sell), 市价 Market Orders (buy_market/sell_market)
     * @param float $rate
     * rate: 下单价格 Sending Price: 限价买单(必填) Price Range 0 < price < 1000000
     * @param float $amount
     * amount: 数量 Quantity: BTC数量大于等于0.01 BTC Quantity > 0.01, LTC数量大于等于0.1 LTC Quantity > 0.10
     * @return array JSON results
     * 返回值 JSON
     *  result: true 返回结果, false 返回错误代码
     *  order_id: int 返回结果 if result true it returns order id
     *  errorCode: int 返回错误代码 if result false it returns Error Code
     */
    public function trade($symbol, $type, $rate = "", $amount = "")
      {
        return $this->callApi(
                               __FUNCTION__,
                               $this->removeVoid(
                                                  Array(
                                                         "symbol" => $symbol,
                                                         "type"   => $type,
                                                         "rate"   => $rate,
                                                         "amount" => $amount
                                                       )
                                                )
                             );
      }
    
    /**请求方式
     * POST https://www.okcoin.cn/api/cancelorder.do
     * 撤销订单 - Cancel Order
     * @param int $order_id
     * order_id: 必选 Required 订单号 order id
     * @param string $symbol
     * symbol: 必选 Required 当前货币兑 pair List: (btc_cny,ltc_cny)
     * @return array JSON results
     * 返回值 JSON
     *  result: true 返回结果, false 返回错误代码
     *  order_id: int 返回结果 if result true it returns id of canceled order
     *  errorCode: int 返回错误代码 if result false it returns Error Code
     */
    public function cancelorder($order_id, $symbol)
      {
        return $this->callApi(
                               __FUNCTION__,
                               Array(
                                      "order_id" => (int)$order_id,
                                      "symbol"   => $symbol
                                    )
                             );
      }
    
    /**请求方式
     * POST https://www.okcoin.cn/api/getorder.do
     * 获取用户挂单 - Get User Pending Order
     * @param int $order_id
     * order_id: 必选 Required 订单号 order id
     * @param string $symbol
     * symbol: 必选 Required 当前货币兑 pair List: (btc_cny,ltc_cny)
     * @return array JSON results
     * 返回值 JSON
     *  result: true 返回结果, false 返回错误代码
     *  orders: array 返回结果 if result true it returns orders array
     *    orders_id:   int
     *    status:      int
     *    symbol:      string "btc_cny", "ltc_cny"
     *    type:        string "sell","buy", "sell_market", "buy_market"
     *    rate:        float
     *    amount:      float
     *    deal_amount: float
     *    avg_rate:    float
     *  errorCode: int 返回错误代码 if result false it returns Error Code
     */
    public function getorder($order_id, $symbol)
      {
        return $this->callApi(
                               __FUNCTION__,
                               Array(
                                      "order_id" => (int)$order_id,
                                      "symbol"   => $symbol
                                    )
                             );
      }
      
    /*#########################Public 行情APIs######################################*/ 
    /**请求方式
     * GET https://www.okcoin.cn/api/ticker.do
     * @param string $symbol
     * symbol: 当前货币兑 pair List: (btc_cny,ltc_cny)
     * @return array JSON results
     * 返回值 JSON
     * ticker: Array
     *   buy:  float
     *   high: float
     *   last: float
     *   low:  float
     *   sell: float
     *   vol:  float
     */
    public function ticker($symbol = "btc_cny")
      { 
        return $this->callApi(
                               __FUNCTION__,
                               $this->removeVoid(Array("symbol" => $symbol))
                             );
      }
      
    /**请求方式
     * GET https://www.okcoin.cn/api/depth.do
     * 订单 order book
     * @param string $symbol
     * symbol: 当前货币兑 pair List: (btc_cny,ltc_cny)
     * @return array JSON results
     * 返回值 JSON
     * ask: Array of float
     * bid: Array of float
     */ 
    public function depth($symbol = "btc_cny")
      { 
        return $this->callApi(
                               __FUNCTION__,
                               $this->removeVoid(Array("symbol" => $symbol))
                             );
      }
      
    /**请求方式
     * GET  https://www.okcoin.cn/api/trades.do
     * 笔交易 Transaction
     * @param string $symbol
     * symbol: 当前货币兑 pair List: (btc_cny,ltc_cny)
     * @param int $since
     * since: if not defined it will return 60 most recent transactions
     * 不加since参数时, 返回的是最近(tid值或date值最大的)的60笔交易
     * @return array JSON results
     * 返回值 JSON Array of Transactions Array
     * 笔交易 each transaction: Array
     *  amount: float
     *  date:   int (timestamp)
     *  price:  float
     *  tid:    int
     *  type:   string  (sell,buy)
     */
    public function trades($symbol = "btc_cny", $since = "")
      { 
        return $this->callApi(
                               __FUNCTION__,
                               $this->removeVoid(Array("symbol" => $symbol, "since" => $since))
                             );
      }
  }
?>
