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
