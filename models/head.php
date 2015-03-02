<?php
/**
 * Head model v1.0
 * User: mitko
 * startDate: 24.1.2014 12:00
 * endDate:
 */

class Head implements Model{

    private $registry;
    private $css = array('style.css'); //nazvy CSS suborov ktore sa maju pridat v hlavicke
    private $js = array('jquery-1.9.0.js','script.js'); //nazvy js suborov
	
	public function __construct(Registry $registry){
		$this->registry= $registry;
	}

    /**
     * Vytvori hlavicku, to je <head></head>
     */
	public function getContent($tagToReplace = ""){
        $file= file_get_contents(BASE_DIR . 'view/' . THEME . '/head.txt');
        if(strpos($file,'{css}') != false){
            $file = str_replace("{css}", $this->replaceCSS(), $file);
        }
        if (strpos($file, '{js}') != false) {
            $file = str_replace("{js}", $this->replaceJS(), $file);
        }
        return $file;
    }

    private function replaceCSS(){
        $css="";
        foreach($this->css as $name){
            $css.= '<link rel="stylesheet" href="' . BASE_LINK . 'view/' . THEME . '/css/' . $name . '" />';
        }
        return $css;
    }

    private function replaceJS(){
        $js="";
        foreach($this->js as $name){
            $js.= '<script src="' . BASE_LINK . 'view/' . THEME . '/javascript/' . $name . '"></script>';
        }
        return $js;
    }
}