<?php
/**
 * ManterAgentesController
 * @author Equipe RUP - Politec
 * @since 09/08/2010
 * @version 1.0
 * @package application
 * @subpackage application.controllers
 * @link http://www.politec.com.br
 * @copyright © 2010 - Politec - Todos os direitos reservados.
 */

require_once "GenericControllerNew.php";

class ManterAgentesController extends GenericControllerNew {
    /**
     * @var integer (variável com o id do usuário logado)
     * @access private
     */
    private $getIdUsuario = 0;



    /**
     * Reescreve o método init()
     * @access public
     * @param void
     * @return void
     */
    public function init() {
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $GrupoAtivo   = new Zend_Session_Namespace('GrupoAtivo');
        // define as permissões
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = 144;  // Proponente
        $PermissoesGrupo[] = 97;  // Gestor do SALIC
        $PermissoesGrupo[] = 93;  // Coordenador de Parecerista
        $PermissoesGrupo[] = 94;  // Parecerista
        $PermissoesGrupo[] = 118; // Componente da Comissão
        $PermissoesGrupo[] = 120; // Coordenador Administrativo CNIC
        $PermissoesGrupo[] = 122; // Coordenador de Acompanhamento
        $PermissoesGrupo[] = 123; // Coordenador Geral de Acompanhamento

        if (isset($auth->getIdentity()->Cpf) &&
                !empty($auth->getIdentity()->Cpf) &&
                isset($_GET['acao']) &&
                $_GET['acao'] == 'cc' &&
                isset($_GET['cpf']) &&
                !empty($_GET['cpf'])) // pega do readequação
        {
            parent::perfil(2); // scriptcase
        }

        if (isset($auth->getIdentity()->Cpf) &&
                !empty($auth->getIdentity()->Cpf) &&
                !isset($_GET['acao']) &&
                !isset($_GET['cpf']) &&
                empty($_GET['cpf'])) // pega do readequação
        {
            parent::perfil(4, $PermissoesGrupo); // migração e novo salic

        } else if(isset($auth->getIdentity()->usu_codigo) && !empty($auth->getIdentity()->usu_codigo)) {
            parent::perfil(1, $PermissoesGrupo); // migração e novo salic

        } else {
            parent::perfil(4, $PermissoesGrupo); // migração e novo salic
        }

        $auth = Zend_Auth::getInstance(); // pega a autenticaç?o
        if (isset($auth->getIdentity()->usu_codigo)) { // autenticacao novo salic
            $this->getIdUsuario = UsuarioDAO::getIdUsuario($auth->getIdentity()->usu_codigo);
            $this->getIdUsuario = ($this->getIdUsuario) ? $this->getIdUsuario["idAgente"] : 0;
        } else { // autenticacao scriptcase
            $this->getIdUsuario = (isset($_GET["idusuario"])) ? $_GET["idusuario"] : 0;
        }

        $Cpflogado  = $this->getIdUsuario;
        $this->view->cpfLogado             = $Cpflogado;
        $this->view->grupoativo            =  $GrupoAtivo->codGrupo;
        $this->view->comboestados          = Estado::buscar();
        $this->view->combotiposenderecos   = Tipoendereco::buscar();
        $this->view->combotiposlogradouros = Tipologradouro::buscar();
        $this->view->comboareasculturais   = ManterAgentesDAO::buscarAreasCulturais();
        $this->view->combotipostelefones   = Tipotelefone::buscar();
        $this->view->combotiposemails      = Tipoemail::buscar();

        //Monta o combo das visões disponiveis
        $visoes = VisaoDAO::buscarVisao(null,null,true);
        $GrupoAtivo   = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $GrupoAtivo = $GrupoAtivo->codGrupo;

        $a = 0;
        $select = null;
        if ( isset ( $auth->getIdentity()->Cpf ) ) {
            $select[$a]['idVerificacao'] = 144; //PROPONENTE
            $select[$a]['Descricao'] = 'Proponente';
        } else {
            foreach($visoes as $visaoGrupo) {
                if($GrupoAtivo == 93 and ($visaoGrupo->idVerificacao == 209 or $visaoGrupo->idVerificacao == 216)) {
                    $select[$a]['idVerificacao'] = $visaoGrupo->idVerificacao;
                    $select[$a]['Descricao'] = $visaoGrupo->Descricao;
                }
                if($GrupoAtivo == 94 and $visaoGrupo->idVerificacao == 209) {
                    $select[$a]['idVerificacao'] = $visaoGrupo->idVerificacao;
                    $select[$a]['Descricao'] = $visaoGrupo->Descricao;
                }
                if($GrupoAtivo == 97) {
                    $select[$a]['idVerificacao'] = $visaoGrupo->idVerificacao;
                    $select[$a]['Descricao'] = $visaoGrupo->Descricao;
                }
                if($GrupoAtivo == 120 and $visaoGrupo->idVerificacao == 210) {
                    $select[$a]['idVerificacao'] = $visaoGrupo->idVerificacao;
                    $select[$a]['Descricao'] = $visaoGrupo->Descricao;
                }
                if($GrupoAtivo == 118 and $visaoGrupo->idVerificacao == 210) {
                    $select[$a]['idVerificacao'] = $visaoGrupo->idVerificacao;
                    $select[$a]['Descricao'] = $visaoGrupo->Descricao;
                }
                if($GrupoAtivo == 122 and ($visaoGrupo->idVerificacao == 210 or $visaoGrupo->idVerificacao == 216 or $GrupoAtivo == 123)) {
                    $select[$a]['idVerificacao'] = $visaoGrupo->idVerificacao;
                    $select[$a]['Descricao'] = $visaoGrupo->Descricao;
                }
                $a++;
            }
        }
//        echo Zend_Debug::dump($visoes); exit(0);

        $this->view->combovisoes = $select;
        parent::init();
    } // fecha método init()



    /**
     * Método index()
     * @access public
     * @param void
     * @return void
     */
    public function indexAction() {

    } // fecha método indexAction()



    /**
     * Método para realizar a buscar de agentes por cpf/cnpj ou por nome
     * @access public
     * @param void
     * @return void
     */
    public function buscaragenteAction() {
        // caso o formulário seja enviado via post
        if ($this->getRequest()->isPost()) {
            // recebe os dados do formulário
            $post = Zend_Registry::get('post');
            $cpf  = Mascara::delMaskCPF(Mascara::delMaskCNPJ($post->cpf)); // deleta a máscara
            $nome = $post->nome;

            try {
                // validação dos campos
                if (empty($cpf) && empty($nome)) {
                    throw new Exception("Dados obrigatórios não informados:<br /><br />É necessário informar o CPF/CNPJ ou o Nome!");
                }
                else if (!empty($cpf) && strlen($cpf) != 11 && strlen($cpf) != 14) // valida cnpj/cpf
                {
                    throw new Exception("O CPF/CNPJ informado é inválido!");
                }
                else if (!empty($cpf) && strlen($cpf) == 11 && !Validacao::validarCPF($cpf)) // valida cpf
                {
                    throw new Exception("O CPF informado é inválido!");
                }
                else if (!empty($cpf) && strlen($cpf) == 14 && !Validacao::validarCNPJ($cpf)) // valida cnpj
                {
                    throw new Exception("O CNPJ informado é inválido!");
                }
                else {
                    // redireciona para a página com a busca dos dados com paginação
                    $this->_redirect("manteragentes/listaragente?cpf=" . $cpf . "&nome=" . $nome);
                } // fecha else
            } // fecha try
            catch (Exception $e) {
                $this->view->message      = $e->getMessage();
                $this->view->message_type = "ERROR";
                $this->view->cpf          = !empty($cpf) ? Validacao::mascaraCPFCNPJ($cpf) : ''; // caso exista, adiciona a máscara
                $this->view->nome         = $nome;
            }
        } // fecha if
    } // fecha método buscaragenteAction()


    /**
     * Método para listar os agentes com paginação
     * @access public
     * @param void
     * @return void
     */
    public function listaragenteAction() {
        // recebe os dados via get
        $get  = Zend_Registry::get('get');
        $cpf  = $get->cpf;
        $nome = $get->nome;

        // realiza a busca por cpf e/ou nome
        $buscar = ManterAgentesDAO::buscarAgentes($cpf, $nome);

        if (!$buscar) {
            // redireciona para a página de cadastro de agentes, e, exibe uma notificação relativa ao cadastro
            parent::message("Agente não cadastrado!<br /><br />Por favor, cadastre o mesmo no formulário abaixo!", "manteragentes/agentes?acao=cc&cpf=" . $cpf . "&nome=" . $nome, "ALERT");
        } else {
            // ========== INÍCIO PAGINAÇÃO ==========
            // criando a paginaçao
            Zend_Paginator::setDefaultScrollingStyle('Sliding');
            Zend_View_Helper_PaginationControl::setDefaultViewPartial('paginacao/paginacao.phtml');
            $paginator = Zend_Paginator::factory($buscar); // dados a serem paginados

            // página atual e quantidade de ítens por página
            $currentPage = $this->_getParam('page', 1);
            $paginator->setCurrentPageNumber($currentPage)->setItemCountPerPage(10); // 10 por página
            // ========== FIM PAGINAÇÃO ==========

            $this->view->buscar     = $paginator;
            $this->view->qtdAgentes = count($buscar); // quantidade de agentes
        } // fecha else
    } // fecha método listaragenteAction()



    /**
     * Método com o formulário para cadastro de agentes
     * @access public
     * @param void
     * @return void
     */
    public function agentesAction() {
        if(isset($_POST['cep'])) {
            xd(1);
        }
    } // fecha método agentesAction()


    /**
     * Método com o formulário para cadastro de dirigentes
     * @access public
     * @param void
     * @return void
     */
    public function dirigentesAction() {
        // configurações do layout padrão para o scriptcase
        // retira o topo e o rodapé
        Zend_Layout::startMvc(array('layout' => 'layout_scriptcase'));
    } // fecha método dirigentesAction()



    /**
     * Método com a página de alteração de visão
     * @access public
     * @param void
     * @return void
     */
    public function alterarvisaoAction() {
        // recebe o id do agente via get
        $get      = Zend_Registry::get('get');
        $idAgente = $get->idAgente;

        // busca todas as visões
        $GrupoAtivo   = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $GrupoAtivo = $GrupoAtivo->codGrupo;

        $visoes = VisaoDAO::buscarVisao(null, null, true);
        $a = 0;
        foreach($visoes as $visaoGrupo) {
            if($GrupoAtivo == 93 and $visaoGrupo->idVerificacao == 209) {
                $select[$a]['idVerificacao'] = $visaoGrupo->idVerificacao;
                $select[$a]['Descricao'] = $visaoGrupo->Descricao;
            }
            if($GrupoAtivo == 94 and $visaoGrupo->idVerificacao == 209) {
                $select[$a]['idVerificacao'] = $visaoGrupo->idVerificacao;
                $select[$a]['Descricao'] = $visaoGrupo->Descricao;
            }
            if($GrupoAtivo == 97) {
                $select[$a]['idVerificacao'] = $visaoGrupo->idVerificacao;
                $select[$a]['Descricao'] = $visaoGrupo->Descricao;
            }
            if($GrupoAtivo == 120 and $visaoGrupo->idVerificacao == 210) {
                $select[$a]['idVerificacao'] = $visaoGrupo->idVerificacao;
                $select[$a]['Descricao'] = $visaoGrupo->Descricao;
            }
            if($GrupoAtivo == 118 and $visaoGrupo->idVerificacao == 210) {
                $select[$a]['idVerificacao'] = $visaoGrupo->idVerificacao;
                $select[$a]['Descricao'] = $visaoGrupo->Descricao;
            }
            $a++;
        }
        $this->view->visao = $select;

        // busca todas as visões do agente
        $visoes = VisaoDAO::buscarVisao($idAgente);
        $a = 0;
        foreach($visoes as $visaoGrupo) {
            if($GrupoAtivo == 93 and ($visaoGrupo->Visao == 209 or $visaoGrupo->Visao == 144)) {
                $selectCad[$a]['idVerificacao'] = $visaoGrupo->Visao;
                $selectCad[$a]['Descricao'] = $visaoGrupo->Descricao;
            }
            if($GrupoAtivo == 94 and ($visaoGrupo->Visao == 209 or $visaoGrupo->Visao == 144)) {
                $selectCad[$a]['idVerificacao'] = $visaoGrupo->Visao;
                $selectCad[$a]['Descricao'] = $visaoGrupo->Descricao;
            }
            if($GrupoAtivo == 97) {
                $selectCad[$a]['idVerificacao'] = $visaoGrupo->Visao;
                $selectCad[$a]['Descricao'] = $visaoGrupo->Descricao;
            }
            if($GrupoAtivo == 120 and ($visaoGrupo->Visao == 210 or $visaoGrupo->Visao == 144)) {
                $selectCad[$a]['idVerificacao'] = $visaoGrupo->Visao;
                $selectCad[$a]['Descricao'] = $visaoGrupo->Descricao;
            }
            if($GrupoAtivo == 118 and ($visaoGrupo->Visao == 210 or $visaoGrupo->Visao == 144)) {
                $selectCad[$a]['idVerificacao'] = $visaoGrupo->Visao;
                $selectCad[$a]['Descricao'] = $visaoGrupo->Descricao;
            }
            $a++;
        }
        //xd($selectCad);
        $this->view->visaoAgente = $visoes;

        // busca o agente pelo id
        $this->view->agente = ManterAgentesDAO::buscarAgentes(null, null, $idAgente);

        // caso o formulário seja enviado via post
        if ($this->getRequest()->isPost()) {
            // recebe os dados do formulário
            $post        = Zend_Registry::get('post');
            $idAgente    = $post->idAgente;
            $visaoAgente = $post->visaoAgente;

            try {
                // ========== ATUALIZA AS VISÕES DO AGENTE ==========

                // exclui todas as visões do agente
                $excluir = VisaoDAO::excluirVisao($idAgente);

                // cadastra todas as visões do agente
                foreach ($visaoAgente as $visao) :
                    $dados = array(
                        'idAgente' => $idAgente,
                        'Visao'    => $visao,
                        'Usuario'  => $this->getIdUsuario, // código do usuário logado
                        'stAtivo'  => 'A');
                    $cadastrar = VisaoDAO::cadastrarVisao($dados);
                endforeach;

                if ($cadastrar) {
                    parent::message("Alteração realizada com sucesso!", "manteragentes/alterarvisao?idAgente=" . $idAgente, "CONFIRM");
                } else {
                    throw new Exception("Erro ao efetuar alteração das visões do agente!");
                }
            } // fecha try
            catch (Exception $e) {
                $this->view->message      = $e->getMessage();
                $this->vies->message_type = "ERROR";
            }
        } // fecha if
    } // fecha método alterarvisaoAction()



	/**
	 * Método para salvar os dados do agente no banco de dados e fazer a busca
	 * assim que o cpf/cnpj for informado
	 * @access public
	 * @param void
	 * @return void
	 */
	public function salvaragenteAction()
	{
		$i = 0; // inicializa o contador
		$this->_helper->layout->disableLayout(); // desabilita o layout
		$this->_helper->viewRenderer->setNoRender(true);
		$novos_valores = array(); // array com os dados do agente
		$v = ''; // flag verificadora de dados válidos/inválidos

		if ($_REQUEST['cpf']) // caso o cpf/cnpj tenha sido informado
		{
			$cpf = Mascara::delMaskCPF(Mascara::delMaskCNPJ($_REQUEST['cpf'])); // deleta as máscaras

			// cpf/cnpj inválidos
			if ((strlen($cpf) == 11 && !Validacao::validarCPF($cpf)) || (strlen($cpf) == 14 && !Validacao::validarCNPJ($cpf)))
			{
				$v = 'not';
				$novos_valores[$i]['msgCPF'] = utf8_encode($v);
			}
			else // cpf/cnpj válidos
			{
				$v = 'ok';
				$novos_valores[$i]['msgCPF'] = utf8_encode($v);

				// busca os dados do agente
				$dados = ManterAgentesDAO::buscarAgentes($cpf);

				// caso o agente não esteja cadastrado, realizará o cadastro de um novo
				if (!$dados)
				{
					$arrayCNPJCPF = array('CNPJCPF' => $cpf);
					$insere = ManterAgentesDAO::cadastrarAgente($arrayCNPJCPF);
					$novos_valores[$i]['Agente'] = utf8_encode('novo');
				}
				else // o agente já encontra-se cadastrado
				{
					$novos_valores[$i]['Agente'] = utf8_encode('cadastrado');
				}

				// busca os dados do agente pelo cpf/cnpj
				$novosdados = ManterAgentesDAO::buscarAgentes($cpf);

				// busca as informações do agente
				foreach ($novosdados as $dado) :
					$novos_valores[$i]['idAgente']                = utf8_encode($dado->idAgente);
					$novos_valores[$i]['Nome']                    = utf8_encode($dado->Nome);
					$novos_valores[$i]['CEP']                     = !empty($dado->CEP) ? utf8_encode(Mascara::addMaskCEP($dado->CEP)) : ' ';
					$novos_valores[$i]['UF']                      = utf8_encode($dado->UF);
					$novos_valores[$i]['Cidade']                  = utf8_encode($dado->Cidade);
					$novos_valores[$i]['dsCidade']                = utf8_encode($dado->dsCidade);
					$novos_valores[$i]['TipoEndereco']            = utf8_encode($dado->TipoEndereco);
					$novos_valores[$i]['TipoLogradouro']          = utf8_encode($dado->TipoLogradouro);
					$novos_valores[$i]['Logradouro']              = utf8_encode($dado->Logradouro);
					$novos_valores[$i]['Numero']                  = utf8_encode($dado->Numero);
					$novos_valores[$i]['Complemento']             = utf8_encode($dado->Complemento);
					$novos_valores[$i]['Bairro']                  = utf8_encode($dado->Bairro);
					$novos_valores[$i]['DivulgarEndereco']        = utf8_encode($dado->DivulgarEndereco);
					$novos_valores[$i]['EnderecoCorrespondencia'] = utf8_encode($dado->EnderecoCorrespondencia);

					// áreas e segmentos
					$novos_valores[$i]['cdArea']                  = utf8_encode($dado->cdArea);
					$novos_valores[$i]['dsArea']                  = utf8_encode($dado->dsArea);
					$novos_valores[$i]['cdSegmento']              = utf8_encode($dado->cdSegmento);
					$novos_valores[$i]['dsSegmento']              = utf8_encode($dado->dsSegmento);
				endforeach;
			} // fecha else
			echo json_encode($novos_valores);
		} // fecha if
		else
		{
			die('0');
		}
	} // fecha método salvaragenteAction()



	/**
	 * Método para salvar os dados do dirigente no banco de dados e fazer a busca
	 * assim que o cpf/cnpj for informado
	 * @access public
	 * @param void
	 * @return void
	 */
	public function salvardirigenteAction()
	{
		$i = 0; // inicializa o contador
		$this->_helper->layout->disableLayout(); // desabilita o layout
		$this->_helper->viewRenderer->setNoRender(true);
		$novos_valores = array(); // array com os dados do agente
		$v = ''; // flag verificadora de dados válidos/inválidos

		if ($_REQUEST['cpf'] && $_REQUEST['idAgenteGeral']) // caso o cpf/cnpj tenha sido informado
		{
			$cpf           = Mascara::delMaskCPF(Mascara::delMaskCNPJ($_REQUEST['cpf'])); // deleta as máscaras
			$idAgenteGeral = $_REQUEST['idAgenteGeral']; // idVinculoPrincipal

			// cpf/cnpj inválidos
			if ((strlen($cpf) == 11 && !Validacao::validarCPF($cpf)) || (strlen($cpf) == 14 && !Validacao::validarCNPJ($cpf)))
			{
				$v = 'not';
				$novos_valores[$i]['msgCPF'] = utf8_encode($v);
			}
			else // cpf/cnpj válidos
			{
				$v = 'ok';
				$novos_valores[$i]['msgCPF'] = utf8_encode($v);

				// busca os dados do dirigente
				$dados = ManterAgentesDAO::buscarAgentes($cpf);

				// caso o dirigente não esteja cadastrado, realizará o cadastro de um novo
				if (!$dados)
				{
					// busca os dados do vinculo do dirigente (idVinculoPrincipal)
					//$buscarAgente = ManterAgentesDAO::buscarAgentes(null, null, $idAgenteGeral);

					// cadastra o dirigente
					$arrayCNPJCPF = array(
						'CNPJCPF'          => $cpf
						//,'CNPJCPFSuperior' => $buscarAgente[0]->CNPJCPF
					);
					$insere = ManterAgentesDAO::cadastrarAgente($arrayCNPJCPF);
					$novos_valores[$i]['Agente'] = utf8_encode('novo');
				}
				else // o agente já encontra-se cadastrado, realizará a alteração
				{
					$novos_valores[$i]['Agente'] = utf8_encode('cadastrado');
				}

				// busca os dados do agente pelo cpf/cnpj
				$novosdados = ManterAgentesDAO::buscarAgentes($cpf);

				foreach ($novosdados as $dado) :
					$novos_valores[$i]['idAgente']                = utf8_encode($dado->idAgente);
					$novos_valores[$i]['Nome']                    = utf8_encode($dado->Nome);
					$novos_valores[$i]['CEP']                     = !empty($dado->CEP) ? utf8_encode(Mascara::addMaskCEP($dado->CEP)) : ' ';
					$novos_valores[$i]['UF']                      = utf8_encode($dado->UF);
					$novos_valores[$i]['Cidade']                  = utf8_encode($dado->Cidade);
					$novos_valores[$i]['dsCidade']                = utf8_encode($dado->dsCidade);
					$novos_valores[$i]['TipoEndereco']            = utf8_encode($dado->TipoEndereco);
					$novos_valores[$i]['TipoLogradouro']          = utf8_encode($dado->TipoLogradouro);
					$novos_valores[$i]['Logradouro']              = utf8_encode($dado->Logradouro);
					$novos_valores[$i]['Numero']                  = utf8_encode($dado->Numero);
					$novos_valores[$i]['Complemento']             = utf8_encode($dado->Complemento);
					$novos_valores[$i]['Bairro']                  = utf8_encode($dado->Bairro);
					$novos_valores[$i]['DivulgarEndereco']        = utf8_encode($dado->DivulgarEndereco);
					$novos_valores[$i]['EnderecoCorrespondencia'] = utf8_encode($dado->EnderecoCorrespondencia);

					// áreas e segmentos
					$novos_valores[$i]['cdArea']                  = utf8_encode($dado->cdArea);
					$novos_valores[$i]['dsArea']                  = utf8_encode($dado->dsArea);
					$novos_valores[$i]['cdSegmento']              = utf8_encode($dado->cdSegmento);
					$novos_valores[$i]['dsSegmento']              = utf8_encode($dado->dsSegmento);
				endforeach;
			} // fecha else

			echo json_encode($novos_valores);
		} // fecha if
		else
		{
			die('0');
		}
	} // fecha método salvardirigenteAction()



	/**
	 * Método para buscar todos os e-mails do agente
	 * @access public
	 * @param void
	 * @return void
	 */
	public function buscaremailsAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o layout
		$this->_helper->viewRenderer->setNoRender(true);

		// caso o id do agente esteja definido
		if ($_REQUEST['idAgente'])
		{
			$Emails = ManterAgentesDAO::buscarEmails($_REQUEST['idAgente']); // busca os e-mails do agente
			$novos_emails = array();

			$e = 0;
			foreach ($Emails as $dado) : 
				$novos_emails[$e]['idInternet']   = utf8_encode($dado->idInternet);
				$novos_emails[$e]['idAgente']     = utf8_encode($dado->idAgente);
				$novos_emails[$e]['TipoInternet'] = utf8_encode($dado->TipoInternet);
				$novos_emails[$e]['tipo']         = utf8_encode($dado->tipo);
				$novos_emails[$e]['Descricao']    = utf8_encode($dado->Descricao);
				$novos_emails[$e]['Status']       = utf8_encode($dado->Status);
				$novos_emails[$e]['Divulgar']     = utf8_encode($dado->Divulgar);
				$e++;
			endforeach;

			echo json_encode($novos_emails);
		} // fecha if

	} // fecha método buscaremailsAction()



	/**
	 * Método para buscar todos os telefones do agente
	 * @access public
	 * @param void
	 * @return void
	 */
	public function buscarfonesAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o layout
		$this->_helper->viewRenderer->setNoRender(true);

		// caso o id do agente esteja definido
		if ($_REQUEST['idAgente'])
		{
			$Fones = ManterAgentesDAO::buscarFones($_REQUEST['idAgente']); // busca todos os telefones do agente
			$novos_fones = array();

			$f = 0;
			foreach ($Fones as $dado) :
				$novos_fones[$f]['TipoTelefone'] = utf8_encode($dado->TipoTelefone);
				$novos_fones[$f]['dsTelefone']   = utf8_encode($dado->dsTelefone);
				$novos_fones[$f]['UF']           = utf8_encode($dado->UF);
				$novos_fones[$f]['ufSigla']      = utf8_encode($dado->ufSigla);
				$novos_fones[$f]['DDD']          = utf8_encode($dado->DDD);
				$novos_fones[$f]['Codigo']       = utf8_encode($dado->Codigo);
				$novos_fones[$f]['Numero']       = utf8_encode($dado->Numero);
				$novos_fones[$f]['Divulgar']     = utf8_encode($dado->Divulgar);
				$f++;
			endforeach;

			echo json_encode($novos_fones);
		} // fecha if

	} // fecha método buscarfonesAction()


        public function buscarenderecosAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o layout
		$this->_helper->viewRenderer->setNoRender(true);

		// caso o id do agente esteja definido
		if ($_REQUEST['idAgente'])
		{
			$Enderecos = ManterAgentesDAO::buscarEnderecos($_REQUEST['idAgente']); // busca todos os endereços do agente
			$novos_enderecos = array();

			$E = 0;
			foreach ($Enderecos as $dado) :
				$enderecos[$E]['Logradouro']      = utf8_encode($dado->Logradouro);
				$enderecos[$E]['TipoLogradouro']  = $dado->TipoLogradouro;
				$enderecos[$E]['Numero']          = $dado->Numero;
				$enderecos[$E]['Bairro']          = utf8_encode($dado->Bairro);
				$enderecos[$E]['Complemento']     = utf8_encode($dado->Complemento);
				$enderecos[$E]['Cep']             = $dado->Cep;
				$enderecos[$E]['Status']          = utf8_encode($dado->Status);
				$enderecos[$E]['Divulgar']        = utf8_encode($dado->Divulgar);
				$enderecos[$E]['Usuario']         = utf8_encode($dado->Usuario);
                                $enderecos[$E]['TipoEndereco']    = utf8_encode($dado->TipoEndereco);
                                $enderecos[$E]['CodTipoEndereco'] = $dado->CodTipoEndereco;
                                $enderecos[$E]['Municipio']       = utf8_encode($dado->Municipio);
                                $enderecos[$E]['CodMun']          = $dado->CodMun;
                                $enderecos[$E]['UF']              = utf8_encode($dado->UF);
                                $enderecos[$E]['CodUF']           = $dado->CodUF;
				$E++;
			endforeach;

			echo json_encode($enderecos);
		} // fecha if

	} // fecha método buscarenderecosAction()





	/**
	 * Método para buscar todos os dirigentes do agente
	 * @access public
	 * @param void
	 * @return void
	 */
	public function buscardirigentesAction()
	{
		$gmtDate = gmdate("D, d M Y H:i:s");
		header("Expires: {$gmtDate} GMT");
		header("Last-Modified: {$gmtDate} GMT");
		header("Cache-Control: no-cache, must-revalidate");
		header("Pragma: no-cache");
		header("Content-Type: text/html; charset=ISO-8859-1", true);

		$this->_helper->layout->disableLayout(); // desabilita o layout

		$post          = Zend_Registry::get('post');
		$cnpjcpf       = Mascara::delMaskCPF(Mascara::delMaskCNPJ($post->cnpj_cpf));
		$idAgenteGeral = Mascara::delMaskCPF(Mascara::delMaskCNPJ($post->idAgente));

		if (!empty($cnpjcpf) && !empty($idAgenteGeral))
		{
			// busca os dirigentes vinculados ao cnpj/cpf informado
			//$Dirigentes = ManterAgentesDAO::buscarVinculados($cnpjcpf);
			$Dirigentes = ManterAgentesDAO::buscarVinculados(null, null, null, null, $idAgenteGeral);
			$this->view->Dirigentes = $Dirigentes;
		}
	} // fecha método buscardirigentesAction()



	/**
	 * Método para buscar as áreas e segmentos culturais do agente
	 * @access public
	 * @param void
	 * @return void
	 */
	public function buscaareasegmentoAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o layout
		$this->_helper->viewRenderer->setNoRender(true);

		// caso a área cultural esteja definida
		if ($_REQUEST['area'])
		{
			$novos_dados = array();
			$i = 0;

			// busca os agentes vinculados a área/segmento cultutal (, $_REQUEST['segmento'])
			$dados = TitulacaoConselheiroDAO::buscaAreaSegmento($_REQUEST['area']);

			// pega a quantidade de titulares na área
			$Q_titulares = TitulacaoConselheiroDAO::buscaTitularArea($_REQUEST['area']);
			$novos_dados[$i]['Q_titulares'] = utf8_encode($Q_titulares[0]->QTD);

			// pega a quantidade de suplentes na área
			$Q_suplentes = TitulacaoConselheiroDAO::buscaSuplentesArea($_REQUEST['area']);
			$novos_dados[$i]['Q_suplentes'] = utf8_encode($Q_suplentes[0]->QTD);			

			// caso não existam mais vagas para titular e suplentes
			if ($Q_titulares[0]->QTD >= 1 && $Q_suplentes[0]->QTD >= 2)
			{
				$novos_dados[$i]['msgAS'] = utf8_encode('A Área Cultural selecionada já conta com 1 Titular e 2 Suplentes!');
			}
			else  if ($Q_titulares[0]->QTD == 0  && $Q_suplentes[0]->QTD == 0){
				$novos_dados[0]['Nome']    = 'Sem cadastro';
				$novos_dados[0]['Titular'] = '';
				$novos_dados[0]['msgAS'] = utf8_encode('Você pode cadastrar <strong> 1 </strong> Titular e <strong>  2 </strong> Suplente(s)!');
                        }else{
				$titularesDisponives = 1 - (int) $Q_titulares[0]->QTD;
				$suplentesDisponives = 2 - (int) $Q_suplentes[0]->QTD;
				$novos_dados[$i]['msgAS'] = utf8_encode('Você pode cadastrar <strong>' . $titularesDisponives . '</strong> Titular e <strong>' . $suplentesDisponives . '</strong> Suplente(s)!');
			}

			// pega os nomes de titulares e suplentes cadastrados
			foreach ($dados as $dado) :
				$novos_dados[$i]['Nome']    = utf8_encode($dado->Nome);
				$novos_dados[$i]['Titular'] = (utf8_encode($dado->stTitular) == 1) ? '(Titular)' : '(Suplente)';
				$i++;
			endforeach;

			echo json_encode($novos_dados);
		} // fecha if
	} // fecha método buscaareasegmentoAction()



	/**
	 * Método para buscar as visões do agente
	 * @access public
	 * @param void
	 * @return void
	 */
	public function buscarvisaoAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o layout
		$this->_helper->viewRenderer->setNoRender(true);

		// caso o id do agente esteja definido
		if ($_REQUEST['idAgente'])
		{
			$novos_dados = array();
			$i = 0;

			// busca as visões vinculadas ao agente
			$dados = VisaoDAO::buscarVisao($_REQUEST['idAgente']);

			foreach ($dados as $dado) :
				$novos_dados[$i]['Visao']     = utf8_encode($dado->Visao);
				$novos_dados[$i]['Descricao'] = utf8_encode($dado->Descricao);
				$novos_dados[$i]['verificacao'] = utf8_encode($dado->idVerificacao);
				$novos_dados[$i]['area']      = ($dado->area) ? utf8_encode($dado->area) : 'false';
				$i++;
			endforeach;

			echo json_encode($novos_dados);
		} // fecha if
	} // fecha método buscarvisaoAction()



	/**
	 * Método para gravação de todos os dados do agente
	 * @access public
	 * @param void
	 * @return void
	 */
	public function gravaragentecompletoAction()
	{

		if ($this->getRequest()->isPost())
		{

			$post     = Zend_Registry::get('post');
			$idAgente = $post->idAgente;
			$cpf      = Mascara::delMaskCPF(Mascara::delMaskCNPJ($post->cpf)); // retira as máscaras
			$TipoNome = (strlen($cpf) == 11 ? 18 : 19); // 18 = pessoa física e 19 = pessoa jurídica
			$Usuario  = $this->getIdUsuario; // id do usuário logado
			



// ================================================ INÍCIO SALVAR NOME ======================================================

			$nome = $post->nome;

			try
			{
				// busca o nome do agente
				$busca = NomesDAO::buscarNome($idAgente);

				if (!$busca) 
				{
					$i = NomesDAO::gravarNome($idAgente, $TipoNome, $nome, 0, $Usuario);
				}
				else 
				{
					$i = NomesDAO::atualizaNome($idAgente, $TipoNome, $nome, 0, $Usuario);
				}
			} 
			catch (Exception $e)
			{
				parent::message("Erro ao salvar o nome: " . $e->getMessage(), "manteragentes/agentes?acao=cc", "ERROR");
			}
// ================================================ FIM SALVAR NOME ======================================================


// ================================================ INICIO SALVAR VISÂO ======================================================
			$Visao = $post->visao;
			
			$grupologado = $post->grupologado;
			
			
			/* 
			 * Validação - Se for componente da comissão ele não salva a visão
			 * Regra o componente da comissão não pode alterar sua visão.
			 */
			
			if($grupologado != 118):
			
			$GravarVisao = array( // insert
				'idAgente' => $idAgente,
				'Visao'    => $Visao,
				'Usuario'  => $Usuario,
				'stAtivo'  => 'A');

			try
			{
				$busca = VisaoDAO::buscarVisao($idAgente, $Visao);

				if (!$busca) 
				{
					$i = VisaoDAO::cadastrarVisao($GravarVisao);
				}
			} 
			catch(Exception $e)
			{
				parent::message("Erro ao salvar a visão: " . $e->getMessage(), "manteragentes/agentes?acao=cc", "ERROR");
			}
			
			
// ================================================ FIM SALVAR VISÂO ======================================================



// ===================== INÍCIO SALVAR TITULAÇÃO (ÁREA/SEGMENTO DO COMPONENTE DA COMISSÃO) ================================


			$titular          = $post->titular;
			$areaCultural     = $post->areaCultural;
			$segmentoCultural = $post->segmentoCultural;

			// só salva área e segmento para a visão de Componente da Comissão e se os campos titular e areaCultural forem informados
			if ((int) $Visao == 210 && ((int) $titular == 0 || (int) $titular == 1) && !empty($areaCultural))
			{
				$GravarComponente = array( // insert
					'idAgente'      => $idAgente,
					'cdArea'        => $areaCultural,
					'cdSegmento'    => $segmentoCultural,
					'stTitular'     => $titular,
					'stConselheiro' => 'A');

				$AtualizarComponente = array( // update
					'cdArea'        => $areaCultural,
					'cdSegmento'    => $segmentoCultural,
					'stTitular'     => $titular
					//'stConselheiro' => 'A' -- Qual caso de uso vai ativa e desativar?										
					);

				try
				{
					// busca a titulação do agente (titular/suplente de área cultural)
					$busca = TitulacaoConselheiroDAO::buscarComponente($idAgente, $Visao);

					if (!$busca)
					{
						$i = TitulacaoConselheiroDAO::gravarComponente($GravarComponente);
					}
					else 
					{
						$i = TitulacaoConselheiroDAO::atualizaComponente($idAgente, $AtualizarComponente);
					}
				} 
				catch(Exception $e)
				{
					parent::message("Erro ao salvar a Área e Segmento: " . $e->getMessage(), "manteragentes/agentes?acao=cc", "ERROR");					
				}
			} 
			
// ============================= FIM SALVAR TITULAÇÃO (ÁREA/SEGMENTO DO COMPONENTE DA COMISSÃO) ===========================

			endif; // Fecha o if da regra do componente da comissão
			
			
// =========================================== INICIO SALVAR ENDEREÇOS ====================================================

            $cepEnderecos               = $post->ceps;
			$tipoEnderecos              = $post->tipoEnderecos;
			$ufEnderecos                = $post->ufs;
			$CidadeEnderecos            = $post->cidades;
			$Enderecos                  = $post->logradouros;
			$divulgarEnderecos          = $post->divulgarEnderecos;
                        
			
			if (!empty($post->correspondenciaEnderecos))
			{ 
				$correspondenciaEnderecos = $post->correspondenciaEnderecos; 
			}
			else
			{
				$correspondenciaEnderecos = -1;
			}

			$tipoLogradouros            = $post->tipoLogradouros;
			$numeros                    = $post->numeros;
            $complementos               = $post->complementos;
            $bairros                    = $post->bairros;
            
			try
			{
				// exclui todos os telefones do agente
				$delete = EnderecoNacionalDAO::deletarEnderecoNacional($idAgente);

				// cadastra todos os telefones
				for ($i = 0; $i < sizeof($cepEnderecos); $i++)
				{
					if($correspondenciaEnderecos == "end"+$i ){$correspondenciaEnderecos = 1; }else{$correspondenciaEnderecos = 0;}

                                        $arrayEnderecos = array(
																'idAgente'      => $idAgente,
				                                                'Cep'           => str_replace(".","",str_replace("-","",$cepEnderecos[$i])),
				                                                'TipoEndereco'  => $tipoEnderecos[$i],
				                                                'UF'            => $ufEnderecos[$i],
				                                                'Cidade'        => $CidadeEnderecos[$i],
				                                                'Logradouro'    => $Enderecos[$i],
				                                                'Divulgar'      => $divulgarEnderecos[$i],
				                                                'TipoLogradouro'=> $tipoLogradouros[$i],
				                                                'Numero'        => $numeros[$i],
				                                                'Complemento'   => $complementos[$i],
				                                                'Bairro'        => $bairros[$i],
				                                                'Status'        => $correspondenciaEnderecos,
																'Usuario'       => $Usuario);
				                                                

                                        $insere = EnderecoNacionalDAO::gravarEnderecoNacional($arrayEnderecos);

				}
				
			} 
			catch (Exception $e)
			{
				parent::message("Erro ao salvar o endereço: " . $e->getMessage(), "manteragentes/agentes?acao=cc", "ERROR");
			}
			
// ============================================= FIM SALVAR ENDEREÇOS ====================================================


// =========================================== INICIO SALVAR TELEFONES ====================================================

			$tipoFones     = $post->tipoFones;
			$ufFones       = $post->ufFones;
			$dddFones      = $post->dddFones;
			$Fones         = $post->Fones;
			$divulgarFones = $post->divulgarFones;

			try
			{
				// exclui todos os telefones do agente
				$delete = Telefone::excluirTodos($idAgente);

				// cadastra todos os telefones
				for ($i = 0; $i < sizeof($Fones); $i++)
				{
					$arrayTelefones = array(
											'idAgente'     => $idAgente,
											'TipoTelefone' => $tipoFones[$i],
											'UF'           => $ufFones[$i],
											'DDD'          => $dddFones[$i],
											'Numero'       => $Fones[$i],
											'Divulgar'     => $divulgarFones[$i],
											'Usuario'      => $Usuario);

					$insere = Telefone::cadastrar($arrayTelefones);
				} 
				
			}
			catch (Exception $e)
			{
				parent::message("Erro ao salvar o componente: " . $e->getMessage(), "manteragentes/agentes?acao=cc", "ERROR");
			}
// =========================================== FIM SALVAR TELEFONES ====================================================



// =========================================== INICIO SALVAR EMAILS ====================================================			

			$tipoEmails     = $post->tipoEmails;
			$Emails         = $post->Emails;
			$divulgarEmails = $post->divulgarEmails;
			$enviarEmails   = $post->enviarEmails;

			try
			{
				// exclui todos os e-mails do agente
				$delete = Email::excluirTodos($idAgente);

				// cadastra todos os e-mails
				for ($i = 0; $i < sizeof($Emails); $i++)
				{
					$arrayEmail = array(
										'idAgente'     => $idAgente,
										'TipoInternet' => $tipoEmails[$i],
										'Descricao'    => $Emails[$i],
										'Status'       => $enviarEmails[$i],
										'Divulgar'     => $divulgarEmails[$i],
										'Usuario'      => $Usuario);

					$insere = Email::cadastrar($arrayEmail);
				} 
				
			}
			catch (Exception $e)
			{
				parent::message("Erro ao salvar o componente: " . $e->getMessage(), "manteragentes/agentes?acao=cc", "ERROR");
			}
			
// =========================================== FIM SALVAR EMAILS ====================================================


		} 

		parent::message("Cadastro realizado com sucesso!", "manteragentes/agentes?acao=cc", "CONFIRM");

		
		
	} 



	/**
	 * Método para gravação de todos os dados do dirigente
	 * @access public
	 * @param void
	 * @return void
	 */
	public function gravardirigentecompletoAction()
	{
		// caso o formulário seja enviado via post
		if ($this->getRequest()->isPost())
		{
			// recebe os dados via post
			$post          = Zend_Registry::get('post');
			$idAgente      = $post->idAgente; // id do dirigente
			$idAgenteGeral = $post->idAgenteGeral; // usuário associado ao dirigente
			$cpf           = Mascara::delMaskCPF(Mascara::delMaskCNPJ($post->cpf)); // retira as máscaras
			$TipoNome      = 18; // pessoa física
			$Usuario       = $this->getIdUsuario; // id do usuário logado



			// ========== INÍCIO SALVAR NOME ==========
			$nome = $post->nome;

			try
			{
				// busca o nome do agente
				$busca = NomesDAO::buscarNome($idAgente);

				if (!$busca) // faz a inserção do nome
				{
					$i = NomesDAO::gravarNome($idAgente, $TipoNome, $nome, 0, $Usuario);
				}
				else // faz a alteração do nome
				{
					$i = NomesDAO::atualizaNome($idAgente, $TipoNome, $nome, 0, $Usuario);
				}	
			} // fecha try
			catch (Exception $e)
			{
				$this->view->message = "Erro ao salvar o nome: " . $e->getMessage();
			}
			// ========== FIM SALVAR NOME ==========



			// ========== INÍCIO SALVAR ENDEREÇO ==========
			$TipoEndereco   = $post->tipoEndereco;
			$TipoLogradouro = $post->tipoLogradouro;
			$Logradouro     = $post->logradouro;
			$Numero         = $post->numero;
			$Bairro         = $post->bairro;
			$Complemento    = $post->complemento;
			$Cidade         = $post->cidade;
			$UF             = $post->uf;
			$Cep            = Mascara::delMaskCEP($post->cep);
			$Divulgar       = $post->divulgarEndereco;
			$Status         = $post->enderecoCorrespondencia;

			$GravarEnderecoNacional = array( // insert
				'idAgente'        => $idAgente,
				'TipoEndereco'    => $TipoEndereco,
				'TipoLogradouro'  => $TipoLogradouro,
				'Logradouro'      => $Logradouro,
				'Numero'          => $Numero,
				'Bairro'          => $Bairro,
				'Complemento'     => $Complemento,
				'Cidade'          => $Cidade,
				'UF'              => $UF,
				'Cep'             => $Cep,
				'Status'          => $Status,
				'Divulgar'        => $Divulgar,
				'Usuario'         => $Usuario);

			$AtualizarEnderecoNacional = array( // update
				'TipoEndereco'    => $TipoEndereco,
				'TipoLogradouro'  => $TipoLogradouro,
				'Logradouro'      => $Logradouro,
				'Numero'          => $Numero,
				'Bairro'          => $Bairro,
				'Complemento'     => $Complemento,
				'Cidade'          => $Cidade,
				'UF'              => $UF,
				'Cep'             => $Cep,
				'Status'          => $Status,
				'Divulgar'        => $Divulgar,
				'Usuario'         => $Usuario);

			try
			{
				// busca o endereço do agente
				$busca = EnderecoNacionalDAO::buscarEnderecoNacional($idAgente);

				if (!$busca) // faz a inserção do endereço
				{
					$i = EnderecoNacionalDAO::gravarEnderecoNacional($GravarEnderecoNacional);
				}
				else // faz a alteração do endereço
				{
					$i = EnderecoNacionalDAO::atualizaEnderecoNacional($idAgente, $AtualizarEnderecoNacional);
				}
			} // fecha try
			catch(Exception $e)
			{
				$this->view->message = "Erro ao salvar o endereço: " . $e->getMessage();
			}
			// ========== FIM SALVAR ENDEREÇO ==========



			// ========== INÍCIO SALVAR VISÃO ==========
			$Visao = $post->visao;

			$GravarVisao = array( // insert
				'idAgente' => $idAgente,
				'Visao'    => $Visao,
				'Usuario'  => $Usuario,
				'stAtivo'  => 'A');

			try
			{
				// busca as visões do agente
				$busca = VisaoDAO::buscarVisao($idAgente, $Visao);

				if (!$busca) // faz a inserção da visão
				{
					$i = VisaoDAO::cadastrarVisao($GravarVisao);
				}
			} // fecha try
			catch(Exception $e)
			{
				$this->view->message = "Erro ao salvar a visão: " . $e->getMessage();
			}
			// ========== FIM SALVAR VISÃO ==========



			// ========== INÍCIO TELEFONES ==========
			// array com os telefones
			$tipoFones     = $post->tipoFones;
			$ufFones       = $post->ufFones;
			$dddFones      = $post->dddFones;
			$Fones         = $post->Fones;
			$divulgarFones = $post->divulgarFones;

			try
			{
				// exclui todos os telefones
				$delete = Telefone::excluirTodos($idAgente);

				// cadastra todos os telefones
				for ($i = 0; $i < sizeof($Fones); $i++)
				{
					$arrayTelefones = array(
						'idAgente'     => $idAgente,
						'TipoTelefone' => $tipoFones[$i],
						'UF'           => $ufFones[$i],
						'DDD'          => $dddFones[$i],
						'Numero'       => $Fones[$i],
						'Divulgar'     => $divulgarFones[$i],
						'Usuario'      => $Usuario);

					$insere = Telefone::cadastrar($arrayTelefones);
				} // fecha for
			} // fecha try
			catch (Exception $e)
			{
				$this->view->message = "Erro ao salvar o componente: " . $e->getMessage();
			}
			// ========== FIM TELEFONES ==========



			// ========== INÍCIO E-MAILS ==========
			// array com os e-mails
			$tipoEmails     = $post->tipoEmails;
			$Emails         = $post->Emails;
			$divulgarEmails = $post->divulgarEmails;
			$enviarEmails   = $post->enviarEmails;

			try
			{
				// exclui todos os e-mails
				$delete = Email::excluirTodos($idAgente);

				// cadastra todos os e-mails
				for ($i = 0; $i < sizeof($Emails); $i++)
				{
					$arrayEmail = array(
						'idAgente'     => $idAgente,
						'TipoInternet' => $tipoEmails[$i],
						'Descricao'    => $Emails[$i],
						'Status'       => $enviarEmails[$i],
						'Divulgar'     => $divulgarEmails[$i],
						'Usuario'      => $Usuario);

					$insere = Email::cadastrar($arrayEmail);
				} // fecha for
			} // fecha try
			catch (Exception $e)
			{
				$this->view->message = "Erro ao salvar o componente: " . $e->getMessage();
			}
			// ========== FIM E-MAILS ==========



			// ========== INÍCIO DIRIGENTES ==========
			// busca os dados do associado ao dirigente (idVinculoPrincipal)
			//$buscarAgente = ManterAgentesDAO::buscarAgentes(null, null, $idAgenteGeral);

			// busca o dirigente vinculado ao cnpj/cpf
			//$dadosDirigente = ManterAgentesDAO::buscarVinculados($buscarAgente[0]->CNPJCPF, null, $idAgente, $idAgenteGeral, $idAgenteGeral);
			$dadosDirigente = ManterAgentesDAO::buscarVinculados(null, null, $idAgente, $idAgenteGeral, $idAgenteGeral);

			// caso o agente não esteja vinculado, realizará a vinculação
			if (!$dadosDirigente)
			{
				// associa o dirigente ao cnpj/cpf
				$dadosVinculacao = array(
					'idAgente'           => $idAgente,
					'idVinculado'        => $idAgenteGeral,
					'idVinculoPrincipal' => $idAgenteGeral,
					'Usuario'            => $Usuario);
				$vincular = ManterAgentesDAO::cadastrarVinculados($dadosVinculacao);
			}
			// ========== FIM DIRIGENTES ==========



		} // fecha if ($this->getRequest()->isPost())

		parent::message("Cadastro realizado com sucesso!", "manteragentes/dirigentes?acao=cc&idAgenteGeral=".$idAgenteGeral, "CONFIRM");
	} // fecha método gravaragentecompletoAction()

} // fecha class