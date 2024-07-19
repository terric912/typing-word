<?php
if(!file_exists("_DBsettings.php")) {
	header("Location: typing.html");
}
require_once("utils.php");
$options=array_column($myDB->doQuery("SELECT k,v FROM options"),'v','k');
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
		#board {
			position: relative;
		}
		.word {
			position: absolute;
			background-color: salmon;
			padding: 0px 10px;
		}
		mark {
			background: transparent;
			color:yellow;
		}
	</style>
</head>
<body class="overflow-hidden" onload="gameInit();">
	<div class="card vh-100">
		<div class="card-header fs-4">
			<div class="d-flex">
				<span class="navbar-brand fw-bold">英打遊戲</span>
<?php if(isLogined()) {?>
				<button class="btn btn-sm btn-warning mx-2" data-bs-toggle="modal" data-bs-target="#modalSettings">開始</button>
				<span class="ms-auto" data-bs-toggle="dropdown"><img class="loginIcon" src="<?php echo getSession("user","pic");?>"></span>
				<div class="dropdown">
					<ul class="dropdown-menu">
						<li><a class="dropdown-item" onclick=";">我的記錄</a></li>
						<li><a class="dropdown-item" onclick="signOut();">登出</a></li>
					</ul>
				</div>
<?php } else if(@$_GG["google_auth"]) { ?>
				<div id="g_id_onload" data-client_id="<?php echo $_GG["google_client_id"];?>" data-context="signin" data-ux_mode="popup" data-callback="signIn" data-auto_prompt="false" data-hd="fhsh.khc.edu.tw"></div>
				<div class="g_id_signin ms-auto" data-type="icon" data-shape="square" data-theme="filled_blue" data-text="signin_with" data-size="medium"></div>
<?php } ?>
			</div>
		</div>
		<div class="card-body bg-secondary-subtle">
			<h5>排行榜</h5>
			<div class="d-flex">
				<ul class="nav nav-tabs flex-column">
<?php
	$Lv=json_decode('[{"code":"j1","title":"國中基本單字","count":1229},{"code":"j2","title":"國中進階單字","count":779},{"code":"s0","title":"高中不分級","count":135},{"code":"s1","title":"高中第一級","count":1047},{"code":"s2","title":"高中第二級","count":1039},{"code":"s3","title":"高中第三級","count":1018},{"code":"s4","title":"高中第四級","count":1022},{"code":"s5","title":"高中第五級","count":1006},{"code":"s6","title":"高中第六級","count":1030}]',true);
	foreach($Lv as $v) {
		printf("%s<li class='nav-item'><button class='nav-link' id='tab-%s' data-bs-toggle='tab' data-bs-target='#pane-%s'>%s</button></li>\n",indent(5),$v['code'],$v['code'],$v['title']);
	}
	printf("%s</ul>\n%s<div class='tab-content p-2'>\n",indent(4),indent(4));
	foreach($Lv as $v) {
		printf("%s<div class='tab-pane fade' id='pane-%s' tabindex='0'><ol>",indent(5),$v['code']);
		$sql=sprintf("SELECT `uid`,`score`,`times`,ROUND(score/(times / 60),2) as `WPM` FROM `records` WHERE `LV`=? ORDER BY `WPM` DESC, `times` DESC, `uid` ASC LIMIT 10");
		foreach($myDB->doQuery($sql,[$v['code']]) as $d) {
			printf("<li>%s@ %5.2f WPM, %3d 秒內完成 %2d 個單字.</li>",$d['uid'],$d['WPM'],$d['times'],$d['score']);
		}
		printf("</ol></div>\n");
	}
?>
				</div>
			</div>
		</div>
		<div class="toast-container d-flex justify-content-center vh-100 vw-100">
			<div class="toast text-bg-info align-self-center">
				<div class="d-flex">
					<div class="toast-body fs-4" id="msg"></div>
					<button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
				</div>
			</div>
		</div>
	</div>

	<div id="modalSettings" class="modal" tabindex="-1" data-bs-backdrop="static">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header"><h5 class="modal-title fw-bold">遊戲設定</h5></div>
				<div class="modal-body">
					<div class="mb-3 row">
					<label class="col-sm-3 col-form-label">英單題庫：</label>
					<div class="col-sm-9"><select class="form-select mb-2" id="optLv" onchange="gameLv=this.value;">
<?php					foreach($Lv as $v) {
							$selected=(@$options["last_gameLv"]==$v['code']);
							printf("%s<option value='%s'%s>%s(%d)</option>\n",indent(6),$v['code'],($selected?" selected":""),$v['title'],$v['count']); 
						} 
?>
					</select></div>
					<label class="col-sm-3 col-form-label">打字時間：</label>
					<div class="col-sm-9"><select class="form-select mb-2" id="optTm" onchange="gameTime=this.value;">
<?php					foreach([0,1,3,5,10] as $m) {
							$selected=(@$options["last_gameTime"]==$m*60);
							printf("%s<option value='%d'%s>%s</option>\n",indent(6),$m*60,($selected?" selected":""),$m?sprintf("%2d分鐘",$m):"不限時"); 
						}
?>
					</select></div>
					</div>
					<div class="form-check form-switch form-check-inline">
						<input class="form-check-input" type="checkbox" id="cbBGM" oninput="toggleBGM(this);"<?php printf("%s",@$options["bgm_enabled"]?" checked":"");?>>
						<label class="form-check-label">背景音樂 BGM</label>
						<input type="range" id="volBGM" min="0" max="1" step="0.05" oninput="get('bgm').volume=this.value;">
					</div>
					<div class="form-check form-switch">
						<input class="form-check-input" type="checkbox" id="cbSFX"<?php printf("%s",@$options["sfx_enabled"]?" checked":"");?>>
						<label class="form-check-label">對錯音效 SFX</label>
					</div>
					<hr><div class="fw-bold">遊戲說明</div><ul>
						<li>正確的單字得分，當局不會重複出現。</li>
						<li>掉落到底、錯過的字，不會扣分，會回題庫下次隨機產生。</li>
						<li>限時模式為時間倒數制，每秒產生一個單字，全題庫隨機產生。</li>
						<li>不限時模式，會將題庫中的單字分三群(長度6以下/7~12/13以上)依序產生，因此前期單字長度較短。開局每兩秒產生一個單字，每當打字速度追上產生速度，會略加速。</li>
					</ul>
				</div>
				<div class="modal-footer">
					<button class="bTime btn btn-outline-primary" data-bs-dismiss="modal" onclick="saveSettings();">開始</button>
				</div>
			</div>
		</div>
	</div>

	<div id="modalTyping" class="modal" tabindex="-1">
		<div class="modal-dialog modal-fullscreen">
			<div class="modal-content">
				<div class="modal-header d-flex">
					<div class="fs-4">Time:<span id="time">0</span>s &emsp;&emsp; Score:<span id="score">0</span>
					<span id="wordCount"> &emsp;&emsp; Words:<span id="countTotal">0/0</span></div>
					<button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" onclick="gameStop();"></button>
				</div>
				<div class="modal-body bg-primary-subtle overflow-visible" id="board"></div>
				<div class="modal-footer">
					<input type="text" id="input" class="form-control form-control-lg" autocapitalize="off" placeholder="在此輸入，按Enter送出" onkeydown="mykeyDown(this);" onkeyup="mykeyUp(this);">
				</div>
			</div>
		</div>
		
	</div>
	<audio id="bgm" loop><source src="bgm.mp3" type="audio/mpeg"></audio>
	<audio id="sfx_o"><source src="sfx_o.mp3" type="audio/mpeg"></audio>
	<audio id="sfx_x"><source src="sfx_x.mp3" type="audio/mpeg"></audio>

	<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://accounts.google.com/gsi/client"></script>
	<script>
		function get(id) { return document.getElementById(id); }
		function gets(qs) { return Array.from(document.querySelectorAll(qs)); }
		function getRnd(min,max) { return Math.floor(Math.random()*(max-min+1))+min; }
		function getRndCols() {
			var col=_cols.pop();
			if(_cols.length==0) {
				for(var i=0;i<Math.ceil(_bw/200);i++) (i != col) && _cols.push(i);
				_cols.sort(()=>{return Math.random()-0.5;})
			}
			return col;
		}
		function boardResize() {
			if(window.innerWidth < 768) {
				get("board").style.flexGrow="0.4";
			} else {
				get("board").style.flexGrow="";
			}
			var _css=getComputedStyle(get("board"));
			_bw=parseFloat(_css["width"]);
			_bh=parseFloat(_css["height"]);
			_rem=_bw % 200-10;
			_cols=[getRnd(0,Math.floor(_bw/200))];
		}
		function mykeyUp(k) {
			if(event.key === 'Enter') return;
			gets("mark").forEach(function(o){o.outerHTML=o.innerHTML;});
			gets(".word").filter(function(o) {
				return this.length && o.innerText.startsWith(this);
			}, k.value).forEach(function(o) {
				o.innerHTML=`<mark>${this}</mark>`+o.word.en.substr(this.length);
			}, k.value);
		}
		function mykeyDown(k) {
			if(event.key !== 'Enter') return;
			var match=false;
			gets(".word").filter(function(o) { 
				return this.trim()==o.innerText;
			}, k.value).forEach(function(o) {
				match=true;
				get("score").innerHTML=(++gameScore);
				o.innerHTML=o.word.tc;
				o.style.background="transparent";
				setTimeout(remove,3000,o);
			});
			if(get("cbSFX").checked) get(match?"sfx_o":"sfx_x").play();
			if(gameScore == wordCount && wordSpeed > 500) {
				wordSpeed-=100;
				clearInterval(wordTimer);
				wordTimer=setInterval(createWord,wordSpeed);
			}
			k.value="";
		}
		function remove(x) {
			clearInterval(x.tmr);
			x.remove();
		}
		function falling(o) {
			o.y++;
			o.style.left=o.x+"px";
			o.style.top=o.y+"px";
			if(o.y+o.h > _bh-5) {
				word.unshift(o.word);
				get("countTotal").innerHTML=`${--wordCount}/${wordTotal}`;
				remove(o);
			}
		}
		function createWord() {
			var x = document.createElement("div");
			x.className="word";
			x.word=word.pop();
			x.innerHTML=x.word.en;
			x.w=x.innerHTML.length * 9 + 20;
			x.h=26;
			x.x=getRndCols() * 200 + getRnd(x.w, _rem) - x.w;
			if(x.x < 5) x.x = 5;
			if(x.x + x.w > _bw) x.x = _bw - x.w - 10;
			x.y=0;
			x.style.left=x.x+"px";
			x.style.top=x.y+"px";
			x.tmr=setInterval(falling, wordSpeed/x.h, x);
			get("board").append(x);
			get("countTotal").innerHTML=`${++wordCount}/${wordTotal}`;
			if($("#wordCount").is(":visible") && (wordTotal==wordCount)) {
				(w.innerHTML.length>12)?changeWords(0,0):(w.innerHTML.length<7)?changeWords(7,12):changeWords(13,18);
				wordCount=0;
			}
		}
		function gameStart() {
			$('#modalTyping').modal('show');
			wordCount=0;
			gameScore=0;
			get("score").innerHTML=0;
			get("time").innerHTML=gameTime;
			get("input").focus();
			$("#wordCount").toggle(gameTime==0);
			if(get("cbBGM").checked) get("bgm").play();
			if(gameTime>0) {
				changeWords();
				wordSpeed=1000;
				timeCount=gameTime;
				gameTimer=setInterval(()=>{
					get("time").innerHTML=(--timeCount);
					if(timeCount<=0) gameStop();
				},1000);
			} else {
				changeWords(0,6);
				wordSpeed=2000;
				timeCount=0;
				gameTimer=setInterval(()=>{
					gameTime++;
					timeStr=gameTime;
					if(gameTime>60) timeStr=`${Math.floor(gameTime/60)}m ${gameTime%60}`;
					get("time").innerHTML=timeStr;
				},1000);
			}
			wordTimer=setInterval(createWord,wordSpeed);
			gamePlaying=1;
		}
		function gameStop() {
			if(!gamePlaying) return;
			gamePlaying=0;
			clearInterval(wordTimer);
			clearInterval(gameTimer);
			if(get("cbBGM").checked) get("bgm").pause();
			gets(".word").forEach(function(o){remove(o);});
			get("msg").innerHTML=`恭喜你得了 ${gameScore} 分！`;
			$(".toast").toast("show");
			if(gameScore>0) addRecord();
		}
		function gameInit() {
			gameScore=0;
			gamePlaying=0;
			loadWords();
			get('volBGM').value="<?php echo @$options["bgm_volume"]?$options["bgm_volume"]:"0.5";?>";
			get('bgm').volume=get('volBGM').value;
			$("#wordCount").hide();
			$("#modalTyping").on('shown.bs.modal',boardResize);
		}
		function changeWords(m=0,n=0) {
			window.word=Array.from(window.words).filter(function(x) {
				var ret=(x.Lv==gameLv);
				if(m>0) ret&&=(x.en.length >= m);
				if(n>0) ret&&=(x.en.length <= n);
				return  ret;
			});
			window.word.sort(()=>{return 0.5-Math.random();});
			wordTotal=window.word.length;
			get("countTotal").innerHTML=`${wordCount}/${wordTotal}`;
		}
		function loadWords() {
			fetch("words.json").then(response => response.json())
				.then(data => {
					window.words = data.map(item => {
						return {en:item.en,Lv:item.Lv,tc:item.tc};
					});
				}).catch(error => {
					console.error('Error loading the JSON file:', error);
				});
		}
		function toggleBGM(cb) {
			if(get("bgm").paused && cb.checked && gameTime>0) {
				get("bgm").play();
			} else {
				get("bgm").pause();
			} 
		}
		function saveSettings() {
			gameLv=$("#optLv").val();
			gameTime=$("#optTm").val();
			$.ajax({url:"ajax.php",type:"POST", cache:false,
				data:{cmd:"saveOpts",data:[$("#optLv").val(),$("#optTm").val(),get("cbBGM").checked?1:0,get("cbSFX").checked?1:0,$("#volBGM").val()]},
				success: function(data) {
					gameStart();
				}
			});
		}
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
