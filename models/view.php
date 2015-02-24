<?php
/**
 * view model v1.0
 * User: mitko
 * startDate: 26.1.2014 15:08
 * endDate:
 * trieda view zostavuje zakladnu kostru stranky, ktora sa nachadza v textovych suborov, kazdy controller ma vlastnu kostru
 */

class view{

    private $registry;
    private $replaceFromModels= array("head","header","footer");

    public function __construct(Registry $Registry){
        $this->registry=$Registry;
    }

    public function buildNewFile($controller){
        $file=file_get_contents(BASE_DIR . "view/" . THEME . "/" . substr(get_class($controller),0,strpos(get_class($controller),"Controller")) . ".view.txt");

        foreach($this->replaceFromModels as $value)//vyberieme tagy ktore sa maju hladat vo view suboroch
        if(strpos($file,"{".$value."}")){
            require_once(BASE_DIR . 'models/' . $value . '.php');
            $model= new $value($this->registry);
            $file= str_replace('{' . $value . '}',$model->$value(),$file);
        }
        $file= str_replace("{title}",$controller->getTitle(),$file);
        return $file;//zvysok sa nahradi v controlleri
    }
}
