<?php
/**
 * header.php class v1.0
 * User: mitko
 * startDate: 8.2.2014 11:53
 * endDate:
 */

class header{

    private $registry;

    public function __construct(Registry $registry){
        $this->registry=$registry;
    }

    /**
     * Vytvori header to je vrchna cast stranky ktora sa vacsinou nemeni
     */
    public function header(){
        $file= file_get_contents(BASE_DIR . 'view/' . THEME . '/header.txt'); //vyberieme pattern hlavicky
        //Pripadne nahradenie tagov
        return $file;
    }
}