<?php

/**
 * Modelo que representa a tabela SAC.dbo.tbSolicitarItem
 * @author Jefferson Alessandro
 * @version 1.0 - 08/01/2013
 */

class tbSolicitarItem extends GenericModel {
   
    protected  $_banco  = 'SAC';
    protected  $_schema = 'dbo';
    protected  $_name   = 'tbSolicitarItem';

    
    public function listaSolicitacoesItens($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('s' => $this->_name),
            array('idSolicitarItem', 'Descricao as Justificativa',
                    new Zend_Db_Expr('
                        CASE
                            WHEN  s.IdPlanilhaItens > 0 THEN i.Descricao
                            ELSE s.NomeDoItem
                       END as ItemSolicitado
                    '),
                    new Zend_Db_Expr("
                        CASE
                            WHEN  s.IdPlanilhaItens > 0 THEN 'Associação'
                            ELSE 'Inclusão'
                       END as TipoSolicitacao
                    "),
                    new Zend_Db_Expr("
                        CASE s.stEstado
                            WHEN 0 THEN 'Solicitado'
                            WHEN 1 THEN 'Atendido'
                            ELSE 'Negado'
                       END as Estado
                    ")
                )
        );
        $select->joinInner(
            array('p' => 'Produto'), "s.idProduto = p.Codigo",
            array('Codigo as idProduto', 'Descricao as Produto'), 'SAC.dbo'
        );
        $select->joinInner(
            array('e' => 'tbPlanilhaEtapa'), "s.idEtapa = e.idPlanilhaEtapa",
            array('idPlanilhaEtapa', 'Descricao as Etapa'), 'SAC.dbo'
        );
        $select->joinLeft(
            array('i' => 'tbPlanilhaItens'), "s.idPlanilhaItens = i.idPlanilhaItens",
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

    public function buscarDadosItem($idItem) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array($this->_name)
        );
        $select->where('idSolicitarItem = ?', $idItem);

        //xd($select->assemble());
        return $this->fetchRow($select);
    }
    
    public function buscarItens($where=array(), $order=array(), $tamanho=-1, $inicio=-1, $qtdeTotal=false) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from( 
            array('sol' => $this->_name),
            array(
                new Zend_Db_Expr("prod.Codigo as idProduto, prod.Descricao as Produto, et.idPlanilhaEtapa, et.Descricao as Etapa, sol.idSolicitarItem"),
                new Zend_Db_Expr("CASE
                                    WHEN  sol.IdPlanilhaItens > 0 THEN it.Descricao
                                    ELSE sol.NomeDoItem
                                END as ItemSolicitado, sol.Descricao as Justificativa, sol.stEstado
                "),
                new Zend_Db_Expr("CASE sol.stEstado
                                    WHEN 0 THEN 'Solicitado'
                                    WHEN 1 THEN 'Atendido'
                                    ELSE 'Negado'
                                END as Estado,Resposta
                ")
            )
        );

        $select->joinInner(
            array('prod' => 'Produto'), 'sol.idProduto = prod.Codigo',
            array(''), 'SAC.dbo'
        );
        
        $select->joinInner(
            array('et' => 'tbPlanilhaEtapa'), 'sol.idEtapa = et.idPlanilhaEtapa',
            array(''), 'SAC.dbo'
        );
        
        $select->joinLeft(
            array('it' => 'TbPlanilhaItens'), 'sol.idPlanilhaItens = it.idPlanilhaItens',
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
}
