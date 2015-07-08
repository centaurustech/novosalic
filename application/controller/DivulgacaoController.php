<?php 
/**
 * Controller Divulgação
 * @author Equipe RUP - Politec
 * @since 15/12/2010
 * @version 1.0
 * @package application
 * @subpackage application.controller
 * @link http://www.cultura.gov.br
 * @copyright ï¿½ 2010 - Ministï¿½rio da Cultura - Todos os direitos reservados.
 */

require_once "GenericControllerNew.php";

class DivulgacaoController extends GenericControllerNew {

    private $idPreProjeto =  null;
    private $idUsuario =  null;
    /**
     * Reescreve o método init()
     * @access public
     * @param void
     * @return void
     */
    public function init() {

        $auth = Zend_Auth::getInstance(); // instancia da autenticação
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
        parent::init();

        //recupera ID do pre projeto (proposta)
        if(!empty ($_REQUEST['idPreProjeto'])){
            $this->idPreProjeto = $_REQUEST['idPreProjeto'];
            //VERIFICA SE A PROPOSTA ESTA COM O MINC
            $Movimentacao = new Movimentacao();
            $rsStatusAtual = $Movimentacao->buscarStatusAtualProposta($_REQUEST['idPreProjeto']);
            $this->view->movimentacaoAtual = isset($rsStatusAtual->Movimentacao) ? $rsStatusAtual->Movimentacao : '';
        }

        $this->idUsuario = isset($auth->getIdentity()->usu_codigo) ? $auth->getIdentity()->usu_codigo : $auth->getIdentity()->IdUsuario;
    }

    // fecha método init()
    /**
     * Redireciona para o fluxo inicial do sistema
     * @access public
     * @param void
     * @return void
     */
     public function indexAction() {
        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
        
        $this->_redirect("/divulgacao/planodivulgacao?idPreProjeto=".$this->idPreProjeto);
    }

    public function planodivulgacaoAction() {
        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
        
        $dao = new DivulgacaoDAO();
        //$this->view->itensDivulgacao = $dao->buscarDigulgacao($this->idPreProjeto);
        
        $rsPlanoDivulgacao = $dao->buscar(array("pd.idProjeto = ?"=>$this->idPreProjeto));
        //xd($rsPlanoDivulgacao->toArray());
        $this->view->itensDivulgacao = $rsPlanoDivulgacao;
        $this->view->idPreProjeto = $this->idPreProjeto;
    }

    public function consultarcomponenteAction() {

        $get = Zend_Registry::get('get');
        $idProjeto = $get->idPreProjeto;
        $this->_helper->layout->disableLayout(); // desabilita o layout
        if(!empty($idProjeto) || $idProjeto=='0') {
            $dao = new DivulgacaoDAO();
            $dados = $dao->buscarDigulgacao($idProjeto);
            $this->view->itensDivulgacao = $dados;
        }
        else {
            return false;
        }
    }

    public function editardivulgacaoAction() {

        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
        
        /*$post = Zend_Registry::get('post');
        $this->view->idpeca = $post->idpeca;
        $this->view->idveiculo = $post->idveiculo;
        $this->view->iddivulgacao = $post->iddivulgacao;
        $dao = new DivulgacaoDAO();
        $this->view->itensplano = $dao->consultarDivulgacao();*/

        $get = Zend_Registry::get('get');
        $idPlanoDivulgacao = $get->cod;
        $idPreProjeto = $get->idPreProjeto;
        
        $tblDivulgacao = new DivulgacaoDAO();

        //busca registro especifico de plano divulgacao
        $rsDivulgacao = $tblDivulgacao->buscar(array("pd.idPlanoDivulgacao = ?"=>$idPlanoDivulgacao))->current();
        //busca todos
        $this->view->itensplano = $tblDivulgacao->consultarDivulgacao();
        $this->view->idpeca = $rsDivulgacao->idPeca;
        $this->view->veiculo = $tblDivulgacao->consultarVeiculo($rsDivulgacao->idPeca);
        $this->view->idveiculo = $rsDivulgacao->idVeiculo;
        $this->view->idDivulgacao = $idPlanoDivulgacao;
        $this->view->idPreProjeto = $this->idPreProjeto;

        //Zend_Debug::dump($post->idpeca);
        //die($post);
    }

    public function novodivulgacaoAction() {

        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
        
        $dao = new DivulgacaoDAO();
        $this->view->itensplano = $dao->consultarDivulgacao();
        
        $this->view->idPreProjeto = $this->idPreProjeto;

        //$dados = array('idProjeto' => $idPronac, 'idPeca' => $idPeca, 'idVeiculo' => $idveiculo, 'Usuario' => $usuario);
    }



    public function veiculoAction() {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $get                  = Zend_Registry::get('get');
        $idOpcao              = $get->idOpcao;
        $veiculo              = $get->veiculo;
        $dao                  = new DivulgacaoDAO();

        $options              = $dao->consultarVeiculo($idOpcao);

        $htmlOptions = "<option value=''> - Selecione - </option>";
        foreach ($options as $option){
            $htmlOptions .= "<option value='{$option->idVerificacaoVeiculo}'";
            if($veiculo==$option->idVerificacaoVeiculo) {$htmlOptions .= ' selected="selected" ';}
            $htmlOptions .= ">". utf8_decode(htmlentities($option->VeiculoDescicao))."</option>";
            }
        
        echo $htmlOptions;



    }




    public function excluirdivulgacaoAction() {
        
        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
        
        $get       = Zend_Registry::get('get');
        $idPreProjeto   = $get->idPreProjeto;
        $idPlanoDivulgacao = $get->cod;

        $dao = new DivulgacaoDAO();
        $dao->excluirdivulgacao($idPlanoDivulgacao);

        parent::message("Opera&ccedil;&atilde;o realizada com sucesso", "/divulgacao/planodivulgacao?idPreProjeto=".$idPreProjeto, "CONFIRM");
        //$this->_redirect('divulgacao/planodivulgacao');
    }
    public function updatedivulgacaoAction() {

        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
        
        $post       = Zend_Registry::get('post');
        $idPreProjeto   = $post->idPreProjeto;
        $peca           = $post->peca;
        $veiculo        = $post->veiculo;
        $idPlanoDivulgacao = $post->idDivulgacao;

        $dao = new DivulgacaoDAO();
        $dados = array('idPlanoDivulgacao <>?' => $idPlanoDivulgacao, 'idProjeto =?' => $idPreProjeto, 'idPeca =?' => $peca, 'idVeiculo =?' => $veiculo);
        
        $verifica = $dao->localiza($dados);
        if(count($verifica) > 0){
            parent::message("Registro j&aacute; cadastrado, transa&ccedil;&atilde;o Cancelada!", "/divulgacao/planodivulgacao?idPreProjeto=".$idPreProjeto, "ERROR");
        }else{
        $dao->UpdateDivulgacao($idPlanoDivulgacao, $peca, $veiculo);

        parent::message("Opera&ccedil;&atilde;o realizada com sucesso", "/divulgacao/planodivulgacao?idPreProjeto=".$idPreProjeto, "CONFIRM");
    
        }
    }


    public function incluirdivulgacaoAction(){

        /* =============================================================================== */ 
        /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
        /* =============================================================================== */
        $this->verificarPermissaoAcesso(true, false, false);
        
        $post       = Zend_Registry::get('post');
        $idPreProjeto   = $post->idPreProjeto;
        $idPeca         = $post->peca;
        $idveiculo      = $post->veiculo;
        $usuario        = $this->idUsuario;
        $dados = array('idProjeto =?' => $idPreProjeto, 'idPeca =?' => $idPeca, 'idVeiculo =?' => $idveiculo);


        $dao = new DivulgacaoDAO();
        $verifica = $dao->localiza($dados);
        
        if(count($verifica) > 0){
            parent::message("Registro j&aacute; cadastrado, transa&ccedil;&atilde;o Cancelada!", "/divulgacao/planodivulgacao?idPreProjeto=".$idPreProjeto, "ERROR");
        }else{
        $dados = array('idProjeto' => $idPreProjeto, 'idPeca' => $idPeca, 'idVeiculo' => $idveiculo, 'Usuario' => $usuario);
        $dao->inserirDivulgacao($dados);

        parent::message("Opera&ccedil;&atilde;o realizada com sucesso", "/divulgacao/planodivulgacao?idPreProjeto=".$idPreProjeto, "CONFIRM");
    
        }
    }

}
?>
