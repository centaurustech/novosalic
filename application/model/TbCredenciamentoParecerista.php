<?php
/**
 * Description of tbCredenciamentoParecerista
 *
 * @author Equipe Politec
 */
class TbCredenciamentoParecerista extends GenericModel {

    protected $_banco 	= 'Agentes';
    protected $_name 	= 'tbCredenciamentoParecerista';
    protected $_schema  = 'dbo';

    
    public function BuscarCredenciamentos($idAgente) 
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('C'=>$this->_name),
            array('C.idAgente', 'C.idCredenciamentoParecerista', 'C.siCredenciamento', 'C.qtPonto', 'C.idVerificacao')
        );

        $select->joinInner(
            array('A'=>'Area'),'A.Codigo = C.idCodigoArea',
            array('A.Descricao as Area'),'SAC.dbo'
        );

        $select->joinInner(
            array('S'=>'Segmento'),'S.Codigo = C.idCodigoSegmento',
            array('S.Descricao as Segmento'),'SAC.dbo'
        );

        $select->joinLeft(
            array('V'=>'Verificacao'),'V.idVerificacao = C.idVerificacao',
            array('V.Descricao as Nivel'),'AGENTES.dbo'
        );

        $select->joinLeft(
            array('x'=>'Visao'),'x.idAgente = C.idAgente',
            array('x.Visao'),'AGENTES.dbo'
        );

        $select->where('C.idAgente = ?', $idAgente);
        $select->where('x.Visao = ?', 209);
        $select->order('A.Descricao');
        $select->order('S.Descricao');

//        xd($select->assemble());

        return $this->fetchAll($select);
    }

    
    //Select count(distinct idCodigoArea) as qtdArea from AGENTES..tbCredenciamentoParecerista where idCodigoSegmento LIKE '1%'
	//Select count(distinct idCodigoSegmento) as qtdSeguimentos from AGENTES..tbCredenciamentoParecerista where idCodigoSegmento LIKE '1%'
    
    public function QtdArea($idAgente) 
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('A'=>$this->_name),
        			  array('count(distinct idCodigoArea) as qtd')
        );

        $select->where('A.idAgente = ?', $idAgente);

        $select->where('A.siCredenciamento = 1');
        //xd($select->__toString());
        return $this->fetchAll($select);

    }

    public function QtdSegmento($idAgente, $idSegmento) 
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('A'=>$this->_name),
        			  array('count(distinct idCodigoSegmento) as qtd')
        );

        $select->where("A.idCodigoSegmento LIKE '".$idSegmento."%'");

        $select->where('A.idAgente = ?', $idAgente);

        $select->where('A.siCredenciamento = 1');
        
        //xd($select->__toString());
        return $this->fetchAll($select);

    }

    public function verificarCadastrado($idAgente, $idSegmento, $idArea) 
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('A'=>$this->_name),
        			  array('*')
        );

        $select->where('A.idCodigoArea = ?', $idArea);

        $select->where('A.idCodigoSegmento = ?', $idSegmento);

        $select->where('A.idAgente = ?', $idAgente);

        $select->where('A.siCredenciamento = 1');
        
        //xd($select->__toString());
        return $this->fetchAll($select);

    }


    public function inserirCredenciamento($dados) 
    {
        $insert = $this->insert($dados);
        return $insert;
    }

    public function alteraCredenciamento($dados, $idCredenciamento)
	{
		
		$where = "idCredenciamentoParecerista = " . $idCredenciamento;
		
		return $this->update($dados, $where);
	} 
    
    public function excluiCredenciamento($idCredenciamento)
	{
		$sql ="DELETE FROM Agentes.dbo.tbCredenciamentoParecerista WHERE idCredenciamentoParecerista = ".$idCredenciamento;
        
        $db  = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
        if($db->query($sql))
        {
            return true;
        }
        else
        {
            return false;
        }
	} 
    
   

}
?>
