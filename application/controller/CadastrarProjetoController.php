<?php

class CadastrarProjetoController extends GenericControllerNew {

    public function init() {
//recupera ID do pre projeto (proposta)
        $this->view->title = "Salic - Sistema de Apoio &agrave;s Leis de Incentivo &agrave; Cultura"; // tï¿½tulo da pï¿½gina
        $auth = Zend_Auth::getInstance(); // pega a autenticação
        $Usuario = new UsuarioDAO(); // objeto usuï¿½rio
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
        $PermissoesGrupo[] = 103; // Coordenador de Analise
        $PermissoesGrupo[] = 132; // Coordenador de Convenios

        if ($auth->hasIdentity()) { // caso o usuï¿½rio esteja autenticado
            // verifica as permissï¿½es
            $PermissoesGrupo = array();

            $PermissoesGrupo[] = 103; // Coordenador de Analise
            $PermissoesGrupo[] = 142; // Coordenador de Convenios


            if (!in_array($GrupoAtivo->codGrupo, $PermissoesGrupo)) { // verifica se o grupo ativo estï¿½ no array de permissï¿½es
                parent::message("Voc&ecirc; n&atilde;o tem permiss&atilde;o para acessar essa &aacute;rea do sistema!", "principal/index", "ALERT");
            }

            // pega as unidades autorizadas, orgãos e grupos do usuï¿½rio (pega todos os grupos)
            $grupos = $Usuario->buscarUnidades($auth->getIdentity()->usu_codigo, 21);

            // manda os dados para a visão
            $this->view->usuario = $auth->getIdentity(); // manda os dados do usuï¿½rio para a visão
            $this->view->arrayGrupos = $grupos; // manda todos os grupos do usuï¿½rio para a visão
            $this->view->grupoAtivo = $GrupoAtivo->codGrupo; // manda o grupo ativo do usuï¿½rio para a visão
            $this->view->orgaoAtivo = $GrupoAtivo->codOrgao; // manda o orgão ativo do usuï¿½rio para a visão
            
            $this->Orgao = $GrupoAtivo->codOrgao;
            $this->Usuario = $auth->getIdentity()->usu_codigo;
        } // fecha if
        else {
            return $this->_helper->redirector->goToRoute(array('controller' => 'index', 'action' => 'logout'), null, true);
        }

        parent::init(); // chama o init() do pai GenericControllerNew
    }

    public function indexAction() {
        
        $Modalidade = new tbModalidade();
        $mecanismo = new Mecanismo();
        $mecanismo2 = $mecanismo->buscar(array('Status = ?' => 1))->toArray();
        unset($mecanismo2[0]); //mecenato
        $tblSituacao = new Situacao();
        $rsSitucao = $tblSituacao->buscar(array("Codigo IN (?)" => array("A01", "A02", "A03", "A04", "A05", "A06", "A07", "A08", "A09", "A10", "A11", "A12", "A13", "A14", "A15", "A16", "A17", "A18", "A19", "A20", "A21", "A22", "A23", "A24", "A25", "A26", "A27", "A37", "A38", "A40", "A41", "B10", "B11", "B12", "B13", "B14", "B15", "E12")));

        $this->view->comboareasculturais = ManterAgentesDAO::buscarAreasCulturais();
        $this->view->comboestados = Estado::buscar();
        $this->view->mecanismo = $mecanismo2;
        $this->view->situacoes = $rsSitucao;
        $this->view->modalidade = $Modalidade->buscarModalidade();


        //$this->view->Segmento       =   $SegmentoDAO->buscar(array('stEstado = ?'=>1));			
        if (isset($_POST['areacultura']) and $_POST['areacultura'] == 'ok') {
            $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
            $post = Zend_Registry::get('post');
            $cdarea = $post->area;
            $dadosSegmento = Segmentocultural::buscar($cdarea);
            $i = 0;
            foreach ($dadosSegmento as $segmento) {
                $vSegmento[$i]['cdsegmento'] = $segmento->id;
                $vSegmento[$i]['descsegmento'] = utf8_encode($segmento->descricao);
                $i++;
            }
            $jsonSegmento = json_encode($vSegmento);
            echo $jsonSegmento;
            die;
        }


        //$this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
    }

    public function cadastrarprojetosAction() {

        $post = Zend_Registry::get('post');

        $sequencial = new tbSequencialProjetos();
        $projetos = new Projetos();
        

        $dados = array();

        
        $dados ['UfProjeto'] = $post->uf;
        $dados ['Area'] = $post->areacultural;
        $dados ['Segmento'] = $post->segmento;
        $dados ['Mecanismo'] = $post->mecanismo;
        $dados ['NomeProjeto'] = $post->nomedoprojeto;
        preg_match_all('#\d+#', $post->nrprocesso, $processo);
        $dados ['Processo'] = implode('',$processo[0]);
        preg_match_all('#\d+#', $post->cnpfcpf, $cgcCpf);
        $dados ['CgcCpf'] = implode('',$cgcCpf[0]);
        $dados ['Situacao'] = $post->situacao;
        $dados ['DtProtocolo'] = date('Y-m-d',strtotime(str_replace("/","-",$post->dtprotocolo)));
        $dados ['Modalidade'] = $post->modalidade;
        $dados ['ProvidenciaTomada'] = utf8_decode(trim($post->providenciatomada));
        $dados['Orgao'] = $this->Orgao;
        $dados['OrgaoOrigem'] = $this->Orgao;
        $dados['Logon'] = $this->Usuario;
        $dados['SolicitadoCusteioReal'] = str_replace(",",".",str_replace(".","",$post->VlCusteio));
        $dados['SolicitadoCapitalReal'] = str_replace(",",".",str_replace(".","",$post->VlCapital));
        $dados['DtAnalise'] = date('Y-m-d H:i:s');
        $dados['DtSituacao'] = date('Y-m-d H:i:s');
        $dados['ResumoProjeto'] = $post->ResumoProjeto;


        try {
            if(count($sequencial->verificarSequencial()->toArray()) == 0 ){ //verifica se ja existe o sequencial para o ano corrente, se não existir insere.
                $dado_sequencial = array('Ano'=>date('Y'),'Sequencial'=>1);
                $sequencial->inserirSequencial($dado_sequencial);
            }else{ //atualiza sequencial de projeto.
                $dado_sequencial = array('Sequencial' => new Zend_DB_Expr('Sequencial + 1'));
                $sequencial->atualizaSequencial($dado_sequencial);
                $nrSequencial = $sequencial->pegaSequencial()->toArray();
                $nrSequencial = $nrSequencial[0]['Sequencial'];
                if($nrSequencial > 9999){
                   $nrSequencial = str_pad($nrSequencial, 5, "0", STR_PAD_LEFT);
                } else {
                   $nrSequencial = str_pad($nrSequencial, 4, "0", STR_PAD_LEFT);
                }
  

            }
            
           $dados['AnoProjeto'] = date('y');
            $dados['Sequencial'] = $nrSequencial;
            
            $projetos->inserir($dados);
            
            
            parent::message("Projeto cadastado com sucesso", "cadastrarprojeto/index", "CONFIRM");
        } catch (Exception $e) {
            xd($e->getTrace());
            parent::message("Erro ao Cadastrar Projeto!", "cadastrarprojeto/index", "ERROR");
        }
    }

    public function validacaoprocessoAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $post = Zend_Registry::get('post');
        $ProcessoMascara = $post->nrprocesso;
        preg_match_all('#\d+#', $post->nrprocesso, $processo);
        $Processo = implode('',$processo[0]);
        header("Content-Type: text/html; charset=ISO-8859-1", true);
        if(Validacao::validarNrProcesso($Processo)){
            $projeto = new Projetos();
            $where = array('Processo =  ?'=>$Processo);
            $nrProcesso = $projeto->VerificaPronac($where)->toArray(); // verifica se processo ja está vinculado a um PRONAC.
            if(count($nrProcesso)> 0){
                $this->view->processo = 'Processo já vinculado a um PRONAC';
            } else {
                // verifica se processo existe no SAD.
                preg_match("#\.(.*?)\/#",$ProcessoMascara,$processoNumero);
                $processoNumero = (ltrim($processoNumero[1], "0"));
                $processoCei  = substr($Processo,1,4);
                $processoAno = substr($Processo,11,4);
                $processoSAD = new tbProcesso();
                $processoNumero = $processoSAD->verificaProcesso($processoNumero, $processoCei, $processoAno)->toArray();
                if(count($processoNumero) > 0){
                    $this->view->processo = 'ok';
                } else {
                    $this->view->processo = 'Processo inexistente no SAD';
                }
                
            }
        } else {
            $this->view->processo = 'Digito verificador do processo incorreto!';
        }
    }
    
    public function validaragenteAction(){
        header("Content-Type: text/html; charset=ISO-8859-1");
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $post = Zend_Registry::get('post');
        
        $agentes = new Agentes();
        
        preg_match_all('#\d+#', $post->CgcCpf, $cgcCpf);
        $CgcCpf = implode('',$cgcCpf[0]);
        
        $where = array('a.CNPJCPF = ?' =>$CgcCpf);
        
        $agente = $agentes->buscarAgenteNome($where)->toArray();
        
        if(count($agente) == 0 ){
            echo json_encode(array('agente'=>false));
        } else{
            echo json_encode(array('agente'=>true , 'descricao' => utf8_encode($agente[0]['Descricao']) ));
        }
        
        
    }

}
