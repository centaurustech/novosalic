<?php
/**
 * Description of Vinculo
 *
 * @author tisomar
 */
class HistoricoSituacao extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "HistoricoSituacao";

	/**
	 * Método para buscar a situação anterior de um projeto
	 * @access public
	 * @param string $pronac
	 * @return array
	 */
        
        public function inserirHistoricoSituacao($dados) {
        try {
            $inserir = $this->insert($dados);
            return $inserir;
        } catch (Zend_Db_Table_Exception $e) {
            return 'Class:Aprovacao Method: inserirAprovacao -> Erro: ' . $e->__toString();
        }
    }
        
	public function buscarSituacaoAnterior($pronac = null)
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from($this->_name);

		// busca pelo pronac
		if (!empty($pronac))
		{
			$select->where("(AnoProjeto+Sequencial) = ?", $pronac);
		}

		$select->order("Contador DESC");

		return $this->fetchRow($select);
	} // fecha método buscarSituacaoAnterior()



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

        public function buscarHistoricosEncaminhamento($where = array(), $order = array(), $tamanho = -1, $inicio = -1, $qtdeTotal = false)
        {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('h' => $this->_name),
                    array(
                        new Zend_Db_Expr(
                            'h.Contador, h.AnoProjeto+h.Sequencial as Pronac, h.DtSituacao, h.Situacao,
                            h.ProvidenciaTomada, u.usu_identificacao as cnpjcpf, u.usu_nome as usuario'
                        )
                    )
                );

            $select->joinInner(
                array('u' => 'Usuarios'), 'u.usu_codigo = h.Logon',
                array(), 'TABELAS.dbo'
            );
            
            //adiciona quantos filtros foram enviados
            foreach ($where as $coluna => $valor) {
                $select->where($coluna, $valor);
            }

            if ($qtdeTotal) {
                //xd($select->assemble());
                return $this->fetchAll($select)->count();
            }

            //adicionando linha order ao select
            $select->order($order);

            // paginacao
            if ($tamanho > -1) {
                $tmpInicio = 0;
                if ($inicio > -1) {
                    $tmpInicio = $inicio;
                }
                $select->limit($tamanho, $tmpInicio);
            }

            //xd($select->assemble());
            return $this->fetchAll($select);
        }
        
        
        public function buscarHistoricosEncaminhamentoIdPronac($where = array(), $order = array(), $tamanho = -1, $inicio = -1, $qtdeTotal = false)
        {
            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                    array('h' => $this->_name),
                    array(
                        new Zend_Db_Expr(
                            'h.Contador, h.AnoProjeto+h.Sequencial as Pronac, h.DtSituacao, h.Situacao,
                            CAST(h.ProvidenciaTomada AS TEXT) as ProvidenciaTomada, u.usu_identificacao as cnpjcpf, u.usu_nome as usuario'
                        )
                    )
                );

            $select->joinInner(
                array('u' => 'Usuarios'), 'u.usu_codigo = h.Logon',
                array(), 'TABELAS.dbo'
            );
            
            $select->joinInner(
                array('p' => 'Projetos'), 'h.AnoProjeto = p.AnoProjeto AND h.Sequencial = p.Sequencial',
                array(), 'SAC.dbo'
            );
            
            //adiciona quantos filtros foram enviados
            foreach ($where as $coluna => $valor) {
                $select->where($coluna, $valor);
            }

            if ($qtdeTotal) {
                //xd($select->assemble());
                return $this->fetchAll($select)->count();
            }

            //adicionando linha order ao select
            $select->order($order);

            // paginacao
            if ($tamanho > -1) {
                $tmpInicio = 0;
                if ($inicio > -1) {
                    $tmpInicio = $inicio;
                }
                $select->limit($tamanho, $tmpInicio);
            }

//             xd($select->assemble());
            return $this->fetchAll($select);
        }
} // fecha class