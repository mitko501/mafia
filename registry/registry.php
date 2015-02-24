<?php
class Registry {
	
	private $objects = array();
    private $hasControllersStored = false;
    private $hasSettingsStored = false;
	private $settings;
	private $controllers = array();
    private $privileges = array();
	public $firephp;
	
	public function __construct(){
		$this->firephp = FirePHP::getInstance(true);
	}
	
	public function createAndStoreObject($object, $key){
		require_once($object . '.class.php');
		$this->objects[$key] = new $object($this);
	}
	
	public function storeSetting($key, $setting){
        if(!$this->hasSettingsStored) {
            $this->settings[$key] = $setting;
            $this->hasControllersStored = true;
        }
	}
	
	public function getSetting($key){
		return $this->settings[$key];
	}
	
	public function storeControllers()
    {
        if (!$this->hasControllersStored) {
            $this->GetObject('MySQL')->ExecuteQuery('SELECT * FROM controllers');
            while ($setting = $this->GetObject('db')->GetRows()) {
                $this->controllers[] = $setting['value'];
                $this->privileges[$setting['value']] = $setting['privileges'];
            }
            $this->hasControllersStored = true;
        }
	}

    public function getPrivileges($controller){
        return $this->privileges[$controller];
    }
	
	public function isController($value){
		return in_array($value, $this->controllers) && $value != "";

	}
	
	public function destroyControllers(){
		unset($this->controllers);
	}
	
	public function getObject($key){
        if(array_key_exists($key,$this->objects)) {
            return $this->objects[$key];
        }

        if(file_exists(BASE_DIR . "registry/" . $key . ".class.php")){
            require_once($key . '.class.php');
            $this->objects[$key] = new $key($this);
            return $this->objects[$key];
        }else{
            trigger_error("Objekt neexistuje!");
            exit();
        }
	}
	
	public function buildURL($urlBits, $queryString='') {
		return $this->getObject('url')->buildURL($urlBits, $queryString, false);
	}

    public function alert($message){
        echo "<script>alert('" . $message . "')</script>";
    }
	
	public function redirectURL($url,$time=0) {
		echo '<script>setTimeout(function() {$(window.location).attr(\'href\', "' . BASE_LINK . $url . '");}, ' . $time . ');</script>' . "\n";
        //echo '<meta http-equiv="refresh" content="' . $time . '; ' . BASE_LINK . $url . '">';
	}

    public function changeURL($url){
        echo "<script>history.replaceState(null, null, '".$url."');</script>";
    }
	
	public function setDebugging($value) {
		$this->firephp->setEnabled($value);
	}
}
?>