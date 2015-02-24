<?php
/**
 * footer class v1.0
 * User: mitko
 * startDate: 26.1.2014 19:19
 * endDate:
 */

class footer{

    private $registry;

    public function __construct(Registry $Registry){
        $this->registry=$Registry;
    }

    public function footer(){
        $file= file_get_contents(BASE_DIR . 'view/' . THEME . '/footer.txt'); //vyberieme pattern footeru
        //Pripadne nahradenie tagov
        return $file;
    }
}