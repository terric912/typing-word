<?php
if(!file_exists("_DBsettings.php")) {
	header("Location: typing.html");
}
require_once("utils.php");

?>
<!DOCTYPE html>
<html lang="zh-hant-tw">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="Author" content="Terric Chen, terric_AT_gmail_com">
	<title>英打遊戲</title>
	<link href="favicon.ico" rel="icon">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
	<style>
		body {
			font-family: Consolas, Verdana, Arial, sans-serif;
		}
		.loginIcon {
			width: 32px;
			border-radius:4px;
		}
	</style>
</head>
<body>
	<div class="card vh-100">
		<div class="card-header fs-4">
			<span class="navbar-brand">英打遊戲</span>
			<div class="d-inline-flex float-end">
<?php if(isLogined()) {?>
				<span data-bs-toggle="dropdown"><img class="loginIcon" src="<?php echo getSession("user","pic");?>"></span>
				<div class="dropdown">
					<ul class="dropdown-menu">
						<li><a class="dropdown-item" href="#" onclick="signOut();">登出</a></li>
					</ul>
				</div>
<?php } else if(@$_GG["google_auth"]) { ?>
				<div id="g_id_onload" data-client_id="<?php echo $_GG["google_client_id"];?>" data-context="signin" data-ux_mode="popup" data-callback="signIn" data-auto_prompt="false" data-hd="fhsh.khc.edu.tw"></div>
				<div class="g_id_signin" data-type="icon" data-shape="square" data-theme="filled_blue" data-text="signin_with" data-size="medium"></div>
<?php } ?>
			</div>
		</div>
		<div class="card-body bg-secondary-subtle">
			<h5>排行榜</h5>
			<div class="d-flex">
				<ul class="nav nav-tabs flex-column">
<?php
	$Lv=["j1"=>"國中基本單字","j2"=>"國中進階單字","s0"=>"高中不分級","s1"=>"高中第一級","s2"=>"高中第二級","s3"=>"高中第三級","s4"=>"高中第四級","s5"=>"高中第五級","s6"=>"高中第六級"];
	foreach($Lv as $k=>$v) {
		printf("%s<li class='nav-item'><button class='nav-link' id='tab-%s' data-bs-toggle='tab' data-bs-target='#pane-%s'>%s</button></li>\n",indent(5),$k,$k,$v);
	}
	printf("%s</ul>\n%s<div class='tab-content p-2'>\n",indent(4),indent(4));
	foreach($Lv as $k=>$v) {
		printf("%s<div class='tab-pane fade' id='pane-%s' tabindex='0'><ol>",indent(5),$k);
		$sql=sprintf("SELECT `uid`,`score`,`times`,ROUND(score/(times / 60),2) as `WPM` FROM `records` WHERE `LV`=? ORDER BY `WPM` DESC, `times` DESC, `uid` ASC LIMIT 10");
		foreach($myDB->doQuery($sql,[$k]) as $d) {
			printf("<li>%s@ %5.2f WPM, %2d words in %3d seconds.</li>",$d['uid'],$d['WPM'],$d['score'],$d['times']);
		}
		printf("</ol></div>\n");
	}
?>
				</div>
			</div>
		</div>
	</div>

	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://accounts.google.com/gsi/client"></script>
	<script>
		function addRecord() {
<?php if(isLogined()) { ?>
			$.ajax({url:"ajax.php",type:"POST", cache:false,
				data:{cmd:"addRecord",data:["<?php echo getSession("user","uid");?>",$("#optLv").val(),gameScore,gameTime-timeCount]},
				success: function(data) {
					location.reload();
				}
			});
<?php }?>
			return;
		}
	    function parseJwt(token,parsed) {
			var base64Url=token.split('.')[1];
			var base64=base64Url.replace(/-/g,'+').replace(/_/g,'/');
			var payload=decodeURIComponent(atob(base64).split('').map(function(c){return '%'+('00'+c.charCodeAt(0).toString(16)).slice(-2);}).join(''));
			return parsed?JSON.parse(payload):payload;
		}
		function signIn(resp) {
			var json=parseJwt(resp.credential,0);
			$.ajax({url:"ajax.php",type:"POST", cache:false,
				data:{cmd:"login",data:json},
				success: function(data) {
					console.log(data.msg);
					if(!data.code) location.reload();
				}
			});
		}
		function signOut() {
			$.ajax({url:"ajax.php",type:"POST", cache:false,
				data:{cmd:"logout"},
				success: function(data) {
					location.reload();
				}
			});
		}
	</script>
</body>
</html>
