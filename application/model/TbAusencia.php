<?php
/**
 * Description of tbAusencia
 *
 * @author Equipe Politec
 */
class TbAusencia extends GenericModel {

    protected $_banco = 'Agentes';
    protected $_name = 'tbAusencia';
    protected $_schema  = 'dbo';

    
    public function BuscarAusencia($idAgente, $ano, $tipo, $mes) 
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('A'=>$this->_name),
        			  array('*','CONVERT(CHAR(10), dtInicioAusencia, 103) as dtInicio',
        			  		'CONVERT(CHAR(10), dtFimAusencia, 103) as dtFim',
        			  		'DATEDIFF ( DAY , dtInicioAusencia , dtFimAusencia ) as qtdDias')
        			  );

        $select->joinInner(
                array('N'=>'Nomes'),'N.idAgente = A.idAgente',
                array('N.Descricao as Nome')
        );

        $select->joinInner(
                array('TP'=>'tbTipoAusencia'),'TP.idTipoAusencia = A.idTipoAusencia',
                array('TP.nmAusencia as tipoAusencia')
        );

        $select->joinLeft(
                array('D'=>'tbDocumento'),'D.idDocumento = A.idDocumento',
                array('*'),'BDCORPORATIVO.scCorp'
        );

        $select->joinLeft(
                array('TA'=>'tbArquivo'),'TA.idArquivo = D.idArquivo',
                array('*'),'BDCORPORATIVO.scCorp'
        );

        $select->joinLeft(
                array('TAI'=>'tbArquivoImagem'),'TAI.idArquivo = TA.idArquivo',
                array('*'),'BDCORPORATIVO.scCorp'
        );
        
        $select->where('A.idTipoAusencia = ?', $tipo);

        if(!empty($idAgente))
        {
        	$select->where('A.idAgente = ?', $idAgente);
        }
        $select->where('YEAR(dtInicioAusencia) = ?', $ano);
        
        if(!empty($mes))
        {
        	$select->where('MONTH(dtInicioAusencia) = ?', $mes);	
        }
        
        $select->order('A.idAlteracao');
        $select->order('A.idAusencia');
        

        return $this->fetchAll($select);

    }
    
    public function BuscarAusenciaPainel($ano) 
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('A'=>$this->_name),
        			  array('*','CONVERT(CHAR(10), dtInicioAusencia, 103) as dtInicio',
        			  		'CONVERT(CHAR(10), dtFimAusencia, 103) as dtFim',
        			  		'DATEDIFF ( DAY , dtInicioAusencia , dtFimAusencia ) as qtdDias')
        			  );

        $select->joinInner(
                array('N'=>'Nomes'),'N.idAgente = A.idAgente',
                array('N.Descricao as Nome')
        );

        $select->joinInner(
                array('TP'=>'tbTipoAusencia'),'TP.idTipoAusencia = A.idTipoAusencia',
                array('TP.nmAusencia as tipoAusencia')
        );

        $select->joinLeft(
                array('D'=>'tbDocumento'),'D.idDocumento = A.idDocumento',
                array('*'),'BDCORPORATIVO.scCorp'
        );

        $select->joinLeft(
                array('TA'=>'tbArquivo'),'TA.idArquivo = D.idArquivo',
                array('*'),'BDCORPORATIVO.scCorp'
        );

        $select->joinLeft(
                array('TAI'=>'tbArquivoImagem'),'TAI.idArquivo = TA.idArquivo',
                array('*'),'BDCORPORATIVO.scCorp'
        );
        
        $select->where('A.idTipoAusencia = ?', 2);
        
        $select->where('A.siAusencia = ?', 0);

        $select->where('YEAR(dtInicioAusencia) = ?', $ano);
        
        $select->order('A.idAlteracao');
        $select->order('A.idAusencia');
        
        //xd($select->__toString());

        return $this->fetchAll($select);

    }

    

    public function UltimoRegistro() 
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('A'=>$this->_name),  
        				array('MAX(idAusencia) as id')  
        );
        
        return $this->fetchAll($select);

    }
    
    public function BuscarAusenciaRepetida($idAgente, $dtInicio, $dtFim) 
    {
    	
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('A'=>$this->_name),
        			  array('*','CONVERT(CHAR(10), dtInicioAusencia, 103) as dtInicio',
        			  		'CONVERT(CHAR(10), dtFimAusencia, 103) as dtFim',
        			  		'DATEDIFF ( DAY , dtInicioAusencia , dtFimAusencia ) as qtdDias')
        			  );

        $select->joinInner(
                array('TP'=>'tbTipoAusencia'),'TP.idTipoAusencia = A.idTipoAusencia',
                array('TP.nmAusencia as tipoAusencia')
        );
        
        
        $select->where('A.idAgente = ?',$idAgente);
        $select->where("'{$dtInicio}' BETWEEN dtInicioAusencia AND dtFimAusencia");
        $select->orWhere('A.idAgente = ?',$idAgente);
        $select->where("'{$dtFim}' BETWEEN dtInicioAusencia AND dtFimAusencia");
        
        $select->order('A.siAusencia');
        
		//xd($select->__toString());
        return $this->fetchAll($select);

    }
    
    public function BuscarAusenciaAtiva($idAgente, $dtAtual, $tipoAusencia) 
    {
    	
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('A'=>$this->_name),
        			  array('*')
        			  );

        $select->joinInner(
                array('TP'=>'tbTipoAusencia'),'TP.idTipoAusencia = A.idTipoAusencia',
                array('TP.nmAusencia as tipoAusencia')
        );
        
        $select->where('A.idAgente = ?',$idAgente);
        $select->where('A.idTipoAusencia = ?',$tipoAusencia);
        if($tipoAusencia == 1)
        {
        	$select->where('A.dtInicioAusencia >= ?', new Zend_Db_Expr('DATEADD(DAY, -10, GETDATE())'));	
        }
        else 
        {
        	$select->where('A.dtInicioAusencia <= ?', new Zend_Db_Expr('GETDATE()'));	
        }
        
        $select->where('A.dtFimAusencia >= ?', new Zend_Db_Expr('GETDATE()'));
        $select->where('A.siAusencia = ?',1);
        
		//xd($select->assemble());
        return $this->fetchAll($select);

    }
    

    public function inserirAusencia($dados) 
    {
        $insert = $this->insert($dados);
        return $insert;
    }

    public function alteraAusencia($dados, $idAusencia)
	{
		
		$where = "idAusencia = " . $idAusencia;
		
		return $this->update($dados, $where);
	} 
    
   

}
?>
