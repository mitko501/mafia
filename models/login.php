<?php
/**
 * Login model v2.0
 * User: mitko
 * startDate: 24.1.2014 17:55
 * endDate: 24.1.2014 20:00
 */

class login{
    private $key;
    private $registry;
    public $error;

    public function __construct(Registry $registry){
        $this->registry=$registry;
    }

    public function logInto($name,$password,$longCookie){ //Funkcia pre prihlasenie s parametrami menom a heslom, $longCookie sluzi na trvale prihlasenie
        $this->registry->getFirePHP()->log('login::logInto');
        if($this->loginCheck()==false){
            if($longCookie==true){
                $this->registry->getFirePHP()->log('login::logInto : login for long time');
                $cookieParams = session_get_cookie_params();
                setcookie(session_name(),$_COOKIE[session_name()],time()+60*60*24*4*12,$cookieParams['path'],$cookieParams['domain'],$cookieParams['secure'],$cookieParams['httponly']);
            }
            $this->registry->getObject('db')->executeQuery('SELECT * FROM users WHERE name="'.$name.'"');
            if($this->registry->getObject('db')->getNumRows()==1){
                $result=$this->registry->getObject('db')->getRows();
                if($result['active']==1){
                    $password=hash('sha512',$password.$result['salt']);
                    if($password==$result['password']){
                        $this->registry->getFirePHP()->log('login::logInto : Good Pass');
                        $user_browser = $_SERVER['HTTP_USER_AGENT'];
                        $user_id = preg_replace("/[^0-9]+/", "", $result['id']);
                        $_SESSION['user_id'] = $user_id;
                        $this->registry->getFirePHP()->log('login::logInto : user_id:'.$_SESSION['user_id']);
                        $username = preg_replace('/[^a-zA-Z0-9_\-]+/', "", $result['name']);
                        $_SESSION['username'] = $username;
                        $this->registry->getFirePHP()->log('login::logInto : user_name:'.$_SESSION['username']);
                        $_SESSION['login_string'] = hash('sha512', $password.$user_browser);
                        $this->registry->getFirePHP()->log('login::logInto : Success, logged in');
                        $this->registry->getObject('usr')->loginSuccessful();
                        return true;
                    }else{
                        $this->error='Nesprávna kombinácia mena a hesla';
                        $this->registry->getFirePHP()->log("login::logInto - " . $this->error);
                        return false;
                    }
                }else{
                    $this->error='Neaktívny užívateľ';
                    $this->registry->getFirePHP()->log("login::logInto - " . $this->error);
                    return false;
                }
            }else{

                $this->error='Zadaný užívateľ neexistuje.';
                $this->registry->getFirePHP()->log("login::logInto - " . $this->error);
                return false;
            }
        }else{
            $this->error= 'Už ste prihlásený';
            $this->registry->getFirePHP()->log("login::logInto - " . $this->error);
            return false;
        }
    }

    public function loginCheck() {
        $this->registry->getFirePHP()->log('login::loginCheck');
        if(isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'])) { //Kontrola session
            $this->registry->getFirePHP()->log('login::loginCheck - SESSION set');
            $user_id = $_SESSION['user_id'];
            $login_string = $_SESSION['login_string'];
            //$username = $_SESSION['username']; zmazat ak sa nepouziva
            $user_browser = $_SERVER['HTTP_USER_AGENT'];
            if ($stmt = $this->registry->getObject('db')->getPDO()->prepare("SELECT password FROM users WHERE id = :id LIMIT 1")) {
                $this->registry->getFirePHP()->log('login::loginCheck - stmt set');
                $stmt->bindValue(':id', $user_id,PDO::PARAM_INT);
                $stmt->execute();
                if($stmt->rowCount() == 1) {
                    $password= $stmt->fetch();
                    $login_check = hash('sha512', $password[0].$user_browser);
                    if($login_check == $login_string) {
                        $this->registry->getFirePHP()->log('login::loginCheck : success');
                        return true;
                    } else {
                        // Not logged in
                        $this->registry->getFirePHP()->log('login::loginCheck : error= loginstring');
                        //$this->logOut();
                        return false;
                    }
                } else {
                    // Not logged in
                    $this->registry->getFirePHP()->log('login::loginCheck : error=no user');
                    //$this->LogOut();
                    return false;
                }
            } else {
                // Not logged in
                $this->registry->log('login::loginCheck : error= stmt');
                //$this->LogOut();
                return false;
            }
        } else {
            $this->registry->getFirePHP()->log('login::loginCheck error= session');
            //$this->LogOut();
            return false;
        }
    }

    public function logOut(){
        $this->registry->getFirePHP()->log('login::LogOut');
        $_SESSION = array();
        // get session parameters
        $params = session_get_cookie_params();
        // Delete the actual cookie.
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        // Destroy session
        if(isset($_COOKIE['SESSION_ID'])){
            $this->registry->getObject('session')->SessDestroy($_COOKIE['SESSION_ID']);
        }
        if(!isset($_SESSION['username'])){
            return true;
        }else{
            return false;
        }
    }
}