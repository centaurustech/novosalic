<?php

class CaptacaoController extends GenericControllerNew
{

    public function init()
    {
        //recupera ID do pre projeto (proposta)
        $this->view->title = "Salic - Sistema de Apoio &agrave;s Leis de Incentivo &agrave; Cultura"; // título da página
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $Usuario = new UsuarioDAO(); // objeto usuário
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo

        if ($auth->hasIdentity()) { // caso o usuário esteja autenticado
            // verifica as permissões
            $PermissoesGrupo = array();

            $PermissoesGrupo[] = 123; // Coordenador - Geral de Acompanhamento
            $PermissoesGrupo[] = 122; // Coordenador de Acompanhamento
            $PermissoesGrupo[] = 121; // Coordenador de Acompanhamento

            if (!in_array($GrupoAtivo->codGrupo, $PermissoesGrupo)) { // verifica se o grupo ativo está no array de permissões
                parent::message("Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar essa &aacute;rea do sistema!", "principal/index", "ALERT");
            }

            // pega as unidades autorizadas, orgãos e grupos do usuário (pega todos os grupos)
            $grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);

            // manda os dados para a visão
            $this->view->usuario = $auth->getIdentity(); // manda os dados do usuário para a visão
            $this->view->arrayGrupos = $grupos; // manda todos os grupos do usuário para a visão
            $this->view->grupoAtivo = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuário para a visão
            $this->view->orgaoAtivo = $GrupoAtivo->codOrgao; // manda o órgão ativo do usuário para a visão
        }
        else {
            return $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'logout'), null, true);
        }

        parent::init(); // chama o init() do pai GenericControllerNew
    }

    public function indexAction()
    {
        // $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
    }

    public function dadosprojetoAction()
    {
        $busca = new CaptacaoDAO();
        $projeto = $busca->buscarProjetos($this->_request->get('pronac'));
        if (count($projeto) > 0) {
            $this->view->projeto = $projeto;
        } else {
            parent::message("Projeto sem autoriza&ccedil;&atilde;o para captar!", "captacao/index", "ALERT");
        }
    }

    public function pesquisarprojetoAction()
    {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $this->_helper->viewRenderer->setNoRender(true);
        $get = Zend_Registry::get('post');
        $pronac = $get->pronac;

        $busca = new ProjetoDAO(); 
        $projeto = $busca->buscarDadosProjeto($pronac);

        if ($projeto) {
            $situacoesDoProjeto = array('E10', 'E12', 'E13');
            if (in_array($projeto['Situacao'], $situacoesDoProjeto)) {
                if (1 == $projeto['Mecanismo']) {
                    echo json_encode(array('resposta' => true));
                } else {
                    $string = utf8_encode('<b>Favor informar o número de PRONAC do tipo mecenato!</b>');
                    echo json_encode(array('resposta' => false, 'conteudo' => $string));
                }
            } else {
                $string = utf8_encode('<b>Apenas os projetos que estão na fase ' . implode(' ou ', $situacoesDoProjeto) . ' podem realizar captação!</b>');
                echo json_encode(array('resposta' => false, 'conteudo' => $string));
            }
        } else {
            $string = '<b>Pronac Inexistente!</b>';
            echo json_encode(array('resposta' => false, 'conteudo' => $string));
        }
    }

    public function localizarInteressadoAction()
    {

        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $get = Zend_Registry::get('get');
        $cpf = $get->cpf;

//		$busca = new Interessado();
        $busca = new Agentes();
        $agente = $busca->BuscaAgente($cpf)->toArray();

        $buscar = new Interessado();
        //$interessado = $buscar->buscar(array('CgcCpf = ?'=>$cpf))->toArray();

        if ($agente) {
            $busca = new Nomes();
            $interessado = $busca->buscar(array('idAgente = ?' => $agente[0]['idAgente']))->toArray();

            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $dadosInteressado['Nome'] = utf8_encode($interessado[0]['Descricao']);
            $jsonEncode = json_encode($dadosInteressado);
            //echo $jsonEncode;
            echo json_encode(array('resposta' => "true", 'conteudo' => $dadosInteressado));
            die;
        } else {
            echo json_encode(array('resposta' => false));
        }
    }

    public function realizarCaptacaoAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();

        $post = Zend_Registry::get('post');

        $interessadoModel = new Interessado();
        $interessados = $interessadoModel->buscar(array('CgcCpf = ?' => $post->cpf));

        if (!$interessados->count()) {
            $agenteModel = new Agentes();
            $agentes = $agenteModel->buscar(array('CNPJCPF = ?' => $post->cpf));
            if (!$agentes->count()) {
                parent::message("CNPJ/CPF não existe na tabela Interessado!", "captacao/index", "ALERT");
            }

            $agente = $agentes->current();
            $nomeModel = new Nomes();

            $tipoPessoa = $agente->TipoPessoa;
            if (!$tipoPessoa) {
                $tipoPessoa = (11 == strlen($agente->CNPJCPF)) ? 1 : 2;
            }

            $agenteNome = $nomeModel->buscar(array('idAgente = ?' => $agente->idAgente))->current();
            $interessadoModel->inserir(array(
                'CgcCpf' => $agente->CNPJCPF,
                'tipoPessoa' => $tipoPessoa,
                'Nome' => $agenteNome->Descricao,
                "Endereco" => "0",
                "Cidade" => "",
                "Uf" => "",
                "Cep" => "",
                "Responsavel" => "",
                "Grupo" => 1,
            ));
        }

        $insert = new Captacao();
        $captado = $insert->inserir(array(
            'AnoProjeto' => $post->anoProjeto,
            'Sequencial' => $post->sequencial,
            'isBemServico' => $this->_request->get('isBemServico'),
            'NumeroRecibo' => 99999,
            'logon' => $post->logon,
            'CgcCpfMecena' => $post->cpf,
            'DtRecibo' => data::dataAmericana($post->dt_recibo),
            'DtChegadaRecibo' => data::dataAmericana($post->dt_minc),
            'TipoApoio' => $post->tpApoio,
            'CaptacaoReal' => $post->valor,
            'CaptacaoUfir' => $post->valor
        ));

        $responseMessage = 'Projeto captado com sucesso!';
        $responseUrl = "captacao/index/pronac/{$this->_request->getParam('anoProjeto')}{$this->_request->getParam('sequencial')}";
        $responseType = 'CONFIRM';
        if (!$captado) {
            $responseMessage = 'Erro ao captar o projeto!';
            $responseType = 'ALERT';
        }
        parent::message($responseMessage, $responseUrl, $responseType);
    }

}
