<?php

include_once 'GenericController.php';

class AlterarprojetoController extends GenericControllerNew {

    private $idusuario = 0;
    private $codGrupo = 0;
    private $codOrgao = 0;
    private $tiposDocumento = array();
    private $getParecerista = 'N';
    private $idAgente = null;

    /**
     * Reescreve o método init()
     * @access public
     * @param void
     * @return void
     */
    public function init() {

        $auth = Zend_Auth::getInstance(); // pega a autenticaç?o
        $this->view->title = "Salic - Sistema de Apoio ?s Leis de Incentivo ? Cultura"; // título da página
        // 3 => autenticaç?o scriptcase e autenticaç?o/permiss?o zend (AMBIENTE PROPONENTE E MINC)
        // utilizar quando a Controller ou a Action for acessada via scriptcase e zend
        // define as permiss?es

        $this->idusuario = $auth->getIdentity()->usu_codigo;
        $GrupoAtivo = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo

        $codGrupo = $GrupoAtivo->codGrupo; //  Grupo ativo na sessão
        $codOrgao = $GrupoAtivo->codOrgao; //  Órgão ativo na sessão
        $this->codGrupo = $codGrupo; //  Grupo ativo na sessão
        $this->codOrgao = $codOrgao;
        $this->view->codOrgao = $codOrgao;
        $this->view->codGrupo = $codGrupo;
        $this->view->grupoativo = $codGrupo;

        //$this->view->idUsuarioLogado = $idusuario;
        $PermissoesGrupo = array();
        $PermissoesGrupo[] = 92;
        $PermissoesGrupo[] = 93;
        $PermissoesGrupo[] = 97;
        $PermissoesGrupo[] = 103;
        $PermissoesGrupo[] = 104;
        $PermissoesGrupo[] = 110;
        $PermissoesGrupo[] = 113;
        $PermissoesGrupo[] = 114;
        $PermissoesGrupo[] = 115;
        $PermissoesGrupo[] = 121;
        $PermissoesGrupo[] = 122;
        $PermissoesGrupo[] = 123;
        $PermissoesGrupo[] = 124;
        $PermissoesGrupo[] = 125;
        $PermissoesGrupo[] = 126;
        $PermissoesGrupo[] = 127;
        $PermissoesGrupo[] = 128;
        $PermissoesGrupo[] = 131;
        $PermissoesGrupo[] = 132;
        $PermissoesGrupo[] = 134;
        $PermissoesGrupo[] = 135;
        $PermissoesGrupo[] = 136;
        $PermissoesGrupo[] = 137;
        $PermissoesGrupo[] = 138;
        $PermissoesGrupo[] = 139;
        $PermissoesGrupo[] = 140;
        $PermissoesGrupo[] = 143;

        parent::perfil(1, $PermissoesGrupo);
        if (isset($auth->getIdentity()->usu_codigo)) {
            $this->getIdUsuario = UsuarioDAO::getIdUsuario($auth->getIdentity()->usu_codigo);
            if ($this->getIdUsuario) {
                $this->getIdUsuario = $this->getIdUsuario["idAgente"];
            } else {
                $this->getIdUsuario = 0;
            }
        } else {
            $this->getIdUsuario = $auth->getIdentity()->IdUsuario;
        }


        $tbDocumentos = new tbTipoDocumentoBDCORPORATIVO();
        $this->view->tiposDocumento = $tbDocumentos->buscar(array(), 'dsTipoDocumento desc');

        $pronac = $this->_request->getParam("pronac");
//        xd($pronac);

        if (!empty($pronac)) {

            if (strlen($pronac) > 7) {
                $pronac = Seguranca::dencrypt($pronac);
            }

            $ano = addslashes(substr($pronac, 0, 2));
            $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));

            $tbProjeto = New Projetos();
            $buscaProjeto = $tbProjeto->buscar(array("AnoProjeto = ?" => $ano, "Sequencial = ?" => $sequencial));
        
            if ( !empty( $buscaProjeto[0] ) ){
                $CgcCpf = $buscaProjeto[0]->CgcCpf;
            }else{
                parent::message("PRONAC n&atilde;o localizado!", "Alterarprojeto/consultarprojeto", "ERROR");
            }
            
            $agentes = New Agentes();
            $buscaTipoPessoa = $agentes->buscar(array('CNPJCPF = ?' => $CgcCpf));

            if ($buscaTipoPessoa[0]->TipoPessoa == 1) {
                $this->view->pj = "true";
            } else {
                $this->view->pj = "false";
            }

            $agentes = New Agentes();
            $buscaTipoPessoa = $agentes->buscar(array('CNPJCPF = ?' => $CgcCpf));
            $this->idAgente = $buscaTipoPessoa[0]->idAgente;

            /* Monta os dados do Agente */

            $idAgente = $this->idAgente;

            $qtdDirigentes = '';
            if (isset($idAgente)) {

                $dados = ManterAgentesDAO::buscarAgentes(null, null, $idAgente);

                if (!$dados) {
                    parent::message("Agente não encontrado!", "agentes/buscaragente", "ALERT");
                }

                $this->view->telefones = ManterAgentesDAO::buscarFones($idAgente);
                $this->view->emails = ManterAgentesDAO::buscarEmails($idAgente);
                $visoes = VisaoDAO::buscarVisao($idAgente);
                $this->view->visoes = $visoes;

                foreach ($visoes as $v) {
                    if ($v->Visao == '209') {
                        $this->getParecerista = 'sim';
                    }
                }

                if ($dados[0]->TipoPessoa == 1) {

                    $dirigentes = ManterAgentesDAO::buscarVinculados(null, null, null, null, $idAgente);
                    $qtdDirigentes = count($dirigentes);
                    $this->view->dirigentes = $dirigentes;
                }

                $this->view->dados = $dados;
                $this->view->qtdDirigentes = $qtdDirigentes;
                $this->view->parecerista = $this->getParecerista;
                $this->view->pronac = $pronac;
                //$this->view->idpronac = $_REQUEST['pronac'];
                $this->view->id = $idAgente;
            }
        }
            
        $this->view->comboestados = Estado::buscar();
        $this->view->combotiposenderecos = Tipoendereco::buscar();
        $this->view->combotiposlogradouros = Tipologradouro::buscar();
        $this->view->comboareasculturais = ManterAgentesDAO::buscarAreasCulturais();
        $this->view->combotipostelefones = Tipotelefone::buscar();
        $this->view->combotiposemails = Tipoemail::buscar();
        parent::init(); // chama o init() do pai GenericControllerNew
    }

// fecha método init()

    public function dirigentesAction() {

        //$pronac = addslashes($post->pronac);
        $pronac = $this->_request->getParam("pronac");

        if (strlen($pronac) > 12) {
            $pronac = Seguranca::dencrypt($pronac);
        } elseif (strlen($pronac) <= 12 && !isset($post->pesquisa) && $post->pesquisa != "true") {
            parent::message("PRONAC n&atilde;o localizado!", "Alterarprojeto/consultarprojeto", "ERROR");
        }

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));
        $this->view->pagina = "alterarprojeto";

        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {

            $tblAprovacao = new Aprovacao();
            $rsAprovacao = $tblAprovacao->buscar(array("AnoProjeto = ?" => $ano, "Sequencial = ?" => $sequencial));
//               if($rsAprovacao->count() <= 0){
//                    parent::message("Este Projeto ainda n&atilde;o foi aprovado", "Alterarprojeto/consultarprojeto", "ERROR");
//               }

            $listaparecer = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $listaparecer[0];
            $this->view->pronac = Seguranca::encrypt($listaparecer[0]->pronac);

            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $listaparecer[0]->IdPRONAC,
                "nmProjeto is not null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;
        } else {
            parent::message("PRONAC n&atilde;o localizado!", "Alterarprojeto/consultarprojeto", "ERROR");
        }
        //xd($listaparecer[0]->Orgao." != ".$this->codOrgao);
        if ($listaparecer[0]->Orgao != $this->codOrgao) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto", "Alterarprojeto/consultarprojeto", "ERROR");
        }
    }

    public function incluirdirigenteAction() {

        //$pronac = addslashes($post->pronac);
        $pronac = $this->_request->getParam("pronac");
        $idDirigente = $this->_request->getParam("idDirigente");
        
        if ( !empty($idDirigente) ){
            $this->view->idDirigente = "true";
        }

        if (strlen($pronac) > 12) {
            $pronac = Seguranca::dencrypt($pronac);
        } elseif (strlen($pronac) <= 12 && !isset($post->pesquisa) && $post->pesquisa != "true") {
            parent::message("PRONAC n&atilde;o localizado!", "Alterarprojeto/consultarprojeto", "ERROR");
        }

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));
        $this->view->pagina = "alterarprojeto";

        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {

            $tblAprovacao = new Aprovacao();
            $rsAprovacao = $tblAprovacao->buscar(array("AnoProjeto = ?" => $ano, "Sequencial = ?" => $sequencial));
//               if($rsAprovacao->count() <= 0){
//                    parent::message("Este Projeto ainda n&atilde;o foi aprovado", "Alterarprojeto/consultarprojeto", "ERROR");
//               }

            $listaparecer = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $listaparecer[0];
            $this->view->pronac = Seguranca::encrypt($listaparecer[0]->pronac);

            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $listaparecer[0]->IdPRONAC,
                "nmProjeto is not null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;
        } else {
            parent::message("PRONAC n&atilde;o localizado!", "Alterarprojeto/consultarprojeto", "ERROR");
        }
        //xd($listaparecer[0]->Orgao." != ".$this->codOrgao);
        if ($listaparecer[0]->Orgao != $this->codOrgao) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto", "Alterarprojeto/consultarprojeto", "ERROR");
        }
    }

    public function salvadirigentegeralAction() {


        $pronac = $this->_request->getParam("pronac");

        $Usuario = $this->getIdUsuario; // id do usuário logado
        //$idAgenteGeral = $this->_request->getParam("id"); // id da instituicao
        $idAgenteGeral = $this->idAgente; // id da instituicao
        // =============================================== INÍCIO SALVAR CPF/CNPJ ==================================================

        $cpf = Mascara::delMaskCPF(Mascara::delMaskCNPJ($this->_request->getParam("cpf"))); // retira as máscaras
        $Tipo = $this->_request->getParam("Tipo");

        $arrayAgente = array('CNPJCPF' => $cpf,
            'TipoPessoa' => $Tipo,
            'Status' => 0,
            'Usuario' => $Usuario
        );

        // Retorna o idAgente cadastrado

        $Agentes = new Agentes();
        $salvaAgente = $Agentes->inserirAgentes($arrayAgente);
        $Agente = $Agentes->BuscaAgente($cpf);
        $idAgente = $Agente[0]->idAgente;

        // ================================================ FIM SALVAR CPF/CNPJ =====================================================			
        // ================================================ INÍCIO SALVAR NOME ======================================================

        $nome = $this->_request->getParam("nome");
        $TipoNome = (strlen($cpf) == 11 ? 18 : 19); // 18 = pessoa física e 19 = pessoa jurídica

        try {
            $gravarNome = NomesDAO::gravarNome($idAgente, $TipoNome, $nome, 0, $Usuario);
        } catch (Exception $e) {
            parent::message("Erro ao salvar o nome: " . $e->getMessage(), "alterarprojeto/incluirdirigente/pronac/" . $pronac, "ERROR");
        }

        // ================================================ FIM SALVAR NOME ======================================================
        // ================================================ INICIO SALVAR VISÂO ======================================================
        $Visao = $this->_request->getParam("visao");
        $grupologado = $this->_request->getParam("grupologado");

        /*
         * Validação - Se for componente da comissão ele não salva a visão
         * Regra o componente da comissão não pode alterar sua visão.
         */

        if ($grupologado != 118):

            $GravarVisao = array(// insert
                'idAgente' => $idAgente,
                'Visao' => $Visao,
                'Usuario' => $Usuario,
                'stAtivo' => 'A');

            try {
                $busca = VisaoDAO::buscarVisao($idAgente, $Visao);
                if (!$busca) {
                    $i = VisaoDAO::cadastrarVisao($GravarVisao);
                }
            } catch (Exception $e) {
                parent::message("Erro ao salvar a visão: " . $e->getMessage(), "alterarprojeto/incluirdirigente/pronac/" . $pronac, "ERROR");
            }


            // ================================================ FIM SALVAR VISÂO ======================================================
            // ===================== INÍCIO SALVAR TITULAÇÃO (ÁREA/SEGMENTO DO COMPONENTE DA COMISSÃO) ================================


            $titular = $this->_request->getParam("titular");
            $areaCultural = $this->_request->getParam("areaCultural");
            $segmentoCultural = $this->_request->getParam("segmentoCultural");

            // só salva área e segmento para a visão de Componente da Comissão e se os campos titular e areaCultural forem informados
            if ((int) $Visao == 210 && ((int) $titular == 0 || (int) $titular == 1) && !empty($areaCultural)) {
                $GravarComponente = array(// insert
                    'idAgente' => $idAgente,
                    'cdArea' => $areaCultural,
                    'cdSegmento' => $segmentoCultural,
                    'stTitular' => $titular,
                    'stConselheiro' => 'A');

                $AtualizarComponente = array(// update
                    'cdArea' => $areaCultural,
                    'cdSegmento' => $segmentoCultural,
                    'stTitular' => $titular
                        //'stConselheiro' => 'A' -- Qual caso de uso vai ativa e desativar?										
                );

                try {
                    // busca a titulação do agente (titular/suplente de área cultural)
                    $busca = TitulacaoConselheiroDAO::buscarComponente($idAgente, $Visao);

                    if (!$busca) {
                        $i = TitulacaoConselheiroDAO::gravarComponente($GravarComponente);
                    } else {
                        $i = TitulacaoConselheiroDAO::atualizaComponente($idAgente, $AtualizarComponente);
                    }
                } catch (Exception $e) {
                    parent::message("Erro ao salvar a área e segmento: " . $e->getMessage(), $e->getMessage(), "alterarprojeto/incluirdirigente/pronac/" . $pronac, "ERROR");
                }
            }

        // ============================= FIM SALVAR TITULAÇÃO (ÁREA/SEGMENTO DO COMPONENTE DA COMISSÃO) ===========================

        endif; // Fecha o if da regra do componente da comissão
        // =========================================== INICIO SALVAR ENDEREÇOS ====================================================

        $cepEndereco = $this->_request->getParam("cep");
        $tipoEndereco = $this->_request->getParam("tipoEndereco");
        $ufEndereco = $this->_request->getParam("uf");
        $CidadeEndereco = $this->_request->getParam("cidade");
        $Endereco = $this->_request->getParam("logradouro");
        $divulgarEndereco = $this->_request->getParam("divulgarEndereco");
        $tipoLogradouro = $this->_request->getParam("tipoLogradouro");
        $numero = $this->_request->getParam("numero");
        $complemento = $this->_request->getParam("complemento");
        $bairro = $this->_request->getParam("bairro");
        $enderecoCorrespodencia = 1;

        try {

            $arrayEnderecos = array(
                'idAgente' => $idAgente,
                'Cep' => str_replace(".", "", str_replace("-", "", $cepEndereco)),
                'TipoEndereco' => $tipoEndereco,
                'UF' => $ufEndereco,
                'Cidade' => $CidadeEndereco,
                'Logradouro' => $Endereco,
                'Divulgar' => $divulgarEndereco,
                'TipoLogradouro' => $tipoLogradouro,
                'Numero' => $numero,
                'Complemento' => $complemento,
                'Bairro' => $bairro,
                'Status' => $enderecoCorrespodencia,
                'Usuario' => $Usuario
            );


            $insere = EnderecoNacionalDAO::gravarEnderecoNacional($arrayEnderecos);
        } catch (Exception $e) {
            parent::message("Erro ao salvar o endereço: " . $e->getMessage(), "alterarprojeto/incluirdirigente/pronac/" . $pronac, "ERROR");
        }


        // ============================================= FIM SALVAR ENDEREÇOS ====================================================
        // =========================================== INICIO SALVAR TELEFONES ====================================================

        $tipoFone = $this->_request->getParam("tipoFone");
        $ufFone = $this->_request->getParam("ufFone");
        $dddFone = $this->_request->getParam("dddFone");
        $Fone = $this->_request->getParam("fone");
        $divulgarFone = $this->_request->getParam("divulgarFone");

        try {
            $arrayTelefones = array(
                'idAgente' => $idAgente,
                'TipoTelefone' => $tipoFone,
                'UF' => $ufFone,
                'DDD' => $dddFone,
                'Numero' => $Fone,
                'Divulgar' => $divulgarFone,
                'Usuario' => $Usuario
            );

            $insere = Telefone::cadastrar($arrayTelefones);
        } catch (Exception $e) {
            parent::message("Erro ao salvar o telefone: " . $e->getMessage(), "alterarprojeto/incluirdirigente/pronac/" . $pronac, "ERROR");
        }


        // =========================================== FIM SALVAR TELEFONES ====================================================
        // =========================================== INICIO SALVAR EMAILS ====================================================			

        $tipoEmail = $this->_request->getParam("tipoEmail");
        $Email = $this->_request->getParam("email");
        $divulgarEmail = $this->_request->getParam("divulgarEmail");
        $enviarEmail = 1;

        try {
            $arrayEmail = array(
                'idAgente' => $idAgente,
                'TipoInternet' => $tipoEmail,
                'Descricao' => $Email,
                'Status' => $enviarEmail,
                'Divulgar' => $divulgarEmail,
                'Usuario' => $Usuario
            );

            $insere = Email::cadastrar($arrayEmail);
        } catch (Exception $e) {
            parent::message("Erro ao salvar o e-mail: " . $e->getMessage(), "alterarprojeto/incluirdirigente/pronac/" . $pronac, "ERROR");
        }

        // =========================================== FIM SALVAR EMAILS ====================================================
        // =========================================== INICIO SALVAR VINCULO ====================================================			

        try {
            // busca o dirigente vinculado ao cnpj/cpf
            $dadosDirigente = ManterAgentesDAO::buscarVinculados(null, null, $idAgente, $idAgenteGeral, $idAgenteGeral);

            // caso o agente não esteja vinculado, realizará a vinculação
            if (!$dadosDirigente) {
                // associa o dirigente ao cnpj/cpf
                $dadosVinculacao = array(
                    'idAgente' => $idAgente,
                    'idVinculado' => $idAgenteGeral,
                    'idVinculoPrincipal' => $idAgenteGeral,
                    'Usuario' => $Usuario
                );

                $vincular = ManterAgentesDAO::cadastrarVinculados($dadosVinculacao);
            }
        } catch (Exception $e) {
            parent::message("Erro ao vincular o dirigente: " . $e->getMessage(), "alterarprojeto/incluirdirigente/pronac/" . $pronac, "ERROR");
        }


        parent::message("Cadastro realizado com sucesso!", "alterarprojeto/dirigentes/pronac/" . $pronac, "CONFIRM");
    }

    // Final da função salvar dirigentes

    public function desvinculadirigenteAction() {


        $Usuario = $this->getIdUsuario; // id do usuário logado
        $pronac = $this->_request->getParam("pronac");
        $idAgenteGeral = $this->_request->getParam("id");
        $idDirigente = $this->_request->getParam("idDirigente");

        try {

            $vincular = new Vinculacao();

            $where = "Idcaptacao = " . $where;

            $where = array('idAgente = ' . $idDirigente,
                'idVinculado = ' . $idAgenteGeral);

            $desvincula = $vincular->Desvincular($where);

            parent::message("Exclusão realizada com sucesso! ", "alterarprojeto/dirigentes/pronac/" . $pronac, "CONFIRM");
        } catch (Exception $e) {
            parent::message("Erro ao vincular o dirigente: " . $e->getMessage(), "alterarprojeto/visualizadirigente/id/" . $idAgenteGeral . "/idDirigente/" . $idDirigente . "/pronac/" . $pronac, "ERROR");
        }
    }

    public function visualizadirigenteAction() {

        $idAgente = $this->_request->getParam("id");
        $pronac = $this->_request->getParam("pronac");
        $idDirigente = $this->_request->getParam("idDirigente");

        if (strlen($pronac) > 12) {
            $pronac = Seguranca::dencrypt($pronac);
        } elseif (strlen($pronac) <= 12 && !isset($post->pesquisa) && $post->pesquisa != "true") {
            parent::message("PRONAC n&atilde;o localizado!", "Alterarprojeto/consultarprojeto", "ERROR");
        }

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));
        $this->view->pagina = "alterarprojeto";

        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {

            $tblAprovacao = new Aprovacao();
            $rsAprovacao = $tblAprovacao->buscar(array("AnoProjeto = ?" => $ano, "Sequencial = ?" => $sequencial));
//               if($rsAprovacao->count() <= 0){
//                    parent::message("Este Projeto ainda n&atilde;o foi aprovado", "Alterarprojeto/consultarprojeto", "ERROR");
//               }

            $listaparecer = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $listaparecer[0];
            $this->view->pronac = Seguranca::encrypt($listaparecer[0]->pronac);

            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $listaparecer[0]->IdPRONAC,
                "nmProjeto is not null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;
        } else {
            parent::message("PRONAC n&atilde;o localizado!", "Alterarprojeto/consultarprojeto", "ERROR");
        }
        //xd($listaparecer[0]->Orgao." != ".$this->codOrgao);
        if ($listaparecer[0]->Orgao != $this->codOrgao) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto", "Alterarprojeto/consultarprojeto", "ERROR");
        }

        //xd($idDirigente);

        if (isset($idAgente)) {

            $dadosDirigenteD = ManterAgentesDAO::buscarVinculados(null, null, $idDirigente, null, $idAgente);
            $dados = ManterAgentesDAO::buscarAgentes(null, null, $idDirigente);
            $this->view->dadosD = $dados;

            if (!$dados) {
                parent::message("Agente não encontrado!", "alterarprojeto/buscaragentedirigente/pronac/" . $pronac, "ALERT");
            }

            $this->view->telefonesD = ManterAgentesDAO::buscarFones($idDirigente);
            $this->view->emailsD = ManterAgentesDAO::buscarEmails($idDirigente);
            $this->view->visoesD = VisaoDAO::buscarVisao($idDirigente);
            $this->view->Instituicao = "sim";
            $this->view->id = $this->_request->getParam("id");
            $this->view->idDirigente = $this->_request->getParam("idDirigente");

            if ($dadosDirigenteD) {
                $this->view->vinculado = "sim";
            }
            
            $tbTipodeDocumento = new VerificacaoAGENTES();
            $whereLista['idTipo = ?'] = 5;
            $rsTipodeDocumento = $tbTipodeDocumento->buscar($whereLista);
            $this->view->tipoDocumento = $rsTipodeDocumento;

            $tbDirigenteMandato = new tbAgentesxVerificacao();
            $buscarMandato = $tbDirigenteMandato->listarMandato(array('idEmpresa = ?' => $idAgente, 'idDirigente = ?' => $idDirigente, 'stMandato = ?' => 0));
            $this->view->mandatos = $buscarMandato;
            $mandatoAtual = $tbDirigenteMandato->listarMandato(array('idEmpresa = ?' => $idAgente,'idDirigente = ?' => $idDirigente, 'stMandato = ?' => 0), array('dtFimMandato DESC'))->current();
            $this->view->mandatosAtual = $mandatoAtual;
        }
    }

    public function mandatoAction() {

        if (!empty($_POST)) {
            $idAgente = $this->_request->getParam("id");
            $idDirigente = $this->_request->getParam("idDirigente");
            $pronac = $this->_request->getParam("pronac");

            $tbDirigenteMandato = new tbAgentesxVerificacao();

            $idVerificacao      = $this->_request->getParam("idVerificacao");
            $dsNumeroDocumento  = $this->_request->getParam("dsNumeroDocumento");
            $idDirigente        = $this->_request->getParam("idDirigente");
            $dtInicioVigencia   = $this->_request->getParam("dtInicioVigencia");
            $dtInicioVigencia   = Data::dataAmericana($dtInicioVigencia);
            $dtTerminoVigencia  = $this->_request->getParam("dtTerminoVigencia");
            $dtTerminoVigencia  = Data::dataAmericana($dtTerminoVigencia);
            $stMandato          = 0;
            $idArquivo          = $this->_request->getParam("idArquivo");

            //validação data do mandato            
            $buscarMandato = $tbDirigenteMandato->mandatoRepetido($idAgente, $dtInicioVigencia, $dtTerminoVigencia);

            if (count($buscarMandato) > 0) {
                parent::message("Não poderá inserir um novo mandato, pois já existe um mandato em vigor para esse dirigente!mandatos", "alterarprojeto/visualizadirigente/id/" . $idAgente . "/idDirigente/" . $idDirigente . "/pronac/" . $pronac, "CONFIRM");
            }

            if (count($_FILES) > 0) {

                foreach ($_FILES['arquivo']['name'] as $key => $val) {
                    $arquivoNome = $_FILES['arquivo']['name'][$key];
                    $arquivoTemp = $_FILES['arquivo']['tmp_name'][$key];
                    $arquivoTipo = $_FILES['arquivo']['type'][$key];
                    $arquivoTamanho = $_FILES['arquivo']['size'][$key];

                    $arquivoExtensao = Upload::getExtensao($arquivoNome); // extensão

                    if ($arquivoExtensao != "doc" && $arquivoExtensao != "docx") {
                        if (!empty($arquivoTemp)) {
                            $idArquivo = $this->cadastraranexo($arquivoNome, $arquivoTemp, $arquivoTipo, $arquivoTamanho);

                            $dados = array(
                                'idArquivo' => $idArquivo,
                                'idTipoDocumento' => 0,
                            );

                            $tabela = new tbDocumento();
                            $idDocumento = $tabela->inserir($dados);
                            if ($idDocumento) {
                                $idDocumento = $tabela->ultimodocumento(array('idArquivo = ? ' => $idArquivo));
                                $idDocumento = $idDocumento->idDocumento;
                            } else {
                                $ERROR .= "Erro no anexo";
                                $idDocumento = 0;
                                $erro = true;
                            }
                        }
                    } else {
                        parent::message("N&atilde;o s&atilde;o permitidos documentos de texto doc/docx!", "alterarprojeto/visualizadirigente/id/" . $idAgente . "/idDirigente/" . $idDirigente . "/pronac/" . $pronac, "CONFIRM");
                    }
                }
            }
            try {
                $arrayMandato = array(
                    'idVerificacao' => $idVerificacao,
                    'dsNumeroDocumento' => $dsNumeroDocumento,
                    'dtInicioMandato' => $dtInicioVigencia,
                    'dtFimMandato' => $dtTerminoVigencia,
                    'stMandato' => $stMandato,
                    'idEmpresa' => $idAgente,
                    'idDirigente' => $idDirigente
                );
//                xd($arrayMandato);

                if ($idArquivo > 0) {
                    $arrayMandato['idArquivo'] = $idArquivo;
                }

                $salvarMandato = $tbDirigenteMandato->inserir($arrayMandato);

                $buscarMandato = $tbDirigenteMandato->buscar(array('idAgentesxVerificacao = ?' => $salvarMandato));

                if (!empty($buscarMandato)) {

                    $dadosBuscar['idVerificacao'] = $buscarMandato[0]->idVerificacao;
                    $dadosBuscar['dsNumeroDocumento'] = $buscarMandato[0]->dsNumeroDocumento;
                    $dadosBuscar['dtInicioMandato'] = $buscarMandato[0]->dtInicioMandato;
                    $dadosBuscar['dtFimMandato'] = $buscarMandato[0]->dtFimMandato;
                    $dadosBuscar['stMandato'] = $buscarMandato[0]->stMandato;
                    $dadosBuscar['idEmpresa'] = $buscarMandato[0]->idEmpresa;
                    $dadosBuscar['idDirigente'] = $buscarMandato[0]->idDirigente;
                    $dadosBuscar['idArquivo'] = $buscarMandato[0]->idArquivo;
//                echo json_encode($dadosBuscar);
//                exit();
                }

                parent::message("Cadastro realizado com sucesso!", "alterarprojeto/visualizadirigente/id/" . $idAgente . "/idDirigente/" . $idDirigente . "/pronac/" . $pronac, "CONFIRM");
            } catch (Exception $e) {
                parent::message("Erro ao salvar o mandato:" . $e->getMessage(), "alterarprojeto/visualizadirigente/id/" . $idAgente . "/idDirigente/" . $idDirigente . "/pronac/" . $pronac, "ERROR");
            }
        }
    }

    /*
     * Método excluirmandato()
     * @access public
     * @param void
     * @return void
     */

    public function excluirmandatoAction() {

        $tbDirigenteMandato = new tbAgentesxVerificacao();

        $idAgente = $this->_request->getParam("id");
        $idDirigente = $this->_request->getParam("idDirigente");
        $idMandato = $this->_request->getParam("idAgentesxVerificacao");
        $pronac = $this->_request->getParam("pronac");

        try {
            $arrayMandato = array('stMandato' => 1);

            $whereMandato['idAgentesxVerificacao = ?'] = $idMandato;
            $tbDirigenteMandato->alterar($arrayMandato, $whereMandato);

            parent::message("Exclusão realizada com sucesso!", "alterarprojeto/visualizadirigente/id/" . $idAgente . "/idDirigente/" . $idDirigente . "/pronac/" . $pronac, "CONFIRM");
        } catch (Exception $e) {
            parent::message("Erro ao excluir o mandato:" . $e->getMessage(), "alterarprojeto/visualizadirigente/id/" . $idAgente . "/idDirigente/" . $idDirigente . "/pronac/" . $pronac, "ERROR");
        }
    }

    public function cadastraranexo($arquivoNome, $arquivoTemp, $arquivoTipo, $arquivoTamanho) {
        if (!empty($arquivoNome) && !empty($arquivoTemp)) {
            $arquivoExtensao = Upload::getExtensao($arquivoNome); // extensï¿½o
            $arquivoBinario = Upload::setBinario($arquivoTemp); // binï¿½rio
            $arquivoHash = Upload::setHash($arquivoTemp); // hash
        }

        // cadastra dados do arquivo
        $dadosArquivo = array(
            'nmArquivo' => $arquivoNome,
            'sgExtensao' => $arquivoExtensao,
            'dsTipoPadronizado' => $arquivoTipo,
            'nrTamanho' => $arquivoTamanho,
            'dtEnvio' => new Zend_Db_Expr('GETDATE()'),
            'dsHash' => $arquivoHash,
            'stAtivo' => 'A');
        $cadastrarArquivo = ArquivoDAO::cadastrar($dadosArquivo);

        // pega o id do ï¿½ltimo arquivo cadastrado
        $idUltimoArquivo = ArquivoDAO::buscarIdArquivo();
        $idUltimoArquivo = (int) $idUltimoArquivo[0]->id;

        // cadastra o binï¿½rio do arquivo
        $dadosBinario = array(
            'idArquivo' => $idUltimoArquivo,
            'biArquivo' => $arquivoBinario);
        $cadastrarBinario = ArquivoImagemDAO::cadastrar($dadosBinario);

        return $idUltimoArquivo;
    }

    public function buscaragentedirigenteAction() {

        // caso o formulário seja enviado via post
        if ($this->getRequest()->isPost()) {
            // recebe os dados do formulário
            $post = Zend_Registry::get('post');
            $cpf = Mascara::delMaskCPF(Mascara::delMaskCNPJ($post->cpf)); // deleta a máscara
            $nome = $post->nome;

            try {
                // validação dos campos
                if (empty($cpf) && empty($nome)) {
                    throw new Exception("Dados obrigatórios não informados:<br /><br />É necessário informar o CPF/CNPJ ou o Nome!");
                } else if (!empty($cpf) && strlen($cpf) != 11 && strlen($cpf) != 14) { // valida cnpj/cpf
                    throw new Exception("O CPF/CNPJ informado é inválido!");
                } else if (!empty($cpf) && strlen($cpf) == 11 && !Validacao::validarCPF($cpf)) { // valida cpf
                    throw new Exception("O CPF informado é inválido!");
                } else if (!empty($cpf) && strlen($cpf) == 14 && !Validacao::validarCNPJ($cpf)) { // valida cnpj
                    throw new Exception("O CNPJ informado é inválido!");
                } else {
                    // redireciona para a página com a busca dos dados com paginação
                    $this->_redirect("agentes/listaragente?cpf=" . $cpf . "&nome=" . $nome);
                } // fecha else
            } // fecha try
            catch (Exception $e) {
                $this->view->message = $e->getMessage();
                $this->view->message_type = "ERROR";
                $this->view->cpf = !empty($cpf) ? Validacao::mascaraCPFCNPJ($cpf) : ''; // caso exista, adiciona a máscara
                $this->view->nome = $nome;
            }
        } // fecha if
    }

    public function agentecadastradoAction() {
        //$this->autenticacao();
        $this->_helper->layout->disableLayout(); // desabilita o layout
        $this->_helper->viewRenderer->setNoRender(true);
        $cpf = $_REQUEST['cpf'];

        $novos_valores = array();

        $dados = ManterAgentesDAO::buscarAgentes($cpf);

        if ((strlen($cpf) == 11 && !Validacao::validarCPF($cpf)) || (strlen($cpf) == 14 && !Validacao::validarCNPJ($cpf))) {
            $novos_valores[0]['msgCPF'] = utf8_encode('invalido');
        } else {
            if (count($dados) != 0) {
                foreach ($dados as $dado) {
                    $novos_valores[0]['msgCPF'] = utf8_encode('cadastrado');
                    $novos_valores[0]['idAgente'] = utf8_encode($dado->idAgente);
                    $novos_valores[0]['Nome'] = utf8_encode($dado->Nome);
                }
            } else {
                $novos_valores[0]['msgCPF'] = utf8_encode('novo');
            }
        }

        echo json_encode($novos_valores);
    }

// fecha método buscaragentedirigenteAction()

    public function vinculadirigenteAction() {

        $Usuario = $this->getIdUsuario; // id do usuário logado
        $pronac = $this->_request->getParam("pronac");
        $idAgenteGeral = $this->_request->getParam("id");
        $idDirigente = $this->_request->getParam("idDirigente");

        try {
            // busca o dirigente vinculado ao cnpj/cpf
            $dadosDirigente = ManterAgentesDAO::buscarVinculados(null, null, $idDirigente, $idAgenteGeral, $idAgenteGeral);

            // caso o agente não esteja vinculado, realizará a vinculação
            if (count($dadosDirigente) == 0) {
                // associa o dirigente ao cnpj/cpf
                $dadosVinculacao = array(
                    'idAgente' => $idDirigente,
                    'idVinculado' => $idAgenteGeral,
                    'idVinculoPrincipal' => $idAgenteGeral,
                    'Usuario' => $Usuario
                );


                $vincular = ManterAgentesDAO::cadastrarVinculados($dadosVinculacao);
                //xd($vincular);
            }

            parent::message("Cadastrado realizado com sucesso! ", "alterarprojeto/dirigentes/id/" . $idAgenteGeral . "/pronac/" . $pronac, "CONFIRM");
        } catch (Exception $e) {
            parent::message("Erro ao vincular o dirigente: " . $e->getMessage(), "alterarprojeto/visualizadirigente/id/" . $idAgenteGeral . "/idDirigente/" . $idDirigente . "/pronac/" . $pronac, "ERROR");
        }
    }

    public function indexAction() {
        $this->_redirect("/alterarprojeto/consultarprojeto");
    }

    public function consultarprojetoAction() {
        $pronac = $this->_request->getParam("pronac");
        $this->view->pronac = $pronac;
    }

    public function alterarprojetoAction() {

        $post = Zend_Registry::get('post');

        //$pronac = addslashes($post->pronac);
        $pronac = $this->_request->getParam("pronac");
        
        if (strlen($pronac) > 12) {
            $pronac = Seguranca::dencrypt($pronac);
        } elseif (strlen($pronac) <= 12 && !isset($post->pesquisa) && $post->pesquisa != "true") {
            parent::message("PRONAC n&atilde;o localizado!", "Alterarprojeto/consultarprojeto", "ALERT");
        }

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));
        $this->view->pagina = "alterarprojeto";

        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {

            $tblAprovacao = new Aprovacao();
            $rsAprovacao = $tblAprovacao->buscar(array("AnoProjeto = ?" => $ano, "Sequencial = ?" => $sequencial));

            $listaparecer = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $listaparecer[0];
            $this->view->pronac = Seguranca::encrypt($listaparecer[0]->pronac);

            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $listaparecer[0]->IdPRONAC,
                "nmProjeto is not null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;
        } else {
            parent::message("PRONAC n&atilde;o localizado!", "Alterarprojeto/consultarprojeto", "ALERT");
        }
        //xd($listaparecer[0]->Orgao." != ".$this->codOrgao);
        if ($listaparecer[0]->Orgao != $this->codOrgao) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto!", "Alterarprojeto/consultarprojeto", "ALERT");
        }
    }

    public function situacaoAction() {
        $get = Zend_Registry::get('get');
        //$pronac = addslashes($get->pronac);
        $pronac = $this->_request->getParam("pronac");
        //verficia se o pronac esta criptografado
        $pronac = Seguranca::dencrypt($pronac);

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));

        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {
            $dadosprojeto = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $dadosprojeto[0];
            $this->view->pronac = Seguranca::encrypt($dadosprojeto[0]->pronac);

            $situacaoDao = new Situacao();
            $situacao = $situacaoDao->listasituacao();
            $this->view->situacao = $situacao;

            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $dadosprojeto[0]->IdPRONAC,
                "cdSituacao is not null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;
            //$this->view->documentos = array();
        } else {
            parent::message("Dados obrigat&oacute;rios n&atilde;o informados", "alterarprojeto/consultarprojeto", "ERROR");
        }
        if ($dadosprojeto[0]->Orgao != $this->codOrgao && $this->codGrupo != 125 && $this->codGrupo != 126) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto", "alterarprojeto/consultarprojeto", "ERROR");
        }
    }

    public function imprimirsituacaoAction() {
        $get = Zend_Registry::get('get');
        //verficia se o pronac esta criptografado
        $pronac = $get->pronac;

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));

        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {
            $dadosprojeto = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $dadosprojeto[0];
            $this->view->pronac = Seguranca::encrypt($dadosprojeto[0]->pronac);

            $situacaoDao = new Situacao();
            $situacao = $situacaoDao->listasituacao();
            $this->view->situacao = $situacao;

            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $dadosprojeto[0]->IdPRONAC,
                "cdSituacao is not null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;
            //$this->view->documentos = array();
        } else {
            parent::message("Dados obrigat&oacute;rios n&atilde;o informados", "alterarprojeto/consultarprojeto", "ERROR");
        }
        if ($dadosprojeto[0]->Orgao != $this->codOrgao && $this->codGrupo != 125 && $this->codGrupo != 126) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto", "alterarprojeto/consultarprojeto", "ERROR");
        }
        $this->_helper->layout->disableLayout();// Desabilita o Zend Layout
    }

    public function sinteseAction() {
        $get = Zend_Registry::get('get');
        $pronac = $this->_request->getParam("pronac");
        //verficia se o pronac esta criptografado
        $pronac = Seguranca::dencrypt($pronac);

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));

        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {
            $dadosprojeto = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $dadosprojeto[0];
            $this->view->pronac = Seguranca::encrypt($dadosprojeto[0]->pronac);
            $this->view->sintese = $validapronac[0]->ResumoProjeto;

            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $dadosprojeto[0]->IdPRONAC,
                "cdSituacao is not null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;

            if (($dadosprojeto[0]->Orgao != $this->codOrgao) || $this->codGrupo != 103) {
                parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto!", "alterarprojeto/consultarprojeto", "ERROR");
            }

        } else {
            parent::message("Dados obrigat&oacute;rios n&atilde;o informados.", "alterarprojeto/consultarprojeto", "ERROR");
        }
    }

    public function areasegmentoAction() {
        $get = Zend_Registry::get('get');
        $pronac = addslashes($get->pronac);
        $pronac = Seguranca::dencrypt($pronac);

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));


        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {
            $listaparecer = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $listaparecer[0];
            $this->view->pronac = Seguranca::encrypt($listaparecer[0]->pronac);
            
            $this->view->comboareasculturais = ManterAgentesDAO::buscarAreasCulturais();
            $this->view->combosegmentosculturais = Segmentocultural::buscarSegmento($listaparecer[0]->Area);

            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $listaparecer[0]->IdPRONAC,
                "cdArea is not null" => '?',
                "cdSegmento is not null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;
        } else {
            parent::message("Dados obrigat&oacute;rios n&atilde;o informados", "Alterarprojeto/consultarprojeto", "ERROR");
        }
        if ($listaparecer[0]->Orgao != $this->codOrgao) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto", "Alterarprojeto/consultarprojeto", "ERROR");
        }
    }

    public function orgaoAction() {
        $get = Zend_Registry::get('get');
        $pronac = addslashes($get->pronac);
        $pronac = Seguranca::dencrypt($pronac);

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));


        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {
            $listaparecer = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $listaparecer[0];
            $this->view->pronac = Seguranca::encrypt($listaparecer[0]->pronac);
            $orgaosDAO = new Orgaos();
            $orgaos = $orgaosDAO->pesquisarTodosOrgaos();
            $this->view->orgaos = $orgaos;

            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $listaparecer[0]->IdPRONAC,
                "cdOrgao is not null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;
        } else {
            parent::message("Dados obrigat&oacute;rios n&atilde;o informados", "Alterarprojeto/consultarprojeto", "ERROR");
        }
        if ($listaparecer[0]->Orgao != $this->codOrgao) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto", "Alterarprojeto/consultarprojeto", "ERROR");
        }
    }

    public function habilitarprojetoAction() {
        $get = Zend_Registry::get('get');
        $pronac = addslashes($get->pronac);
        $pronac = Seguranca::dencrypt($pronac);

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));

        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);
        
        $tbTipoInabilitado = new tbTipoInabilitado();
        $combo = $tbTipoInabilitado->buscar(array('idTipoInabilitado <> ?' => 8), array(1));
        $this->view->combo = $combo;

        if (count($validapronac) > 0) {
            $listaparecer = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $listaparecer[0];
            $this->view->pronac = Seguranca::encrypt($listaparecer[0]->pronac);
            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $listaparecer[0]->IdPRONAC,
                "cdOrgao is null" => '?',
                "cdArea  is null" => '?',
                "cdSegmento is null" => '?',
                "nmProjeto  is null" => '?',
                "cdSituacao  is null" => '?',
                "dtInicioExecucao is null" => '?',
                "dtFimExecucao is null" => '?',
                "idEnquadramento is null" => '?',
                "cgccpf is null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;
        } else {
            parent::message("Dados obrigat&oacute;rios n&atilde;o informados", "Alterarprojeto/consultarprojeto", "ERROR");
        }
        if ($listaparecer[0]->Orgao != $this->codOrgao) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto", "Alterarprojeto/consultarprojeto", "ERROR");
        }
    }

    public function enquadramentoAction() {
        $get = Zend_Registry::get('get');
        $pronac = addslashes($get->pronac);
        $pronac = Seguranca::dencrypt($pronac);

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));


        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {
            $listaparecer = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $listaparecer[0];
            $this->view->pronac = Seguranca::encrypt($listaparecer[0]->pronac);
            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $listaparecer[0]->IdPRONAC,
                "idEnquadramento is not null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;

            if ($listaparecer[0]->Mecanismo != 1) {
                parent::message("N&tilde;o é permitido enquadramento para este PRONAC", "Alterarprojeto/consultarprojeto", "ERROR");
            }
        } else {
            parent::message("Dados obrigat&oacute;rios n&atilde;o informados", "Alterarprojeto/consultarprojeto", "ERROR");
        }
        if ($listaparecer[0]->Orgao != $this->codOrgao) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto", "Alterarprojeto/consultarprojeto", "ERROR");
        }
    }

    public function periododeexecucaoAction() {
        $get = Zend_Registry::get('get');
        $pronac = addslashes($get->pronac);
        $pronac = Seguranca::dencrypt($pronac);

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));


        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {
            $listaparecer = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $listaparecer[0];
            $this->view->pronac = Seguranca::encrypt($listaparecer[0]->pronac);
            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $listaparecer[0]->IdPRONAC,
                "dtInicioExecucao is not null" => '?',
                "dtFimExecucao is not null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;
        } else {
            parent::message("Dados obrigat&oacute;rios n&atilde;o informados", "Alterarprojeto/consultarprojeto", "ERROR");
        }
        if ($listaparecer[0]->Orgao != $this->codOrgao) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto", "Alterarprojeto/consultarprojeto", "ERROR");
        }
    }

    public function anexosAction() {
        $get = Zend_Registry::get('get');
        $pronac = addslashes($get->pronac);
        $pronac = Seguranca::dencrypt($pronac);

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));


        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {
            $listaparecer = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $this->view->parecer = $listaparecer[0];
            $this->view->pronac = Seguranca::encrypt($listaparecer[0]->pronac);
            $documentosDao = new tbDocumentosAgentes();
            $documentos = $documentosDao->buscatodosdocumentos($validapronac[0]->Logon, $validapronac[0]->idProjeto, $validapronac[0]->IdPRONAC);
            $this->view->documentos = $documentos;
        } else {
            parent::message("Dados obrigat&oacute;rios n&atilde;o informados", "Alterarprojeto/consultarprojeto", "ERROR");
        }
        if ($listaparecer[0]->Orgao != $this->codOrgao) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto", "Alterarprojeto/consultarprojeto", "ERROR");
        }
    }

    public function proponenteAction() {

        $get = Zend_Registry::get('get');
        $pronac = addslashes($get->pronac);
        $pronac = Seguranca::dencrypt($pronac);

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));


        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $projeto = new Projetos();
        $validapronac = $projeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {
            $listaparecer = $projeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $agente = new Agentes();
            $agente = $agente->buscarAgenteNome(array("CNPJCPF = ?" => $listaparecer[0]->CgcCpf));
            if (count($agente) > 0) {
                $this->view->agente = $agente[0]['Descricao'];
            } else {
                $this->view->agente = '<font color="#FF0000"><b>Agente n&atilde;o cadastrado</b>';
            }
            $this->view->parecer = $listaparecer[0];
            $this->view->pronac = Seguranca::encrypt($listaparecer[0]->pronac);

            $documentoDao = new tbHistoricoAlteracaoProjeto();
            $where = array(
                "P.idPRONAC =?" => $listaparecer[0]->IdPRONAC,
                "cgccpf is not null" => '?'
            );
            $documentos = $documentoDao->listadocumentosanexados($where);
            $this->view->documentos = $documentos;
        } else {
            parent::message("Dados obrigat&oacute;rios n&atilde;o informados", "Alterarprojeto/consultarprojeto", "ERROR");
        }
        if ($listaparecer[0]->Orgao != $this->codOrgao) {
            parent::message("Usu&aacute;rio sem autoriza&ccedil;&atilde;o no org&atilde;o do projeto", "Alterarprojeto/consultarprojeto", "ERROR");
        }
    }

    public function buscaragenteAction() {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout->disableLayout();
        $get = Zend_Registry::get('get');
        $cpfatual = str_replace("/", "", str_replace("-", "", str_replace(".", "", addslashes($get->cpf))));
        $cpf = str_replace("/", "", str_replace("-", "", str_replace(".", "", addslashes($get->cpf))));

        if (strlen($cpf) == 11 or strlen($cpf) == 14) {
            $agente = new Agentes();
            $agente = $agente->buscarAgenteNome(array("CNPJCPF = ?" => $cpf));

            $inabilitados = new Inabilitado();

            $whereInabilitadoAtual['CgcCpf = ?'] = $cpfatual;
            $whereInabilitadoAtual['Habilitado = ?'] = 'N';
            $buscarInabilitadoAtual = $inabilitados->buscar($whereInabilitadoAtual);

            $whereInabilitado['CgcCpf = ?'] = $cpf;
            $whereInabilitado['Habilitado = ?'] = 'N';
            $buscarInabilitado = $inabilitados->buscar($whereInabilitado);

            if (count($buscarInabilitadoAtual) > 0) {
                echo "<font color='#FF0000'><b>Proponente atual est&aacute; Inabilitado</b></font><input type='hidden' name='nome' id='nome' value=''>";
            } else if (count($buscarInabilitado) > 0) {
                echo "<font color='#FF0000'><b>Proponente Inabilitado</b></font><input type='hidden' name='nome' id='nome' value=''>";
            } else if (count($agente) > 0) {
                echo utf8_encode($agente[0]->Descricao) . "<input type='hidden' name='nome' id='nome' value='" . utf8_encode($agente[0]->Descricao) . "'>";
            } else {
                echo "<font color='#FF0000'><b>Proponente n&atilde;o cadastrado</b></font><input type='hidden' name='nome' id='nome' value=''>";
            }
        } else {
            echo "<font color='#FF0000'><b>CPF / CNPJ inv&aacute;lido</b></font><input type='hidden' name='nome' id='nome' value=''>";
        }
    }

    public function salvaalterarprojetoAction() {
        $post = Zend_Registry::get('post');

        //$pronac = addslashes($post->pronac);
        $pronac = $this->_request->getParam("pronac");
        //verficia se o pronac esta criptografado
        if (strlen($pronac) > 12) {
            $pronac = Seguranca::dencrypt($pronac);
        }

        if (!empty($post->Situacao)) {
            $providenciaTomada = $post->justificativa;
        } else {
            $providenciaTomada = '';
        }

        $ano = addslashes(substr($pronac, 0, 2));
        $sequencial = addslashes(substr($pronac, 2, strlen($pronac)));

        $arrBusca = array(
            'tbr.anoprojeto =?' => $ano,
            'tbr.sequencial =?' => $sequencial,
        );
        $tblProjeto = new Projetos();
        $validapronac = $tblProjeto->VerificaPronac($arrBusca);

        if (count($validapronac) > 0) {
            $dadosProjeto = $tblProjeto->buscarTodosDadosProjeto($validapronac[0]->IdPRONAC);
            $dadosProjeto = $dadosProjeto[0];
        } else {
            parent::message("Dados obrigat&oacute;rios n&atilde;o informados", "alterarprojeto/consultarprojeto", "ERROR");
        }

        // Verificando se o Projeto atual está desabilitado e se pode ser habilitado!
        if ((!empty($post->habilitado)) && ($post->habilitado == 'S')) {
            $tbl = new Inabilitado();

            $whereI['AnoProjeto = ?'] = $dadosProjeto->AnoProjeto;
            $whereI['Sequencial = ?'] = $dadosProjeto->Sequencial;
            $retorno = $tbl->Localizar($whereI);
            $msg = 'O proponente não pode ser habilitado à presente data. Para habilitá-lo, favor anexar documento.';

            if ((count($retorno) > 0) && ($retorno[0]->idTipoInabilitado > 0) && ($retorno[0]->idTipoInabilitado <= 7) && ($retorno[0]->Anos < 1)) {
                parent::message($msg, "alterarprojeto/" . $post->pagina . "?pronac=" . Seguranca::encrypt($dadosProjeto->pronac) . "&menu=" . $post->menu, "ALERT");
            } else if ((count($retorno) > 0) && ($retorno[0]->idTipoInabilitado >= 4) && ($retorno[0]->idTipoInabilitado <= 5) && ($retorno[0]->Anos < 2)) {
                parent::message($msg, "alterarprojeto/" . $post->pagina . "?pronac=" . Seguranca::encrypt($dadosProjeto->pronac) . "&menu=" . $post->menu, "ALERT");
            } else if ((count($retorno) > 0) && ($retorno[0]->idTipoInabilitado >= 6) && ($retorno[0]->idTipoInabilitado <= 7) && ($retorno[0]->Anos < 3)) {
                parent::message($msg, "alterarprojeto/" . $post->pagina . "?pronac=" . Seguranca::encrypt($dadosProjeto->pronac) . "&menu=" . $post->menu, "ALERT");
            }
        }

        $dados = Null;
        $dados = array(//Monta dados para o historico
            'idPRONAC' => $dadosProjeto->IdPRONAC,
            'idLogon' => $this->idusuario,
            'cdArea' => null,
            'cdSegmento' => null,
            'nmProjeto' => null,
            'cdSituacao' => null,
            'cdOrgao' => null,
            'dtInicioExecucao' => null,
            'dtFimExecucao' => null,
            'idEnquadramento' => null,
            'CGCCPF' => null,
            'dsProvidenciaTomada' => $providenciaTomada,
            'dsHistoricoAlteracaoProjeto' => $post->justificativa,
            'dtHistoricoAlteracaoProjeto' => date("Y-m-d H:i:s")
        );

        if (!empty($post->Area)) {
            $dados['cdArea'] = $dadosProjeto->Area;
            $dados['dsProvidenciaTomada'] = 'Area -> ' . $post->Area;
        }
        if (!empty($post->Segmento)) {
            $dados['cdSegmento'] = $dadosProjeto->Segmento;
            $dados['dsProvidenciaTomada'] .= '/ Segmento -> ' . $post->Segmento;
        }
        if (!empty($post->NomeProjeto)) {
            $dados['nmProjeto'] = $dadosProjeto->NomeProjeto;
            $dados['dsProvidenciaTomada'] = $post->NomeProjeto;
        }
        if (!empty($post->sinteseProjeto)) {
            $dados['dsProvidenciaTomada'] = "Sintese do Projeto -> ".$post->sinteseProjeto;
        }
        if (!empty($post->Situacao)) {
            $dados['cdSituacao'] = $dadosProjeto->Situacao;
            $dados['dsProvidenciaTomada'] = $providenciaTomada;
        }
        if (!empty($post->Orgao)) {
            $dados['cdOrgao'] = $dadosProjeto->Orgao;
            $dados['dsProvidenciaTomada'] = $post->Orgao;
        }
        if (!empty($post->dtInicioExecucao)) {
            $dados['dtInicioExecucao'] = $dadosProjeto->DtInicioExecucao;
            $dados['dsProvidenciaTomada'] = "Inicio -> " . $post->dtInicioExecucao;
        }
        if (!empty($post->dtFimExecucao)) {
            $dados['dtFimExecucao'] = $dadosProjeto->DtFimExecucao;
            $dados['dsProvidenciaTomada'] .= "/ Fim -> " . $post->dtFimExecucao;
        }
        if (!empty($post->idEnquadramento)) {
            $dados['idEnquadramento'] = $dadosProjeto->Enquadramento;
            $dados['dsProvidenciaTomada'] = $post->idEnquadramento;
        }
        if (!empty($post->CGCCPF)) {
            $dados['CGCCPF'] = $dadosProjeto->CgcCpf;
            $dados['dsProvidenciaTomada'] = str_replace("/", "", str_replace("-", "", str_replace(".", "", addslashes($post->CGCCPF))));
        }
        if (!empty($post->habilitado)) {
            $dados['dsProvidenciaTomada'] = "Inabilitado = " . $post->habilitado;
        }
        if (!empty($post->dtInicioExecucao) && !empty($post->dtFimExecucao)) {
            $dados['dtInicioExecucao'] = ConverteData($post->dtInicioExecucao, 13);
            $dtInicio = $dados['dtInicioExecucao'];
            $dados['dtFimExecucao'] = ConverteData($post->dtFimExecucao, 13);
            $dtFim = $dados['dtFimExecucao'];
        }

        $tblHistoricoAlteracaoProjeto = new tbHistoricoAlteracaoProjeto();
        $idHistAlteracaoProjeto = $tblHistoricoAlteracaoProjeto->inserir($dados); //salva historico 

        if ($idHistAlteracaoProjeto > 1) { //Se tiver salvo o historico atualiza a tabela projeto
            $tblHistoricoAlteracaoDoc = new tbHistoricoAlteracaoDocumento();
            if (!empty($post->documentoid)) {
                foreach ($post->documentoid as $documentoid) {//Salva o relacionamento da tabela documentos com a de historico
                    $dados = array(
                        'idHistoricoAlteracaoProjeto' => $idHistAlteracaoProjeto,
                        'idDocumento' => $documentoid,
                        'idDocumentosExigidos' => '58'
                    );
                    $respostaArqXLog = $tblHistoricoAlteracaoDoc->salvar($dados);
                }
            }

            if (!empty($post->arquivoid)) {
                foreach ($post->arquivoid as $arquivoid) { //Atualiza a situacao dos arquivos para ativo
                    $atualizaArquivo = ArquivoDAO::alterar(array('stAtivo' => 'A'), $arquivoid);
                }
            }

            $dados = null;
            $dados = array(
                'idPRONAC' => $dadosProjeto->IdPRONAC,
            );

            if (!empty($post->Situacao)) {
                $this->validasituacao($dadosProjeto);
            }
            if (!empty($post->habilitado)) {
                $this->salvahabilitado($dadosProjeto);
            }
            //funcao para inserir o novo proponente na tabela Interesados

            if (!empty($post->CGCCPF)) {
                $interessadoTb = new Interessado();
                $interessado = $interessadoTb->Busca($where = array("CgcCpf = ? " => str_replace("/", "", str_replace("-", "", str_replace(".", "", addslashes($post->CGCCPF))))));

                if (count($interessado) <= 0) {
                    $dadosProponente = array(
                        "CgcCpf" => str_replace("/", "", str_replace("-", "", str_replace(".", "", addslashes($post->CGCCPF)))),
                        "Nome" => $post->nome,
                        "Endereco" => "0",
                        "Cidade" => "",
                        "Uf" => "",
                        "Cep" => "",
                        "Responsavel" => "",
                        "Grupo" => 1
                    );

                    if (str_replace("/", "", str_replace("-", "", str_replace(".", "", addslashes($post->CGCCPF)))) > 11) {
                        $dadosProponente['tipoPessoa'] = 1;
                    } else {
                        $dadosProponente['tipoPessoa'] = 1;
                    }
                    $interessadoTb->inserir($dadosProponente);
                }
                
                $Agentes = new Agentes();
                $tbDocumentosAgentes = new tbDocumentosAgentes();
                $ag = $Agentes->buscar(array('CNPJCPF = ?'=> Mascara::delMaskCPFCNPJ($post->CGCCPF)))->current();
                $docs = $tbDocumentosAgentes->buscarDocumentos(array('a.idAgente = ?'=>$ag->idAgente));
                if(count($docs) == 0){
                    parent::message("Os documentos do novo proponente não estão cadastrados no sistema. Favor anexar os documentos!", "alterarprojeto/" . $post->pagina . "?pronac=" . Seguranca::encrypt($dadosProjeto->pronac) . "&menu=" . $post->menu, "ALERT");
                }
            }

            if (!empty($post->Area)) {
                $dados['Area'] = $post->Area;
            }
            if (!empty($post->Segmento)) {
                $dados['Segmento'] = $post->Segmento;
            }
            if (!empty($post->NomeProjeto)) {
                $dados['NomeProjeto'] = $post->NomeProjeto;
            }
            if (!empty($post->sinteseProjeto)) {
                $dados['ResumoProjeto'] = $post->sinteseProjeto;
            }
            if (!empty($post->Situacao)) {
                $dados['Situacao'] = $post->Situacao;
                $dados['DtSituacao'] = date("Y-m-d H:i:s");
                $dados['ProvidenciaTomada'] = $providenciaTomada;
            }
            if (!empty($post->Orgao)) {
                $dados['Orgao'] = $post->Orgao;
            }
            if (!empty($post->dtInicioExecucao)) {
                $dados['DtInicioExecucao'] = $dtInicio;
            }
            if (!empty($post->dtFimExecucao)) {
                $dados['DtFimExecucao'] = $dtFim;
            }
            if (!empty($post->CGCCPF)) {
                $dados['CgcCpf'] = str_replace("/", "", str_replace("-", "", str_replace(".", "", addslashes($post->CGCCPF))));
            }

            /**
             * ==============================================================
             * INICIO DA ATUALIZACAO DO VINCULO DO PROPONENTE
             * ==============================================================
             */
            $Projetos = new Projetos();
            $Agentes = new Agentes();
            $Visao = new Visao();
            $tbVinculo = new TbVinculo();
            $tbVinculoProposta = new tbVinculoProposta();

            /* ========== BUSCA OS DADOS DO PROPONENTE ANTIGO ========== */
            $buscarCpfProponenteAntigo = $Projetos->buscar(array('AnoProjeto+Sequencial = ?' => $post->pronac));
            $cpfProponenteAntigo = count($buscarCpfProponenteAntigo) > 0 ? $buscarCpfProponenteAntigo[0]->CgcCpf : 0;
            $buscarIdProponenteAntigo = $Agentes->buscar(array('CNPJCPF = ?' => $cpfProponenteAntigo));
            $idProponenteAntigo = count($buscarIdProponenteAntigo) > 0 ? $buscarIdProponenteAntigo[0]->idAgente : 0;
            $idPreProjetoVinculo = count($buscarCpfProponenteAntigo) > 0 ? $buscarCpfProponenteAntigo[0]->idProjeto : 0;

            /* ========== BUSCA OS DADOS DO NOVO PROPONENTE ========== */
            $buscarNovoProponente = $Agentes->buscar(array('CNPJCPF = ?' => Mascara::delMaskCPFCNPJ($post->CGCCPF)));
            $idNovoProponente = count($buscarNovoProponente) > 0 ? $buscarNovoProponente[0]->idAgente : 0;
            $buscarVisao = $Visao->buscar(array('Visao = ?' => 144, 'idAgente = ?' => $idNovoProponente));

            /* ========== BUSCA OS DADOS DA PROPOSTA VINCULADA ========== */
            $idVinculo = $tbVinculoProposta->buscar(array('idPreProjeto = ?' => $idPreProjetoVinculo));

            /* ========== ATUALIZA O VINCULO DO PROPONENTE ========== */
//            if (count($buscarVisao) > 0 && count($idVinculo) > 0) :

//                $whereVinculo = array('idVinculo = ?' => $idVinculo[0]->idVinculo);
//
//                $dadosVinculo = array(
//                    'idAgenteProponente' => $idNovoProponente
//                    , 'dtVinculo' => new Zend_Db_Expr('GETDATE()'));
//
//                $tbVinculo->alterar($dadosVinculo, $whereVinculo);
//            else :
//                parent::message("O usuário informado não é Proponente ou o Projeto não está vinculado a uma Proposta!", "alterarprojeto/" . $post->pagina . "?pronac=" . Seguranca::encrypt($dadosProjeto->pronac), "ERROR");
//            endif;

            /**
             * ==============================================================
             * FIM DA ATUALIZACAO DO VINCULO DO PROPONENTE
             * ==============================================================
             */
            //ATUALIZA DADOS DO PROJETO
            $idProjeto = $tblProjeto->salvar($dados);


            if (!empty($post->idEnquadramento)) {
                $dados = null;
                $dados = array(
                    'IdEnquadramento' => $dadosProjeto->IdEnquadramento,
                    'Enquadramento' => $post->idEnquadramento,
                    'DtEnquadramento' => date("Y-m-d H:i:s"),
                    'Logon' => $this->idusuario
                );
                $tblEnquadramento = new Enquadramento();
                $idEnquadramento = $tblEnquadramento->alterarEnquadramento($dados);
            }

            if ($idProjeto == $dadosProjeto->IdPRONAC) {
                if ($post->idEnquadramento) {
                    if ($idEnquadramento == $dadosProjeto->IdEnquadramento && $idEnquadramento !== false) {
                        parent::message("Altera&ccedil;&atilde;o realizada com sucesso!", "alterarprojeto/" . $post->pagina . "?pronac=" . Seguranca::encrypt($dadosProjeto->pronac) . "&menu=" . $post->menu, "CONFIRM");
                    } else {
                        parent::message("Esse Projeto n&atilde;o possui Enquadramento", "alterarprojeto/" . $post->pagina . "?pronac=" . Seguranca::encrypt($dadosProjeto->pronac) . "&menu=" . $post->menu, "ERROR");
                    }
                } else {
                    parent::message("Altera&ccedil;&atilde;o realizada com sucesso!", "alterarprojeto/" . $post->pagina . "?pronac=" . Seguranca::encrypt($dadosProjeto->pronac) . "&menu=" . $post->menu, "CONFIRM");
                }
            } else {
                parent::message("Erro ao salvar dados", "alterarprojeto/" . $post->pagina . "?pronac=" . Seguranca::encrypt($dadosProjeto->pronac) . "&menu=" . $post->menu, "ERROR");
            }
        } else {
            parent::message("Erro ao salvar dados", "alterarprojeto/" . $post->pagina . "?pronac=" . Seguranca::encrypt($dadosProjeto->pronac), "ERROR");
        }
    }

    public function incluirarquivoAction() {
        $this->_helper->layout->disableLayout(); // desabilita o Zend_Layout
        $post = Zend_Registry::get('post');
        $idArquivo = $this->anexararquivo();
        $this->view->erro = "";
        $this->view->arquivo = array();
        $dados = array(
            'idArquivo' => $idArquivo,
            'idTipoDocumento' => $post->classificacao,
            'dsDocumento' => substr($post->justificativa, 0, 399)
        );

        $tabela = new tbDocumento();
        try {
            $idDocumento = $tabela->inserir($dados);
            if ($post->tipodocumento == "Proponente") {
                $dados = array(
                    'idTipoDocumento' => $post->classificacao,
                    'idDocumento' => $idDocumento['idDocumento'],
                    'idAgente' => $post->idagente,
                    'stAtivoDocumentoAgente' => 1
                );
                $tabela = new tbDocumentoAgenteBDCORPORATIVO();
                $tabela->inserir($dados);
            } else {
                $dados = array(
                    'idTipoDocumento' => $post->classificacao,
                    'idDocumento' => $idDocumento['idDocumento'],
                    'idPronac' => $post->idpronac,
                    'stAtivoDocumentoProjeto' => 1
                );
                $tabela = new tbDocumentoProjetoBDCORPORATIVO();
                $tabela->inserir($dados);
            }

            $tabela = new tbDocumento();
            if ($idDocumento) {
                $idDocumento = $tabela->ultimodocumento(array('idArquivo = ? ' => $idArquivo));
                $idDocumento = $idDocumento->idDocumento;
            } else {
                $this->view->erro = "O aquivo n&atilde;o pode ser anexado";
            }

            $dados = array(
                "id" => $idDocumento,
                "arquivoid" => $idArquivo,
                "data" => date('d/m/Y H:i'),
                "arquivo" => $_FILES['arquivo']['name']
            );

            $this->view->arquivo = $dados;
        } catch (Zend_Exception_Db $e) {
            $this->view->erro = "erro " . $e;
        }


        //xd("<script>windows.parent.inserir();alert('".$dadosarquivo."')</script>");
    }

    private function validasituacao($dadosProjeto) {

        $post = Zend_Registry::get('post');
        $tbl = new Captacao();
        $capitacao = $tbl->listaCaptacao($dadosProjeto->AnoProjeto, $dadosProjeto->Sequencial);

        $erro = "";
        $valor = 0;
        $situacao = 0;
        foreach ($capitacao as $capitacao) {
            $valor = $valor + $capitacao->CaptacaoReal;
        }
        $tbl2 = new Situacao();
        $situacao = $tbl2->listasituacao(array($post->Situacao));
        $situacao = $situacao[0]->StatusProjeto;

        if ($post->Situacao == 'E04' and $valor == 0) {
            $erro = "Projeto sem capta&ccedil;&atilde;o de recursos n&atilde;o pode ser arquivado nesta situa&ccedil;&atilde;o. A situa&ccedil;&atilde;o do projeto n&atilde;o foi alterada";
        } elseif ($post->Situacao == 'E10' and $valor > 0) {
            $erro = "Situa&ccedil;&atilde;o incorreta. Para o projeto com capta&ccedil;&atilde;o a correta &eacute; E12. A situa&ccedil;&atilde;o do projeto n&atilde;o foi alterada";
        } elseif ($post->Situacao == 'E11' and $valor > 0) {
            $erro = "Situa&ccedil;&atilde;o incorreta. Para o projeto com capta&ccedil;&atilde;o a correta &eacute; E15. A situa&ccedil;&atilde;o do projeto n&atilde;o foi alterada";
        } elseif ($post->Situacao == 'E12' and $valor == 0) {
            $erro = "Situa&ccedil;&atilde;o incorreta. Para o projeto sem capta&ccedil;&atilde;o a correta &eacute; E10. A situa&ccedil;&atilde;o do projeto n&atilde;o foi alterada";
        } elseif ($post->Situacao == 'E15' and $valor == 0) {
            $erro = "Situa&ccedil;&atilde;o incorreta. Para o projeto sem capta&ccedil;&atilde;o a correta &eacute; E11. A situa&ccedil;&atilde;o do projeto n&atilde;o foi alterada";
        } elseif ($post->Situacao == 'E16' and $valor > 0) {
            $erro = "Situa&ccedil;&atilde;o incorreta. Para o projeto com capta&ccedil;&atilde;o a correta &eacute; E23. A situa&ccedil;&atilde;o do projeto n&atilde;o foi alterada";
        } elseif ($post->Situacao == 'E23' and $valor == 0) {
            $erro = "Situa&ccedil;&atilde;o incorreta. Para o projeto sem capta&ccedil;&atilde;o a correta &eacute; E16. A situa&ccedil;&atilde;o do projeto n&atilde;o foi alterada";
        } elseif ($post->Situacao == 'K00' and $valor > 0) {
            $erro = "Projeto com capta&ccedil;&atilde;o de recursos n&atilde;o pode ser arquivado. A situa&ccedil;&atilde;o do projeto n&atilde;o foi alterada";
        } elseif ($situacao == 0) {
            $erro = "Situa&ccedil;&atilde;o desabilitada. A situa&ccedil;&atilde;o do projeto n&atilde;o foi alterada";
        }

        if (strlen($erro) > 1) {
            parent::message($erro, "alterarprojeto/" . $post->pagina . "?pronac=" . Seguranca::encrypt($dadosProjeto->pronac), "ERROR");
        } else {
            $tbl3 = new Projetos();
            $tbl3->alterarSituacao(null, $dadosProjeto->AnoProjeto . $dadosProjeto->Sequencial, $post->Situacao); //Salvar Historico na tabela Situaç?o*/
            return true;
        }
    }

    private function salvahabilitado($dadosProjeto) {
        $post = Zend_Registry::get('post');
        $dados = array(
            'CgcCpf' => $dadosProjeto->CgcCpf,
            'AnoProjeto' => $dadosProjeto->AnoProjeto,
            'Sequencial' => $dadosProjeto->Sequencial,
            'Orgao' => $dadosProjeto->Orgao,
            'Logon' => $this->idusuario,
            'Habilitado' => $post->habilitado,
            'idTipoInabilitado' => ($post->habilitado == 'S') ? null : !empty($post->penalidade) ? $post->penalidade : null,
            'dtInabilitado' => ($post->habilitado == 'S') ? null : date("Y-m-d H:i:s")
        );
        $tbl = new Inabilitado();
        $retorno = $tbl->BuscarInabilitado($dadosProjeto->CgcCpf, $dadosProjeto->AnoProjeto, $dadosProjeto->Sequencial);

        if (count($retorno) > 0) {
            $retorno2 = $tbl->alterar($dados, array('AnoProjeto = ?'=>$dadosProjeto->AnoProjeto, 'Sequencial = ?'=>$dadosProjeto->Sequencial));
        } else {
            $retorno2 = $tbl->inserir($dados);
        }
        if ($retorno2) {
            return true;
        } else {
            return false;
        }
    }

    private function anexararquivo() {
        // pega as informações do arquivo
        $idUltimoArquivo = 'null';
        $post = Zend_Registry::get('post');
        if (is_file($_FILES['arquivo']['tmp_name'])) {
            $arquivoNome = $_FILES['arquivo']['name']; // nome
            $arquivoTemp = $_FILES['arquivo']['tmp_name']; // nome temporário
            $arquivoTipo = $_FILES['arquivo']['type']; // tipo
            $arquivoTamanho = $_FILES['arquivo']['size']; // tamanho
            if (!empty($arquivoNome) && !empty($arquivoTemp)) {
                $arquivoExtensao = Upload::getExtensao($arquivoNome); // extensão
                $arquivoBinario = Upload::setBinario($arquivoTemp); // binário
                $arquivoHash = Upload::setHash($arquivoTemp); // hash
            }

            // cadastra dados do arquivo
            // cadastra dados do arquivo
            $dadosArquivo = array(
                'nmArquivo' => $arquivoNome,
                'sgExtensao' => $arquivoExtensao,
                'dsTipoPadronizado' => $arquivoTipo,
                'nrTamanho' => $arquivoTamanho,
                'dtEnvio' => new Zend_Db_Expr('GETDATE()'),
                'dsHash' => $arquivoHash,
                'stAtivo' => 'I');
            $cadastrarArquivo = ArquivoDAO::cadastrar($dadosArquivo);

            // pega o id do último arquivo cadastrado
            $idUltimoArquivo = ArquivoDAO::buscarIdArquivo();
            $idUltimoArquivo = (int) $idUltimoArquivo[0]->id;

            // cadastra o binário do arquivo
            $dadosBinario = array(
                'idArquivo' => $idUltimoArquivo,
                'biArquivo' => $arquivoBinario);
            $cadastrarBinario = ArquivoImagemDAO::cadastrar($dadosBinario);
        }
        return $idUltimoArquivo;
    }

}

?>
