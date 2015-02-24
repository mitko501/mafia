<?php
/**
 * Url class v2.0
 * User: mitko
 * startDate: 24.1.2014 12:00
 * endDate:
 * Trieda url riesi parametre URL, kontroluje hlavne validitu, a takisto opravnenia pre jednotlive controlleri
 */
class url{
	private $urlBits=array();
	private $registry;
    private $secondBit=array("login" => array("/^logout$/")); //povolene url pre druhy parameter ako pattern pre preg_match napr.:"main" => array("/^url$/","/^[0-9]+$/" povoluje pre controller main http://..../main/url alebo /main/{vsetky cisla}
	
	public function __construct(Registry $registry){
		$this->registry= $registry;
        $this->registry->storeControllers(); //potiahnut zoznam controllerov z DB
        $firstPage= ($this->registry->getPrivileges(MAIN_CONTROLLER) <= $this->registry->getObject('usr')->getUserInfo('privileges')) ? MAIN_CONTROLLER : LOGIN_CONTROLLER;
		if(isset($_GET['page'])){
			$urldata=addslashes($_GET['page']); //pridat spatne lomitka
			if(!empty($urldata)){
				$data=explode('/',$urldata);
				if($this->registry->isController($data[0])){ //skontrolovat validitu
                    if($this->registry->getObject('usr')->getUserInfo('privileges') >= $this->registry->getPrivileges($data[0])){ //skontrolovat orpavnenia
					    $this->urlBits[0]= $data[0];
                        //druhy parameter URL
                        if(isset($data[1])){ //v priapade existencie druheho parametra zistujeme jeho validitu
                            $validity=false;//predpokladame, ze dalsie parametre su nevalidne
                                foreach($this->secondBit[$data[0]] as $pattern){
                                    if(preg_match($pattern,$data[1])){ //skontrolujeme ci podla niektoreho z patternov je parameter validny
                                        $validity=true;
                                    }
                                }
                            if($validity==true){
                                $this->urlBits[1]=$data[1];
                            }
                        }
                    }else{
                        $this->registry->alert("Nemáte oprávnenie na zobrazenie tejto stránky");
                        $this->registry->changeURL($firstPage);
                        $this->urlBits[0]= $firstPage;
                    }
				}else{
                    $this->registry->changeURL("error");
                    $this->urlBits[0]="error";
				}
			}else{
                $this->registry->changeURL($firstPage);
				$this->urlBits[0]=$firstPage;
			}
		}else{
            $this->registry->changeURL($firstPage);
			$this->urlBits[0]=$firstPage;
		}
    }
	
	public function getUrlBit($index){
	    if(isset($this->urlBits[$index])){
	        return $this->urlBits[$index];
        }else{
            return false;
        }
	}
}
?>