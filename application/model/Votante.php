<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Votante
 *
 * @author augusto
 */
class Votante extends GenericModel {

    protected $_banco = 'BDCORPORATIVO';
    protected $_name = 'tbVotante';
    protected $_schema = 'SCsac';

    public function selecionarvotantes($idreuniao) {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(array('tbv' => $this->_schema . "." . $this->_name),
                    array
                        (
                        'tbv.idAgente'
                    )
            );
            $select->joinInner(
                    array('nm' => 'nomes'),
                    "nm.idagente = tbv.idagente",
                    array('nm.descricao'),
                    'agentes.dbo'
            );
            $select->where('tbv.idreuniao = ?', $idreuniao);
            $select->order('nm.descricao asc');
            return $this->fetchAll($select);
    }

}

?>
