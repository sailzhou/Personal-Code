/*
* qq登录授权过程中用到的回调地址，此文将回调地址单独作为一个文件存在
* 
*/

header('location:'.create_url('mobile/login',array('api'=>'qq','code'=>$_GET['code'])));
