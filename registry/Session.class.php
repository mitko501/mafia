<?php

class session{


    public function __construct(Registry $Registry) {
        $this->registry=$Registry;
        // set our custom session functions.
        session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc'));

        // This line prevents unexpected effects when using objects as save handlers.
        register_shutdown_function('session_write_close');
        $this->start_session('SESSION_ID',false);
    }

    public function start_session($session_name, $secure) {
        // Make sure the session cookie is not accessable via javascript.
        $httponly = true;

        // Hash algorithm to use for the sessionid. (use hash_algos() to get a list of available hashes.)
        $session_hash = 'sha512';

        // Check if hash is available
        if (in_array($session_hash, hash_algos())) {
            // Set the has function.
            ini_set('session.hash_function', $session_hash);
        }
        // How many bits per character of the hash.
        // The possible values are '4' (0-9, a-f), '5' (0-9, a-v), and '6' (0-9, a-z, A-Z, "-", ",").
        ini_set('session.hash_bits_per_character', 5);

        // Force the session to only use cookies, not URL variables.
        ini_set('session.use_only_cookies', 1);

        // Get session cookie parameters
        $cookieParams = session_get_cookie_params();
        // Set the parameters
        session_set_cookie_params($cookieParams["lifetime"], $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
        // Change the session name
        session_name($session_name);
        // Now we cat start the session
        session_start();
        // This line regenerates the session and delete the old one.
        // It also generates a new encryption key in the database.
        // session_regenerate_id(true);
    }

    public function StartLongSession(){
        session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc'));

        // This line prevents unexpected effects when using objects as save handlers.
        register_shutdown_function('session_write_close');
        $httponly = true;
        $secure=false;
        // Hash algorithm to use for the sessionid. (use hash_algos() to get a list of available hashes.)
        $session_hash = 'sha512';

        // Check if hash is available
        if (in_array($session_hash, hash_algos())) {
            // Set the has function.
            ini_set('session.hash_function', $session_hash);
        }
        // How many bits per character of the hash.
        // The possible values are '4' (0-9, a-f), '5' (0-9, a-v), and '6' (0-9, a-z, A-Z, "-", ",").
        ini_set('session.hash_bits_per_character', 5);

        // Force the session to only use cookies, not URL variables.
        ini_set('session.use_only_cookies', 1);

        // Get session cookie parameters
        $cookieParams = session_get_cookie_params();
        // Set the parameters

        // session_set_cookie_params($lifetime, $cookieParams["path"], $cookieParams["domain"], $secure, $httponly);
        // Change the session name
        session_name('SESSION_ID');
        // Now we cat start the session
        session_start();

    }

    function open() {
        $this->db = $this->registry->getObject('db')->getPDO();
        return true;
    }

    function close() {
        $this->db=null;
        return true;
    }

    function read($id) {
        if(!isset($this->read_stmt)){
            $this->read_stmt = $this->db->prepare("SELECT data FROM sessions WHERE id = :id LIMIT 1");
        }
        $this->read_stmt->bindValue(':id', $id, PDO::PARAM_STR);
        $this->read_stmt->execute();
        $data = $this->read_stmt->fetch();
        $data = $data[0];
        $key = $this->getkey($id);
        $data = $this->decrypt($data, $key);
        return $data;
    }

    function write($id, $data) {
        // Get unique key
        $key = $this->getkey($id);
        // Encrypt the data
        $data = $this->encrypt($data, $key);

        $time = time();
        if(!isset($this->w_stmt)) {
            $this->w_stmt = $this->db->prepare("REPLACE INTO sessions (id, set_time, data, session_key) VALUES (?, ?, ?, ?)");
        }

        $this->w_stmt->execute(array($id, $time, $data, $key));
        return true;
    }

    function destroy($id){
        if(!isset($this->delete_stmt)) {
            $this->delete_stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
        }
        $this->delete_stmt->bindValue(1, $id, PDO::PARAM_STR);
        $this->delete_stmt->execute();
        return true;
    }

    function gc($max) {
        if(!isset($this->gc_stmt)) {
            $this->gc_stmt = $this->db->prepare("DELETE FROM sessions WHERE set_time < ?");
        }
        $old = time() - $max;
        $this->gc_stmt->bindValue(1, $old, PDO::PARAM_STR);
        $this->gc_stmt->execute();
        return true;
    }

    function SessDestroy($id){
        if(!isset($this->delete_stmt)) {
            $this->delete_stmt = $this->db->prepare("DELETE FROM sessions WHERE id = ?");
        }
        $this->delete_stmt->bindValue(1, $id,PDO::PARAM_STR);
        $this->delete_stmt->execute();
        return true;
    }

    private function getkey($id) {
        if(!isset($this->key_stmt)) {
            $this->key_stmt = $this->db->prepare("SELECT session_key FROM sessions WHERE id = ? LIMIT 1");
        }
        $this->key_stmt->bindValue(1, $id,PDO::PARAM_STR);
        $this->key_stmt->execute();
        if($this->key_stmt->rowCount() == 1) {
            $key = $this->key_stmt->fetch();
            return $key[0];
        } else {
            $random_key = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
            return $random_key;
        }
    }

    private function encrypt($data, $key) {
        $salt = 'ndw0yr7)_#$_#(@*_S:ACMAS:CM+)E(R@#)(R)_SCosad-30i-90093-090-9-0392=-40';
        $key = substr(hash('sha512', $salt.$key.$salt), 0, 32);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $iv));
        return $encrypted;
    }

    private function decrypt($data, $key) {
        $salt = 'ndw0yr7)_#$_#(@*_S:ACMAS:CM+)E(R@#)(R)_SCosad-30i-90093-090-9-0392=-40';
        $key = substr(hash('sha512', $salt.$key.$salt), 0, 32);
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($data), MCRYPT_MODE_ECB, $iv);
        return $decrypted;
    }
}
?>