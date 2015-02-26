<?php
/**
 * Url class v3.0
 * User: mitko
 * startDate: 26.2.2015 20:00
 * endDate:
 * Trieda url riesi parametre URL, kontroluje hlavne validitu, a takisto opravnenia pre jednotlive controlleri
 */
class Url{
	private $urlArguments; //array of string
    private $hasArgumentsParsed;
    private $urlData; //urlData before checking validity array of strings
    private $controller; //string
    private $hasControllerParsed;
    private $registry; //Registry

	public function __construct(Registry $registry) {
        $this->registry = $registry;

        if (isset($_GET['page'])) {
            $this->urlData = explode('/', $_GET["page"]);
        } else {
            $this->controller = MAIN_CONTROLLER;
        }
    }

    private function parseController(){
        if($this->hasControllerParsed){
            return;
        }

        if ($this->registry->isController($this->urlData[0])) {
            if ($this->registry->getObject('user')->getUserPrivileges() >= $this->registry->getPrivileges($this->urlData[0])) {
                $this->controller = $this->urlData[0];
                array_shift($this->urlData); //delete first
            }
        } else {
            $this->controller = MAIN_CONTROLLER;
        }
    }

    private function parseArguments() {
        if($this->hasArgumentsParsed){
            return;
        }

        foreach($this->urlData as $argument){
            if(preg_match('/^([a-z]|[A-Z]|[0-9])*$/', $argument) && $argument != ""){
                $this->urlArguments[] = $argument;
            }else{
                break;
            }
        }
    }

    public function getController() {
        if (!$this->hasControllerParsed) {
            $this->parseController();
        }
        return $this->controller;
    }

    public function getArgument($index){
        if (!$this->hasArgumentsParsed) {
            $this->parseArguments();
        }

        if (isset($this->urlArguments[$index])){
            return $this->urlArguments[$index];
        }
        return false;
    }
}
?>