<?php 
/*
 * Classe: EditalComposição
 * Modulo: Editais
 * Criado por: Emanuel Melo 
 */
class tbEditalComposicao extends GenericModel {
    
    protected  $_banco = 'SAC';
    protected  $_name = 'tbEditalComposicao';

    /*
     * Metodo: buscarComposicaoEdital
     * Entrada: void
     * Saida: Array de Composições
    */
    public function buscarComposicaoEdital(){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        return $this->fetchAll($select);
    }
    
    
     public function salvarEditalComposicao($dados){
        $insert = $this->insert($dados);
        return $insert; 
    }
}
?>
