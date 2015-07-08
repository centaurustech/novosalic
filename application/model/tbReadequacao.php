<?php
/**
 * DAO tbReadequacao
 * @author jeffersonassilva@gmail.com - XTI
 * @since 19/11/2013
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbReadequacao extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "tbReadequacao";


	/**
	 * Método para alterar
	 * @access public
	 * @param array $dados
	 * @param integer $where
	 * @return integer (quantidade de registros alterados)
	 */
	public function alterarDados($dados, $where)
	{
		$where = "idRecurso = " . $where;
		return $this->update($dados, $where);
	} // fecha método alterarDados()
    

    /* 
     * Criada em 03/14
     * @author: Jefferson Alessandro
     */
    public function readequacoesCadastradasProponente($where=array(), $order=array()) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("a.idReadequacao, a.idPronac, a.dtSolicitacao, CAST(a.dsSolicitacao AS TEXT) AS dsSolicitacao, CAST(a.dsJustificativa AS TEXT) AS dsJustificativa, b.dsReadequacao, c.idArquivo, d.nmArquivo, a.idTipoReadequacao"),
            )
        );

        $select->joinInner(
            array('b' => 'tbTipoReadequacao'), 'a.idTipoReadequacao = b.idTipoReadequacao',
            array(''), 'SAC.dbo'
        );
        $select->joinLeft(
            array('c' => 'tbDocumento'), 'a.idDocumento = c.idDocumento',
            array(''), 'BDCORPORATIVO.scCorp'
        );
        $select->joinLeft(
            array('d' => 'tbArquivo'), 'c.idArquivo = d.idArquivo',
            array(''), 'BDCORPORATIVO.scCorp'
        );

       //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $select->order($order);

        //xd($select->assemble());
        return $this->fetchAll($select);
    }

    /* 
     * Alterada em 06/03/14
     * @author: Jefferson Alessandro
     */
    public function painelReadequacoes($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("b.idPronac, a.idReadequacao, b.AnoProjeto+b.Sequencial as PRONAC, b.NomeProjeto, a.dtSolicitacao, c.dsReadequacao, a.siEncaminhamento")
            )
        );
        $select->joinInner(
            array('b' => 'Projetos'), 'a.idPronac = b.idPronac',
            array('b.CgcCpf'), 'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'tbTipoReadequacao'), 'c.idTipoReadequacao = a.idTipoReadequacao',
            array(''), 'SAC.dbo'
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
    
    /* 
     * Busca os dados da readequacao com os campos de VARCHAR(MAX) convertidos e completos.
     */
    public function buscarReadequacao($idReadequacao) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            array(
                'idReadequacao',
                'idPronac',
                'idTipoReadequacao',
                'dtSolicitacao',
                'idSolicitante',
                new Zend_Db_Expr('CAST(a.dsJustificativa AS TEXT) AS dsJustificativa'),
                new Zend_Db_Expr('CAST(a.dsSolicitacao AS TEXT) AS dsSolicitacao'),
                'idDocumento',
                'idAvaliador',
                'dtAvaliador',
                new Zend_Db_Expr('CAST(a.dsAvaliacao AS TEXT) AS dsAvaliacao'),
                'stAtendimento',
                'siEncaminhamento',
                'stAnalise',
                'idNrReuniao',
                'stEstado',
            )
        );

        $select->where('idReadequacao = ?', $idReadequacao);

        //xd($select->assemble());
        return $this->fetchAll($select);
    }
    
    /* 
     * Alterada em 06/03/14
     * @author: Jefferson Alessandro
     */
    public function buscarDadosReadequacoes($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            new Zend_Db_Expr("
                a.idReadequacao,
                a.idPronac,
                a.dtSolicitacao,
                CAST(a.dsSolicitacao AS TEXT) AS dsSolicitacao,
                CAST(a.dsJustificativa AS TEXT) AS dsJustificativa,
                a.idSolicitante,
                a.idAvaliador,
                a.dtAvaliador,
                CAST(a.dsAvaliacao AS TEXT) AS dsAvaliacao,
                a.idTipoReadequacao,
                CAST(c.dsReadequacao AS TEXT) AS dsReadequacao,
                a.stAtendimento,
                a.siEncaminhamento,
                CAST(b.dsEncaminhamento AS TEXT) AS dsEncaminhamento,
                a.stEstado,
                e.idArquivo,
                e.nmArquivo
            ")
        );
        $select->joinInner(
            array('b' => 'tbTipoEncaminhamento'), 'a.siEncaminhamento = b.idTipoEncaminhamento',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'tbTipoReadequacao'), 'c.idTipoReadequacao = a.idTipoReadequacao',
            array(''), 'SAC.dbo'
        );
        $select->joinLeft(
            array('d' => 'tbDocumento'), 'd.idDocumento = a.idDocumento',
            array(''), 'BDCORPORATIVO.scCorp'
        );
        $select->joinLeft(
            array('e' => 'tbArquivo'), 'e.idArquivo = d.idArquivo',
            array(''), 'BDCORPORATIVO.scCorp'
        );

       //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $select->order($order);
        
            //xd($select->assemble());
        return $this->fetchAll($select);
    }
    
    public function visualizarReadequacao($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            new Zend_Db_Expr("
                a.idReadequacao,
                a.idPronac,
                a.dtSolicitacao,
                CAST(a.dsSolicitacao AS TEXT) AS dsSolicitacao,
                CAST(a.dsJustificativa AS TEXT) AS dsJustificativa,
                a.idSolicitante,
                a.idAvaliador,
                a.dtAvaliador,
                CAST(a.dsAvaliacao AS TEXT) AS dsAvaliacao,
                a.idTipoReadequacao,
                CAST(c.dsReadequacao AS TEXT) AS dsReadequacao,
                a.stAtendimento,
                a.siEncaminhamento,
                CAST(b.dsEncaminhamento AS TEXT) AS dsEncaminhamento,
                a.stEstado,
                e.idArquivo,
                e.nmArquivo
            "), 'SAC.dbo'
        );
        $select->joinInner(
            array('b' => 'tbTipoEncaminhamento'), 'a.siEncaminhamento = b.idTipoEncaminhamento',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'tbTipoReadequacao'), 'c.idTipoReadequacao = a.idTipoReadequacao',
            array(''), 'SAC.dbo'
        );
        $select->joinLeft(
            array('d' => 'tbDocumento'), 'd.idDocumento = a.idDocumento',
            array(''), 'BDCORPORATIVO.scCorp'
        );
        $select->joinLeft(
            array('e' => 'tbArquivo'), 'e.idArquivo = d.idArquivo',
            array(''), 'BDCORPORATIVO.scCorp'
        );
//        $select->joinLeft(
//            array('f' => 'Aprovacao'), 'f.idReadequacao = a.idReadequacao',
//            array(''), 'SAC.dbo'
//        );

       //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $select->order($order);
        
        //xd($select->assemble());
        return $this->fetchAll($select);
    }

    /* 
     * Criada em 16/03/2014
     * @author: Jefferson Alessandro
     * Função usada para detalhar a readequação para análise do componente da comissão.
     */
    public function buscarDadosReadequacoesCnic($where=array(), $order=array()) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            new Zend_Db_Expr("
                a.idReadequacao,
                a.idPronac,
                a.dtSolicitacao,
                CAST(a.dsSolicitacao AS TEXT) AS dsSolicitacao,
                CAST(a.dsJustificativa AS TEXT) AS dsJustificativa,
                a.idSolicitante,
                a.idAvaliador,
                a.dtAvaliador,
                CAST(a.dsAvaliacao AS TEXT) AS dsAvaliacao,
                a.idTipoReadequacao,
                CAST(c.dsReadequacao AS TEXT) AS dsReadequacao,
                a.stAtendimento,
                a.siEncaminhamento,
                CAST(b.dsEncaminhamento AS TEXT) AS dsEncaminhamento,
                a.stEstado,
                e.idArquivo,
                e.nmArquivo,
                f.idDistribuirReadequacao
            ")
        );
        $select->joinInner(
            array('b' => 'tbTipoEncaminhamento'), 'a.siEncaminhamento = b.idTipoEncaminhamento',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'tbTipoReadequacao'), 'c.idTipoReadequacao = a.idTipoReadequacao',
            array(''), 'SAC.dbo'
        );
        $select->joinLeft(
            array('d' => 'tbDocumento'), 'd.idDocumento = a.idDocumento',
            array(''), 'BDCORPORATIVO.scCorp'
        );
        $select->joinLeft(
            array('e' => 'tbArquivo'), 'e.idArquivo = d.idArquivo',
            array(''), 'BDCORPORATIVO.scCorp'
        );
        $select->joinInner(
            array('f' => 'tbDistribuirReadequacao'), 'f.idReadequacao = a.idReadequacao',
            array(''), 'SAC.dbo'
        );

       //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $select->order($order);

        //xd($select->assemble());
        return $this->fetchAll($select);
    }
    
    /* 
     * Criada em 12/03/14
     * @author: Jefferson Alessandro - jeffersonassilva@gmail.com
     */
    public function painelReadequacoesAnalise($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false, $idPerfil=0) {
        if($idPerfil == 121){
            $nome = 'd.usu_nome AS Tecnico';
        } else {
            $nome = 'd.Descricao AS Tecnico';
        }
        
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => 'tbDistribuirReadequacao'),
            array(
                new Zend_Db_Expr("a.idDistribuirReadequacao, c.IdPRONAC, c.AnoProjeto+c.Sequencial AS Pronac, c.NomeProjeto, a.DtEncaminhamento, $nome, a.idAvaliador AS idTecnico, CAST(b.dsSolicitacao as TEXT) AS dsSolicitacao, b.idReadequacao, e.dsReadequacao as tipoReadequacao")
            ), 'SAC.dbo'
        );
        $select->joinInner(
            array('b' => 'tbReadequacao'), 'a.idReadequacao = b.idReadequacao',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'Projetos'), 'c.IdPRONAC = b.IdPRONAC',
            array('c.CgcCpf'), 'SAC.dbo'
        );
        
        if($idPerfil == 121){
            $select->joinLeft(
                array('d' => 'Usuarios'), 'a.idAvaliador = d.usu_codigo',
                array(''), 'TABELAS.dbo'
            );
        } else {
            $select->joinLeft(
                array('d' => 'Nomes'), 'a.idAvaliador = d.idAgente',
                array(''), 'AGENTES.dbo'
            );
        }
        
        $select->joinInner(
            array('e' => 'tbTipoReadequacao'), 'e.idTipoReadequacao = b.idTipoReadequacao',
            array(''), 'SAC.dbo'
        );

       //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        if ($qtdeTotal) {
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
    
    /* 
     * Criada em 14/03/2014
     * @author: Jefferson Alessandro
     * Função acessada pelo componente da comissão.
     */
    public function painelReadequacoesComponente($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("b.idPronac, a.idReadequacao, b.AnoProjeto+b.Sequencial as PRONAC, b.NomeProjeto, a.dtSolicitacao, c.dsReadequacao, a.siEncaminhamento, d.idDistribuirReadequacao")
            )
        );
        $select->joinInner(
            array('b' => 'Projetos'), 'a.idPronac = b.idPronac',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'tbTipoReadequacao'), 'c.idTipoReadequacao = a.idTipoReadequacao',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('d' => 'tbDistribuirReadequacao'), 'd.idReadequacao = a.idReadequacao',
            array(''), 'SAC.dbo'
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
    
    public function readequacoesNaoSubmetidas($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("b.idPronac, a.idReadequacao, b.AnoProjeto+b.Sequencial AS PRONAC, b.NomeProjeto, a.dtSolicitacao, a.idTipoReadequacao, f.dsReadequacao, c.usu_nome AS Componente, d.Descricao AS dsArea, e.Descricao AS dsSegmento"),
            )
        );

        $select->joinInner(
            array('b' => 'Projetos'), 'a.idPronac = b.idPronac',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'Usuarios'), 'a.idAvaliador = c.usu_codigo',
            array(''), 'TABELAS.dbo'
        );
        $select->joinInner(
            array('d' => 'Area'), 'b.Area = d.Codigo',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('e' => 'Segmento'), 'b.Segmento = e.Codigo',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('f' => 'tbTipoReadequacao'), 'f.idTipoReadequacao = a.idTipoReadequacao',
            array(''), 'SAC.dbo'
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
    
    public function buscarDadosParecerReadequacao($where=array(), $order=array()) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            new Zend_Db_Expr("
                a.idReadequacao, c.DtParecer, c.ResumoParecer, c.ParecerFavoravel, c.Logon as idAvaliador, d.usu_nome as nmAvaliador
            ")
        );
        $select->joinInner(
            array('b' => 'tbReadequacaoXParecer'), 'b.idReadequacao = a.idReadequacao',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'Parecer'), 'c.IdParecer = b.idParecer',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('d' => 'Usuarios'), 'd.usu_codigo = c.Logon',
            array(''), 'TABELAS.dbo'
        );
        
       //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $select->order($order);

        //xd($select->assemble());
        return $this->fetchAll($select);
    }
    
    public function buscarReadequacoesEnviadosPlenaria($idNrReuniao) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('a' => $this->_name),
            new Zend_Db_Expr("
                a.stAnalise,
                (b.AnoProjeto+b.Sequencial) AS pronac, 
                b.NomeProjeto, 
                b.IdPRONAC, 
                c.Descricao AS area, 
                d.Descricao AS segmento,
                h.usu_nome AS nomeComponente,
                a.idReadequacao,
                i.dsReadequacao,
                a.idTipoReadequacao
            ")
        );
        $select->joinInner(
            array('b' => 'Projetos'), 'b.IdPRONAC = a.idPronac',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('c' => 'Area'), 'b.Area = c.Codigo',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('d' => 'Segmento'), 'b.Segmento = d.Codigo',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('g' => 'tbDistribuirReadequacao'), 'g.idReadequacao = a.idReadequacao AND g.idUnidade = 400',
            array(''), 'SAC.dbo'
        );
        $select->joinInner(
            array('h' => 'Usuarios'), 'h.usu_codigo = g.idAvaliador',
            array(''), 'TABELAS.dbo'
        );
        $select->joinInner(
            array('i' => 'tbTipoReadequacao'), 'i.idTipoReadequacao = a.idTipoReadequacao',
            array(''), 'SAC.dbo'
        );
        
        $select->where('a.stEstado = ? ', 0);
        $select->where('a.idNrReuniao = ? ', $idNrReuniao);
        $select->where('a.siEncaminhamento = ? ', 8);
        $select->where("NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbConsolidacaoVotacao AS cv WHERE a.idNrReuniao = cv.idNrReuniao AND a.idPronac = cv.IdPRONAC AND a.idTipoReadequacao = cv.tpTipoReadequacao)", '');
		$select->order(array(6,1));

        //xd($select->assemble());
        return $this->fetchAll($select);
    }
    
    public function atualizarReadequacoesProximaPlenaria($idNrReuniao) {
        $sql = "UPDATE SAC.dbo.tbReadequacao
                     SET idNrReuniao = idNrReuniao + 1
                FROM  SAC.dbo.tbReadequacao a
                INNER JOIN SAC.dbo.Projetos c on (a.idPronac = c.IdPRONAC)
                WHERE siEncaminhamento = 8
                      AND NOT EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbConsolidacaoVotacao b WHERE a.IdPRONAC = b.IdPRONAC AND a.idNrReuniao = $idNrReuniao )";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
    } // fecha método buscarPlanilhaDeCustos()
    
    public function atualizarStatusReadequacoesNaoSubmetidos($idNrReuniao) {
        $sql = "UPDATE SAC.dbo.tbReadequacao
                    SET stEstado = 1
               FROM  SAC.dbo.tbReadequacao a
               INNER JOIN SAC.dbo.Projetos c on (a.idPronac = c.IdPRONAC)
               WHERE a.stEstado = 0 and
                    (a.siEncaminhamento = 9 and a.idNrReuniao = $idNrReuniao ) or
                    (a.siEncaminhamento = 8 and a.stEstado = 0
                    AND EXISTS(SELECT TOP 1 * FROM BDCORPORATIVO.scSAC.tbConsolidacaoVotacao b WHERE a.idPronac = b.IdPRONAC AND a.idNrReuniao = $idNrReuniao ))";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);
        return $resultado;
    } // fecha método buscarPlanilhaDeCustos()
    
} // fecha class