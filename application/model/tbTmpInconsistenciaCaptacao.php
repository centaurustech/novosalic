<?php

/**
 * DAO tbTmpInconsistenciaCaptacao 
 * @author emanuel.sampaio - Politec
 * @since 17/02/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */
class tbTmpInconsistenciaCaptacao extends GenericModel
{
    /* dados da tabela */

    protected $_banco = "SAC";
    protected $_schema = "dbo";
    protected $_name = "tbTmpInconsistenciaCaptacao";

    /**
     * Método para cadastrar
     * @access public
     * @param array $dados
     * @return integer (retorna o último id cadastrado)
     */
    public function cadastrarDados($dados)
    {
        return $this->insert($dados);
    }

// fecha método cadastrarDados()

    /**
     * Método para excluir
     * @access public
     * @param integer $where
     * @return integer (quantidade de registros excluídos)
     */
    public function excluirDados($where)
    {
        $where = "idTmpCaptacao = " . $where;
        return $this->delete($where);
    }

    /**
     * 
     */
    public function buscarInconsistenciasPorCaptacao($idCaptacao)
    {
        $sqlClone = $this->select()->setIntegrityCheck(false)->from($this->_name, array(new Zend_Db_Expr('MAX(idTipoInconsistencia)')))
          ->where('idTmpCaptacao = ?', $idCaptacao)
          ->where('idTipoInconsistencia in (?)', array(TipoInconsistenciaBancariaModel::PROPONENTE_INCENTIVADOR_IGUAIS, TipoInconsistenciaBancariaModel::SEM_VISAO_INCENTIVADOR));

        $tipoInconsistenciaTable = new tbTipoInconsistencia();
        $selectTipoInconsistencia = $tipoInconsistenciaTable
          ->select()
          ->setIntegrityCheck(false)
          ->from($tipoInconsistenciaTable->info(Zend_Db_Table::NAME), array('idTipoInconsistencia'))
          ->where('idTipoInconsistencia not in (?)', array(TipoInconsistenciaBancariaModel::PROPONENTE_INCENTIVADOR_IGUAIS, TipoInconsistenciaBancariaModel::SEM_VISAO_INCENTIVADOR));

        $sql = $this->select()->setIntegrityCheck(false)->from(array('inc' => $this->_name))
          ->join(array('i' => 'tbTipoInconsistencia'), "i.idTipoInconsistencia = inc.idTipoInconsistencia")
          ->where('inc.idTmpCaptacao = ?', $idCaptacao)
          ->where('(inc.idTipoInconsistencia in (?)', $selectTipoInconsistencia)
          ->orWhere('inc.idTipoInconsistencia = ?)', $sqlClone);

        return $this->fetchAll($sql);
    }

}
