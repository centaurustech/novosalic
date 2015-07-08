<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mantertermodecisao
 *
 * @author Tiago
 */
class MantertermodecisaoController extends GenericControllerNew {

    /**
     * Reescreve o método init()
     * @access public
     * @param void
     * @return void
     */
    public function init() {
        // verifica as permissões
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = 97; // Gestor Salic

        parent::perfil(1, $PermissoesGrupo);

        parent::init();

        // cria a sessao com o grupo ativo
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo');
        $this->codGrupo = $GrupoAtivo->codGrupo;
    }

    public function indexAction() {

        $tbModeloTermoDecisao = New tbModeloTermoDecisao();
        $buscarTermoDecisao = $tbModeloTermoDecisao->buscarTermoDecisao();


        $this->view->termo = $buscarTermoDecisao;
    }

    public function termodecisaoAction() {

             
        $tipoTermo = $this->_request->getParam("tipoTermo");
        $tipoParecer = $this->_request->getParam("tipoParecer");
        $orgao = $this->_request->getParam("orgao");
        $tipoTermo = (int) $tipoTermo;
        $tipoParecer = (int) $tipoParecer;
        
        $msg = $this->_request->getParam("msg");
        $tipoMsg = $this->_request->getParam("tipoMsg");
        $this->view->msg = $msg;
        $this->view->tipoMsg = $tipoMsg;
        
        $verificacao = New Verificacao();
        $buscaTipoRecurso = $verificacao->buscar(array('idVerificacao = ?' => $tipoTermo));
        
        $this->view->listaTipoRecurso = $buscaTipoRecurso;
        $this->view->sefic = "";
        $this->view->sav = "";

        if (empty($_POST)) {
            $this->view->validar = "s";
        } else {
            $this->view->validar = "n";

            $orgao = $this->_request->getParam("orgao");

            if (!empty($orgao)) {
                if ($orgao == "sefic" || $orgao == 251) {
                    $idOrgao = 251;
                    $this->view->sefic = "true";
                    $this->view->sav = "false";
                } else {
                    $idOrgao = 160;
                    $this->view->sefic = "false";
                    $this->view->sav = "true";
                }
            }

            $tbModeloTermoDecisao = New tbModeloTermoDecisao();
            $buscarTermoDecisao = $tbModeloTermoDecisao->buscarTermoDecisao(array('o.Codigo = ?' => $idOrgao, 'idVerificacao = ?' => $tipoTermo, 'stModeloTermoDecisao = ?' => $tipoParecer));

            if (!empty($buscarTermoDecisao[0])) {
                $this->view->idtermo = $buscarTermoDecisao[0]->idModeloTermoDecisao;
                $this->view->idorgao = $buscarTermoDecisao[0]->Codigo;
                $this->view->sigla = $buscarTermoDecisao[0]->Sigla;
                $this->view->termo = $buscarTermoDecisao[0]->idVerificacao;
                $this->view->parecer = $buscarTermoDecisao[0]->stModeloTermoDecisao;
                $this->view->texto = $buscarTermoDecisao[0]->meModeloTermoDecisao;
            } else {
                $this->view->incluir = "";
                $this->view->termo = $tipoTermo;
                $this->view->parecer = $tipoParecer;
                $this->view->texto = "Digite o texto do Termo de Decisão.";
            }
        }
    }
    
    public function incluirtermodecisaoAction() {

        $tipoTermo = $this->_request->getParam("tipoTermo");
        $tipoParecer = $this->_request->getParam("tipoParecer");
        $orgao = $this->_request->getParam("orgao");
        $tipoTermo = (int) $tipoTermo;
        $tipoParecer = (int) $tipoParecer;
        
        $verificacao = New Verificacao();
        $buscaTipoRecurso = $verificacao->buscar(array('idTipo = ?' => Constantes::cteIdTipoTermoDecisao));

        $this->view->listaTipoRecurso = $buscaTipoRecurso;

        if (empty($_POST)) {
            $this->view->validar = "s";
        } else {
            $this->view->validar = "n";

            if (!empty($orgao)) {
                if ($orgao == "sefic") {
                    $idOrgao = 251;
                    $this->view->sefic = "true";
                    $this->view->sav = "false";
                } else {
                    $idOrgao = 160;
                    $this->view->sefic = "false";
                    $this->view->sav = "true";
                }
            }

            $tbModeloTermoDecisao = New tbModeloTermoDecisao();
            $buscarTermoDecisao = $tbModeloTermoDecisao->buscarTermoDecisao(array('o.Codigo = ?' => $idOrgao, 'idVerificacao = ?' => $tipoTermo, 'stModeloTermoDecisao = ?' => $tipoParecer));

            if (!empty($buscarTermoDecisao[0])) {
                $this->view->idtermo = $buscarTermoDecisao[0]->idModeloTermoDecisao;
                $this->view->idorgao = $buscarTermoDecisao[0]->Codigo;
                $this->view->sigla = $buscarTermoDecisao[0]->Sigla;
                $this->view->termo = $buscarTermoDecisao[0]->idVerificacao;
                $this->view->parecer = (int) $buscarTermoDecisao[0]->stModeloTermoDecisao;
                $this->view->texto = $buscarTermoDecisao[0]->meModeloTermoDecisao;
            } else {
                $this->view->incluir = "";
                $this->view->termo = $tipoTermo;
                $this->view->parecer = (int) $tipoParecer;
                $this->view->texto = "Digite o Termo de Decisão.";
            }
        }
    }

    public function salvatermoAction() {

        $orgao = $this->_request->getParam("orgao");
        $tipoTermo = $this->_request->getParam("tipoTermo");
        $tipoParecer = $this->_request->getParam("tipoParecer");
        $dsTermoDecisao = $this->_request->getParam("dsTermoDecisao");
        $idTermo = $this->_request->getParam("idTermo");
        $tbModeloTermoDecisao = New tbModeloTermoDecisao();

        if(!empty($orgao) || !empty($tipoParecer)){
            
            try{
                //atualiza termo de decisao
                if (!empty($idTermo)) {

                    $rsTermoDecisao = $tbModeloTermoDecisao->buscar(array("idModeloTermoDecisao = ?" => $idTermo))->current();
                    $rsTermoDecisao->idOrgao = $orgao;
                    $rsTermoDecisao->idVerificacao = $tipoTermo;
                    $rsTermoDecisao->stModeloTermoDecisao = $tipoParecer;
                    $rsTermoDecisao->meModeloTermoDecisao = $dsTermoDecisao;
                    $rsTermoDecisao->save();
                    $msg = "Alteração realizada com sucesso";
                } else { //insere termo de decisao

                    $dados = array(// insert
                        'idOrgao' => $orgao,
                        'idVerificacao' => $tipoTermo,
                        'stModeloTermoDecisao' => $tipoParecer,
                        'meModeloTermoDecisao' => $dsTermoDecisao);

                    $salvarTermoDecisao = $tbModeloTermoDecisao->inserir($dados);
                    $msg = "Cadastro realizado com sucesso";
                }
                $tipoMsg = "CONFIRM";
            }catch (Exception $e){
                $msg = "Erro ao realizar opera&ccedil;&atilde;o. ".$e->getMessage();
                $tipoMsg = "ERROR";
            }
            $this->_forward("termodecisao",null,null,array('msg'=>$msg,'tipoMsg'=>$tipoMsg));
        }else{
            $this->_forward("index");
        }
        //parent::message("{$acao} com sucesso! ", "mantertermodecisao/index", "CONFIRM");
    }

    public function verificartermoAction() {

        $orgao = $this->_request->getParam("orgao");
        $tipoTermo = $this->_request->getParam("tipoTermo");
        $tipoParecer = $this->_request->getParam("tipoParecer");
        
        $orgao = (int) $orgao;
        $tipoTermo = (int) $tipoTermo;
        $tipoParecer = (int) $tipoParecer;

        $tbModeloTermoDecisao = New tbModeloTermoDecisao();
        $buscarTermoDecisao = $tbModeloTermoDecisao->buscar(array('idOrgao = ?' => $orgao, 'idVerificacao = ?' => $tipoTermo, 'stModeloTermoDecisao = ?' => $tipoParecer));
        $verificaTermoDecisao = $buscarTermoDecisao[0];

        if (!empty($verificaTermoDecisao)) {
            $result['existe'] = true;
            echo json_encode($result);
            exit();
        } else {
            $result['existe'] = false;
            echo json_encode($result);
            exit();
        }
    }

}

