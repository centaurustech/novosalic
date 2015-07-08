<?php
/**
 * Description of tbDocumentosPreProjeto
 *
 * @author Danilo Lisboa
 */
class tbDocumentosPreProjeto  extends GenericModel {
     protected $_banco   = "SAC";
     protected $_schema  = "dbo";
     protected $_name = 'tbDocumentosPreProjeto';


    /**
     * Retorna registros do banco de dados
     * @param array $where - array com dados where no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @param array $order - array com orders no formado "coluna_1 desc","coluna_2"...
     * @param int $tamanho - numero de registros que deve retornar
     * @param int $inicio - offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function buscarDocumentos($where=array(), $order=array(), $tamanho=-1, $inicio=-1) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
            array("a"=>$this->_name),
            array("CodigoDocumento", new Zend_Db_Expr("2 as tpDoc"), 'idProjeto as Codigo', 'Data', 'imDocumento', 'NoArquivo', 'TaArquivo', 'idDocumentosPreprojetos')
        );
        $slct->joinInner(
            array("b"=>"DocumentosExigidos"), "a.CodigoDocumento = b.Codigo",
            array("Descricao"), "SAC.dbo"
        );
        
        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $slct->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $slct->limit($tamanho, $tmpInicio);
        }
        //xd($slct->__toString());
        return $this->fetchAll($slct);
    }

    /**
     * Retorna registros do banco de dados
     * @param array $where - array com dados where no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @param array $order - array com orders no formado "coluna_1 desc","coluna_2"...
     * @param int $tamanho - numero de registros que deve retornar
     * @param int $inicio - offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function abrir($id) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);

        $slct->from(
                array("a"=>$this->_name),
                array("NoArquivo", "imDocumento")
        );

        $slct->where("idDocumentosPreprojetos = ?", $id);

        //xd($slct->__toString());
        //$this->fetchAll("SET TEXTSIZE 10485760;");
        $db = $this->getDefaultAdapter();
        $db->fetchAll("SET TEXTSIZE 10485760;");
        return $this->fetchAll($slct);
    }
}
?>
