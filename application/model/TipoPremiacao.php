<?php 
/*
 * Classe: Tipo Premiacao
 * Modulo: Editais
 * Criado por: Emanuel Melo 
 */
class TipoPremiacao extends GenericModel {
    
    protected  $_banco = 'SAC';
    protected  $_name = 'tbTipoPremiacao';

    /*
     * Metodo: buscarTipoPremiacao
     * Entrada: void
     * Saida: Array de Tipos de Premiações
    */
    public function buscarTipoPremiacao(){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        return $this->fetchAll($select);
    }

}
?>
