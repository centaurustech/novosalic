<?php
/**
 * Description of Sgcacesso
 *
 * @author augusto 
 */

class CaptacaoConversao extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "CaptacaoConversao";



	public function buscarCaptacaoConversao()
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from($this);
		return $this->fetchAll($select);
	} // fecha método buscarCaptacaoConversao()



	public function BuscarTotalCaptacaoConversao($retornaSelect = false, $where = array())
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from(array($this->_name)
			,array('AnoProjeto'
				,'Sequencial'
				,'Conv' => 'ISNULL(sum(valor), 0)')
		);

		$select->group('AnoProjeto');
		$select->group('Sequencial');

                foreach ($where as $coluna => $valor) {
                    $select->where($coluna, $valor);
                }

		if ($retornaSelect == true)
		{
			return $select;
		}
		else
		{
			return $this->fetchAll($select);
		}
	} // fecha método BuscarTotalCaptacaoConversao()

} // fecha class