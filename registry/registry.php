<?php
class Registry {
	
	private $objects;
    private $hasControllersStored = false;
    private $hasSettingsStored = false;
	private $settings;
	private $controllers=array();
    private $privileges= array();
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
            $this->GetObject('db')->ExecuteQuery('SELECT * FROM controllers');
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
		return $this->objects[$key];
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