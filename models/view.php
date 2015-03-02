<?php
/**
 * view model v1.0
 * User: mitko
 * startDate: 26.1.2014 15:08
 * endDate:
 * trieda view zostavuje zakladnu kostru stranky, ktora sa nachadza v textovych suborov, kazdy controller ma vlastnu kostru
 */

class View implements Model{

    private $registry;

    public function __construct(Registry $Registry){
        $this->registry=$Registry;
    }

    public function getContent($tagToReplace = ""){
        if($tagToReplace != "") {
            $file = $this->buildNewFile($tagToReplace);

            if(preg_match_all('/^\{[a-zA-Z0-9\-]+\}', $file, $matches)){
                var_dump($matches);

                foreach($matches as $tag){
                    $tag = substr($tag, 1,-1);

                    $result = "";
                    $result = $this->tryToReplaceFromModels($tag);
                    if($result != ""){
                        $file = str_replace("{" . $tag . "}", $result, $file);
                    }
                }
            }
            return $file;
        }
        return "";
    }

    /**
     * @param $controller which call this method
     * @return mixed|string file with replaced tags
     *
     * Load file which belong to controler which called this method
     * Replace tags
     * And return ready file
     *
     * chcelo by to nejako zautomatizovat, aby sa nemuselo ukladat, ktore tagy sa mozu nahradit a ktore nie
     * nejako ich vyhladavat a skusit vsetky moznosti ako ho nahradit
     */
    private function buildNewFile($filemame){
        $file = file_get_contents(BASE_DIR . "view/" . THEME . "/" . $filename . ".view.txt");
        return $file;
    }

    private function tryToReplaceFromModels($tag){
        if(strpos($tag,"-")){
            $modelFileName = strtolower(substr($tag, 0, strpos($tag, "-")));
            $modelClassName = ucfirst($modelFileName);
            $tagToReplace = strtolower(substr($tag, strpos($tag, "-")));
        }else{
            $modelFileName = strtolower($tag);
            $modelClassName = ucfirst($modelFileName);
            $tagToReplace = "";
        }

        if(file_exists(BASE_DIR . "/models/" . $modelFileName)){
            require_once(BASE_DIR . "/models/" . $modelFileName);
            $model = new $modelClassName();
            return $model->getContent($tagToReplace);
        }
        return "";

    }
}

