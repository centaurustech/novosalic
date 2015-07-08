<?php 
/*
 * Classe: EditalComposição
 * Modulo: Editais
 * Criado por: Emanuel Melo 
 */
class tbCriterioParticipacao extends GenericModel {
    
    protected  $_banco = 'SAC';
    protected  $_name = 'tbCriterioparticipacao';

  
    public function buscarCriteriosparticipacao(){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        return $this->fetchAll($select);
    }
    
    public function buscarcriterioporidEdital($idEdital){ 
       
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->where('idEdital = ?', $idEdital);
        return $this->fetchAll($select);
    }
    
    public function buscarCriterioPorIdCategoria($idCategoria){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->where('idCategoria = ?', $idCategoria);
        return $this->fetchAll($select);
    }
    
    public function salvarcriterioparticipacao($dados){
        $insert = $this->insert($dados);
        return $insert;
    }
}
?>
