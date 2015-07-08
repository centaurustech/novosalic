<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 * Description of Orgaos
 * @author augusto
 */
class Orgaos extends GenericModel{

    protected $_banco = 'SAC';
    protected $_name  = 'Orgaos';

    public function pesquisarTodosOrgaos() {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->distinct();
        $select->from(
                array('o'=>$this->_name),
                array(
                'o.Codigo',
                'Tabelas.dbo.fnEstruturaOrgao(o.codigo, 0) as Sigla',
                'org.org_nomeautorizado'
                )
        );
        $select->joinInner(
                array('org'=>'vwUsuariosOrgaosGrupos'),
                'org.uog_orgao = o.Codigo ',
                array('org.org_nomeautorizado'),
                'Tabelas.dbo'
        );
        $select->where('o.Status = ?', 0);
        $select->where('o.idSecretaria IS NOT NULL');
//        $select->order('o.Codigo ASC');
        $select->order('2');

        return $this->fetchAll($select);
    }
    
    public function pesquisarNomeOrgao($codOrgao){
    	$select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                        array('o'=>$this->_name),
                        array(
                                'o.Codigo',
                                'o.Sigla as NomeOrgao'
                             )
                     );
        $select->where("o.Codigo = ?", $codOrgao);

       return $this->fetchAll($select);
    }

    public function codigoOrgaoSuperior($codOrgao){
    	$select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('o'=>$this->_name),
                      array('o.Codigo',
                            'o.Sigla',
                            'o.idSecretaria as Superior')
		);
        
		$select->where("o.Codigo = ?", $codOrgao);

       return $this->fetchAll($select);
    }


    /**
     * Retorna registros do banco de dados
     * @param array $where - array com dados where no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @param array $order - array com orders no formado "coluna_1 desc","coluna_2"...
     * @param int $tamanho - numero de registros que deve retornar
     * @param int $inicio - offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
//    public function buscarOrgaoPorSegmento($area, $segmento)
//    {
//        $slct = $this->select();
//        $slct->setIntegrityCheck(false);
//        $slct->from(
//                    array("o"=>$this->_name),
//                    array("Codigo", "Sigla")
//                    );
//        $slct->joinInner(
//                        array("se"=>"Segmento"),
//                        "o.Codigo = se.idOrgao",
//                        array(),
//                        "SAC.dbo"
//                        );
//        $slct->joinInner(
//                        array("a"=>"Area"),
//                        "a.Codigo = substring(se.Codigo, 1, 1)",
//                        array(),
//                        "SAC.dbo"
//                        );
//
//        //adiciona quantos filtros foram enviados
//        $slct->where("se.stEstado = ?", 1);
//        $slct->where("se.Codigo = ?", $area);
//        $slct->orWhere("se.Codigo = ?", $segmento);
//        //xd($slct->assemble());
//        return $this->fetchAll($slct);
//    }

    /**
     * Retorna registros do banco de dados
     * @param array $where - array com dados where no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @param array $order - array com orders no formado "coluna_1 desc","coluna_2"...
     * @param int $tamanho - numero de registros que deve retornar
     * @param int $inicio - offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function buscarOrgaoPorSegmento($segmento)
    {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                    array("o"=>$this->_name),
                    array("Codigo", "Sigla")
                    );
        $slct->joinInner(
                        array("se"=>"Segmento"),
                        "o.Codigo = se.idOrgao",
                        array(),
                        "SAC.dbo"
                        );

        //adiciona quantos filtros foram enviados
        //$slct->where("se.stEstado = ?", 1);
        $slct->where("se.Codigo = ?", $segmento);
        //xd($slct->assemble());
        return $this->fetchAll($slct);
    }
}
?>
