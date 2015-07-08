<?php
/**
 * DAO Internet
 * @author emanuel.sampaio - Politec
 * @since 18/02/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class Internet extends GenericModel
{
	protected $_banco  = "AGENTES";
	protected $_schema = "dbo";
	protected $_name   = "Internet";



	/**
	 * Método para envio de e-mail
	 * @access public
	 * @param string $email
	 * @param string $assunto
	 * @param string $texto
	 * @param string $perfil
	 * @param string $formato
	 * @return void
	 */
	public function enviarEmail($email, $assunto, $texto, $perfil = "PerfilGrupoPRONAC", $formato = "HTML")
	{
		$sql = "EXEC msdb.dbo.sp_send_dbmail
					@profile_name          = '" . $perfil . "'
					,@recipients           = '" . $email . "'
					,@body                 = '" . $texto . "'
					,@body_format          = '" . $formato . "'
					,@subject              = '" . $assunto . "'
					,@exclude_query_output = 1;";

		return $this->getAdapter()->query($sql);
	} // fecha método enviarEmail()



	/**
	 * Método para buscar o(s) e-mail(s) do agente
	 * @access public
	 * @param string $cpfcnpj
	 * @param integer $idAgente
	 * @param integer $statusEmail (1 = ATIVADO, 0 = DESATIVADO)
	 * @param integer $statusDivulgacao (1 = ATIVADO, 0 = DESATIVADO)
	 * @param boolean $buscarTodos (informa se busca todos ou somente um)
	 * @return array || object
	 */
	public function buscarEmailAgente($cpfcnpj = null, $idAgente = null, $statusEmail = null, $statusDivulgacao = null, $buscarTodos = true)
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from(
			array("e" => $this->_name)
			,array("e.idInternet AS idEmail"
				,"e.TipoInternet AS TipoEmail"
				,"e.Descricao    AS Email"
				,"e.Status"
				,"e.Divulgar")
		);
		$select->joinInner(
			array("a" => "Agentes")
			,"a.idAgente = e.idAgente"
			,array()
		);

		// busca pelo cnpj ou cpf
		if (!empty($cpfcnpj))
		{
			$select->where("a.CNPJCPF = ?", $cpfcnpj);
		}

		// busca pelo id do agente
		if (!empty($idAgente))
		{
			$select->where("a.idAgente = ?", $idAgente);
		}

		// busca pelo email ativado/desativado
		if (!empty($statusEmail))
		{
			$select->where("e.Status = ?", $statusEmail);
		}

		// busca pelo email de divulgacao
		if (!empty($statusDivulgacao))
		{
			$select->where("e.Divulgar = ?", $statusDivulgacao);
		}

		return $buscarTodos ? $this->fetchAll($select) : $this->fetchRow($select);
	} // fecha método buscarEmailAgente()



	/**
	 * Método para cadastrar
	 * @access public
	 * @param array $dados
	 * @return integer (retorna o último id cadastrado)
	 */
	public function cadastrarEmailAgente($dados)
	{
		return $this->insert($dados);
	} // fecha método cadastrarEmailAgente()



	/**
	 * Método para excluir
	 * @access public
	 * @param integer $idAgente (excluir todos os e-mails de um agente)
	 * @param integer $idInternet (excluir um determinado e-mail)
	 * @return integer (quantidade de registros excluídos)
	 */
	public function excluirEmailAgente($idAgente = null, $idInternet = null)
	{
		// exclui todos os e-mails de um agente
		if (!empty($idAgente))
		{
			$where = "idAgente = " . $idAgente;
		}

		// exclui um determinado e-mail
		else if (!empty($idInternet))
		{
			$where = "idInternet = " . $idInternet;
		}

		return $this->delete($where);
	} // fecha método excluirEmailAgente()

} // fecha class