<?php 
/*
 * Classe: tbTipoParticipacao
 * Modulo: Editais
 * Criado por: Emanuel Melo 
 */
class tbTipoParticipacao extends GenericModel {
    
    protected  $_banco = 'SAC';
    protected  $_name = 'tbTipoParticipacao';

    
    public function buscarTipoParticipacao(){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        return $this->fetchAll($select);
    }
    
}
?>