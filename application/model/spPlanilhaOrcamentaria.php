<?php

/**
 * Description of spPlanilhaOrcamentaria
 * Criado em 01/10/2013 - Jefferson Alessandro
 */
class spPlanilhaOrcamentaria extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'spPlanilhaOrcamentaria';

    public function exec($idPronac, $tipoPlanilha){
        
        // tipoPlanilha = 0 : Planilha Orçamentária da Proposta
        // tipoPlanilha = 1 : Planilha Orçamentária do Proponente
        // tipoPlanilha = 2 : Planilha Orçamentária do Parecerista
        // tipoPlanilha = 3 : Planilha Orçamentária Aprovada Ativa
        // tipoPlanilha = 4 : Cortes Orçamentários Aprovados
        // tipoPlanilha = 5 : Remanejamento menor que 20%
        // tipoPlanilha = 6 : Readequação
        
        $sql = "exec ".$this->_banco.".dbo.".$this->_name." $idPronac, $tipoPlanilha";
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchAll($sql);
    }
}
?>
