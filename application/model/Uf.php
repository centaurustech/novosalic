<?php 

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Uf
 *
 * @author 01610881125
 */
class Uf extends GenericModel {

    protected $_banco = 'AGENTES';
    protected $_name = 'UF';
    protected $_schema = 'dbo';

    public function buscarRegiao() {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array($this->_name), array(
            'Regiao'
                )
        );
        $select->order('Regiao');
        $select->group('Regiao');
        return $this->fetchAll($select);
    }

    public function buscaRegiaoPorPRONAC($PRONAC) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                array('uf'=>$this->_name), array(
            'Regiao'
                )
        );
        $select->joinInner(array('p' => 'Projetos'), 'uf.Sigla = p.UfProjeto', array(), 'SAC.dbo');

        $select->where('(p.AnoProjeto+p.Sequencial) = ?', $PRONAC);

        return $this->fetchAll($select);
    }

}

?>
