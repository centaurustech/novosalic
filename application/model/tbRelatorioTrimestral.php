<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tbRelatorioTrimestral
 *
 * @author 01129075125
 */
class tbRelatorioTrimestral extends GenericModel{

    protected $_banco = 'SAC';
    protected $_schema = 'dbo';
    protected $_name = 'tbRelatorioTrimestral';


    /**
     * Método para consultar se existe algum registro para o idRelatorio
     * @access public
     * @param array $dados
     * @param integer $where
     * @return integer (quantidade de registros alterados)
     */
    public function buscarDadosRelatorio($idRelatorio) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            array('*')
        );
        if ($idRelatorio) {
            $select->where('a.idRelatorio = ?', $idRelatorio);
        }
//        xd($select->assemble());
        return $this->fetchAll($select);
    }

    public function buscarRelatorioTrimestral($idRelatorioTrimestral) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_name),
            array('idRelatorioTrimestral',
                'idRelatorio',
                'CAST(dsParecer AS TEXT) AS dsParecer',
                'CAST(dsObjetivosMetas AS TEXT) AS dsObjetivosMetas',
                'dtCadastro',
                'stRelatorioTrimestral',
                'nrRelatorioTrimestral')
        );
        $select->where('a.idRelatorioTrimestral = ?', $idRelatorioTrimestral);
//        xd($select->assemble());
        return $this->fetchAll($select);
    }


        /**
	 * Método para buscar o relatório trimestral - Habilitar Menu
	 * @access public
	 * @param array $dados
	 * @return array dos dados cadastrados
	 */
        public function buscarTodosRelatoriosTrimestrais($idpronac)
        {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name)
            );
            $select->joinInner(
                    array('b' => 'tbRelatorio'),
                    'b.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->where('b.idPRONAC = ?', $idpronac);
            $select->where('b.tpRelatorio = ?', 'T');
            $select->where('a.stRelatorioTrimestral != ?', 1);
            return $this->fetchAll($select);
        }


        /**
	 * Método para buscar o relatório trimestral
	 * @access public
	 * @param array $dados
	 * @return array dos dados cadastrados
	 */
        public function buscarRelatorioPronac($idpronac, $status = 1)
        {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name)
            );
            $select->joinInner(
                    array('b' => 'tbRelatorio'),
                    'b.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->where('b.idPRONAC = ?', $idpronac);
            $select->where('a.stRelatorioTrimestral = ?', $status);
            $select->where('b.tpRelatorio = ?', 'T');
//            xd($select->assemble());
            return $this->fetchAll($select);
        }


        /**
	 * Método para buscar o relatório trimestral
	 * @access public
	 * @param array $dados
	 * @return array dos dados cadastrados
	 */
        public function buscarUltimoNrRel($idpronac)
        {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name)
            );
            $select->joinInner(
                    array('b' => 'tbRelatorio'),
                    'b.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->where('b.idPRONAC = ?', $idpronac);
            $select->order('b.idRelatorio desc');
//            xd($select->assemble());
            return $this->fetchAll($select);
        }

        /**
	 * Método para buscar o relatório trimestral
	 * @access public
	 * @param array $dados
	 * @return array dos dados cadastrados
	 */
        public function buscarstRel($idRelatorio)
        {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name)
            );
            $select->joinInner(
                    array('b' => 'tbRelatorio'),
                    'b.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->where('b.idRelatorio = ?', $idRelatorio);
            $select->where('a.stRelatorioTrimestral = ?', 1);
//            xd($select->assemble());
            return $this->fetchAll($select);
        }

        public function buscarUsandoCAST($idRelatorioTrimestral)
        {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name),
                    array('a.idRelatorioTrimestral', 'a.idRelatorio', 'CAST(a.dsParecer AS TEXT) AS dsParecer', 'CAST(a.dsObjetivosMetas AS TEXT) AS dsObjetivosMetas', 'a.dtCadastro', 'a.stRelatorioTrimestral', 'a.nrRelatorioTrimestral')
            );
            $select->where('a.idRelatorioTrimestral = ?', $idRelatorioTrimestral);
//            xd($select->assemble());
            return $this->fetchAll($select);
        }

        /**
	 * Método para buscar o relatório trimestral - Habilitar Menu
	 * @access public
	 * @param array $dados
	 * @return array dos dados cadastrados
	 */
        public function buscarRelatorioMenu($idpronac)
        {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('a' => $this->_schema . '.' . $this->_name)
            );
            $select->joinInner(
                    array('b' => 'tbRelatorio'),
                    'b.idRelatorio = a.idRelatorio',
                    array('*'),
                    'SAC.dbo'
                    );
            $select->where('b.idPRONAC = ?', $idpronac);
            $select->where('a.stRelatorioTrimestral = ?', 1);
            $select->where('b.tpRelatorio = ?', 'T');
//            xd($select->assemble());
            return $this->fetchAll($select);
        }

}
?>
