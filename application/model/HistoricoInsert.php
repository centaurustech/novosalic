<?php
/**
 * DAO HistoricoInsert
 * @author emanuel.sampaio - Politec
 * @since 18/02/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class HistoricoInsert extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "sysobjects";
	public $dados = 'HISTORICO_INSERT';


	/**
	 * Método para verificar se a trigger HISTORICO_INSERT está habilitada
	 * @access public
	 * @param void
	 * @return integer (0 = Habilitado e 1 = desabilitado)
	 */
	public function statusHISTORICO_INSERT()
	{
		$sql = "SELECT ObjectProperty(Object_id(name), 'ExecIsTriggerDisabled') AS Habilitado 
				FROM {$this->_banco}.{$this->_schema}.{$this->_name}
				WHERE name = 'HISTORICO_INSERT'";

		// seta para o banco SAC que é onde encontra-se a trigger
		$DIRBANCO = Zend_Registry::get('DIR_CONFIG');
		$Conexao  = 'conexao_sac';
		$config   = new Zend_Config_Ini($DIRBANCO, $Conexao);
		$registry = Zend_Registry::getInstance();
		$registry->set('config', $config); // registra
		$db = Zend_Db::factory($config->db);
		Zend_Db_Table::setDefaultAdapter($db);

		// executa a query
		$resultado = $db->fetchAll($sql);

		// encerra a conexão
		$db = Zend_Db_Table::getDefaultAdapter();
		$db->closeConnection();

		return $resultado[0]['Habilitado'];
	} // fecha método statusHISTORICO_INSERT()

} // fecha class