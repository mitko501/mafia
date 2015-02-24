<?php
/**
 * Head model v1.0
 * User: mitko
 * startDate: 24.1.2014 12:00
 * endDate:
 */

class head{

	private $registry;
    private $css=array('free-login-form-style.css'); //nazvy CSS suborov ktore sa maju pridat v hlavicke
    private $js=array('jquery-1.9.0.js','script.js'); //nazvy js suborov
	
	public function __construct(Registry $registry){
		$this->registry= $registry;
	}
	
	public function head(){
		$file= file_get_contents(BASE_DIR . 'view/' . THEME . '/head.txt'); //vyberieme pattern hlavicky
		if(strpos($file,'{css}')>=0){ //zacneme s CSS
            $css="";
            foreach($this->css as $name){
                $css.= '<link rel="stylesheet" href="' . BASE_LINK . 'view/' . THEME . '/css/' . $name . '" />';
            }
            $file= str_replace("{css}",$css,$file); //vlozime css do kodu
        }
        if(strpos($file,'{js}')>=0){ //Dalej nahradime javascriptove subory
            $js="";
            foreach($this->js as $name){
                $js.= '<script src="' . BASE_LINK . 'view/' . THEME . '/javascript/' . $name . '"></script>';
            }
            $file= str_replace("{js}",$js,$file); //vlozime css do kodu
        }
        return $file; //vratime, title sa nahradi az v triede view pretoze ma pristup k metodam jednotlivych controllerov
		
	}
}
?>