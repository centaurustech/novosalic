<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class tbDocumentosAgentes extends GenericModel {

    protected $_banco = 'SAC';
    protected $_name  = 'tbDocumentosAgentes';


    public function buscarDocumentos($where=array(), $order=array(), $tamanho=-1, $inicio=-1) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
            array("a"=>$this->_name),
            array("CodigoDocumento", new Zend_Db_Expr("1 as tpDoc"), 'idAgente as Codigo', 'Data', 'imDocumento', 'NoArquivo', 'TaArquivo', 'idDocumentosAgentes')
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
    
    //Essa consulta nao possui o dado binário do arquivo. Somente os demais dados do arquivo!
    public function buscarDadosDocumentos($where=array(), $order=array(), $tamanho=-1, $inicio=-1) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
            array("a"=>$this->_name),
            array("CodigoDocumento", new Zend_Db_Expr("1 as tpDoc"), 'idAgente as Codigo', 'Data', 'NoArquivo', 'TaArquivo', 'idDocumentosAgentes')
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

    public function buscatodosdocumentos($idAgente,$idProjeto,$idPronac,$nome=0,$codigo=0,$order=array(), $tamanho=-1, $inicio=-1){

        //seleciona todos os arquivos referente ao proponente do projeto
        $slct1 = $this->select();
        $slct1->setIntegrityCheck(false);
        $slct1->from(
                array('d' => $this->_name),
                array(
                 "d.CodigoDocumento as CodigoDocumento",
                 //'"Anexado pelo Proponente" as Classificacao',
                 "d.idAgente as Codigo",
                 "Data",
                 "NoArquivo",
                 "TaArquivo",
                 "idDocumentosAgentes"),
                array()
        );

        $slct1->joinInner(
                array('e' => 'DocumentosExigidos'),
                'e.Codigo = d.CodigoDocumento',
                array('Descricao',new Zend_Db_Expr("'1' as AgenteDoc, '' as ProjetoDoc"))
        );

        $slct1->where("d.idagente = ".$idAgente);

        
        //seleciona todos os arquivos referente ao projeto -- Para arquivos anexados da forma antiga
        $slct2 = $this->select();
        $slct2->setIntegrityCheck(false);
        $slct2->from(
                array('d' => 'tbDocumentosPreProjeto'),
                array(
                    "d.CodigoDocumento as CodigoDocumento",
                    //"(Anexado pelo Proponente as Classificacao)",
                    "idProjeto as Codigo",
                    "Data",
                    "NoArquivo",
                    "TaArquivo",
                    "idProjeto as idDocumentosAgentes"),
                array()
        );

        

        $slct2->joinInner(
                array('e' => 'DocumentosExigidos'),
                'e.Codigo = d.CodigoDocumento',
                array('Descricao',new Zend_Db_Expr("'1' as AgenteDoc, '' as ProjetoDoc"))
        );

        $slct2->where("idProjeto = ".$idProjeto);


        //seleciona todos os arquivos anexados pelo minc ao projeto -- Para arquivos anexados da forma antiga
        $slct3 = $this->select();
        $slct3->setIntegrityCheck(false);
        $slct3->from(
                array('d' => 'tbDocumento'),
                array(
                    "d.idTipoDocumento as CodigoDocumento",
                    "d.idPronac as Codigo",
                    "d.dtDocumento as Data",
                    "d.NoArquivo",
                    "d.TaArquivo",
                    "d.idDocumento as idDocumentosAgentes"),
                array()
        );


        $slct3->joinInner(
                array('e' => 'tbTipoDocumento'),
                'd.idTipoDocumento = e.idTipoDocumento',
                array('e.dsTipoDocumento as Descricao', new Zend_Db_Expr("'' as AgenteDoc, '1' as ProjetoDoc"))
        );

        $slct3->where("d.idPronac = ".$idPronac);


        //seleciona todos os arquivos anexados pelo minc ao projeto no Justificativa de Manuntenç?o
        $slct4 = $this->select();
        $slct4->setIntegrityCheck(false);
        $slct4->from(
                array('d' => 'tbHistoricoAlteracaoDocumento'),
                array(
                    "d.idDocumentosExigidos as CodigoDocumento"
                    //"'Anexados no Manuntenç?o'",
                    ),
                array()
        );


        $slct4->joinInner(
                array('e1' => 'tbHistoricoAlteracaoProjeto'),
                'd.idHistoricoAlteracaoProjeto = e1.idHistoricoAlteracaoProjeto',
                array('e1.idPRONAC AS Codigo')
        );
        $slct4->joinInner(
                array('e2' => 'tbDocumento'),
                'd.idDocumento = e2.idDocumento',
                array(),
                'BDCORPORATIVO.scCorp'
        );
        $slct4->joinInner(
                array('a' => 'tbArquivo'),
                'a.idArquivo = e2.idArquivo',
                array('a.dtEnvio as Data', 'a.nmArquivo as NoArquivo', 'a.nrTamanho as TaArquivo', 'a.idArquivo  as idDocumentosAgentes'),
                'BDCORPORATIVO.scCorp'
        );

        $slct4->joinInner(
                array('e3' => 'tbArquivoImagem'),
                'a.idArquivo = e3.idArquivo',
                array(''),
                'BDCORPORATIVO.scCorp'
        );
        



        $slct4->joinLeft(
                array('E' => 'tbTipoDocumento'),
                'e2.idTipoDocumento = E.idTipoDocumento',
                array('E.dsTipoDocumento as Descricao'),
                'BDCORPORATIVO.scCorp'
        );

        $slct4->joinLeft(
                array('ArqAg' => 'tbDocumentoAgente'),
                'ArqAg.idDocumento = e2.idDocumento',
                array("ArqAg.idAgente as AgenteDoc"),
                'BDCORPORATIVO.scCorp'
        );

        $slct4->joinLeft(
                array('ArqPr' => 'tbDocumentoProjeto'),
                'ArqPr.idDocumento = e2.idDocumento',
               array("ArqPr.idPronac as ProjetoDoc"),
                'BDCORPORATIVO.scCorp'
        );

        $slct4->where("e1.idPronac = ".$idPronac);



        //seleciona todos os arquivos do projeto
        $slct5 = $this->select();
        $slct5->setIntegrityCheck(false);
        $slct5->from(
                array('d' => 'tbDocumentoProjeto'),
                array(
                    "d.idTipoDocumento AS CodigoDocumento"
                    //"'Anexados no Manuntenç?o'",
                    ),
                'BDCORPORATIVO.scCorp'
        );


        $slct5->joinInner(
                array('e1' => 'Projetos'),
                'd.idPRONAC = e1.idPronac',
                array('e1.idPRONAC AS Codigo')
        );
        $slct5->joinInner(
                array('e2' => 'tbDocumento'),
                'd.idDocumento = e2.idDocumento',
                array(),
                'BDCORPORATIVO.scCorp'
        );
        $slct5->joinInner(
                array('a' => 'tbArquivo'),
                'a.idArquivo = e2.idArquivo',
                array('a.dtEnvio as Data', 'a.nmArquivo as NoArquivo', 'a.nrTamanho as TaArquivo', 'a.idArquivo  as idDocumentosAgentes'),
                'BDCORPORATIVO.scCorp'
        );

        $slct5->joinInner(
                array('e3' => 'tbArquivoImagem'),
                'a.idArquivo = e3.idArquivo',
                array(''),
                'BDCORPORATIVO.scCorp'
        );


        $slct5->joinLeft(
                array('E' => 'tbTipoDocumento'),
                'e2.idTipoDocumento = E.idTipoDocumento',
                array('E.dsTipoDocumento as Descricao'),
                'BDCORPORATIVO.scCorp'
        );

        $slct5->joinLeft(
                array('ArqAg' => 'tbDocumentoAgente'),
                'ArqAg.idDocumento = e2.idDocumento',
                array("ArqAg.idAgente as AgenteDoc"),
                'BDCORPORATIVO.scCorp'
        );

        $slct5->joinLeft(
                array('ArqPr' => 'tbDocumentoProjeto'),
                'ArqPr.idDocumento = e2.idDocumento',
               array("ArqPr.idPronac as ProjetoDoc"),
                'BDCORPORATIVO.scCorp'
        );

        $slct5->where("d.idPronac = ".$idPronac);

           
        //Une todas os Selects
        $slct = $this->select();

        if(isset($idProjeto)){
            $slct->union(array($slct1, $slct2, $slct3, $slct4, $slct5));
        }else{
            $slct->union(array($slct1, $slct3, $slct4, $slct5));
        }

        $slctMaster = $this->select();
        $slctMaster->setIntegrityCheck(false);
        $slctMaster->from(
                        array('Master'=>$slct),
                        array('*')
                     );

        //adicionando linha order ao select
        $slctMaster->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $slctMaster->limit($tamanho, $tmpInicio);
        }
        
        //xd($slct->__toString());
        //xd(str_replace('"',"",$slct->assemble()));
        //xd($slctMaster->assemble());
        return $this->fetchAll($slctMaster);



    }


    public function abrir($id) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);

        $slct->from(
                array("a"=>$this->_name),
                array("NoArquivo", "imDocumento")
        );

        $slct->where("idDocumentosAgentes = ?", $id);

        //xd($slct->__toString());
        //$this->fetchAll("SET TEXTSIZE 10485760;");
        $db = $this->getDefaultAdapter();
        $db->fetchAll("SET TEXTSIZE 10485760;");
        return $this->fetchAll($slct);
    }



    }


?>
