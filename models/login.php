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
		$this->registry->firephp->log('login::logInto');
		if($this->loginCheck()==false){
			if($longCookie==true){
		        $this->registry->firephp->log('login::logInto : login for long time');
				$cookieParams = session_get_cookie_params();
				setcookie(session_name(),$_COOKIE[session_name()],time()+60*60*24*4*12,$cookieParams['path'],$cookieParams['domain'],$cookieParams['secure'],$cookieParams['httponly']);
			}
			$this->registry->getObject('db')->ExecuteQuery('SELECT * FROM users WHERE name="'.$name.'"');
			if($this->registry->getObject('db')->GetNumRows()==1){
				$result=$this->registry->getObject('db')->GetRows();
                if($result['active']==1){
                    $password=hash('sha512',$password.$result['salt']);
                    if($password==$result['password']){
                        $this->registry->firephp->log('login::logInto : Good Pass');
                        $user_browser = $_SERVER['HTTP_USER_AGENT'];
                        $user_id = preg_replace("/[^0-9]+/", "", $result['id']);
                        $_SESSION['user_id'] = $user_id;
                        $this->registry->firephp->log('login::logInto : user_id:'.$_SESSION['user_id']);
                        $username = preg_replace("/[^a-zA-Z0-9_\-]+/", "", $result['name']);
                        $_SESSION['username'] = $username;
                        $this->registry->firephp->log('login::logInto : user_name:'.$_SESSION['username']);
                        $_SESSION['login_string'] = hash('sha512', $password.$user_browser);
                        $this->registry->firephp->log('login::logInto : Success, logged in');
                        $this->registry->getObject('usr')->loginSuccessful();
                        return true;
                    }else{
                        $this->error='Nesprávna kombinácia mena a hesla';
                        $this->registry->firephp->log("login::logInto - " . $this->error);
                        return false;
                    }
                }else{
                    $this->error='Neaktívny užívateľ';
                    $this->registry->firephp->log("login::logInto - " . $this->error);
                    return false;
                }
            }else{
                $this->error='Zadaný užívateľ neexistuje.';
                $this->registry->firephp->log("login::logInto - " . $this->error);
                return false;
            }
        }else{
            $this->error= 'Už ste prihlásený';
            $this->registry->firephp->log("login::logInto - " . $this->error);
            return false;
        }
    }
	
	public function loginCheck() {
		$this->registry->firephp->log('login::loginCheck');
		if(isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['login_string'])) { //Kontrola session
            $this->registry->firephp->log('login::loginCheck - SESSION set');
            $user_id = $_SESSION['user_id'];
			$login_string = $_SESSION['login_string'];
			//$username = $_SESSION['username']; zmazat ak sa nepouziva
			$user_browser = $_SERVER['HTTP_USER_AGENT'];
            if ($stmt = $this->registry->getObject('db')->getPDO()->prepare("SELECT password FROM users WHERE id = ? LIMIT 1")) {
                $this->registry->firephp->log('login::loginCheck - stmt set');
				$stmt->bind_param('i', $user_id);
			    $stmt->execute();
				$stmt->store_result();
                if($stmt->num_rows == 1) {
					$stmt->bind_result($password);
					$stmt->fetch();
					$stmt->close();
					$login_check = hash('sha512', $password.$user_browser);
					if($login_check == $login_string) {
                        $this->registry->firephp->log('login::loginCheck : success');
						return true;
					} else {
						// Not logged in
						$this->registry->firephp->log('login::loginCheck : error= loginstring');
						//$this->logOut();
						return false;
					}
				} else {
					// Not logged in
					$this->registry->firephp->log('login::loginCheck : error=no user');
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
			$this->registry->firephp->log('login::loginCheck error= session');
			//$this->LogOut();
			return false;
		}
	}
	
	public function logOut(){
		$this->registry->firephp->log('login::LogOut');
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

/* REGISTRACIA

if (isset($_POST['username'], $_POST['email'], $_POST['p'])) {
    // Sanitize and validate the data passed in
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Not a valid email
        $error_msg .= '<p class="error">The email address you entered is not valid</p>';
    }
 
    $password = filter_input(INPUT_POST, 'p', FILTER_SANITIZE_STRING);
    if (strlen($password) != 128) {
        // The hashed pwd should be 128 characters long.
        // If it's not, something really odd has happened
        $error_msg .= '<p class="error">Invalid password configuration.</p>';
    }
 
    // Username validity and password validity have been checked client side.
    // This should should be adequate as nobody gains any advantage from
    // breaking these rules.
    //
 
    $prep_stmt = "SELECT id FROM members WHERE email = ? LIMIT 1";
    $stmt = $mysqli->prepare($prep_stmt);
 
   // check existing email  
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
 
        if ($stmt->num_rows == 1) {
            // A user with this email address already exists
            $error_msg .= '<p class="error">A user with this email address already exists.</p>';
                        $stmt->close();
        }
                $stmt->close();
    } else {
        $error_msg .= '<p class="error">Database error Line 39</p>';
                $stmt->close();
    }
 
    // check existing username
    $prep_stmt = "SELECT id FROM members WHERE username = ? LIMIT 1";
    $stmt = $mysqli->prepare($prep_stmt);
 
    if ($stmt) {
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
 
                if ($stmt->num_rows == 1) {
                        // A user with this username already exists
                        $error_msg .= '<p class="error">A user with this username already exists</p>';
                        $stmt->close();
                }
                $stmt->close();
        } else {
                $error_msg .= '<p class="error">Database error line 55</p>';
                $stmt->close();
        }
 
    // TODO: 
    // We'll also have to account for the situation where the user doesn't have
    // rights to do registration, by checking what type of user is attempting to
    // perform the operation.
 
    if (empty($error_msg)) {
        // Create a random salt
        //$random_salt = hash('sha512', uniqid(openssl_random_pseudo_bytes(16), TRUE)); // Did not work
        $random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
 
        // Create salted password 
        $password = hash('sha512', $password . $random_salt);
 
        // Insert the new user into the database 
        if ($insert_stmt = $mysqli->prepare("INSERT INTO members (username, email, password, salt) VALUES (?, ?, ?, ?)")) {
            $insert_stmt->bind_param('ssss', $username, $email, $password, $random_salt);
            // Execute the prepared query.
            if (! $insert_stmt->execute()) {
                header('Location: ../error.php?err=Registration failure: INSERT');
            }
        }
        header('Location: ./register_success.php');
    }
}
*/