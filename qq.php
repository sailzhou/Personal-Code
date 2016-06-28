/*
* qq登录授权过程中用到的回调地址，此文将回调地址单独作为一个文件存在
* 回调地址用于获取Authorization Code，这里作为一个文件中转，方便后续的信息获取
*/

header('location:'.create_url('mobile/login',array('api'=>'qq','code'=>$_GET['code'])));
