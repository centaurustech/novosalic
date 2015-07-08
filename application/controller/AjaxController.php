<?php

class AjaxController extends GenericControllerNew {

    public function municipioAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $iduf = $_POST['iduf'];
        try {
            $municipio = new Municipios();
            $buscarmunicipio = $municipio->buscar(array('idUFIBGE = ?' => $iduf));

            $municipio = array();
            $cont = 0;
            foreach ($buscarmunicipio as $dadosmunicipio) {
                $municipio[$cont]['idmun'] = $dadosmunicipio->idMunicipioIBGE;
                $municipio[$cont]['descmun'] = utf8_encode($dadosmunicipio->Descricao);
                $cont++;
            }
            echo json_encode($municipio);
            die;
        } catch (Zend_Exception $e) {
            echo json_encode(array('error' => 'true'));
            die;
        }
    }

    public function agentesAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $idOrgao = $_POST['idorgao'];

        $u = new Usuariosorgaosgrupos();
        $buscarAgentes = $u->buscardadosAgentes($idOrgao, 121);

        $dadosagente = array();
        $cont = 0;
        foreach ($buscarAgentes as $dados) {
            $dadosagente[$cont]['usu_codigo'] = $dados->usu_codigo;
            $dadosagente[$cont]['usu_nome'] = utf8_encode($dados->usu_nome);
            $dadosagente[$cont]['perfil'] = utf8_encode($dados->perfil);
            $dadosagente[$cont]['gru_codigo'] = utf8_encode($dados->gru_codigo);
            $dadosagente[$cont]['idAgente'] = $dados->idAgente;
            $cont++;
        }
        if (count($dadosagente) > 0) {
            echo json_encode($dadosagente);
        } else {
            echo json_encode(array('error'=>true));
        }
        die;
    }

    public function fundoClassificacaoAction(){
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $id = $_POST["id"];
        $tbl = new tbClassificaDocumento();
        $rs = $tbl->fundoSetorialXClassificacao(array("f.stModalidadeDocumento is not null"=>"", "cdTipoFundo = ?"=>$id), array("dsClassificaDocumento ASC"));

        try{
            $dados = array();
            $cont = 0;
            foreach ($rs as $dado) {
                $dados[$cont]['id'] = $dado->idClassificaDocumento;
                $dados[$cont]['desc'] = utf8_encode($dado->dsClassificaDocumento);
                $cont++;
            }
            //xd($dados);
            echo json_encode($dados);
            die;
        } catch (Zend_Exception $e) {
            echo json_encode(array('error' => 'true'));
            die;
        }

    }

    public function classificacaoEditalAction(){
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $id = $_POST["id"];
        $tbl = new tbFormDocumento();
        $rs = $tbl->buscar(array("idClassificaDocumento = ?"=>$id),array("nmFormDocumento ASC"));

        try{
            $dados = array();
            $cont = 0;
            foreach ($rs as $dado) {
                $dados[$cont]['id'] = $dado->idEdital;
                $dados[$cont]['desc'] = utf8_encode($dado->nmFormDocumento);
                $cont++;
            }
            //xd($_POST);
            //$dados = array_unique($dados);
            echo json_encode($dados);
            die;
        } catch (Zend_Exception $e) {
            echo json_encode(array('error' => 'true'));
            die;
        }

    }

}

?>