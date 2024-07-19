<?php 
require_once("utils.php");
switch(@$_POST["cmd"]) {
case "login": /* var user={"email":"..@...", "email_verified":true, "name":"", "picture":"https://...", "family_name":"", "given_name":"",... }; */
	$data=json_decode($_POST["data"],true);
	$ret=["msg"=>"","code"=>0];
	if(!$data["email_verified"]) $ret=["msg"=>"Login failed!","code"=>1];
	$res=$myDB->doQuery("SELECT * FROM account WHERE uid=?",[explode("@",$data["email"])[0]],true);
	if(!$res) {
		$myDB->doQuery("INSERT INTO account (`uid`,`name`,`auth`) VALUES (?,?,1)",[explode("@",$data["email"])[0],$data["name"]]);
		$ret=["msg"=>"首次登入！","code"=>0];
	} else {
		$user=$res;
		$user["email"]=$data["email"];
		$user["pic"]=$data["picture"];
		setSession("user",$user);
		$ret=["msg"=>"成功登入！","code"=>0];
	}
	die(json_encode($ret));
default: break;
}

if(!isLogined()) {
	header("Location: index.php");
	exit;
}

switch(@$_POST["cmd"]) {
case "logout":
	$email=getSession("user","email");
	unsetSession("user");
	session_destroy();
	unset($myDB);
	die($email);
case "addRecord":
	$opt=$_POST["data"];
	$opt[2]=intval($opt[2]);
	$opt[3]=intval($opt[3]);
	$myDB->doQuery("INSERT INTO `records` (`uid`,`Lv`,`score`,`times`) VALUES (?,?,?,?)",$opt);
	break;
case "saveOpts": 
	$keys=["last_gameLv","last_gameTime","bgm_enabled","sfx_enabled","bgm_volume"];
	foreach(array_combine($keys,$_POST["data"]) as $k=>$v) {
		if(0==$myDB->getCount("SELECT v FROM options WHERE k=?",[$k])) {
			$myDB->doQuery("INSERT INTO options (k,v) VALUES (?,?)",[$k,$v]);
		} else {
			$myDB->doQuery("UPDATE options SET v=? WHERE k=?",[$v,$k]);
		}
	}
	break;	
default: exit;
}
?>
