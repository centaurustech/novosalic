<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tbModeloTermoDecisao
 *
 * @author Tiago
 */
class tbModeloTermoDecisao extends GenericModel {

    protected $_banco = "SAC";
    protected $_schema = "dbo";
    protected $_name = "tbModeloTermoDecisao";

    public function buscarTermoDecisao($where = null) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('ttd' => $this->_name), 
                array('ttd.idModeloTermoDecisao',
                      'ttd.idOrgao',
                      'ttd.idVerificacao',
                      'ttd.stModeloTermoDecisao',
                      'CAST(ttd.meModeloTermoDecisao AS TEXT) AS meModeloTermoDecisao',
           )
        );
        $select->joinInner(
                array('o' => 'Orgaos'), 'ttd.idOrgao = o.Codigo', 
                array('o.Codigo', 'o.Sigla')
        );
        if ( !empty( $where ) ){
        foreach ($where as $coluna => $valor) {
                    $select->where($coluna, $valor);
            }
        }
        //xd($select->query());
        return $this->fetchAll($select);
    }
    

}

?>
