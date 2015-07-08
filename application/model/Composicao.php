<?php 
/*
 * Classe: Composição
 * Modulo: Editais
 * Criado por: Emanuel Melo 
 */
class Composicao extends GenericModel {
    
    protected  $_banco = 'SAC';
    protected  $_name = 'tbComposicao';

    /*
     * Metodo: buscarComposicao
     * Entrada: void
     * Saida: Array de Composições
    */
    public function buscarComposicao($where = array(), $order = array(), $dbg = null){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        
        foreach ($where as $coluna => $valor) :
            $select->where($coluna, $valor);
        endforeach;

        $select->order($order);

        if($dbg){
            xd($select->assemble());
        }

        return $this->fetchAll($select);
    }

}
