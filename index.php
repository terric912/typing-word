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
		<div class="card-body bg-secondary-subtle"></div>
	</div>

	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://accounts.google.com/gsi/client"></script>
	<script>
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
