<?php
class MySQL{
    /**
     * MySQL Class v 3.0
     * 13.7.2014
     * @author Michal Hajas
     * Take care of connection with DB
     */

    private $connections = array(); //all connections
    private $activeConnection = 0; //index of active connection
    private $last = array();
    private $before; //predchadzajuce spojenie
    private $registry;

    public function __construct($registry){
        $this->registry = $registry;
    }

    /**
     * @param $dbname name of DB
     * create new connection
     */
    public function newConnection($dbname){
        require_once(BASE_DIR . 'config/config.php');
        $dbname = (empty($dbname)) ? $config['DB'] : $dbname;

        try {
            $this->connections[] = new PDO("mysql:host=" . $config['host'] . ";dbname=" . $dbname . ";charset=utf8", $config['name'], $config['pass'],array(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION));
        }catch(PDOException $e) {
            trigger_error('Spojenie zlyhalo.');
            $this->registry->getFirePHP()->log('[MySQL::newConnection]: Connection failed: ' . $e->getMessage());
            exit();
        }

        $this->before = $this->activeConnection;
        $this->activeConnection = count($this->connections) - 1;

        $this->registry->getFirePHP()->log('[MySQL::newConnection]: Connection success: dbname="' . $dbname . '"');
    }

    /**
     * @return PDO object
     */
    public function getPDO(){
        return $this->connections[$this->activeConnection];
    }

    /**
     * @param $index of connection
     * set active conneciton
     */
    public function setActiveConnection($index){
        $this->before = $this->activeConnection;
        $this->activeConnection = $index;
    }

    /**
     * @return int index of active connection
     */
    public function getActiveConnection(){
        return $this->activeConnection;
    }

    public function setBeforeActive(){
        $before = $this->activeConnection;
        $this->activeConnection = $this->before;
        $this->before = $before;
    }

    public function executeQuery($sql){
        //echo $sql;
        $this->registry->getFirePHP()->log("[MySQL::executeQuery]: trying to execute query: " . $sql); //DEBUG

        try{
            $this->last[$this->activeConnection] = $this->connections[$this->activeConnection]->prepare($sql);
            $this->last[$this->activeConnection]->execute();
        }catch(PDOException $e) {
            trigger_error('Chyba pri pokuse o vykonanie dotazu');
            $this->registry->getFirePHP()->log('[MySQL::executeQuery]: query error: ' . $e->getMessage());
        }

        $this->registry->getFirePHP()->log("[MySQL::executeQuery]: query executed and returned: " . $this->getNumRows()); //DEBUG
    }

    /**
     * SQL HELPING
     */

    public function insert($table, $data){
        $cols = '';
        $values = '';

        foreach($data as $col => $value){
            $cols .= $col.',';
            $values .= ":$col,";
        }

        $cols = substr($cols, 0, -1);
        $values = substr($values, 0, -1);

        $sql= ("INSERT INTO " . $table . "(" . $cols . ") VALUES(" . $values . ") ");

        try {
            $this->last[$this->activeConnection] = $this->connections[$this->activeConnection]->prepare($sql);

            foreach ($data as $col => $value) {
                if (!is_array($value)) {
                    $this->last[$this->activeConnection]->bindValue(":$col", $value, PDO::PARAM_STR);
                } else {
                    $this->last[$this->activeConnection]->bindValue(":$col", $value[0], $data['type']);
                }
            }
            $this->last[$this->activeConnection]->execute();
        }catch(PDOException $e) {
            trigger_error('Chyba pri pokuse o vykonanie dotazu');
            $this->registry->getFirePHP()->log('[MySQL::insert]: query error: ' . $e->getMessage());
        }

        $this->registry->getFirePHP()->log('[MySQL::insert]: query executed: ');
    }

    public function update($table, $data, $where){
        $update='';
        foreach ($data as $field => $value) {
            $update .= " " . $field . "=:" . $field . ",";
        }
        $update = substr($update, 0, -1);
        $where= (strpos($where,'WHERE') === false && !empty($where)) ? 'WHERE ' . $where : $where;
        $sql = 'UPDATE ' . $table . ' SET ' . $update . ' ' . $where;

        try {
            $this->last[$this->activeConnection] = $this->connections[$this->activeConnection]->prepare($sql);

            foreach ($data as $col => $value) {
                if (!is_array($value)) {
                    $this->last[$this->activeConnection]->bindValue(":$col", $value, PDO::PARAM_STR);
                } else {
                    $this->last[$this->activeConnection]->bindValue(":$col", $value[0], $data['type']);
                }
            }
            $this->last[$this->activeConnection]->execute();
        }catch(PDOException $e) {
            trigger_error('Chyba pri pokuse o vykonanie dotazu');
            $this->registry->getFirePHP()->log('[mysql::update]: Query error: ' . $e->getMessage());
        }
    }

    /**
     * Resulting
     */

    /**
     * @return num of returned rows
     */
    public function getNumRows(){
        return $this->last[$this->activeConnection]->rowCount();
    }

    /**
     * tieto ostatne by mali byt jasne
     */
    public function getRows(){
        return $this->last[$this->activeConnection]->fetch(PDO::FETCH_ASSOC);
    }


    public function closeThisConnection(){
        $this->connections[$this->activeConnection] = null;
    }

    public function closeConnection($index){
        $this->connections[$index] = null;
    }

    public function closeAllConections(){
        foreach($this->connections as $value){
            $value = null;
        }
    }

    /**
     * destructor
     */
    public function __deconstruct() {
        foreach($this->connections as $value) {
            $value = null;
        }
    }
}
?>
