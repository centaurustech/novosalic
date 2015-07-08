<?php

class tbPlanilhaOrcamentaria extends GenericModel {

    protected $_banco   = "SAC";
    protected $_schema  = "dbo";
    protected $_name    = 'tbPlanilhaOrcamentariaEdital';

    
    public function salvarPlanilhaOrcamentaria($dadosPlanilhaOrcamentaria){
           $insert = $this->insert($dadosPlanilhaOrcamentaria);
           return $insert; 
    }
  
}
?>
