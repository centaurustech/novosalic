<?php
/**
 * DAO Municipios
 * @author emanuel.sampaio <emanuelonline@gmail.com>
 * @since 18/04/2012
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2012 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://salic.cultura.gov.br
 */

class Municipios extends GenericModel
{
	protected $_banco  = 'AGENTES';
	protected $_name   = 'Municipios';
	protected $_schema = 'dbo';



	/**
	 * Busca os municípios para as combos
	 * @access public
	 * @param array $where (filtros)
	 * @param array $order (ordenação)
	 * @return object
	 */
	public function combo($where = array(), $order = array())
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from($this->_schema . '.' . $this->_name
			,array('idMunicipioIBGE AS id'
				,'Descricao AS descricao'
			)
		);

		// adiciona quantos filtros foram enviados
		foreach ($where as $coluna => $valor) :
			$select->where($coluna, $valor);
		endforeach;

		// adicionando linha order ao select
		$select->order($order);

		return $this->fetchAll($select);
	} // fecha método combo()
        
        
	public function buscaCompleta($where = array(), $order = array(), $dbg = null){
            
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(array('mu' => $this->_name),
                            array('mu.idMunicipioIBGE',
                                  'mu.IdMeso',
                                  'mu.idMicro',
                                  'mu.Descricao as dsMunicipio',
                                  'mu.Latitude',
                                  'mu.Longitude')
            );

            $select->joinInner(array('uf' => 'UF'),'mu.idUFIBGE = uf.idUF',
                                array('uf.idUF',
                                      'uf.Sigla',
                                      'uf.Descricao as dsUF',
                                      'uf.Regiao')
            );

            foreach ($where as $coluna => $valor) :
                    $select->where($coluna, $valor);
            endforeach;

            $select->order($order);
            
            if($dbg){
                xd($select->assemble());
            }

            return $this->fetchAll($select);
	}
}