<?php
/**
 *qq登录授权接口类
 * 
 */
class qq_login{

    private $appkey='eb98a7a9b2d50b8c596b0eca30b212b0';       //申请的接口appid
    private $appid ='101323721';        //申请的接口appkey
    private $redirect_uri='http://9117.w3c.ren/qq.php';  //指定的回调地址
    private $scope = '';
    public $open_id = '';
    private $access_token = '';
    public $code = '';           //用户认证code
    private $code_url= "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=%s&redirect_uri=%s&scope=%s";
    /**
     * 初始化配置
     */
    public function __construct($appkey=null,$appid=null,$redirect_uri=null,$scope=null,$code=null){
       if($appkey){
           $this->appkey = $appkey;
       }
       if($appid){
           $this->appid = $appid;
       }
       if($redirect_uri){
           $this->redirect_uri = urlencode($redirect_uri);
       }
       if($redirect_uri){
           $this->redirect_uri = $redirect_uri;
       }
       if($scope){
           $this->$scope = $scope;
       }
       if($code){
           $this->code = $code;
       }

        if(!$_GET['code']){
            $this->getCode();
        }


    }

    public function getCode(){
        $url = $this->code_url;
        $url = sprintf($url,$this->appid,urlencode($this->redirect_uri),$this->scope);
        header("location:".$url);
    }
    /**
     * 获取Access Token
     */
    public function getAccessToken(){
        //利用Code获取Access Token
        $url = "https://graph.qq.com/oauth2.0/token?grant_type=authorization_code&client_id=".$this->appid."&client_secret=".$this->appkey."&code=".$this->code."&state=1&redirect_uri=".$this->redirect_uri;
           $res = self::http($url);
           if(!preg_match("/^callback/",$res)){
              $token = explode('&',$res);
               foreach($token as $val){
                  $tmp = explode("=",$val);
                  $token_arr[$tmp[0]] =$tmp[1];
               }
               $this->access_token = $token_arr['access_token'];
               return  $token_arr;
          }else{
              $str = str_replace(array('callback( ',');')," ",$res);
              $message = json_decode($str,true);
               $result = array(
                'err' => $message['error'],
                'msg' => '获取Access Token失败，原因为：'.$message['error_description']
              );
                return  $result;
     }
}

    /**
     * 获取用户openID
     */
    public function getOpenId(){
        $this->getAccessToken();
        $url = "https://graph.qq.com/oauth2.0/me?access_token=".$this->access_token;
        $res = self::http($url);
        $str = str_replace(array('callback( ',');')," ",$res);
        $open_id = json_decode($str,true);
        $this->open_id = $open_id['openid'];
        return $open_id;
     }
     /**
      * 获取用户信息
      */
     public function getUserInfo(){
         $open_id = $this->getOpenId();
         $url = "https://graph.qq.com/user/get_user_info?access_token=".$this->access_token."&oauth_consumer_key=".$open_id['client_id']."&openid=".$open_id['openid'];
         $res = self::http($url);
         $user_info =json_decode($res,true);
         return $user_info;
    }

    /**
     * CURL请求方法
     * @param $url $method
     * null $postfields
     * array $header_array
     * null $userpwd
     * @return mixed
     */
    public static function http ($url, $method="GET", $postfields = NULL, $header_array = array(), $userpwd = NULL)
    {
        $ci = curl_init();
        /* Curl 设置 */
        curl_setopt($ci, CURLOPT_USERAGENT, 'Mozilla/4.0');
        curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ci, CURLOPT_TIMEOUT, 30);
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ci, CURLOPT_HEADER, FALSE);
        if ($userpwd) {
            curl_setopt($ci, CURLOPT_USERPWD, $userpwd);
        }
        $method = strtoupper($method);
        switch ($method) {
            case 'GET':
                if (! empty($postfields)) {
                    $url = $url . '?' . http_build_query($postfields);
                }
                break;
            case 'POST':
                curl_setopt($ci, CURLOPT_POST, TRUE);
                if (! empty($postfields)) {
                    curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
                }
                break;
        }
        $header_array2 = array();
        foreach ($header_array as $k => $v) {
            array_push($header_array2, $k . ': ' . $v);
        }
        curl_setopt($ci, CURLOPT_HTTPHEADER, $header_array2);
        curl_setopt($ci, CURLINFO_HEADER_OUT, TRUE);
        curl_setopt($ci, CURLOPT_URL, $url);
        $response = curl_exec($ci);
        curl_close($ci);
        return $response;
    }
}

?>
