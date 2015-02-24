<?php

/**
 * User class v2.0
 * User: mitko
 * startDate: 24.1.2014 12:00
 * endDate:
 */

class user{

    private $userInfo=array("privileges" => 0, "online" => false);
	private $registry;
	
	public function __construct(Registry $registry){
		$this->registry= $registry;
        require_once(BASE_DIR . 'models/login.php');
        $login= new login($this->registry);
        if($login->loginCheck()==true){
            $this->loginSuccessful();
        }
	}

    public function loginSuccessful(){
        $this->userInfo['online']=true;
        $this->userInfo['privileges']=1;
    }

    public function getUserInfo($index){
        if(isset($this->userInfo[$index])){
            return $this->userInfo[$index];
        }else{
            return false;
        }
    }

    public function setUserInfo($index,$value){
        return $this->userInfo[$index]=$value;
    }
}
?>