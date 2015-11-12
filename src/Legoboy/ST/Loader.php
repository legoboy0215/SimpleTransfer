<?php

namespace Legoboy\ST;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Loader extends PluginBase{
	
	private $db;
	private $users = 0;
	
	public function onEnable(){
		if(!file_exists($this->getDataFolder())){
			@mkdir($this->getDataFolder());
		}
		$this->saveDefaultConfig();
		var_dump($this->getServer()->getPluginManager()->getPlugin("SimpleAuth")->getDataFolder());
		$this->db = new \mysqli(is_null($this->getConfig()->get("server-name")) ? $this->getConfig()->get("server-name") : "localhost", $this->getConfig()->get("username"), $this->getConfig()->get("password"), $this->getConfig()->get("simpleauth-dbname"), is_null($this->getConfig()->get("port")) ? ((int)$this->getConfig()->get("port")) : 3306);
		// Check connection
		if($this->db->connect_error){
			die("Connection failed: " . $this->db->connect_error);
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
		var_dump($this->getServer()->getPluginManager()->getPlugin("SimpleAuth")->getDataFolder());
		$this->process();
	}
	
	public function process(){
		$a = range('a', 'z');
		foreach($a as $letter){
			foreach(glob($this->getServer()->getPluginManager()->getPlugin("SimpleAuth")->getDataFolder() . "/players/" . $letter) as $file){
				$data = new Config($file, Config::YAML);
				$pname = trim(strtolower(basename($file, ".yml")));
				$regdate = $data->get("registerdate");
				$logindate = $data->get("logindate");
				$ip = $data->get("lastip");
				$hash = $data->get("hash");
				/*$this->db->query("UPDATE simpleauth_players SET name = 
				'" . $pname . "', hash = '" . $hash . "', registerdate = '"
				. $regdate ."', logindate = '" . $logindate . "', lastip = '"
				. $ip . "' WHERE ");*/
				$result = $this->db->query("INSERT INTO simpleauth_players (name, hash, registerdate, logindate, lastip)
											VALUES ('$pname', '$hash', '$regdate', '$logindate', '$ip')"
						  );
				if($result){
					$this->users++;
				}else{
					$this->getServer()->getPluginManager()->disablePlugin($this);
					$this->getLogger()->critical("Unable to sumbit user to MySQL Database: Unknown Error. Disabling Plugin...");
				}
				if($this->users % 100 === 0){
					$this->getLogger()->notice((string) $this->users . " processed.");
				}
			}
		}
	}
}