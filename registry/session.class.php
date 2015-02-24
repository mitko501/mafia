<?php
class mysql{
    /**
     * Mysql Class v 3.0
     * 13.7.2014
     * @author Michal Hajas
     */

    private $connections=array(); //all connections
    private $ActiveConnection= 0; //index of active connection
    private $Last=array();
    private $Before; //predchadzajuce spojenie
    private $registry;

    public function __construct($registry){
        $this->registry= $registry;

    }

    public function newConnection($dbname){
        require_once(BASE_DIR . 'config/config.php');
        $dbname= (empty($dbname)) ? $Config['DB'] : $dbname;
        try {
            $this->connections[] = NEW PDO("mysql:host=" . $Config['host'] . ";dbname=" . $dbname . ";charset=utf8", $Config['name'], $Config['pass'],array(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION));
        }catch(PDOException $e) {
            trigger_error('Spojenie zlyhalo.');
            $this->registry->firephp->log('[mysql::newConnection]: Connection failed: ' . $e->getMessage());
            exit();
        }
        $this->Before= $this->ActiveConnection;
        $this->ActiveConnection = count($this->connections) - 1;
    }

    public function getPDO(){
        return $this->connections[$this->ActiveConnection];
    }

    public function setActiveConnection($index){
        $this->Before=$this->ActiveConnection;
        $this->ActiveConnection=$index;
    }

    public function getActiveConnection(){
        return $this->ActiveConnection;
    }

    public function setBeforeActive(){
        $Before= $this->ActiveConnection;
        $this->ActiveConnection= $this->Before;
        $this->Before= $Before;
    }

    public function executeQuery($sql){
        //echo $sql;
        $this->registry->firephp->log("[mysql::executeQuery]: query: " . $sql); //DEBUG

        try{
            $this->Last[$this->ActiveConnection] = $this->connections[$this->ActiveConnection]->prepare($sql);
            $this->Last[$this->ActiveConnection]->execute();
        }catch(PDOException $e) {
            trigger_error('Chyba pri pokuse o vykonanie dotazu');
            $this->registry->firephp->log('[mysql::executeQuery]: Query error: ' . $e->getMessage());
        }

        $this->registry->firephp->log("[mysql::executeQuery]: query returned: " . $this->getNumRows()); //DEBUG
    }

    /**
     * SQL HELPING
     */

    public function insert($table, $data){
        $cols='';
        $values='';
        foreach($data as $col => $value){
            $cols.= $col.',';
            $values .= ":$col,";
        }
        $cols = substr($cols, 0, -1);
        $values = substr($values, 0, -1);

        $sql= ("INSERT INTO ".$table ."(". $cols .") VALUES(". $values . ") ");


        try {
            $this->Last[$this->ActiveConnection] = $this->connections[$this->ActiveConnection]->prepare($sql);

            foreach ($data as $col => $value) {
                if (!is_array($value)) {
                    $this->Last[$this->ActiveConnection]->bindValue(":$col", $value, PDO::PARAM_STR);
                } else {
                    $this->Last[$this->ActiveConnection]->bindValue(":$col", $value[0], $data['type']);
                }
            }
            $this->Last[$this->ActiveConnection]->execute();
        }catch(PDOException $e) {
            trigger_error('Chyba pri pokuse o vykonanie dotazu');
            $this->registry->firephp->log('[mysql::insert]: Query error: ' . $e->getMessage());
        }

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
            $this->Last[$this->ActiveConnection] = $this->connections[$this->ActiveConnection]->prepare($sql);

            foreach ($data as $col => $value) {
                if (!is_array($value)) {
                    $this->Last[$this->ActiveConnection]->bindValue(":$col", $value, PDO::PARAM_STR);
                } else {
                    $this->Last[$this->ActiveConnection]->bindValue(":$col", $value[0], $data['type']);
                }
            }
            $this->Last[$this->ActiveConnection]->execute();
        }catch(PDOException $e) {
            trigger_error('Chyba pri pokuse o vykonanie dotazu');
            $this->registry->firephp->log('[mysql::update]: Query error: ' . $e->getMessage());
        }
    }

    /**
     * Result
     */

    public function getNumRows(){
        return $this->Last[$this->ActiveConnection]->rowCount();
    }

    public function getRows(){
        return $this->Last[$this->ActiveConnection]->fetch(PDO::FETCH_ASSOC);
    }


    public function closeThisConnection(){
        $this->connections[$this->ActiveConnection]= null;
    }

    public function closeConnection($index){
        $this->connections[$index] = null;
    }

    public function __deconstruct() {
        $this->connections[$this->ActiveConnection] = null;

    }

}
?>
