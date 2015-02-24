<?php
/**
 * Main Controller v2.0
 * User: mitko
 * startDate: 24.1.2014 12:00
 * endDate:
 */

class mainController{

	private $registry;
    private $file;

	public function __construct(Registry $registry){
		$this->registry= $registry;
        $this->registry->firephp->log("mainController");
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
        return "maine";
    }
}
?>