<?php

class AtomLegilo{

    public $url;
    public $prinskribo_grando;
    public $elemento_nombro;
    protected $laste_komentis;


    function __construct($fluourl){

            
        $this->url = $fluourl;


        $this->priskribo_grando = 100;
        $this->elemento_nombro = 7;


        /** 
        * Kiam oni lastfoje afiŝis pepon? La unuan fojon oni uzas 
        * gsfluon, ĝi donas la valoron '' al la variablo $laste_afishis
        **/        
        if (!(get_post_meta( get_the_ID(), 'wpgs_laste_komentis', true ) == '')) {
            
            $this->laste_komentis = date_create(get_post_meta( get_the_ID(), 'wpgs_laste_komentis', true ));
            
        }else{

            $this->laste_komentis = date_create('01-01-1970');
        }

    }


    function ghisdatigi_daton($afish_id) {

        update_post_meta( $afish_id, 'wpgs_laste_komentis', $this->laste_komentis->format('Y-m-d H:i:s'));
    }


    /**
     * Legas la rssfluon indikitan de la uzanto, kontrolas ĉu estas novaj elementoj
     * kaj revenigas tabelon el novaj elementoj kreitaj laŭ la klaso Elemento
     *
    **/
    function legi($afish_id) {

        $afish_id = $afish_id;
        
        $fluo = simplexml_load_file($this->url);

        $n = 0;

        $elementoj = array();

        // Iteracia kontrolo de ĉiuj ricevitaj elementoj
        foreach($fluo->entry as $ero){

            if($ero->author->name!=NULL && $ero->content!='' && $n< $this->elemento_nombro){

                $elemento = new Elemento($afish_id, $ero->author->name, $ero->author->uri, $ero->author->link[1]->attributes()->href, $ero->content, date_create($ero->published));

                //$elemento->aranghi_kategoriojn($ero->category);

                array_push($elementoj, $elemento);
            }
	        $n++; 
        }
        
        $elementoj = array_reverse($elementoj);
        $novaj_elementoj = array();     

        
        foreach($elementoj as $elemento){
        
            // la unuan fojon oni rulas gsfluon laste_afishis egalas al 01-01-1970
            
            //Ĉu la elemento estas nova?
            if ($elemento->novas($this->laste_komentis)) {
                
                // Aldonado de la elemento al la revenigota tabelo
                array_push($novaj_elementoj, $elemento);

                // Ĝisdatigo de la dato kiu estos konservota kiel dato por lasta afiŝo
                $this->laste_komentis = $elemento->publikig_dato;
            }
        }
        
        return $novaj_elementoj;
    }

}


class Elemento {

    public $afisho_id;
    public $auhtoro;
    public $auhtoro_url;
    public $enhavo;
    public $tipo;
    public $patro;
    public $publikig_dato;
    public $avataro;

    function __construct($afisho_id, $auhtoro, $auhtoro_url, $avataro, $enhavo, $dato) {
        
        $this->afisho_id = $afisho_id;
        $this->auhtoro = $auhtoro;
        $this->auhtoro_url = $auhtoro_url;
        $this->enhavo = $enhavo;
        $this->publikig_dato = $dato;
        $this->avataro = $avataro;
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
