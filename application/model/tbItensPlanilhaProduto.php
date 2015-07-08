<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of tbAcesso
 *
 * @author 01129075125
 */
class tbItensPlanilhaProduto extends GenericModel{

    protected $_banco  = 'SAC'; 
    protected $_schema = 'dbo';
    protected $_name   = 'tbItensPlanilhaProduto';

    
    /**
     * Método para consultar o Valor Real por ano
     * @access public
     * @param array $dados
     * @param integer $where
     * @return integer (quantidade de registros alterados)
     */
    public function buscaItemProduto($where=array()) {
    	$this->_db->beginTransaction();
		try {
		    $select = $this->select();
			$select->setIntegrityCheck(false);		    
			    	
			$select->from(
                            array('p' => $this->_schema . '.' . $this->_name),
                            array('p.idPlanilhaItens')
                            ); 
							       
			$select->joinInner(
                            array('pr' => 'Produto'),'p.idProduto = pr.Codigo',
                            array('pr.Descricao as Produto')
                            ); 
							       
			$select->joinInner(
                            array('e' => 'TbPlanilhaEtapa'),'p.idPlanilhaEtapa = e.idPlanilhaEtapa',
                            array('e.Descricao as Etapa')
                            );  
							  
			$select->joinInner(
                            array('i' => 'TbPlanilhaItens'),'p.idPlanilhaItens = i.idPlanilhaItens',
                            array('i.Descricao as Item')
                            );						        
							    
	    	//adiciona quantos filtros foram enviados
			foreach ($where as $coluna => $valor) {
				$select->where($coluna, $valor);
			}			
	
			$select->order('e.Descricao');
			$select->order('i.Descricao');		
			$this->_db->commit();		
			return $this->fetchAll($select);
		} catch (Exception $e) {
			$this->_db->rollBack();
    		return false;
		}
    }
    
    public function totalBuscaPaginacao($where=array()){
    $this->_db->beginTransaction();
		try {
		    $select = $this->select();
			$select->setIntegrityCheck(false);		    
			    	
			$select->from(
							array('p' => $this->_schema . '.' . $this->_name),
							array('total'=>'count(*)')
							); 
							       
			$select->joinInner(
							array('pr' => 'Produto'),'p.idProduto = pr.Codigo',
							array('pr.Descricao as Produto')
							); 
							       
			$select->joinInner(
							array('e' => 'TbPlanilhaEtapa'),'p.idPlanilhaEtapa = e.idPlanilhaEtapa',
							array('e.Descricao as Etapa')
							);  
							  
			$select->joinInner(
							array('i' => 'TbPlanilhaItens'),'p.idPlanilhaItens = i.idPlanilhaItens',
							array('i.Descricao as Item')
							);						        
							    
	    	//adiciona quantos filtros foram enviados
			foreach ($where as $coluna => $valor) {
				$select->where($coluna, $valor);
			}		
			$this->_db->commit();		
			return $this->fetchAll($select);
		} catch (Exception $e) {
			$this->_db->rollBack();
    		return false;
		}    	
    }
    
    public function buscarEtapasDoItem($where=array(), $order=array()) {
    	
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->distinct();
        $select->from(
                array('p' => $this->_schema . '.' . $this->_name), 
                array('p.idPlanilhaItens',
                      'p.idPlanilhaEtapa')
        );

        $select->joinInner(
                array('e' => 'tbPlanilhaEtapa'), 
                'p.idPlanilhaEtapa = e.idPlanilhaEtapa', 
                array('e.Descricao as Etapa')
        );
        
        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $select->where($coluna, $valor);
        }
        
        $select->order($order);
        //xd($select->assemble());
        return $this->fetchAll($select);	
    }
    
    public function itensPorItemEEtapaReadequacao($idEtapa, $idProduto){
    	
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
            array('a' => $this->_schema . '.' . $this->_name),
            array('idPlanilhaItens')
        );

        $select->joinInner(
            array('b' => 'tbPlanilhaItens'), 'a.idPlanilhaItens = b.idPlanilhaItens', 
            array('Descricao as Item')
        );
        
        $select->where('a.idPlanilhaEtapa = ?', $idEtapa);
        $select->where('a.idProduto = ?', $idProduto);
        
        $select->order('2'); // Descricao
        
        //xd($select->assemble());
        return $this->fetchAll($select);	
}
}
?>
