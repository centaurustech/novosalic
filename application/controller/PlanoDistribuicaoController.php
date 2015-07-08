<?php 
class PlanoDistribuicaoController extends GenericControllerNew
{
        private $intTamPag;
        private $_idPreProjeto = null;

	/**
	 * Reescreve o método init()
	 * @access public
	 * @param void
	 * @return void
	 */
	public function init()
	{
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

            // inicializando variaveis com valor padrao
            $this->intTamPag = 10;

            //carregando variaveis vindas de GET
            $get = Zend_Registry::get('get');
            if(!empty ($_REQUEST['idPreProjeto'])){
                $this->_idPreProjeto = $get->idPreProjeto;
                //VERIFICA SE A PROPOSTA ESTA COM O MINC
                $Movimentacao = new Movimentacao();
                $rsStatusAtual = $Movimentacao->buscarStatusAtualProposta($_REQUEST['idPreProjeto']);
                $this->view->movimentacaoAtual = isset($rsStatusAtual->Movimentacao) ? $rsStatusAtual->Movimentacao : '';
            }else{
                if($_REQUEST['idPreProjeto'] != '0'){
                    parent::message("Necessário informar o número da proposta.", "/manterpropostaincentivofiscal/index", "ERROR");
                }
            }

            parent::init();
            
            /* =============================================================================== */ 
            /* ==== VERIFICA PERMISSAO DE ACESSO DO PROPONENTE A PROPOSTA OU AO PROJETO ====== */
            /* =============================================================================== */
            $this->verificarPermissaoAcesso(true, false, false);
	}

        public function indexAction(){
            $pag = 1;
            $get = Zend_Registry::get('get');
            if (isset($get->pag)) $pag = $get->pag;
            if (isset($get->tamPag)) $this->intTamPag = $get->tamPag;
            $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
            $fim = $inicio + $this->intTamPag;
            $tblPlanoDistribuicao = new PlanoDistribuicao();
            $total = $tblPlanoDistribuicao->pegaTotal(array("a.idProjeto = ?"=>$this->_idPreProjeto, "a.stPlanoDistribuicaoProduto = ?"=>1));
            $tamanho = (($inicio+$this->intTamPag)<=$total) ? $this->intTamPag : $total - ($inicio) ;

            
            $rsPlanoDistribuicao = $tblPlanoDistribuicao->buscar(array("a.idProjeto = ?"=>$this->_idPreProjeto, "a.stPlanoDistribuicaoProduto = ?"=>1), array("idPlanoDistribuicao DESC"), $tamanho, $inicio);

            if ($fim>$total) $fim = $total;
            $totalPag = (int)(($total % $this->intTamPag == 0)?($total/$this->intTamPag):(($total/$this->intTamPag)+1));

            $arrDados = array(
                            "pag"=>$pag,
                            "total"=>$total,
                            "inicio"=>($inicio+1),
                            "fim"=>$fim,
                            "totalPag"=>$totalPag,
                            "planosDistribuicao"=>$rsPlanoDistribuicao,
                            "formulario"=>$this->_urlPadrao."/plano-distribuicao/frm-plano-distribuicao?idPreProjeto=".$this->_idPreProjeto,
                            "urlApagar"=>$this->_urlPadrao."/plano-distribuicao/apagar?idPreProjeto=".$this->_idPreProjeto,
                            "urlPaginacao"=>$this->_urlPadrao."/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto
                        );
            
            $this->montaTela("planodistribuicao/index.phtml", $arrDados);
        }

        public function consultarComponenteAction()
        {
            $get = Zend_Registry::get("get");
            $idPreProjeto = $get->idPreProjeto;
            $this->_helper->layout->disableLayout(); // desabilita o layout

            if(!empty($idPreProjeto) || $idPreProjeto=='0')
            {
                $tblPlanoDistribuicao = new PlanoDistribuicao();
                $rsPlanoDistribuicao = $tblPlanoDistribuicao->buscar(array("idProjeto=?"=>$idPreProjeto, "stPlanoDistribuicaoProduto=?"=>1), array("idPlanoDistribuicao DESC"),10);
                $arrDados = array("planosDistribuicao"=>$rsPlanoDistribuicao);
                $this->montaTela("planodistribuicao/consultar-componente.phtml", $arrDados);
            }
            else {
                return false;
            }

        }

        public function frmPlanoDistribuicaoAction(){
            
            $this->_helper->viewRenderer->setNoRender(true);
            $this->_helper->layout->disableLayout();
            header("Content-Type: text/html; charset=ISO-8859-1");

            $bln_exitePP = "false"; //Nao existe Produto Principal cadastrado

            $get = Zend_Registry::get("get");
            if(!empty($get->idPlanoDistribuicao)){
                $tblPlanoDistribuicao = new PlanoDistribuicao();
                $rsPlanoDistribuicao = $tblPlanoDistribuicao->buscarPlanoDistribuicao(array('idPlanoDistribuicao = ?' =>$get->idPlanoDistribuicao));
                $arrDados["planoDistribuicao"] = $rsPlanoDistribuicao;
            }

            $tblProduto = new Produto();
            $rsProdutos = $tblProduto->buscar(array("stEstado = ?"=>0),array("Descricao ASC"));

            //BUSCA POR PRODUTO PRINCIPAL CADASTRADO
            $tblPlanoDistribuicao = new PlanoDistribuicao();
            $arrPlanoDistribuicao = $tblPlanoDistribuicao->buscar(array("a.idProjeto = ?"=>$this->_idPreProjeto, "a.stPrincipal = ?"=>1, "a.stPlanoDistribuicaoProduto = ?"=>1), array("idPlanoDistribuicao DESC"))->toArray();
            if(!empty($arrPlanoDistribuicao)){
                $bln_exitePP = "true"; //Existe Produto Principal Cadastrado
            }
            
            $tblLogomarca = new Logomarca();
            $rsLogomarcas = $tblLogomarca->buscar(array("idTipo=?"=>3));

            $arrDados["combologomarcas"] = $rsLogomarcas;
            $arrDados["comboprodutos"] = $rsProdutos;
            $arrDados["comboareasculturais"] = ManterAgentes::buscarAreasCulturais();
            $arrDados["acaoSalvar"] = $this->_urlPadrao."/plano-distribuicao/salvar?idPreProjeto=".$this->_idPreProjeto;
            $arrDados["urlApagar"] = $this->_urlPadrao."/plano-distribuicao/apagar?idPreProjeto=".$this->_idPreProjeto;
            $arrDados["acaoCancelar"] = $this->_urlPadrao."/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto;
            $arrDados["bln_exitePP"] = $bln_exitePP;
            $this->montaTela("planodistribuicao/formplanodistribuicao.phtml", $arrDados);
        }

        public function salvarAction(){
            
            $post = Zend_Registry::get("post");
            $precopromocional = str_replace(",", ".", str_replace(".", "", $post->precopromocional));
            $preconormal = str_replace(",", ".", str_replace(".", "", $post->preconormal));
            $QtdeProduzida = $post->qtdenormal+$post->qtdepromocional+$post->patrocinador+$post->beneficiarios+$post->divulgacao;
            $dados = array(
                     "Area"=>$post->areaCultural,
                     "idProjeto"=>$this->_idPreProjeto,
                     "idProduto"=>$post->produto,
                     "idPosicaoDaLogo"=>$post->logomarca,
                     "Segmento"=>$post->segmentoCultural,
                     "QtdeProduzida"=>$QtdeProduzida,
                     "QtdeVendaNormal"=>$post->qtdenormal,
                     "QtdeVendaPromocional"=>$post->qtdepromocional,
                     "dsJustificativaPosicaoLogo"=>$post->dsJustificativaPosicaoLogo,
                     "PrecoUnitarioNormal"=>$preconormal,
                     "PrecoUnitarioPromocional"=>$precopromocional,
                     "stPrincipal"=>$post->prodprincipal
                     );
            if(isset($post->idPlanoDistribuicao)){ $dados["idPlanoDistribuicao"] = $post->idPlanoDistribuicao; }
            if(isset($post->patrocinador)){ $dados["QtdePatrocinador"] = $post->patrocinador; }
            if(isset($post->divulgacao)){ $dados["QtdeProponente"] = $post->divulgacao; }
            if(isset($post->beneficiarios)){ $dados["QtdeOutros"] = $post->beneficiarios; }
			$dados["stPlanoDistribuicaoProduto"] = 1;
            
            $tblPlanoDistribuicao = new PlanoDistribuicao();
            
            //VERIFICA SE JA EXISTE PRODUTO PRINCIPAL JA CADASTRADO
            $arrBusca = array();
            $arrBusca['a.idProjeto = ?'] = $this->_idPreProjeto;
            $arrBusca['a.stPrincipal = ?'] = 1;
            $arrBusca['idPlanoDistribuicao <> ?'] = $post->idPlanoDistribuicao;
            $arrBusca['stPlanoDistribuicaoProduto = ?'] = 1;
            $arrPlanoDistribuicao = $tblPlanoDistribuicao->buscar($arrBusca, array("idPlanoDistribuicao DESC"))->toArray();
           
            if( $post->patrocinador!=0 || $post->divulgacao!=0 || $post->beneficiarios!=0 || $post->qtdenormal!=0 || $post->qtdepromocional!=0){
                if(!empty($arrPlanoDistribuicao) && $post->prodprincipal == "1"){
                    parent::message("J&aacute; existe um Produto Principal cadastrado. A opera&ccedil;&atilde;o foi cancelada.", "/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto, "ERROR");
                }
                if( $post->patrocinador > ($QtdeProduzida/10) ){
                    parent::message("A quantidade destinada ao patrocinador n&atilde;o pode ser maior do que 10% do n&uacute;mero Exemplares/Ingressos.", "/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto, "ERROR");
                }
                if( $post->divulgacao > ($QtdeProduzida/10) ){
                    parent::message("A quantidade destinada &agrave; divulga&ccedil;&atilde;o n&atilde;o pode ser maior do que 10% do n&uacute;mero Exemplares/Ingressos.", "/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto, "ERROR");
                }
                if( $post->beneficiarios < ($QtdeProduzida/10) ){
                    parent::message("A quantidade destinada &agrave; popula&ccedil;&atilde;o de baixa renda n&atilde;o pode ser menor do que 10% do n&uacute;mero Exemplares/Ingressos.", "/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto, "ERROR");
                }
                if( (int)str_replace(".", "",$precopromocional) > (int)str_replace(".", "",$preconormal) ){
                    parent::message("O valor normal n&atilde;o pode ser menor ou igual ao valor promocional!", "/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto, "ERROR");
                }
                if( $post->qtdenormal == null ){
                    parent::message("Favor preencher o campo Normal(Qntd).", "/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto, "ERROR");
                }
                if( $post->qtdepromocional == null ){
                    parent::message("Favor preencher o campo Promocional(Qntd).", "/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto, "ERROR");
                }
            }
            
            if(isset($post->produto)){
                //VERIFICA SE PRODUTO JA ESTA CADASTRADO - NÃO PODE GRAVAR O MESMO PRODUTO MAIS DE UMA VEZ.
                $arrBuscaProduto['a.idProjeto = ?'] = $this->_idPreProjeto;
                $arrBuscaProduto['a.idProduto = ?'] = $post->produto;
                $objProduto = $tblPlanoDistribuicao->buscar($arrBuscaProduto);
                if($objProduto[0]['idPlanoDistribuicao']){
                    parent::message("Produto já cadastrado no plano de distribuição desta proposta!", "/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto, "ERROR");
                }
            }
            $retorno = $tblPlanoDistribuicao->salvar($dados);
            if($retorno > 0){
                parent::message("Operação realizada com sucesso!", "/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto, "CONFIRM");
            } else {
                parent::message("Não foi possível realizar a operação!", "/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto, "ERROR");
            }
        }
        
        
        public function apagarAction(){
            $get = Zend_Registry::get("get");
            $tblPlanoDistribuicao = new PlanoDistribuicao();
            $retorno = $tblPlanoDistribuicao->apagar($get->idPlanoDistribuicao);

            if($retorno > 0){
                parent::message("Operação realizada com sucesso!", "/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto, "CONFIRM");
            }else{
                parent::message("Não foi possível realizar a operação!", "/plano-distribuicao/index?idPreProjeto=".$this->_idPreProjeto, "ERROR");
            }
        }

        public function testeAction(){
            $tblPlanoDistribuicao = new PlanoDistribuicao();
            $rs = $tblPlanoDistribuicao->fetchAll();
            echo "<pre>";
            print_r($rs);

            die("<br>parando");
        }
}
?>
