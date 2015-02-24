<?php
/**
 * Login Controller v2.0
 * User: mitko
 * startDate: 24.1.2014 12:00
 * endDate:
 */

class loginController{

	private $registry;
    private $file;

    public function __construct(Registry $registry){
        $this->registry= $registry;
        $this->registry->firephp->log("loginController");
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')){
        //if(1==1){ //DEBUG
            $this->registry->firephp->log("loginController - Ajax");
            if($this->registry->getObject('url')->getUrlBit(1)==false){
                $this->login();
            }elseif($this->registry->getObject('url')->getUrlBit(1)=='logout'){
                $this->logout();
            }
        }else{
            require_once(BASE_DIR.'models/view.php');
            $file=new view($this->registry);
            $this->file= $file->buildNewFile($this);
            $this->buildBody();
        }
    }

	private function BuildBody(){
        echo $this->file;
	}

    private function login(){
        $this->registry->firephp->log("loginController::login");
        if(isset($_POST['name'],$_POST['password'])){
            $this->registry->firephp->log("loginController - Set data");
            if(isset($_POST['long']) && $_POST['long']=='on'){
                $cookie=true;
                $this->registry->firephp->log("loginController::login - long login");
            }else{
                $this->registry->firephp->log("loginController::login - regular login");
                $cookie=false;
            }
            require_once(BASE_DIR . 'models/login.php');
            $login= new login($this->registry);

            if($login->loginCheck()!=true){
                $this->registry->firephp->log("loginController::login - neprihlásený");
                $login->logInto(preg_replace("/[^a-zA-Z0-9_\-]+/", "", $_POST['name']),preg_replace("/[^a-zA-Z0-9_\-]+/", "", $_POST['password']),$cookie);
                if($login->loginCheck()==true){
                    $this->registry->firephp->log("loginController::login - logged");
                    $result['success']=true;
                    $result['message']='Úspešne ste sa prihlásili.';
                }else{
                    $this->registry->firephp->log("loginController::login - error");
                    $result['success']=false;
                    $result['message']=$login->error;
                }
            }else{
                $this->registry->firephp->log("loginController::login - already logged");
                $result['changelocation']=true;
                $result['message']='Už ste prihlásený';
                $result['success']=false;
            }
        }else{
            $result['message']="Zle zadané údaje";
            $result['success']=false;
        }
        echo json_encode($result);
    }

    private function logout(){
        require_once(BASE_DIR . 'models/login.php');
        $login= new login($this->registry);
        if($login->loginCheck()==true){
            $login->logOut();
            if($login->loginCheck()==true){
                $result['success']=false;
                $result['message']='Nepodarilo sa vás odhlásiť. Skúste to neskôr alebo kontaktujte administrátora';
            }else{
                $result['success']=true;
            }
        }else{
            $result['success']=false;
            $result['message']='Nieste prihlásený.';
        }
        echo json_encode($result);
    }

    public function getTitle(){
        return "login";
    }
}
?>