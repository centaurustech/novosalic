<?php
/**
 * Description of tbAusencia
 *
 * @author Equipe Politec
 */
class TbInformacaoProfissional extends GenericModel {

    protected $_banco = 'Agentes';
    protected $_name = 'tbInformacaoProfissional';
    protected $_schema  = 'dbo';

    
    public function BuscarInfo($idAgente, $situacao) 
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        
        $select->from(array('A'=>$this->_name),
        			  array('*','CONVERT(CHAR(10), dtInicioVinculo, 103) as dtInicio',
        			  			'CONVERT(CHAR(10), dtFimVinculo, 103) as dtFim')
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
        
        if(!empty($situacao))
        {
        	$select->where('A.siInformacao = ?', $situacao);	
        }
        
        $select->where('A.idAgente = ?', $idAgente);
        
        $select->order('A.dtInicioVinculo');
        
        return $this->fetchAll($select);

    }
    
    public function AnosExperiencia($idAgente) 
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        
        $select->from(array('A'=>$this->_name),
        			  array('DATEDIFF ( YEAR , dtInicioVinculo , dtFimVinculo ) as qtdAnos')
        );


		$select->where('A.idAgente = ?', $idAgente);
        
        return $this->fetchAll($select);

    }


    public function inserirInfo($dados) 
    {
        $insert = $this->insert($dados);
        return $insert;
    }

    public function alteraInfo($dados, $idInfo)
	{
		
		$where = "idInformacaoProfissional = " . $idInfo;
		
		return $this->update($dados, $where);
	} 
    
   

}
?>
