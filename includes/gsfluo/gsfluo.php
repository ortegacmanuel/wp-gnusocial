<?php

class RssLegilo{

    public $url;
    public $prinskribo_grando;
    public $elemento_nombro;
    protected $laste_afishis;


    function __construct($fluourl){

            
        $this->url = $fluourl;


        $this->priskribo_grando = 100;
        $this->elemento_nombro = 7;


        /** 
        * Kiam oni lastfoje afiŝis pepon? La unuan fojon oni uzas 
        * gsfluon, ĝi donas la valoron '' al la variablo $laste_afishis
        **/        
        if (file_exists("dato.txt")) {
    
            $dato_dosiero = fopen('dato.txt','r');

            while ($linio = fgets($dato_dosiero)) {

                $this->laste_afishis = date_create($linio);
            }
            fclose($dato_dosiero);
        }else{

            $this->laste_afishis = date_create('01-01-1970');
        }

    }


    function ghisdatigi_daton() {

        $dato_dosiero = fopen("dato.txt", "w");
        fwrite($dato_dosiero, $this->laste_afishis->format('Y-m-d H:i:s'));
        fclose($dato_dosiero);
    }


    /**
     * Legas la rssfluon indikitan de la uzanto, kontrolas ĉu estas novaj elementoj
     * kaj revenigas tabelon el novaj elementoj kreitaj laŭ la klaso Elemento
     *
    **/
    function legi() {

        $fluo = simplexml_load_file($this->url);

        $n = 0;

        $elementoj = array();

        // Iteracia kontrolo de ĉiuj ricevitaj elementoj
        foreach($fluo->channel->item as $ero){

            if($ero->title!=NULL && $ero->title!='' 
                    && $ero->description!=NULL && $ero->description!='' && $n< $this->elemento_nombro){

                $elemento = new Elemento($ero->title, $ero->description, $ero->link, '', date_create($ero->pubDate));

                $elemento->aranghi_kategoriojn($ero->category);

                array_push($elementoj, $elemento);
            }
	        $n++; 
        }
        
        $elementoj = array_reverse($elementoj);
        $novaj_elementoj = array();               

        
        foreach($elementoj as $elemento){
        
            // la unuan fojon oni rulas gsfluon laste_afishis egalas al 01-01-1970
            
            //Ĉu la elemento estas nova?
            if ($elemento->novas($this->laste_afishis)) {
                
                // Aldonado de la elemento al la revenigota tabelo
                array_push($novaj_elementoj, $elemento);

                // Ĝisdatigo de la dato kiu estos konservota kiel dato por lasta afiŝo
                $this->laste_afishis = $elemento->publikig_dato;
            }
        }
        
        return $novaj_elementoj;
    }

}


class Elemento {

    public $titolo; 
    public $priskribo;
    public $ligilo;
    public $kategorioj;
    public $publikig_dato;

    function __construct($titolo, $priskribo, $ligilo, $kategorioj, $dato) {
        
        $this->titolo = $titolo;
        $this->priskribo = $priskribo;
        $this->ligilo = $ligilo;
        $this->kategorioj = $kategorioj;
        $this->publikig_dato = $dato;
    }

    /**
     * Kontrolas ĉu la elemento estas nova kompare al provizita dato
     * 
     *@param $dato kiu devas aparteni al la datumtipo date
    **/
    function novas($dato){

        if ($this->publikig_dato > $dato) {
            return True;
        }else{
            return False;
        }
    }

    /**
     * Aranĝas kategoriojn laŭ la formo #kategorio1 #kategorio2 #kategorio3
     * 
     *@param $dato kiu devas aparteni al la datumtipo date
    **/
    function aranghi_kategoriojn($kategorioj) {

        foreach ($kategorioj as $kategorio) {
            $this->kategorioj .= '#' . str_replace(' ', '', $kategorio) . ' ';
        }
    }
}


class GsKonektilo {

    public $api_url;
    public $salutnomo;
    public $pasvorto;

    function __construct($api_url, $salutnomo, $pasvorto) {
        
        $this->api_url = $api_url;
        $this->salutnomo = $salutnomo;
        $this->pasvorto = $pasvorto;

    }

    function afishi($titolo, $url, $priskribo, $kategorioj ) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERPWD, $this->salutnomo.":".$this->pasvorto);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array("status" => $titolo . " " . $url  . " " . $priskribo . " ". $kategorioj));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        $output = curl_exec($ch);
        curl_close($ch);
        
        return $output;
    }
}
