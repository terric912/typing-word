<?php
if(!file_exists("_DBsettings.php")) {
	define("DBHOST","localhost");
	define("DBNAME","dbname");
	define("DBUSER","dbuser");
	define("DBPSWD","dbpass");
	$_GG["google_auth"]=false;
	$_GG["google_client_id"]="";
} else require_once("_DBsettings.php");

class MyDB extends PDO {
	private $_db = null;
	private $_link = null;

	public function __construct() {
		$_DSN=sprintf("mysql:host=%s;dbname=%s;charset=UTF8",DBHOST,DBNAME);
		$this->_db=new PDO($_DSN,DBUSER,DBPSWD);
	}
	public function __destruct() {
		unset($this->_db);
		unset($this->_link);
		$this->_db=null;
    }
	public function dbClose() {
		unset($this->_db);
		unset($this->_link);
		$this->_db=null;
	}
	public function setPrepare($sql) {
		$this->_link = $this->_db->prepare($sql);
	}
	public function doExecute($opt=null) {
		if ($opt) $this->_link->execute($opt);
		else $this->_link->execute();
	}
	private function _query($sql=null,$opt=null) {
		if(!isset($sql)) return;
		$this->setPrepare($sql);
		$this->doExecute($opt);
	}
	public function getCount($sql=null,$opt=null) {
		$this->_query($sql,$opt);
		return $this->_link->rowCount();
	}
	public function doQuery($sql,$opt=null,$onlyone=false) {
		$this->_query($sql,$opt);
		if ($onlyone) return $this->_link->fetch(PDO::FETCH_BOTH);
		return $this->_link->fetchAll(PDO::FETCH_ASSOC);
	}
}
global $myDB;
if(!$myDB) $myDB=new MyDB();
?>
