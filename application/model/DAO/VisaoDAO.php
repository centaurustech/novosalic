<?php
/**
 * VisaoDAO
 * @author Equipe RUP - Politec
 * @since 2010
 * @version 1.0
 * @package application
 * @subpackage application.model.DAO
 * @link http://www.politec.com.br
 * @copyright © 2010 - Politec - Todos os direitos reservados.
 */

class VisaoDAO extends Zend_Db_Table
{
	/**
	 * Nome da tabela do banco
	 */
	protected $_name = 'AGENTES.dbo.Visao';



	/**
	 * Método para buscar as visões do agente
	 * @access public
	 * @static
	 * @param integer $idAgente
	 * @param integer $visao
	 * @param boolean $todasVisoes
	 * @return object
	 */
	public static function buscarVisao($idAgente = null, $visao = null, $todasVisoes = false)
	{
		// busca todas as visões existentes no banco
		if ($todasVisoes)
		{
			$sql = "SELECT DISTINCT idVerificacao, Descricao from AGENTES.dbo.Verificacao where IdTipo = 16 and Sistema = 21";
		}
		// busca todas as visões do usuário
		else
		{
			$sql = "SELECT
                                distinct VIS.idVisao ,
                                VER.Descricao,
                                VER.idVerificacao,
                                VIS.idAgente ,
                                VIS.Visao ,
                                VIS.Usuario ,
                                VIS.stAtivo ,
                                ar.Descricao as area
                                FROM AGENTES.dbo.Visao VIS
                                inner join AGENTES.dbo.Verificacao VER on VER.idVerificacao = VIS.Visao
                                left join AGENTES.dbo.tbTitulacaoConselheiro ttc on ttc.idagente =  vis.idagente
                                left join SAC.dbo.Area ar on ttc.cdArea = ar.Codigo ";

		$sql.= " WHERE VER.idVerificacao = VIS.Visao
				AND VER.IdTipo = 16 and Sistema = 21";

		if (!empty($idAgente)) // busca pelo id do agente
		{
			$sql.= " AND VIS.idAgente = " . $idAgente;
		}
		if (!empty($visao)) // busca pela visão
		{
			$sql.= " AND VIS.Visao = " . $visao;
		}
                }
		$sql.= " ORDER BY 2";
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$dados = $db->fetchAll($sql);
		return $dados;
	} // fecha método buscarVisao()



    /**
     * Método para cadastrar a visão de um agente
     * @access public
     * @static
     * @param array $dados
     * @return boolean
     */
    public static function cadastrarVisao($dados)
    {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        $insert = $db->insert('AGENTES.dbo.Visao', $dados); // cadastra

        return $insert ? true : false;
    } // fecha método cadastrarVisao()

	/**
	 * Método para alterar a visão de um agente
	 * @access public
	 * @static
	 * @param integer $idAgente
	 * @param array $dados
	 * @return boolean
	 */
	public static function alterarVisao($idAgente, $dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$where = "idAgente = " . $idAgente; // condição para alteração

		$update = $db->update('AGENTES.dbo.Visao', $dados, $where); // altera

		if ($update)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método alterarVisao()



	/**
	 * Método para excluir a visão de um agente
	 * @access public
	 * @static
	 * @param integer $idAgente
	 * @return boolean
	 */
	public static function excluirVisao($idAgente)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$where = "idAgente = " . $idAgente; // condição para exclusão

		$delete = $db->delete('AGENTES.dbo.Visao', $where); // exclui

		if ($delete)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método excluirVisao()

} // fecha class