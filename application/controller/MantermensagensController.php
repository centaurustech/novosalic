<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Mantermensagens
 *
 * @author augusto
 */
class MantermensagensController extends GenericControllerNew {

    private $getIdUsuario = 0;

    /**
     * Reescreve o método init()
     * @access public
     * @param void
     * @return void
     */
    public function init() {
//        Zend_Layout::startMvc(array('layout' => 'layout_scriptcase'));
        
        $this->view->title = "Salic - Sistema de Apoio às Leis de Incentivo à Cultura"; // título da página
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $Usuario = new Usuario(); // objeto usuário
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        if ($auth->hasIdentity()) { // caso o usuário esteja autenticado
            // verifica as permissões
            $PermissoesGrupo = array();
            $PermissoesGrupo[] = 131;  // Coordenador de Admissibilidade  /* Deve estar habilitado - Demanda Manter mensagens */
            $PermissoesGrupo[] = 92;   // Técnico de Admissibilidade      /* Deve estar habilitado - Demanda Manter mensagens */
            $PermissoesGrupo[] = 93;  // Coordenador de Parecerista
            $PermissoesGrupo[] = 122;  // Coordenador de Acompanhamento
            $PermissoesGrupo[] = 123;  // Coordenador Geral de Acompanhamento
            $PermissoesGrupo[] = 121;  // Técnico de Acompanhamento
            $PermissoesGrupo[] = 129;  // Técnico de Acompanhamento
            $PermissoesGrupo[] = 94;  // Parecerista
            $PermissoesGrupo[] = 103; // Coordenador de Análise
            $PermissoesGrupo[] = 110; // Técnico de Análise
            $PermissoesGrupo[] = 118; // Componente da Comissão
            $PermissoesGrupo[] = 126; // Coordenador Geral de Prestação de Contas
            $PermissoesGrupo[] = 125; // Coordenador de Prestação de Contas
            $PermissoesGrupo[] = 124; // Técnico de Prestação de Contas
            $PermissoesGrupo[] = 132; // Chefe de Divisão
            $PermissoesGrupo[] = 136; // Coordenador de Entidade Vinculada
            $PermissoesGrupo[] = 134; // Coordenador de Fiscalizaç?o
            $PermissoesGrupo[] = 135; // Técnico de  Fiscalizaç?o
            $PermissoesGrupo[] = 138; // Coordenador de Avaliaçao
            $PermissoesGrupo[] = 139; // Técnico de  Avaliaçao
            if (!in_array($GrupoAtivo->codGrupo, $PermissoesGrupo)) { // verifica se o grupo ativo está no array de permissões
                parent::message("Você não tem permissão para acessar essa área do sistema!", "principal/index", "ALERT");
            }

            // pega as unidades autorizadas, orgãos e grupos do usuário (pega todos os grupos)
            $grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);

            $auth = Zend_Auth::getInstance(); // pega a autenticaç?o
            if (isset($auth->getIdentity()->usu_codigo)) { // autenticacao novo salic
                $this->getIdUsuario = UsuarioDAO::getIdUsuario($auth->getIdentity()->usu_codigo);
                $this->getIdUsuario = ($this->getIdUsuario) ? $this->getIdUsuario["idAgente"] : 0;
            } else { // autenticacao scriptcase {
                $this->getIdUsuario = (isset($_GET["idusuario"])) ? $_GET["idusuario"] : 0;
            }

            // manda os dados para a visão
            $this->view->usuario = $auth->getIdentity(); // manda os dados do usuário para a visão
            $this->view->arrayGrupos = $grupos; // manda todos os grupos do usuário para a visão
            $this->view->grupoAtivo = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuário para a visão
            $this->view->orgaoAtivo = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão
            $this->orgaoAtivo = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão
        } else { // caso o usuário não esteja autenticado
            return $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'logout'), null, true);
        }
        
        //busca orgao superior
        $orgaoSup = new Orgaos();
        $this->secretaria = $orgaoSup->codigoOrgaoSuperior($this->orgaoAtivo);
        $this->secretaria = $this->secretaria[0]->Superior;
        
        parent::init(); // chama o init() do pai GenericControllerNew
    }

// fecha método init()

    public function incluirmensagemAction() {
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo');
        $usuario = new Usuario();
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $Agente = $usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
        $usu_codigo = $auth->getIdentity()->usu_codigo;
        $mensagemprojeto = new Mensagemprojeto();
        $projetos = new Projetos();
        $pa = new Parecer();

        if (isset($_POST['idpronac'])) {
            $quebra = chr(13) . chr(10); // Quebra de linha no TXT
            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $tipomensagem = $_POST['tipomensagem'];
            $postAgente = explode('/', $_POST['idAgente']);
            $idDestinatario = $postAgente[0];
            $perfil = $postAgente[1];
            $dados = array(
                'dtMensagem' => date('Y-m-d H:i:s'),
                'dsMensagem' => TratarString::escapeString($_POST['dsMensagem']),
                'stAtivo' => 1,
                'cdTipoMensagem' => $tipomensagem,
                'idDestinatario' => $idDestinatario,
                'idRemetente' => $Agente['idAgente'],
                'IdPRONAC' => $_POST['idpronac']
            );
            if ($_POST['idMensagemProjeto']) {
                $dados = array_merge(array('idMensagemOrigem' => $_POST['idMensagemProjeto']), $dados);
            }
            $idmensagemprojeto = $mensagemprojeto->inserir($dados);
            $arquivo = getcwd() . '/public/mensagem/mensagem-destinatario-' . $idDestinatario . '.txt';
            $fp = fopen($arquivo, "a+");
            $dadosmensagem = array(
                'idpronac' => $_POST['idpronac'],
                'perfilDestinatario' => $perfil,
                'perfilRemetente' => $GrupoAtivo->codGrupo,
                'idmensagemprojeto' => $idmensagemprojeto,
                'status' => 'N'
            );
            $escreve = fwrite($fp, json_encode($dadosmensagem) . $quebra);
            fclose($fp);
            echo "<script>window.close();</script>";
        }

        $idpronac = $this->_request->getParam('idpronac');
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $grupologado = $GrupoAtivo->codGrupo;

        $dadosWhereMensagemPrj = array(
            'IdPRONAC = ?' => $idpronac,
            'idDestinatario = ?' => $Agente['idAgente']
        );
        $mensagem = $mensagemprojeto->buscarMensagemProjeto($dadosWhereMensagemPrj);
        foreach ($mensagem as $resu) {
            if ($Agente['idAgente'] == $resu->idDestinatario) {
                $dados = array('stAtivo' => '0');
                $where = "idMensagemProjeto = " . $resu->idMensagemProjeto;
                $mensagemprojeto->alterarMensagemProjeto($dados, $where);
            }
        }

        $dadosProjeto = $projetos->buscar(array('idpronac = ?' => $idpronac))->current()->toArray();
        $orgaoorigem = $dadosProjeto['OrgaoOrigem'];
        $this->view->mensagens = isset($mensagem) ? $mensagem : false;
        $this->view->dadosProjeto = $dadosProjeto;
        $this->view->idpronac = $idpronac;
        $usuariosorgao = new Usuariosorgaosgrupos();
//*************************** NOVO *****************************************************
        $num = 0;
        /* Perfil de Coordenador e Técnico de Adminissibilidade  */
        $movimentacaoDAO = new Movimentacao();
        $atores = $movimentacaoDAO->buscarTecCoordAdmissibilidade($idpronac, $usu_codigo);
        foreach ($atores as $ator) {
            $encaminha[$num]['idAgente'] = $ator->idAgente;
            $encaminha[$num]['nome'] = $ator->Nome;
            $encaminha[$num]['perfil'] = $ator->cdPerfil;
            $encaminha[$num]['TipoUsuario'] = $ator->Perfil;
            $encaminha[$num]['orgao'] = $ator->Orgao;
            $num++;
        }
        /* Fim Perfil de Coordenador e Técnico de Adminissibilidade  */
        /* Perfil de Coordenador de Parecerista / Parecerista  */
        $DistribuirParecerDAO = new tbDistribuirParecer();
       /*$where = array(
            'gru.gru_codigo = ?' => 94,
            'dp.idPRONAC = ? ' => $idpronac,
            //'usu.usu_codigo <> ? ' => $usu_codigo
        );*/
        $atores = $DistribuirParecerDAO->buscarPareceristaCoordParecer($idpronac);
        /*$prepara = array();
        foreach ($atores as $ator) {
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['idAgente'] = $ator->idAgente;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['nome'] = $ator->Nome;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['perfil'] = $ator->cdPerfil;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['orgao'] = $ator->Orgao;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['TipoUsuario'] = $ator->Perfil;
        }*/
        /*$where = array(
            'gru2.gru_codigo = ?' => 93,
            'dp.idPRONAC = ? ' => $idpronac,
            'usu.usu_codigo <> ? ' => $usu_codigo
        );
        $atores = $DistribuirParecerDAO->buscarPareceristaCoordParecer($where);
        foreach ($atores as $ator) {
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['idAgente'] = $ator->idAgente2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['nome'] = $ator->Nome2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['perfil'] = $ator->cdPerfil2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['orgao'] = $ator->Orgao2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['TipoUsuario'] = $ator->Perfil2;
        }*/
        
        /*foreach ($prepara as $orgao) {
            foreach ($orgao as $perfil) {
                foreach ($perfil as $Agente) {
                    $encaminha[$num]['idAgente'] = $Agente['idAgente'];
                    $encaminha[$num]['nome'] = $Agente['nome'];
                    $encaminha[$num]['perfil'] = $Agente['perfil'];
                    $encaminha[$num]['orgao'] = $Agente['orgao'];
                    $encaminha[$num]['TipoUsuario'] = $Agente['TipoUsuario'];
                    $num++;
                }
            }
        }*/
        
        
        foreach ($atores as $ator) {
            $encaminha[$num]['idAgente'] = $ator->idAgente;
                    $encaminha[$num]['nome'] = $ator->Nome;
                    $encaminha[$num]['perfil'] =  $ator->cdPerfil;
                    $encaminha[$num]['orgao'] = $ator->Orgao;
                    $encaminha[$num]['TipoUsuario'] = $ator->Perfil;
            $num++;
        }
        
        /* FIM Perfil de Coordenador de Parecerista / Parecerista  */
        /* Perfil de componente da comissão  */
        $tbTitulacaoConselheiroDAO = new tbTitulacaoConselheiro();
        $sql = $tbTitulacaoConselheiroDAO->buscarTitulacao(true);
        $tbDistribuicaoProjetoComissaoDAO = new tbDistribuicaoProjetoComissao();
        $atores = $tbDistribuicaoProjetoComissaoDAO->buscarComponente($idpronac, $usu_codigo);
        foreach ($atores as $ator) {
            $encaminha[$num]['idAgente'] = $ator->idAgente;
            $encaminha[$num]['nome'] = $ator->Nome;
            $encaminha[$num]['TipoUsuario'] = $ator->Perfil;
            $encaminha[$num]['Area'] = $ator->Area;
            $encaminha[$num]['perfil'] = '118';
            $num++;
        }
        /* FIM Perfil de componente da comissão  */
        /* Perfil de Acompanhamento Readequaç?o */
        $tbPedidoAlteracaoProjetoDAO = new tbPedidoAlteracaoProjeto();
        $atores = $tbPedidoAlteracaoProjetoDAO->buscarAtoresReadequacao($idpronac, $usu_codigo);
        $prepara = array();
        foreach ($atores as $ator) {
            $prepara[$ator->cdPerfil][$ator->idAgente]['idAgente'] = $ator->idAgente;
            $prepara[$ator->cdPerfil][$ator->idAgente]['nome'] = $ator->Nome;
            $prepara[$ator->cdPerfil][$ator->idAgente]['TipoUsuario'] = $ator->Perfil;
            $prepara[$ator->cdPerfil][$ator->idAgente]['perfil'] = $ator->cdPerfil;
            $prepara[$ator->cdPerfil][$ator->idAgente]['orgao'] = $ator->Orgao;
            if ($ator->idAgente2 != 0) {
                $prepara[$ator->cdPerfil2][$ator->idAgente2]['idAgente'] = $ator->idAgente2;
                $prepara[$ator->cdPerfil2][$ator->idAgente2]['nome'] = $ator->Nome2;
                $prepara[$ator->cdPerfil2][$ator->idAgente2]['TipoUsuario'] = $ator->Perfil2;
                $prepara[$ator->cdPerfil2][$ator->idAgente]['perfil'] = $ator->cdPerfil2;
                $prepara[$ator->cdPerfil2][$ator->idAgente2]['orgao'] = $ator->Orgao;
            }
            $prepara[$ator->cdPerfil3][$ator->idAgente3]['idAgente'] = $ator->idAgente3;
            $prepara[$ator->cdPerfil3][$ator->idAgente3]['nome'] = $ator->Nome3;
            $prepara[$ator->cdPerfil3][$ator->idAgente3]['TipoUsuario'] = $ator->Perfil3;
            $prepara[$ator->cdPerfil3][$ator->idAgente]['perfil'] = $ator->cdPerfil3;
            $prepara[$ator->cdPerfil3][$ator->idAgente3]['orgao'] = $ator->Orgao;
        }
        foreach ($prepara as $perfil) {
            foreach ($perfil as $agente) {
                $encaminha[$num]['idAgente'] = $agente['idAgente'];
                $encaminha[$num]['nome'] = $agente['nome'];
                $encaminha[$num]['perfil'] = $agente['perfil'];
                $encaminha[$num]['orgao'] = $agente['orgao'];
                $encaminha[$num]['TipoUsuario'] = $agente['TipoUsuario'];
                $num++;
            }
        }
        /* FIM Perfil de Acompanhamento Readequaç?o  */
        /* Perfil de Acompanhamento Avaliaç?o */
        $tbParecerConsolidadoDAO = new tbParecerConsolidado();
        $atores = $tbParecerConsolidadoDAO->buscarAtoresCoordenadorAvaliacao($idpronac, $usu_codigo);
        $prepara = array();
        foreach ($atores as $ator) {
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['idAgente'] = $ator->idAgente;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['nome'] = $ator->Nome;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['perfil'] = $ator->cdPerfil;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['orgao'] = $ator->Orgao;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['TipoUsuario'] = $ator->Perfil;
        }


        $tbRelatorioDAO = new tbRelatorio();
        $atores = $tbRelatorioDAO->buscarTecnicoAcompanhamento($idpronac, $usu_codigo);
        //$prepara = array();
        foreach ($atores as $ator) {
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['idAgente'] = $ator->idAgente;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['nome'] = $ator->Nome;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['perfil'] = $ator->cdPerfil;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['orgao'] = $ator->Orgao;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['TipoUsuario'] = $ator->Perfil;
        }
        foreach ($prepara as $orgao) {
            foreach ($orgao as $perfil) {
                foreach ($perfil as $Agente) {
                    $encaminha[$num]['idAgente'] = $Agente['idAgente'];
                    $encaminha[$num]['nome'] = $Agente['nome'];
                    $encaminha[$num]['perfil'] = $Agente['perfil'];
                    $encaminha[$num]['orgao'] = $Agente['orgao'];
                    $encaminha[$num]['TipoUsuario'] = $Agente['TipoUsuario'];
                    $num++;
                }
            }
        }
        /* FIM Perfil de Acompanhamento Avaliaç?o */

        /* Perfil de Acompanhamento Fiscalizacao */
        $tbFiscalizacaoDAO = new tbFiscalizacao();
        $atores = $tbFiscalizacaoDAO->buscarAtoresFiscalizacao($idpronac, $usu_codigo);
        $prepara = array();
        /*foreach ($atores as $ator) {
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['idAgente'] = $ator->idAgente;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['nome'] = $ator->Nome;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['perfil'] = $ator->cdPerfil;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['orgao'] = $ator->Orgao;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['TipoUsuario'] = $ator->Perfil;

            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['idAgente'] = $ator->idAgente2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['nome'] = $ator->Nome2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['perfil'] = $ator->cdPerfil2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['orgao'] = $ator->Orgao2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['TipoUsuario'] = $ator->Perfil2;
        }*/
        /*foreach ($prepara as $orgao) {
            foreach ($orgao as $perfil) {
                foreach ($perfil as $Agente) {
                    $encaminha[$num]['idAgente'] = $Agente['idAgente'];
                    $encaminha[$num]['nome'] = $Agente['nome'];
                    $encaminha[$num]['perfil'] = $Agente['perfil'];
                    $encaminha[$num]['orgao'] = $Agente['orgao'];
                    $encaminha[$num]['TipoUsuario'] = $Agente['TipoUsuario'];
                    $num++;
                }
            }
        }*/
        foreach ($atores as $ator) {
            $encaminha[$num]['idAgente'] = $ator->idAgente;
            $encaminha[$num]['nome'] = $ator->Nome;
            $encaminha[$num]['perfil'] = $ator->cdPerfil;
            $encaminha[$num]['orgao'] = $ator->Orgao;
            $encaminha[$num]['TipoUsuario'] = $ator->Perfil;
            $num++;
        }
        
        /* FIM Perfil de Acompanhamento Fiscalizacao */

        /* Perfil de Modulo Prestaç?o de Contas */
        $tbEncaminhamentoPrestacaoContasDAO = new tbEncaminhamentoPrestacaoContas();
        $atores = $tbEncaminhamentoPrestacaoContasDAO->buscarAtoresPrestacaoContas($idpronac, $usu_codigo);
        $prepara = array();
        foreach ($atores as $ator) {
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['idAgente'] = $ator->idAgente;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['nome'] = $ator->Nome;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['perfil'] = $ator->cdPerfil;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['orgao'] = $ator->Orgao;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['TipoUsuario'] = $ator->Perfil;

            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['idAgente'] = $ator->idAgente2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['nome'] = $ator->Nome2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['perfil'] = $ator->cdPerfil2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['orgao'] = $ator->Orgao2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['TipoUsuario'] = $ator->Perfil2;
        }
        foreach ($prepara as $orgao) {
            foreach ($orgao as $perfil) {
                foreach ($perfil as $Agente) {
                    $encaminha[$num]['idAgente'] = $Agente['idAgente'];
                    $encaminha[$num]['nome'] = $Agente['nome'];
                    $encaminha[$num]['perfil'] = $Agente['perfil'];
                    $encaminha[$num]['orgao'] = $Agente['orgao'];
                    $encaminha[$num]['TipoUsuario'] = $Agente['TipoUsuario'];
                    $num++;
                }
            }
        }
        /* FIM Perfil de Modulo Prestaç?o de Contas */


        $this->view->grupologado = array('controller' => 'dadosprojeto', 'action' => 'index');



        if (!empty($encaminha)) {
            $this->view->BuscarSelect = $encaminha;
        } else {
            $this->view->BuscarSelect = "";
        }
    }

    public function consultarmensagemAction() {

        $idpronac = $this->_request->getParam('idpronac');
        
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $grupologado = $GrupoAtivo->codGrupo;
        $usuario = new Usuario();
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $Agente = $usuario->getIdUsuario($auth->getIdentity()->usu_codigo);
        $usu_codigo = $auth->getIdentity()->usu_codigo;
        $idAgente = $Agente['idAgente'];
        $mensagemprojeto = new Mensagemprojeto();
        $projetos = new Projetos();

        //$idpronac = $this->_request->getParam('idpronac');
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $grupologado = $GrupoAtivo->codGrupo;

        $dadosWhereMensagemPrj = array(
            'IdPRONAC = ?' => $idpronac,
            'idDestinatario = ?' => $Agente['idAgente']
        );
        $mensagem = $mensagemprojeto->buscarMensagemProjeto($dadosWhereMensagemPrj);
        foreach ($mensagem as $resu) {
            if ($Agente['idAgente'] == $resu->idDestinatario) {
                $dados = array('stAtivo' => '0');
                $where = "idMensagemProjeto = " . $resu->idMensagemProjeto;
                $mensagemprojeto->alterarMensagemProjeto($dados, $where);
            }
        }

        $dadosProjeto = $projetos->buscar(array('idpronac = ?' => $idpronac))->current()->toArray();
        $orgaoorigem = $dadosProjeto['OrgaoOrigem'];
//        $this->view->mensagens = isset($mensagem) ? $mensagem : false;
//        $this->view->dadosProjeto = $dadosProjeto;
//        $this->view->idpronac = $idpronac;
        $usuariosorgao = new Usuariosorgaosgrupos();
//*************************** NOVO *****************************************************
        $num = 0;
        /* Perfil de Coordenador e Técnico de Adminissibilidade  */
        $movimentacaoDAO = new Movimentacao();
        $atores = $movimentacaoDAO->buscarTecCoordAdmissibilidade($idpronac, $usu_codigo);
        
        foreach ($atores as $ator) {
            $encaminha[$num]['idAgente'] = $ator->idAgente;
            $encaminha[$num]['nome'] = $ator->Nome;
            $encaminha[$num]['TipoUsuario'] = $ator->Perfil;
            $num++;
        }
        /* Fim Perfil de Coordenador e Técnico de Adminissibilidade  */
        /* Perfil de Coordenador de Parecerista / Parecerista  */
        $DistribuirParecerDAO = new tbDistribuirParecer();
        /*$where = array(
            //'gru.gru_codigo = ?' => 94,
            'dp.idPRONAC = ? ' => $idpronac
            //'usu.usu_codigo <> ? ' => $usu_codigo
        );*/
        $atores = $DistribuirParecerDAO->buscarPareceristaCoordParecer($idpronac);
        $prepara = array();
        foreach ($atores as $ator) {
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['idAgente'] = $ator->idAgente;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['nome'] = $ator->Nome;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['perfil'] = $ator->cdPerfil;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['orgao'] = $ator->Orgao;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['TipoUsuario'] = $ator->Perfil;
        }
        /*$where = array(
            'gru2.gru_codigo = ?' => 93,
            'dp.idPRONAC = ? ' => $idpronac
            //'usu.usu_codigo <> ? ' => $usu_codigo
        );
        $atores = $DistribuirParecerDAO->buscarPareceristaCoordParecer($where);
        foreach ($atores as $ator) {
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['idAgente'] = $ator->idAgente2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['nome'] = $ator->Nome2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['perfil'] = $ator->cdPerfil2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['orgao'] = $ator->Orgao2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['TipoUsuario'] = $ator->Perfil2;
        }*/
        foreach ($prepara as $orgao) {
            foreach ($orgao as $perfil) {
                foreach ($perfil as $Agente) {
                    $encaminha[$num]['idAgente'] = $Agente['idAgente'];
                    $encaminha[$num]['nome'] = $Agente['nome'];
                    $encaminha[$num]['perfil'] = $Agente['perfil'];
                    $encaminha[$num]['orgao'] = $Agente['orgao'];
                    $encaminha[$num]['TipoUsuario'] = $Agente['TipoUsuario'];
                    $num++;
                }
            }
        }
        /* FIM Perfil de Coordenador de Parecerista / Parecerista  */
        /* Perfil de componente da comissão  */
        $tbTitulacaoConselheiroDAO = new tbTitulacaoConselheiro();
        $sql = $tbTitulacaoConselheiroDAO->buscarTitulacao(true);
        $tbDistribuicaoProjetoComissaoDAO = new tbDistribuicaoProjetoComissao();
        $atores = $tbDistribuicaoProjetoComissaoDAO->buscarComponente($idpronac, $usu_codigo);
        foreach ($atores as $ator) {
            $encaminha[$num]['idAgente'] = $ator->idAgente;
            $encaminha[$num]['nome'] = $ator->Nome;
            $encaminha[$num]['TipoUsuario'] = $ator->Perfil;
            $encaminha[$num]['Area'] = $ator->Area;
            $encaminha[$num]['perfil'] = '118';
            $num++;
        }
        /* FIM Perfil de componente da comissão  */
        /* Perfil de Acompanhamento Readequaç?o */
        $tbPedidoAlteracaoProjetoDAO = new tbPedidoAlteracaoProjeto();
        $atores = $tbPedidoAlteracaoProjetoDAO->buscarAtoresReadequacao($idpronac, $usu_codigo);
        $prepara = array();
        foreach ($atores as $ator) {
            $prepara[$ator->cdPerfil][$ator->idAgente]['idAgente'] = $ator->idAgente;
            $prepara[$ator->cdPerfil][$ator->idAgente]['nome'] = $ator->Nome;
            $prepara[$ator->cdPerfil][$ator->idAgente]['TipoUsuario'] = $ator->Perfil;
            $prepara[$ator->cdPerfil][$ator->idAgente]['perfil'] = $ator->cdPerfil;
            $prepara[$ator->cdPerfil][$ator->idAgente]['orgao'] = $ator->Orgao;
            if ($ator->idAgente2 != 0) {
                $prepara[$ator->cdPerfil2][$ator->idAgente2]['idAgente'] = $ator->idAgente2;
                $prepara[$ator->cdPerfil2][$ator->idAgente2]['nome'] = $ator->Nome2;
                $prepara[$ator->cdPerfil2][$ator->idAgente2]['TipoUsuario'] = $ator->Perfil2;
                $prepara[$ator->cdPerfil2][$ator->idAgente]['perfil'] = $ator->cdPerfil2;
                $prepara[$ator->cdPerfil2][$ator->idAgente2]['orgao'] = $ator->Orgao;
            }
            $prepara[$ator->cdPerfil3][$ator->idAgente3]['idAgente'] = $ator->idAgente3;
            $prepara[$ator->cdPerfil3][$ator->idAgente3]['nome'] = $ator->Nome3;
            $prepara[$ator->cdPerfil3][$ator->idAgente3]['TipoUsuario'] = $ator->Perfil3;
            $prepara[$ator->cdPerfil3][$ator->idAgente]['perfil'] = $ator->cdPerfil3;
            $prepara[$ator->cdPerfil3][$ator->idAgente3]['orgao'] = $ator->Orgao;
        }
        foreach ($prepara as $perfil) {
            foreach ($perfil as $agente) {
                $encaminha[$num]['idAgente'] = $agente['idAgente'];
                $encaminha[$num]['nome'] = $agente['nome'];
                $encaminha[$num]['perfil'] = $agente['perfil'];
                $encaminha[$num]['orgao'] = $agente['orgao'];
                $encaminha[$num]['TipoUsuario'] = $agente['TipoUsuario'];
                $num++;
            }
        }
        /* FIM Perfil de Acompanhamento Readequaç?o  */
        /* Perfil de Acompanhamento Avaliaç?o */
        $tbParecerConsolidadoDAO = new tbParecerConsolidado();
        $atores = $tbParecerConsolidadoDAO->buscarAtoresCoordenadorAvaliacao($idpronac, $usu_codigo);
        $prepara = array();
        foreach ($atores as $ator) {
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['idAgente'] = $ator->idAgente;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['nome'] = $ator->Nome;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['perfil'] = $ator->cdPerfil;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['orgao'] = $ator->Orgao;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['TipoUsuario'] = $ator->Perfil;
        }


        $tbRelatorioDAO = new tbRelatorio();
        $atores = $tbRelatorioDAO->buscarTecnicoAcompanhamento($idpronac, $usu_codigo);
        //$prepara = array();
        foreach ($atores as $ator) {
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['idAgente'] = $ator->idAgente;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['nome'] = $ator->Nome;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['perfil'] = $ator->cdPerfil;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['orgao'] = $ator->Orgao;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['TipoUsuario'] = $ator->Perfil;
        }
        foreach ($prepara as $orgao) {
            foreach ($orgao as $perfil) {
                foreach ($perfil as $Agente) {
                    $encaminha[$num]['idAgente'] = $Agente['idAgente'];
                    $encaminha[$num]['nome'] = $Agente['nome'];
                    $encaminha[$num]['perfil'] = $Agente['perfil'];
                    $encaminha[$num]['orgao'] = $Agente['orgao'];
                    $encaminha[$num]['TipoUsuario'] = $Agente['TipoUsuario'];
                    $num++;
                }
            }
        }
        /* FIM Perfil de Acompanhamento Avaliaç?o */

        /* Perfil de Acompanhamento Fiscalizacao */
        $tbFiscalizacaoDAO = new tbFiscalizacao();
        $atores = $tbFiscalizacaoDAO->buscarAtoresFiscalizacao($idpronac, $usu_codigo);
        $prepara = array();
        foreach ($atores as $ator) {
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['idAgente'] = $ator->idAgente;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['nome'] = $ator->Nome;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['perfil'] = $ator->cdPerfil;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['orgao'] = $ator->Orgao;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['TipoUsuario'] = $ator->Perfil;

            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['idAgente'] = $ator->idAgente2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['nome'] = $ator->Nome2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['perfil'] = $ator->cdPerfil2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['orgao'] = $ator->Orgao2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['TipoUsuario'] = $ator->Perfil2;
        }
        foreach ($prepara as $orgao) {
            foreach ($orgao as $perfil) {
                foreach ($perfil as $Agente) {
                    $encaminha[$num]['idAgente'] = $Agente['idAgente'];
                    $encaminha[$num]['nome'] = $Agente['nome'];
                    $encaminha[$num]['perfil'] = $Agente['perfil'];
                    $encaminha[$num]['orgao'] = $Agente['orgao'];
                    $encaminha[$num]['TipoUsuario'] = $Agente['TipoUsuario'];
                    $num++;
                }
            }
        }
        /* FIM Perfil de Acompanhamento Fiscalizacao */

        /* Perfil de Modulo Prestaç?o de Contas */
        $tbEncaminhamentoPrestacaoContasDAO = new tbEncaminhamentoPrestacaoContas();
        $atores = $tbEncaminhamentoPrestacaoContasDAO->buscarAtoresPrestacaoContas($idpronac, $usu_codigo);
        $prepara = array();
        foreach ($atores as $ator) {
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['idAgente'] = $ator->idAgente;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['nome'] = $ator->Nome;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['perfil'] = $ator->cdPerfil;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['orgao'] = $ator->Orgao;
            $prepara[$ator->Orgao][$ator->cdPerfil][$ator->idAgente]['TipoUsuario'] = $ator->Perfil;

            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['idAgente'] = $ator->idAgente2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['nome'] = $ator->Nome2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['perfil'] = $ator->cdPerfil2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['orgao'] = $ator->Orgao2;
            $prepara[$ator->Orgao2][$ator->cdPerfil2][$ator->idAgente2]['TipoUsuario'] = $ator->Perfil2;
        }
        foreach ($prepara as $orgao) {
            foreach ($orgao as $perfil) {
                foreach ($perfil as $Agente) {
                    $encaminha[$num]['idAgente'] = $Agente['idAgente'];
                    $encaminha[$num]['nome'] = $Agente['nome'];
                    $encaminha[$num]['perfil'] = $Agente['perfil'];
                    $encaminha[$num]['orgao'] = $Agente['orgao'];
                    $encaminha[$num]['TipoUsuario'] = $Agente['TipoUsuario'];
                    $num++;
                }
            }
        }
        /* FIM Perfil de Modulo Prestaç?o de Contas */



        $dadosWhereMensagemPrj = array(
            'IdPRONAC = ?' => $idpronac
        );
        $mensagem = $mensagemprojeto->buscarMensagemProjeto($dadosWhereMensagemPrj);
        foreach ($mensagem as $resu) {
            if ($idAgente == $resu->idDestinatario) {
                $dados = array('stAtivo' => '0');
                $where = "idMensagemProjeto = " . $resu->idMensagemProjeto;
                $mensagemprojeto->alterarMensagemProjeto($dados, $where);
            }
        }
        $arquivo = getcwd() . '/public/mensagem/mensagem-destinatario-' . $idAgente . '.txt';
        if (file_exists($arquivo)) {
            $read = fopen($arquivo, 'r');
            if ($read) {
                $i = 0;
                $valores = array();
                while (($buffer = fgets($read, 4096)) !== false) {
                    $ler[] = json_decode($buffer, true);
                    $i++;
                }
                $chave = TratarArray::multi_array_search($idpronac, $ler);
                $quebra = chr(13) . chr(10); // Quebra de linha no TXT
                if (is_array($chave))
                    foreach ($chave as $chavesachadas) {
                        if ($ler[$chavesachadas]['status'] != 'L') {
                            $ler[$chavesachadas]['status'] = 'L';
                            fclose($read);
                            unlink($arquivo);
                            if (count($ler) > 0) {
                                $fp = fopen($arquivo, "a+");
                                foreach ($ler as $gravar) {
                                    fwrite($fp, json_encode($gravar) . $quebra);
                                }
                                fclose($fp);
                            }
                        }
                        $valorperfil['idmensagemprojeto'][$ler[$chavesachadas]['idmensagemprojeto']]['remetente'] = $ler[$chavesachadas]['perfilRemetente'];
                        $valorperfil['idmensagemprojeto'][$ler[$chavesachadas]['idmensagemprojeto']]['destinatario'] = $ler[$chavesachadas]['perfilDestinatario'];
                    }
//                xd($valorperfil);
                @$this->view->mensagemperfil = $valorperfil;
            }
        }
        $dadosProjeto = $projetos->buscar(array('idpronac = ?' => $idpronac))->current()->toArray();
        $this->view->mensagens = isset($mensagem) ? $mensagem : false;
        $this->view->dadosProjeto = $dadosProjeto;
        $this->view->idpronac = $idpronac;
        $this->view->agentelogado = $idAgente;
        $this->view->logado = $grupologado;

        if (!empty($encaminha)) {
            $this->view->BuscarSelect = $encaminha;
        } else {
            $this->view->BuscarSelect = "";
        }
    }

}