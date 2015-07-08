<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * 
 */

class tbTextoEdital extends GenericModel {
    
    protected  $_banco = 'SAC';
    protected  $_name = 'tbTextoEdital';
    protected  $_schema  = 'dbo';
    
    public function buscarTextoEdital($idEdital){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->where('idEdital = ?', $idEdital);
        $select->order('nrTexto');
        return $this->fetchAll($select);
    }
    
    public function buscarUltimaOrdem($idEdital){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->where('idEdital = ?', $idEdital);
        $select->limit(1);
        $select->order('nrTexto DESC');
        return $this->fetchAll($select)->toArray();
    }
    
}

?>
