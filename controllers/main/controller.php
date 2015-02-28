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
        $this->registry->getFirePHP()->log("mainController::_construct");
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')){
            //Ajaxove poziadavky to preberieme neskor
        }else{
            //toto by sme mali tiez premysliet nejako inak, ale uvidime ako
            //zatial to funguje tak ze vytvori objekt view a on sa postara o zvysok vysledok sa ulozi
            //do atributu aby sa s tym dalo pracovat este aj tu aj ked nebudu nahradene vsetky tagy
            require_once(BASE_DIR.'models/view.php');
            $file = new view($this->registry);
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