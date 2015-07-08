<?php 
/*
 * Classe: Item de Fluxo
 * Modulo: Editais
 * Criado por: Emanuel Melo 
 */
class ItemFluxo extends GenericModel {
    
    protected  $_banco = 'SAC';
    protected  $_name = 'tbTipoItemFluxo';

    /*
     * Metodo: buscarItemFluxo
     * Entrada: void
     * Saida: Array de Itens de Fluxo
    */
    public function buscarItensFluxo(){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        return $this->fetchAll($select);
    }
}
?>
