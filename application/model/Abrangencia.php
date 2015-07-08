<?php 
class Abrangencia extends Zend_Db_Table_Abstract 
{
    protected $_name = "Abrangencia";
    //protected $_primary = "idAbrangencia";

    public function __construct() {
        $db = new Conexao(Zend_Registry::get('DIR_CONFIG'), "conexao_sac");
        parent::__construct();
    }

    /**
     * Retorna registros do banco de dados
     * @param array $where - array com dados where no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @param array $order - array com orders no formado "coluna_1 desc","coluna_2"...
     * @param int $tamanho - numero de registros que deve retornar
     * @param int $inicio - offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function buscar($where=array(), $order=array(), $tamanho=-1, $inicio=-1)
    {

        $sql = "SELECT
                    a.*,
                    p.Descricao pais,
                    u.descricao uf,
                    m.descricao cidade
                FROM
                    SAC.dbo.Abrangencia a
                INNER JOIN AGENTES.dbo.Pais p on (a.idPais=p.idPais and a.stAbrangencia = 1)
                LEFT JOIN AGENTES.dbo.Uf u on (a.idUF=u.idUF)
                LEFT JOIN AGENTES.dbo.Municipios m on (a.idMunicipioIBGE=m.idMunicipioIBGE)
                ";

        $ct=1;
        foreach ($where as $coluna=>$valor)
        {
            if($ct==1)
                $sql .= "WHERE ".$coluna." = '".$valor."'";
            else
                $sql .= " AND ".$coluna." = '".$valor."'";
            $ct++;
        }
        
        $db  = Zend_Registry::get('db');
	$db->setFetchMode(Zend_DB::FETCH_OBJ);
	return $db->fetchAll($sql);

    }

    
    
    public function verificarIgual($idPais, $idUF, $idMunicipio, $idPreProjeto)
    {

        $sql = "SELECT * FROM SAC.dbo.Abrangencia WHERE idProjeto = ".$idPreProjeto."
				 AND idPais = ".$idPais."
				 AND idUF = ".$idUF." 
				 AND idMunicipioIBGE = ".$idMunicipio."
				 AND stAbrangencia = 1";
        
        $db  = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);
		return $db->fetchAll($sql);

    }

    /**
     * Grava registro. Se seja passado um ID ele altera um registro existente
     * @param array $dados - array com dados referentes as colunas da tabela no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @return ID do registro inserido/alterado ou FALSE em caso de erro
     */
    public function salvar($dados)
    {
        
        //INSTANCIANDO UM OBJETO DE ACESSO AOS DADOS DA TABELA
        $tblAbrangencia = new Abrangencia();
        
        //DECIDINDO SE INCLUI OU ALTERA UM REGISTRO
        $dados['stAbrangencia'] = 1;
        if(isset($dados['idAbrangencia']) && !empty ($dados['idAbrangencia'])){
            //UPDATE
            $rsAbrangencia = $tblAbrangencia->find($dados['idAbrangencia'])->current();
        }else{
            //INSERT
            $dados['idAbrangencia'] = null;
            return $tblAbrangencia->insert($dados);
            //$rsAbrangencia = $tblAbrangencia->createRow();
        }
        
        //ATRIBUINDO VALORES AOS CAMPOS QUE FORAM PASSADOS
        if(!empty($dados['idProjeto']))       { $rsAbrangencia->idProjeto = $dados['idProjeto']; }
        if(!empty($dados['idPais']))          { $rsAbrangencia->idPais = $dados['idPais']; }
        $rsAbrangencia->idUF = $dados['idUF']; //if(!empty($dados['idUF'])) { $rsAbrangencia->idUF = $dados['idUF']; }
        $rsAbrangencia->idMunicipioIBGE = $dados['idMunicipioIBGE'];//if(!empty($dados['idMunicipioIBGE'])) { $rsAbrangencia->idMunicipioIBGE = $dados['idMunicipioIBGE']; }
        if(!empty($dados['Usuario']))         { $rsAbrangencia->Usuario = $dados['Usuario']; }
		$rsAbrangencia->stAbrangencia = 1;

        //SALVANDO O OBJETO
        $id = $rsAbrangencia->save();

        if($id){
            return $id;
        }else{
            return false;
        }
    }

    /**
     * Apaga registro do banco
     * @param number $idAbrangencia - ID do registro que deve ser apagado
     * @return true or false
     */
    public function excluir($idAbrangencia)
    {
        $sql ="DELETE FROM SAC.dbo.Abrangencia WHERE idAbrangencia = ".$idAbrangencia;
        
        $db  = Zend_Registry::get('db');
	$db->setFetchMode(Zend_DB::FETCH_OBJ);
        if($db->query($sql)){
            return true;
        }else{
            return false;
        }

    }

    /**
     * Apaga locais de ralizacao a partir do ID do PreProjeto 
     * @param number $idProjeto - ID do PerProjeto ao qual as lcoalizações estão vinculadas
     * @return true or false
     */
    public function excluirPeloProjeto($idProjeto)
    {
        $sql ="DELETE FROM SAC.dbo.Abrangencia WHERE idProjeto = ".$idProjeto . " AND stAbrangencia = 1";
        
        $db  = Zend_Registry::get('db');
	$db->setFetchMode(Zend_DB::FETCH_OBJ);
	//xd($sql);
	if($db->query($sql)){
            return true;
        }else{
            return false;
        }

    }


    public function abrangenciaProjeto($retornaSelect = false){

        $selectAbrangencia = $this->select();
        $selectAbrangencia->setIntegrityCheck(false);
        $selectAbrangencia->from(
                        array($this->_name),
                        array(
                                'idAbrangencia'=>new Zend_Db_Expr('min(idAbrangencia)'),
                                'idProjeto',
                                'idUF',
                                'idMunicipioIBGE'
                             )
                     );
        $selectAbrangencia->group('idProjeto');
        $selectAbrangencia->group('idUF');
        $selectAbrangencia->group('idMunicipioIBGE');


        if($retornaSelect)
            return $selectAbrangencia;
        else
            return $this->fetchAll($selectAbrangencia);

    }

    public function abrangenciaProjetoPesquisa($retornaSelect = false,$where = array()){

        $selectAbrangencia = $this->select();
        $selectAbrangencia->setIntegrityCheck(false);
        $selectAbrangencia->from(
                        array('abr'=>$this->_name),
                        array(
                                'idAbrangencia'=>new Zend_Db_Expr('min(idAbrangencia)'),
                                'idProjeto'
                             )
                     );


        $selectAbrangencia->joinInner(
                            array('mun'=>'Municipios'),
                            "mun.idUFIBGE = abr.idUF and mun.idMunicipioIBGE = abr.idMunicipioIBGE",
                            array(),
                            'AGENTES.dbo'
                          );
        $selectAbrangencia->joinInner(
                            array('uf'=>'UF'),
                            "uf.idUF = abr.idUF",
                            array(),
                            'AGENTES.dbo'
                );
		$selectAbrangencia->where('abr.stAbrangencia = ?', 1);

        foreach ($where as $coluna => $valor) {
            $selectAbrangencia->where($coluna, $valor);
        }

        $selectAbrangencia->group('idProjeto');


        if($retornaSelect)
            return $selectAbrangencia;
        else
            return $this->fetchAll($selectAbrangencia);

    }
}
?>
