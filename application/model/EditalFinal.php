<?php 
/*
 * Classe: EditalComposição
 * Modulo: Editais
 * Criado por: Emanuel Melo 
 */
class EditalFinal extends GenericModel {
    
    protected  $_banco = 'SAC';
    protected  $_name = 'tbTextoEdital';

  
    public function buscarEditalFinal($idEdital){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->where('idEdital = ?', $idEdital);
        return $this->fetchAll($select);
    }
    
    public function salvarTextoEdital($dados){
        $insert = $this->insert($dados);
        return $insert;
    }
}
?>
