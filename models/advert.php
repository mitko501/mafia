<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mitko
 * Date: 9.10.2013
 * Time: 10:41
 */

class advert{

    private $functions=array('showAdverts');
    private $registry;

    public function __construct(Registry $registry){
        $this->registry=$registry;
    }

    public function returnContent($func){
        if(in_array($func,$this->functions)){
            return $this->$func();
        }else{
            return 'Tento obsah nieje dostupný';
        }
    }


    public function showAdverts($limit=40  ,$includerName='lastFive'){
        $file='';
        $this->registry->getObject('db')->ExecuteQuery('
        SELECT * FROM advert_links
        LEFT JOIN adverts
        ON advert_links.id=adverts.link_id
        WHERE date>DATE_ADD(NOW(), INTERVAL -' . VALIDITY_DAYS . ' DAY)
        ORDER BY lastUpdate DESC
        LIMIT '.$limit.'
        ');
        $includer= file_get_contents(BASE_DIR.'view/' . THEME . '/includers/'.$includerName.'.txt');
        while($row=$this->registry->getObject('db')->GetRows()){
            $file2=$includer;
            foreach($row as $index=>$value){
                if($index=='text' && $includerName=='lastFive'){
                    $file2=str_replace('{titleText}',$value,$file2);
                    $value=(strlen($value)>20) ? substr($value,0,30).'...' : $value;
                }elseif($index=='head' && $includerName=='lastFive'){
                    $file2=str_replace('{titleHead}',$value,$file2);
                    $value=(strlen($value)>=20) ? substr($value,0,25).'...' : $value;
                }elseif($index=='lastUpdate'){
                    $date = new DateTime();
                    $date->setTimestamp($value);
                    $value= $date->format("Y-m-d H:i:s");
                }elseif($index=='price'){
                    $value=(is_numeric($value)) ? $value.'€' : $value;
                }elseif($index=='link_id'){
                    $editLink=BASE_LINK.'edit/'.$value;
                }

                $file2=str_replace('{'.$index.'}',$value,$file2);
            }
            $file2=str_replace('{editLink}',$editLink,$file2);
            $file.=$file2;
        }
        return $file;
    }
}

