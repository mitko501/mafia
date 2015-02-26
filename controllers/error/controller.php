<?php
/**
 * error controller v1.0
 * User: mitko
 * startDate: 1.2.2014 18:40
 * endDate:
 */

class errorController{

    private $registry;
    private $file;

    public function __construct(Registry $registry){
        $this->registry= $registry;
        $this->registry->getFirePHP()->log("404 error");
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')){
            //Ajaxove poziadavky
        }else{
            require_once(BASE_DIR.'models/view.php');
            $file=new view($this->registry);
            $this->file= $file->buildNewFile($this);
            $this->buildBody();
        }
    }

    private function buildBody(){
        echo $this->file;

    }

    public function getTitle(){
        return "error";
    }
}
?>