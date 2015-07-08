<?php
/**
 * DAO tbDistribuirParecer
 * @author emanuel.sampaio - Politec
 * @since 17/02/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */


class Verificacao extends GenericModel
{
	/**
	 * Devolução ao Fundo Nacional de Cultura - FNC
	 */
	const DEVOLUCAO_JUDICIAL = 349;
	const DEVOLUCAO_FUNDO_NACIONAL_CULTURA = 350;
	const OUTRAS_DEVOLUCOES_DE_RECURSOS_CAPTADOS = 351;

	/* dados da tabela */
	protected $_banco   = "SAC";
	protected $_schema  = "dbo";
	protected $_name    = "Verificacao";



	 /**
	 * Método para cadastrar
	 * @access public
	 * @param array $dados
	 * @return integer (retorna o último id cadastrado)
	 */
	public function cadastrarDados($dados)
	{
		return $this->insert($dados);
	} // fecha método cadastrarDados()

        function tipoDiligencia($consulta = array()){

            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                            array($this->_name),
                            array(
                                    'idVerificacao',
                                    'Descricao'
                                 )
                         );
//            $select->where('idTipo = ?', 8);
//            $select->where('stEstado = ?', 1);
            foreach ($consulta as $coluna=>$valor)
            {
                $select->where($coluna, $valor);
            }
            //xd($select->assemble());
            return $this->fetchAll($select);

        }



	/**
	 * Busca por tipo
	 * @access public
	 * @param array $where (filtros)
	 * @param array $order (ordenação)
	 * @return object
	 */
	public function buscarTipos($where = array(), $order = array())
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from(array('v' => $this->_schema . '.' . $this->_name)
			,array('v.idVerificacao'
				,'LTRIM(v.Descricao) AS dsVerificacao')
		);
		$select->joinInner(
			array('t' => 'Tipo')
			,'v.idTipo = t.idTipo'
			,array('t.idTipo'
				,'t.Descricao AS dsTipo')
			,'SAC.dbo'
		);

		// adiciona quantos filtros foram enviados
		foreach ($where as $coluna => $valor) :
			$select->where($coluna, $valor);
		endforeach;

		// adicionando linha order ao select
		$select->order($order);

		return $this->fetchAll($select);
	} // fecha método buscarTipos()

        function combosNatureza($idTipo){

            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array(new Zend_Db_Expr('Agentes.dbo.Verificacao')), // 'v' => $this->_schema . '.' . $this->_name),
                    array('idVerificacao', 'Descricao')
            );
            $select->where('idTipo = ?', $idTipo);
            return $this->fetchAll($select);

        }
        
        
        function buscarOrigemRecurso($where = array()){
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(  array(new Zend_Db_Expr('SAC.dbo.Verificacao')),
                            array('idVerificacao', 'Descricao'));
            
            if($where){
                foreach ($where as $coluna => $valor) :
                    $select->where($coluna, $valor);
		endforeach;
            }
            
            return $this->fetchAll($select);
        }


} // fecha class