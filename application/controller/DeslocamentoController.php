<?php
require_once "GenericControllerNew.php";

class DeslocamentoController extends GenericControllerNew {

    private $idPreProjeto = null;

    /**
     * Reescreve o método init()
     * @access public
     * @param void
     * @return void
     */
    public function init() {
        $auth = Zend_Auth::getInstance(); // instancia da autenticaç?o
        $PermissoesGrupo = array();

	//Da permissao de acesso a todos os grupos do usuario logado afim de atender o UC75
        if(isset($auth->getIdentity()->usu_codigo)){
            //Recupera todos os grupos do Usuario
            $Usuario = new Usuario(); // objeto usuário
            $grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);
            foreach ($grupos as $grupo){
                $PermissoesGrupo[] = $grupo->gru_codigo;
            }
        }

        isset($auth->getIdentity()->usu_codigo) ? parent::perfil(1, $PermissoesGrupo) : parent::perfil(4, $PermissoesGrupo);

        // pega o idAgente do usuário logado
        $auxUsuario = isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_codigo : $auth->getIdentity()->IdUsuario;
        $this->getIdUsuario = UsuarioDAO::getIdUsuario($auxUsuario);

        if ($this->getIdUsuario) {
            $this->getIdUsuario = $this->getIdUsuario["idAgente"];
        }
        else {
            $this->getIdUsuario = 0;
        }

        $this->view->comboestados = Estado::buscar();
        $this->view->paises = DeslocamentoDAO::buscarPais();

        parent::init();

        //recupera ID do pre projeto (proposta)
        if(!empty ($_REQUEST['idPreProjeto'])) {
            $this->idPreProjeto = $_REQUEST['idPreProjeto'];
            //VERIFICA SE A PROPOSTA ESTA COM O MINC
            $Movimentacao = new Movimentacao();
            $rsStatusAtual = $Movimentacao->buscarStatusAtualProposta($_REQUEST['idPreProjeto']);
            $this->view->movimentacaoAtual = $rsStatusAtual->Movimentacao;
        }else {
            if($_REQUEST['idPreProjeto'] != '0'){
                parent::message("Necessário informar o número da proposta.", "/manterpropostaincentivofiscal/index", "ERROR");
            }
        }

    }

    public function indexAction() {
        if (empty ( $_GET['verifica'] ))
        {
            $this->_helper->layout->disableLayout();
        }
        if($_GET) {
            $id = null;

            if(!empty($_GET['id'])) {
                $id = $_GET['id'];
            }

            $idPreProjeto = $this->idPreProjeto;

            $dados = DeslocamentoDAO::buscarDeslocamentos($idPreProjeto, $id);

            if($id) {
                foreach($dados as $d) {
                    $idPaisO 		= $d->idPaisOrigem;
                    $idUFO		= $d->idUFOrigem;
                    $idCidadeO 		= $d->idMunicipioOrigem;
                    $idPaisD		= $d->idPaisDestino;
                    $idUFD		= $d->idUFDestino;
                    $idCidadeD 		= $d->idMunicipioDestino;
                    $Qtde  		= $d->Qtde;
                }

                $this->view->combocidadesO = Cidade::buscar($idUFO);
                $this->view->combocidadesD = Cidade::buscar($idUFD);

                $this->view->idPaisO 	= $idPaisO;
                $this->view->idPaisD 	= $idPaisD;
                $this->view->idUFO 	= $idUFO;
                $this->view->idUFD 	= $idUFD;
                $this->view->idCidadeO 	= $idCidadeO;
                $this->view->idCidadeD 	= $idCidadeD;
                $this->view->Qtde 	= $Qtde;
                $this->view->idDeslocamento = $id;
            }

            $this->view->idPreProjeto	= $idPreProjeto;
            $this->view->deslocamentos = DeslocamentoDAO::buscarDeslocamentos($idPreProjeto, null);
        }

        //Zend_Debug::dump(Cidade::buscar($idO));
        //exit();
    }

    public function salvarAction() {

        $post = Zend_Registry::get('post');

        $idPreProjeto	= $post->idPreProjeto;
        $idDeslocamento = $post->idDeslocamento;
        $paisOrigem 	= $post->paisOrigem;
        $paisDestino 	= $post->paisDestino;
        $uf 		= $post->uf;
        $ufD 		= $post->ufD;
        $cidade	 	= $post->cidade;
        $cidadeD 	= $post->cidadeD;
        $quantidade 	= $post->quantidade;

        if(!$uf) {
            $uf = 0;
        }
        if(!$ufD) {
            $ufD = 0;
        }
        if(!$cidade) {
            $cidade = 0;
        }
        if(!$cidadeD) {
            $cidadeD = 0;
        }

        $dados = array(
                'idProjeto' 		=> $idPreProjeto,
                'idPaisOrigem' 		=> $paisOrigem,
                'idUFOrigem' 		=> $uf,
                'idMunicipioOrigem' 	=> $cidade,
                'idPaisDestino' 	=> $paisDestino,
                'idUFDestino' 		=> $ufD,
                'idMunicipioDestino'    => $cidadeD,
                'Qtde' 			=> $quantidade,
                'idUsuario' 		=> $this->getIdUsuario
        );

            $deslocamentos = DeslocamentoDAO::buscarDeslocamentosGeral(array("de.idPaisOrigem = "=>$dados["idPaisOrigem"],"de.idPaisDestino = "=>$dados["idPaisDestino"],
            "de.idMunicipioOrigem = "=>$dados["idMunicipioOrigem"],"de.idMunicipioDestino = "=>$dados["idMunicipioDestino"], "de.idProjeto = "=>$idPreProjeto, "de.Qtde = "=>$dados["Qtde"]));

            if(!empty($deslocamentos)){
                parent::message("Trecho j&aacute; cadastrado, transa&ccedil;&atilde;o cancelada!", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "ALERT");
                die;
            }

        $db = Zend_Db_Table::getDefaultAdapter();
        $db->beginTransaction();

        try {
            if($idDeslocamento == '') {
                $salvar   = DeslocamentoDAO::salvaDeslocamento($dados);
                $db->commit();
                parent::message("Cadastro realizado com sucesso!", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "CONFIRM");
            }
            else {
                $atualizaaliza = DeslocamentoDAO::atualizaDeslocamento($paisOrigem,$uf,$cidade,$paisDestino,$ufD,$cidadeD,$quantidade,$idDeslocamento);
                $db->commit();
                parent::message("Altera&ccedil;&atilde;o realizada com sucesso!", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "CONFIRM");
            }

        }catch(Zend_Exception $ex) {
            $db->rollback();
            echo $ex->getMessage();
        }
        parent::message("N&atilde;o foi poss&iacute;vel realizar a opera&ccedil;&atilde;o! <br>", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "ERROR");
        //$this->_redirect("deslocamento/index?idPreProjeto=".$idPreProjeto);

    }

    public function excluirAction() {
        if($_GET['id']) {
            try {
                $excluir = DeslocamentoDAO::excluiDeslocamento($_GET['id']);

                parent::message("Exclusão realizada com sucesso!", "/localderealizacao/index?idPreProjeto=".$this->idPreProjeto.$edital, "CONFIRM");

            }catch(Zend_Exception $ex) {
                $this->view->message      = $e->getMessage();
                $this->vies->message_type = "ERROR";
            }
        }
        $this->_redirect("localderealizacao\index");
    }

    public function alterarAction() {
        $id = isset($_GET['id']);
        $idPreProjeto = $this->idPreProjeto;
        $this->_redirect("localderealizacao\index?idPreProjeto=".$idPreProjeto."&id=".$id);
    }

    public function consultarcomponenteAction() {
        $this->_helper->layout->disableLayout(); // desabilita o layout
        if(!empty($this->idPreProjeto)) {
            $this->view->deslocamentos = DeslocamentoDAO::buscarDeslocamentos($this->idPreProjeto, null);
        } else {
            die;
        }
    }



}