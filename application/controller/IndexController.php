<?php
/**
 * Login e autenticação
 * @author Equipe RUP - Politec
 * @since 20/07/2010
 * @version 1.0
 * @package application
 * @subpackage application.controller
 * @link http://www.cultura.gov.br
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 */
 
require_once "GenericControllerNew.php";

class IndexController extends GenericControllerNew
{
    /**
     * Método principal
     * @access public
     * @param void
     * @return void
     */
    public function indexAction()
    {
        $this->_forward("index", "login");
    }

    public function loginUsuarioAction()
    {
        $this->_helper->layout->disableLayout();
        $this->renderScript("/index/index.phtml");
    }

	/**
	 * Efetua o login no sistema
	 * @access public
	 * @param void
	 * @return void
	 */
	public function loginAction(){
            $this->_helper->layout->disableLayout(); // desabilita Zend_Layout

            // recebe os dados do formulário via post
            $post     = Zend_Registry::get('post');
            $username = Mascara::delMaskCNPJ(Mascara::delMaskCPF($post->Login)); // recebe o login sem mêscaras
            $password = $post->Senha; // recebe a senha

            try {
                // valida os dados
                if (empty($username) || empty($password)) // verifica se os campos foram preenchidos
                {
                    throw new Exception("Login ou Senha inválidos!");
                }
                else if (strlen($username) == 11 && !Validacao::validarCPF($username)) // verifica se o CPF ï¿½ vï¿½lido
                {
                    throw new Exception("O CPF informado é invalido!");
                }
                else if (strlen($username) == 14 && !Validacao::validarCNPJ($username)) // verifica se o CNPJ ï¿½ vï¿½lido
                {
                    throw new Exception("O CPF informado é invalido!");
                }
                else {
                    // realiza a busca do usuário no banco, fazendo a autenticação do mesmo
                    $Usuario = new Usuario();
                    $buscar = $Usuario->login($username, $password);
                    if ($buscar) // acesso permitido
                    {
                        $auth = Zend_Auth::getInstance(); // instancia da autenticação

                        // registra o primeiro grupo do usuário (pega unidade autorizada, orgï¿½o e grupo do usuário)
                        $Grupo   = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21); // busca todos os grupos do usuário

                        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
                        $GrupoAtivo->codGrupo = $Grupo[0]->gru_codigo; // armazena o grupo na sessão
                        $GrupoAtivo->codOrgao = $Grupo[0]->uog_orgao; // armazena o órgão na sessão
                        $this->orgaoAtivo = $GrupoAtivo->codOrgao;

                        // redireciona para o Controller protegido
                        return $this->_helper->redirector->goToRoute(array('controller' => 'principal'), null, true);
                    } // fecha if
                    else {
                        //se nenhum registro foi encontrado na tabela Usuario, ele passa a tentar se logar como proponente.
                        //neste ponto o _forward encaminha o processamento para o metodo login do controller login, que recebe
                        //o post igualmente e tenta encontrar usuario cadastrado em SGCAcesso
                        $this->_forward("login", "login");
                        //throw new Exception("Usuário inexistente!");
                    }
                } // fecha else
            } // fecha try
            catch (Exception $e) {
                parent::message($e->getMessage(), "index", "ERROR");
            }
	} // fecha loginAction



	/**
	 * Efetua o logout do sistema
	 * @access public
	 * @param void
	 * @return void
	 */
	public function logoutAction()
	{
		$auth = Zend_Auth::getInstance();
		$auth->clearIdentity(); // limpa a autenticação
                unset($_SESSION);
		$this->_redirect('index');
	} // fecha logoutAction



	/**
	 * Altera o pefil do usuário
	 * @access public
	 * @param void
	 * @return void
	 */
	public function alterarperfilAction()
	{
		$get      = Zend_Registry::get('get');
		$codGrupo = $get->codGrupo; // grupo do usuário logado
		$codOrgao = $get->codOrgao; // órgão do usuário logado

		$auth       = Zend_Auth::getInstance(); // pega a autenticação
		$GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
		$GrupoAtivo->codGrupo = $codGrupo; // armazena o grupo ativo na sessão
		$GrupoAtivo->codOrgao = $codOrgao; // armazena o órgão ativo na sessão

                if($GrupoAtivo->codGrupo == "1111" && $GrupoAtivo->codOrgao == "2222"){
                    $auth   = Zend_Auth::getInstance();
                    $tblSGCacesso = new Sgcacesso();
                    $rsSGCacesso = $tblSGCacesso->buscar(array("Cpf = ? "=>$auth->getIdentity()->usu_identificacao))->current()->toArray();
                    $objAuth = $auth->getStorage()->write((object)$rsSGCacesso);
                    
                    $_SESSION["GrupoAtivo"]["codGrupo"] = $GrupoAtivo->codGrupo;
                    $_SESSION["GrupoAtivo"]["codOrgao"] = $GrupoAtivo->codOrgao;
                    parent::message("Seu perfil foi alterado no sistema. Voc&ecirc; ter&aacute; acesso a outras funcionalidades!", "principalproponente", "ALERT");
                }

                //Reescreve a sessao com o novo orgao superior
                $tblUsuario = new Usuario();
                $codOrgaoMaxSuperior = $tblUsuario->recuperarOrgaoMaxSuperior($codOrgao);
                $_SESSION['Zend_Auth']['storage']->usu_org_max_superior = $codOrgaoMaxSuperior;

		// redireciona para a página inicial do sistema
		parent::message("Seu perfil foi alterado no sistema. Voc&ecirc; ter&aacute; acesso a outras funcionalidades!", "principal", "ALERT");
	} // fecha alterarPerfilAction()


        public function verificamensagemusuarioAction(){
                $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
                $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
                $usuario = new Usuario();
                $pr = new Projetos();
                $auth = Zend_Auth::getInstance(); // pega a autenticação
                $Agente = $usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
                $idAgente = $Agente['idAgente'];
                $camMensagem = getcwd().'/public/mensagem/mensagem-destinatario-'.$idAgente.'.txt';
                $verificarmensagem = array();
                if (file_exists($camMensagem)) {
                    $read = fopen($camMensagem, 'r');
                    if ($read) {
                        while (($buffer = fgets($read, 4096)) !== false) {
                            $verificarmensagem[] = json_decode($buffer, true);
                        }
                        fclose($read);
                    }
                }
                $qtdmensagem = count($verificarmensagem);
//                xd($verificarmensagem);
                if($qtdmensagem > 0){
                    $a = 0;
                    $idpronac = 0;
                    $mensagem = array();
                    foreach($verificarmensagem as $resu){
                            if($resu['status']== 'N' and $resu['idpronac'] != $idpronac and $GrupoAtivo->codGrupo == $resu['perfilDestinatario']){
                                $mensagem[$a]['idpronac'] = $resu['idpronac'];
                                $buscarpronac = $pr->buscar(array('IdPRONAC = ?'=>$resu['idpronac']))->current();
                                $mensagem[$a]['pronac'] = $buscarpronac->AnoProjeto.$buscarpronac->Sequencial;
                                $a++;
                                $idpronac = $resu['idpronac'];
                            }
                    }
                    echo count($mensagem) > 0 ? json_encode($mensagem) : json_encode(array('error'=>true));
                }
                else{
                    echo json_encode(array('error'=>true));
                }
                exit();
        }
        
        
    public function montarPlanilhaOrcamentariaAction() {
        
        $auth = Zend_Auth::getInstance(); // pega a autenticacao
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $get = Zend_Registry::get('get');
        
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $this->view->idPerfil = $GrupoAtivo->codGrupo;
        
        $this->view->idPronac = $get->idPronac;
        $spPlanilhaOrcamentaria = new spPlanilhaOrcamentaria();
        $planilhaOrcamentaria = $spPlanilhaOrcamentaria->exec($get->idPronac, $get->tipoPlanilha); 
        $planilha = $this->montarPlanilhaOrcamentaria($planilhaOrcamentaria, $get->tipoPlanilha);
        
        // tipoPlanilha = 0 : Planilha Orçamentária da Proposta
        // tipoPlanilha = 1 : Planilha Orçamentária do Proponente
        // tipoPlanilha = 2 : Planilha Orçamentária do Parecerista
        // tipoPlanilha = 3 : Planilha Orçamentária Aprovada Ativa
        // tipoPlanilha = 4 : Cortes Orçamentários Aprovados
        // tipoPlanilha = 5 : Remanejamento menor que 20%
        // tipoPlanilha = 6 : Readequação
        
        $link = isset($get->link) ? true : false;
        
        $this->montaTela(
            'index/montar-planilha-orcamentaria.phtml', array(
            'tipoPlanilha' => $get->tipoPlanilha,
            'tpPlanilha' => (count($planilhaOrcamentaria)>0) ? isset($planilhaOrcamentaria[0]->tpPlanilha) ? $planilhaOrcamentaria[0]->tpPlanilha : '' : '',
            'planilha' => $planilha,
            'link' => $link
            )
        );
    }

} // fecha class