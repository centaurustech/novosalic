<?php
/**
 * DAO tbDiligencia
 * @author emanuel.sampaio - Politec
 * @since 17/02/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbDiligencia extends GenericModel
{
	/* dados da tabela */
	protected $_banco   = "SAC";
	protected $_schema  = "dbo";
	protected $_name    = "tbDiligencia";



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

	/**
	 * Método para buscarDados
	 * @access public
	 * @param array $dados
	 * @return array 
	 */
	public function buscarDados($idPronac)
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from(
			array('d' => $this->_name),
		        array("*"));
		
        $select->where("d.idPronac = ".$idPronac);
        $select->where("stEstado = 0");
        $select->where("DtResposta IS NULL");
        return $this->fetchAll($select);
	} // fecha método buscarDados()

	public function buscaDiligencia($idPronac=null, $idProduto=null, $idSolicitante=null, $idDiligencia=null)
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from(
						array('d' => $this->_name),
		                array('d.idPronac',
		                	  'd.idDiligencia',
		                	  'd.idTipoDiligencia',
		                	  'd.DtSolicitacao',
		                	  'd.Solicitacao',
		                	  'd.idSolicitante',
		                	  'd.DtResposta',
		                	  'd.Resposta',
		                	  'd.idProponente',
		                	  'd.stEstado',
		                	  'd.idPlanoDistribuicao',
		                	  'd.idArquivo',
		                	  'd.idCodigoDocumentosExigidos',
		                	  'd.idProduto',
		                	  'd.stProrrogacao',
		                	  'd.stEnviado')
		);
		
		$select->joinInner(
				array('p'=>'Projetos'),'d.idPronac = p.IdPRONAC',
				array('(p.AnoProjeto + p.Sequencial) AS PRONAC',
					  'p.NomeProjeto')
		);
		
		$select->joinLeft(
				array('doc'=>'DocumentosExigidos'),'d.idCodigoDocumentosExigidos = doc.Codigo',
				array('doc.Opcao',
					  'doc.Descricao as DocumentosExigidos')
		);
		                
		$select->joinInner(
				array('v'=>'Verificacao'),'d.idTipoDiligencia = v.idVerificacao',
				array('v.Descricao')
		);                
		                
		$select->joinLeft(
				array('a'=>'tbArquivo'),'d.idArquivo = a.idArquivo',
				array('a.nmArquivo','a.sgExtensao'),
				'BDCORPORATIVO.scCorp'
		);                


		
		
		if($idPronac !=null || $idProduto !=null || $idSolicitante !=null || $idDiligencia !=null){
                
                if($idPronac !=null)
                {
                	$select->where('d.idPronac = ?', $idPronac);
                }
                if($idProduto !=null)
                {
					$select->where('d.idProduto = ?', $idProduto);
                }
                if($idSolicitante !=null)
                {
                	$select->where('d.idSolicitante = ?', $idSolicitante);
                }
                if($idDiligencia !=null){
                    $select->where('d.idDiligencia = ?', $idDiligencia);
                }
            }
            		                
        
        return $this->fetchAll($select);
	} // fecha método buscaDiligencia()
	
	
	
	
	
	
	
	
	/**
	 * Método para alterar
	 * @access public
	 * @param array $dados
	 * @param integer $where
	 * @return integer (quantidade de registros alterados)
	 */
	public function alterarDados($dados, $where)
	{
		$where = "idDistribuirParecer = " . $where;
		return $this->update($dados, $where);
	} // fecha método alterarDados()



	/**
	 * Método para excluir
	 * @access public
	 * @param integer $where
	 * @return integer (quantidade de registros excluídos)
	 */
	public function excluirDados($where)
	{
		$where = "idDistribuirParecer = " . $where;
		return $this->delete($where);
	} // fecha método excluirDados()



	/**
	 * Função para checar o status da diligência
	 */
	public function fnChecarDiligencia($idPronac)
	{
		$sql = 'SELECT SAC.dbo.fnchecarDiligencia('. $idPronac .') AS Diligencia';

		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);	
	} // fecha método fnChecarDiligencia()

} // fecha class