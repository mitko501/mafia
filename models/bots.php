<?php
/**
 * Created by JetBrains PhpStorm.
 * User: mitko
 * Date: 11.10.2013
 * Time: 15:09
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


    public function showBots($limit=400,$includerName='bots'){
        $file='';

        $includer= file_get_contents(BASE_DIR . 'view/' . THEME . '/includers/'.$includerName.'.txt');

        if($includerName=='bot'){
            $this->registry->getObject('db')->ExecuteQuery('
            SELECT region FROM zoznam_psc
            GROUP BY region
            ');
            while($region=$this->registry->getObject('db')->GetRows()){
                $regions[]=$region['region'];
            }

            $this->registry->getObject('db')->ExecuteQuery('
            SELECT region FROM bots
            WHERE id='.$this->registry->getObject('url')->GetUrlBit('1').' LIMIT 1');
            $row=$this->registry->getObject('db')->GetRows();
            $selectedRegions=explode('/',$row['region']);
            $reg='';
            foreach($regions as $value){
                if(in_array($value,$selectedRegions)){
                    $selected='selected';
                }else{
                    $selected='';
                }
                $reg.='<option value="'.$value.'" '.$selected.' >'.$value.'</option>';
            }
            $includer=str_replace('{regions}',$reg,$includer);
        }elseif($includerName=='bots'){
            $file.='<h1>Boti</h1>
                    <a href="'.BASE_LINK.'bots/create">Vytvorit nového bota</a>
                    <table id="lastFive">
                        <tr class="head">
                            <td class="id">ID</td>
                            <td>Meno</td>
                            <td>Priezvisko</td>
                            <td>Telefón</td>
                            <td>Email</td>
                            <td>Zaujma sa</td>
                            <td>limit</td>
                            <td>Kraj</td>
                            <td>Url(uprav)/Zmaž</td>
                        </tr>';
        }
        if($this->registry->getObject('url')->GetUrlBit('1')!=0){
            $where= 'WHERE id='.$this->registry->getObject('url')->GetUrlBit('1').' ';
        }else{
            $where='';
        }
        $this->registry->getObject('db')->ExecuteQuery('
        SELECT * FROM bots
        '.$where.'
        LIMIT '.$limit
        );
        while($row=$this->registry->getObject('db')->GetRows()){
            $file2=$includer;
            foreach($row as $index=>$value){
                $file2=str_replace('{'.$index.'}',$value,$file2);
            }
            $file2= str_replace('{botInfo}',BASE_LINK.'bots/'.$row['id'],$file2);
            $file.=$file2;
        }
        $file.=($includerName=='bots') ? '</table>' : '';
        return $file;
    }
}

?>