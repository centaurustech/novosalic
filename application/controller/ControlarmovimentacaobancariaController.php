<?php
/**
 * Controller Controlarmovimentacaobancaria
 * @author Equipe RUP - Politec
 * @since 20/01/2011
 * @version 1.0
 * @package application
 * @subpackage application.controller
 * @link http://www.cultura.gov.br
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 */

require_once 'GenericControllerNew.php';

class ControlarmovimentacaobancariaController extends GenericControllerNew
{
	/**
	 * @access private
	 * @var integer (idAgente do usuário logado)
	 */
	private $getIdUsuario = 0; // código do usuário logado
	private $getIdGrupo   = 0; // código do grupo logado
	private $getIdOrgao   = 0; // código do órgão logado



	/**
	 * @access private
	 * @var object (tabelas utilizadas)
	 */
	private $tbMovimentacaoBancaria;
	private $tbMovimentacaoBancariaItem;
	private $tbMovimentacaoBancariaItemxTipoInconsistencia;
	private $tbTipoInconsistencia;
	private $Projetos;
	private $Enquadramento;
	private $ContaBancaria;
	private $Nomes;
	private $spMovimentacaoBancaria;
	private $tbDepositoIdentificadoMovimentacao;



	/**
	 * @access private
	 * @var string (diretório onde se enconta o arquivo .txt)
	 */
	private $arquivoTXT = 'MovimentacaoBancaria';



	/**
	 * Reescreve o método init()
	 * @access public
	 * @param void
	 * @return void
	 */
	public function init()
	{
		$this->view->title = 'Salic - Sistema de Apoio às Leis de Incentivo à Cultura'; // título da página

		/* ========== INÍCIO PERFIL ========== */
		// define os grupos que tem acesso
		$PermissoesGrupo = array();
		$PermissoesGrupo[] = 121; // Técnico de Acompanhamento
		$PermissoesGrupo[] = 122; // Coordenador de Acompanhamento
		$PermissoesGrupo[] = 123; // Coordenador - Geral de Acompanhamento
		$PermissoesGrupo[] = 129; // Técnico de Acompanhamento
		//$PermissoesGrupo[] = ; // Coordenador de Avaliação
		//$PermissoesGrupo[] = 134; // Coordenador de Fiscalização
		//$PermissoesGrupo[] = 124; // Técnico de Prestação de Contas
		//$PermissoesGrupo[] = 125; // Coordenador de Prestação de Contas
		//$PermissoesGrupo[] = 126; // Coordenador - Geral de Prestação de Contas
		$PermissoesGrupo[] = 144; // Proponente

		// pega o idAgente do usuário logado
		$auth = Zend_Auth::getInstance(); // pega a autenticação
		if (isset($auth->getIdentity()->usu_codigo)) // autenticacao novo salic
		{
			$this->getIdUsuario = UsuarioDAO::getIdUsuario($auth->getIdentity()->usu_codigo);
			$this->getIdUsuario = ($this->getIdUsuario) ? $this->getIdUsuario['idAgente'] : 0;
			parent::perfil(1, $PermissoesGrupo); // novo salic
		}
		else // autenticacao proponente
		{
			$this->getIdUsuario = (isset($_GET['idusuario'])) ? $_GET['idusuario'] : 0;
			parent::perfil(4, $PermissoesGrupo); // migracao
		}
		/* ========== FIM PERFIL ==========*/


		/* ========== INÍCIO ÓRGÃO ========== */
		$GrupoAtivo   = new Zend_Session_Namespace('GrupoAtivo'); // cria a sessão com o grupo ativo
		$this->getIdGrupo = $GrupoAtivo->codGrupo; // id do grupo ativo
		$this->getIdOrgao = $GrupoAtivo->codOrgao; // id do órgão ativo

		if (isset($auth->getIdentity()->usu_codigo) && $this->getIdOrgao != 251 && $this->getIdOrgao != 272 && $this->getIdOrgao != 166) // aceita somente o órgão SEFIC/SACAV
		{
			parent::message("Você não tem permissão para acessar essa área do sistema!", "principal/index", "ALERT");
		}
		/* ========== FIM ÓRGÃO ========== */

		parent::init();
	} // fecha método init()



	/**
	 * Redireciona para o fluxo inicial
	 * @access public
	 * @param void
	 * @return void
	 */
	public function indexAction()
	{
		$this->_forward('form'); // redireciona para o formulário
	} // fecha método indexAction()



	/**
	 * Formulário para gerar o extrato
	 * @access public
	 * @param void
	 * @return void
	 */
	public function formAction()
	{
		// recebe o id do pronac caso o mesmo seja passado (perfil proponente)
		$idPronac = $this->_request->getParam('idPronac');

		if (!empty($idPronac)) :
			$this->Projetos = new Projetos();
			$this->view->dadosProjeto = $this->Projetos->buscarTodosDadosProjeto($idPronac);
		endif;
	}



	/**
	 * Gera o extrato bancário de acordo com o filtro na pesquisa.
	 * Esse método só é executado quando é feita uma solicitação via POST.
	 * @access public
	 * @param void
	 * @return void
	 */
	public function pesquisarAction()
	{
                ini_set('memory_limit', '-1');
                set_time_limit(0);
		// caso o formulário seja enviado via post
		if ($this->getRequest()->isPost())
		{
			// recebe os dados via post
			$post            = Zend_Registry::get('post');
                        
                        if($post->relPaginacao){
                            header("Content-Type: text/html; charset=ISO-8859-1");
                            $this->_helper->layout->disableLayout(); // desabilita o layout
                        }
                        
                        
			$id_pronac       = $post->id_pronac;
			$pronac          = $post->nr_pronac;
			$conta_rejeitada = isset($post->conta_rejeitada) ? true : false;
			$periodo         = $post->periodo;
			$operacao        = $post->operacao;

			// monta a url para proponente ou agente
			if (!empty($id_pronac)) :
				$query_string = '/idpronac/' . $id_pronac; // proponente
			else :
				$query_string = ''; // agente
			endif;

			try
			{
				// para o projeto com período de execução superior a 24 meses, 
				// quando na consulta a opção selecionada for "Todo o período".
				$this->Projetos   = new Projetos();
				$periodo_execucao = $this->Projetos->buscarPeriodoExecucao(null, $pronac);

				if (empty($periodo[0]) && isset($periodo_execucao['qtdDias']) && ((int) $periodo_execucao['qtdDias'] >= 730))
				{
					parent::message('Favor solicite o extrato à SEFIC!', 'controlarmovimentacaobancaria/form' . $query_string, 'ALERT');
				}
				else if (!empty($periodo[1]) && !Data::validarData($periodo[1])) // valida a data inicial
				{
					parent::message('A data inicial é inválida!', 'controlarmovimentacaobancaria/form' . $query_string, 'ERROR');
				}
				else if (!empty($periodo[2]) && !Data::validarData($periodo[2])) // valida a data final
				{
					parent::message('A data final é inválida!', 'controlarmovimentacaobancaria/form' . $query_string, 'ERROR');
				}
				else
				{
                                    
                                    $this->tbMovimentacaoBancaria = new tbMovimentacaoBancaria();
                                    $dados = array();
                                    $pag = 1;
                                    $inicio = 0;
                                    $fim = 0;
                                    $totalPag = 0;
                                    $total = $this->tbMovimentacaoBancaria->buscarDados($pronac , $conta_rejeitada , $periodo , $operacao , null , null , true)->current();
                                    if($total->total > 0){
                                        //Controla a pagincao
                                        $this->intTamPag = 500; 
                                        
                                        if (isset($post->pag)) $pag = $post->pag;
                                        if (isset($post->tamPag)) $this->intTamPag = $post->tamPag;
                                        $inicio = ($pag>1) ? ($pag-1)*$this->intTamPag : 0;
                                        $fim = $inicio + $this->intTamPag;

                                        $totalPag = (int)(($total->total % $this->intTamPag == 0)?($total->total/$this->intTamPag):(($total->total/$this->intTamPag)+1));
                                        $tamanho = ($fim > $total->total) ? $total->total - $inicio : $this->intTamPag;
                                        if ($fim>$total->total) $fim = $total->total;

                                        // busca os dados no banco e manda para a visão

                                        
                                       $dados = $this->tbMovimentacaoBancaria->buscarDados($pronac, $conta_rejeitada, $periodo, $operacao , $tamanho , $inicio , null );
                                        
                                    }
                                    
                                    $this->view->dados            = $dados;
                                    $this->view->total            = $total->total;
                                    $this->view->periodo          = $periodo;
                                    $this->view->conta_rejeitada  = $conta_rejeitada;
                                    
                                    $this->view->pag = $pag;
                                    $this->view->inicio = ($inicio+1);
                                    $this->view->fim = $fim;
                                    $this->view->totalPag = $totalPag;
                                    $this->view->parametrosBusca = $_POST;
                                    $this->view->cabecalho = $post->cabecalho;
				}
			} // fecha try
			catch (Exception $e)
			{
				$this->view->message      = $e->getMessage();
				$this->view->message_type = 'ERROR';
			}

		} // fecha if post
		else
		{
			parent::message('Por favor, defina um filtro válido para gerar o extrato bancário!', 'controlarmovimentacaobancaria/form', 'ALERT');
		}

	} // fecha método pesquisarAction()



	/**
	 * Método para gerar o pdf do extrato
	 * @access public
	 * @param void
	 * @return void
	 */
	public function gerarpdfAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o layout
	} // fecha método gerarpdfAction()



	/**
	 * Método para enviar o arquivo txt do banco do brasil
	 * @access public
	 * @param void
	 * @return void
	 */
	public function uploadAction()
	{
		if ($this->getIdGrupo != 121 && $this->getIdGrupo != 122 && $this->getIdGrupo != 129) // só Técnico de Acompanhamento que pode acessar
		{
			parent::message('Você não tem permissão para acessar essa área do sistema!', 'principal/index', 'ALERT');
		}

		// caso o formulário seja enviado via post
		if ($this->getRequest()->isPost())
		{
			// configuração o php.ini para 100MB
			@set_time_limit(0);
			@ini_set('mssql.textsize',      10485760000);
			@ini_set('mssql.textlimit',     10485760000);
			@ini_set('mssql.timeout',       10485760000);
			@ini_set('upload_max_filesize', '100M');

			// pega as informações do arquivo
			$arquivoNome    = $_FILES['arquivo']['name']; // nome
			$arquivoTemp    = $_FILES['arquivo']['tmp_name']; // nome temporário
			$arquivoTipo    = $_FILES['arquivo']['type']; // tipo
			$arquivoTamanho = $_FILES['arquivo']['size']; // tamanho

			if (!empty($arquivoNome) && !empty($arquivoTemp))
			{
				$arquivoExtensao = strtolower(Upload::getExtensao($arquivoNome)); // extensão
			}

			// caminho do arquivo txt
			$so               = stripos($_SERVER['SERVER_SOFTWARE'], 'win32') != FALSE ? 'WINDOWS' : 'LINUX'; // sistema operacional
			$bar              = $so == 'WINDOWS' ? '\\' : '/';                                                // configura a barra de acordo com o SO
			$this->arquivoTXT = getcwd() . $bar . 'public' . $bar . 'txt' . $bar . $this->arquivoTXT;         // diretório interno do arquivo

			$dir = $this->arquivoTXT; // diretório onde se encontram os arquivos do banco

			try
			{
				// integração MODELO e VISÃO
				if (empty($arquivoTemp)) // nome do arquivo
				{
					throw new Exception('Por favor, informe o arquivo!');
				}
				else if (($arquivoExtensao != 'ret' && $arquivoExtensao != 'txt') || ($arquivoTipo != 'text/plain' && $arquivoTipo != 'application/octet-stream')) // extensão do arquivo
				{
					throw new Exception('A extensão do arquivo é inválida, envie somente arquivos <strong>.txt</strong>!');
				}
//				else if ($arquivoTamanho > 14680064) // tamanho máximo do arquivo: 14MB
//				{
//					throw new Exception('O arquivo não pode ser maior do que <strong>14MB</strong>!');
//				}
//				else if ($arquivoTamanho <= 150) // tamanho mínimo do arquivo: 150 bytes
//				{
//					throw new Exception('O layout do arquivo enviado é inválido!');
//				}
				// faz o envio do arquivo
				else
				{
					$this->tbDepositoIdentificadoMovimentacao = new tbDepositoIdentificadoMovimentacao();

					// verifica se existe algum dado na tabela
					$buscar = $this->tbDepositoIdentificadoMovimentacao->buscar()->toArray();

					if (count($buscar) > 0)
					{
						throw new Exception('Aguarde um momento, pois, já existe um arquivo sendo processado!');
					}
					// verifica se já existe um arquivo com o mesmo nome
					else if (file_exists($dir . '/' . $arquivoNome))
					{
						throw new Exception('O arquivo <strong>' . $arquivoNome . '</strong> já existe!');
					}
					else
					{
						// envia o arquivo
						if (move_uploaded_file($arquivoTemp, $dir . '/' . $arquivoNome))
						{
							// abre o diretório
							if (($abrir = opendir($dir)) === false)
							{
								throw new Exception('Não foi possível abrir o diretório <strong>' . $dir . '</strong>!');
							}

							// busca todos os arquivos do diretório
							$i = 0;

							while (($arq = readdir($abrir)) !== false)
							{

								// verifica se a extensão do arquivo é .txt ou .ret
								if ((substr(strtolower($arq), -4) == '.txt') || (substr(strtolower($arq), -4) == '.ret'))
								{
									// array contendo o caminho/nome completo de cada arquivo
									$arquivos[] = $dir . $bar . $arq;

									if ($i == 0)
									{
										// abre o arquivo para leitura
										$abrir_arquivo_header = fopen($arquivos[0], 'r');


										// pega a linha do arquivo
										$linha_header = fgets($abrir_arquivo_header, 4096);

										// faz a validação do arquivo de acordo com o layout
										$nr_banco               = substr($linha_header, 76, 3);  // NÚMERO DO BANCO
										$nm_arquivo             = substr($linha_header, 11, 15); // NOME DO ARQUIVO
										$dt_arquivo             = substr($linha_header, 181, 8); // DATA DA GERAÇÃO DO ARQUIVO
//										$dt_inicio_movimentacao = substr($linha_header, 52, 8); // DATA DE INÍCIO DA MOVIMENTAÇÃO BANCÁRIA
//										$dt_fim_movimentacao    = substr($linha_header, 60, 8); // DATA FINAL DA MOVIMENTAÇÃO BANCÁRIA
                                        
                                        
										// faz a validação do arquivo pelo header
										// verifica pelo header se o arquivo já existe
                                        if (substr($linha_header, 0, 1) == 0){

//											if (!is_numeric($nr_banco) || !Data::validarData($dt_arquivo) || !Data::validarData($dt_inicio_movimentacao) || !Data::validarData($dt_fim_movimentacao))
											if (!is_numeric($nr_banco) || !Data::validarData($dt_arquivo))
											{
												// fecha o arquivo
												fclose($abrir_arquivo_header);

												// exclui o arquivo
												unlink($arquivos[0]);

												throw new Exception('O layout do arquivo enviado é inválido!');
											}

											// busca a data de geração do arquivo para evitar inserção de registros duplicados
											$dataGeracaoArquivo = Data::dataAmericana(Mascara::addMaskDataBrasileira($dt_arquivo));

											// verifica se o arquivo já está cadastrado no banco de dados
											$this->tbMovimentacaoBancaria = new tbMovimentacaoBancaria();
											$buscarArquivoCadastrado = $this->tbMovimentacaoBancaria->buscar(array('dtArquivo = ?' => $dataGeracaoArquivo));

											if (count($buscarArquivoCadastrado) > 0)
											{
												// fecha o arquivo
												fclose($abrir_arquivo_header);

												// exclui o arquivo
												unlink($arquivos[0]);

												throw new Exception('Esse arquivo já foi enviado!');
											}

											$i++;								
                                        }

										// fecha o arquivo
										fclose($abrir_arquivo_header);

										// exclui o arquivo
										unlink($arquivos[$i]);

									} // fecha if ($i == 0)

								} // fecha if

							} // fecha while

							// caso exista arquivo(s) .txt ou .ret no diretório:
							// 	1. Varre o conteúdo de cada arquivo
							// 	2. Grava o conteúdo de cada linha no banco
							// 	3. Deleta o arquivo do diretório
							if (isset($arquivos) && count($arquivos) > 0)
							{
								// ========== INÍCIO - VARRE O ARQUIVO DETALHADAMENTE ==========
								foreach ($arquivos as $arquivoTXT) :

									// abre o arquivo para leitura
									$abrir_arquivo = fopen($arquivoTXT, 'r');

									// início while de leitura do arquivo linha por linha
									$i = 0;
									$dsInformacao = array();
									while (!feof($abrir_arquivo))
									{
										// pega a linha do arquivo
										$linha = fgets($abrir_arquivo, 4096);

										// caso a linha não seja vazia e o primeiro caractere for numérico
										if (!empty($linha) && is_numeric( substr($linha, 0, 1) ))
										{
											// armazena as linhas do arquivo em um array
											$dsInformacao[$i] = trim($linha);
										}
										$i++;
									} // fim while de leitura do arquivo linha por linha

									// fecha o arquivo
									fclose($abrir_arquivo);

									// exclui o arquivo
									unlink($arquivoTXT);

                                    $auth = Zend_Auth::getInstance(); // pega a autenticação
									// grava linha por linha do arquivo no banco
									foreach ($dsInformacao as $ds) :
										if (!$this->tbDepositoIdentificadoMovimentacao->inserir( array('dsInformacao' => $ds, 'idUsuario' => $auth->getIdentity()->usu_codigo) ))
										{
											throw new Exception('Erro ao enviar arquivo!');
										}
									endforeach;

								endforeach;
								// ========== FIM - VARRE O ARQUIVO DETALHADAMENTE ==========
                                                                
                                //$this->tbDepositoIdentificadoMovimentacao->DepositoIdentificadoMovimentacao();
                                                                
							} // fecha if
                                                        

							parent::message('Arquivo enviado com sucesso!', 'controlarmovimentacaobancaria/upload', 'CONFIRM');
						} // fecha if upload
						else
						{
							parent::message('Erro ao enviar arquivo!', 'controlarmovimentacaobancaria/upload', 'ERROR');
						}					
					} // fecha else
				}
			} // fecha try
			catch (Exception $e)
			{
				$this->view->message       = $e->getMessage();
				$this->view->message_type  = 'ERROR';
			}
		} // fecha if post

	} // fecha método uploadAction()



	/**
	 * Método para executar a sp de movimentação bancária
	 * @access public
	 * @param void
	 * @return void
	 */
	public function finalizarAction()
	{
		// caso o formulário seja enviado via post
		if ($this->getRequest()->isPost())
		{
			// configuração o php.ini para 100MB
			@set_time_limit(0);
			@ini_set('mssql.textsize',      10485760000);
			@ini_set('mssql.textlimit',     10485760000);
			@ini_set('mssql.timeout',       10485760000);
			@ini_set('upload_max_filesize', '100M');

			try
			{
				// executa a sp
				$this->spMovimentacaoBancaria = new spMovimentacaoBancaria();
				$this->spMovimentacaoBancaria->verificarInconsistencias();

				parent::message('Rotina executada com sucesso!', 'controlarmovimentacaobancaria/form', 'CONFIRM');
			} // fecha try
			catch (Exception $e)
			{
				$this->view->message      = $e->getMessage();
				$this->view->message_type = 'ERROR';
			}

		} // fecha if post
		else
		{
			parent::message('Por favor, pressione o botão finalizar!', 'controlarmovimentacaobancaria/form', 'ALERT');
		}

	} // fecha método finalizarAction()



	/**
	 * Salva o arquivo do banco do brasil:
	 * toda vez que acessar aqui, verificar se o arquivo do BB existe,
	 * caso o arquivo exista, grava os dados no banco e exclui o arquivo.
	 * ****************************************************************************************************
	 * OBS: Por enquanto foi substituído pela TRIGGER/SP, mas, é bom não retirá-lo para o caso de precisar
	 * ****************************************************************************************************
	 * @access public
	 * @param void
	 * @return void
	 */
	public function salvararquivobbAction()
	{
		$this->_helper->layout->disableLayout(); // desabilita o layout

		// diretório onde se encontram os arquivos do banco
		$dir = getcwd() . $this->arquivoTXT;

		// abre o diretório
		if (($abrir = opendir($dir)) === false)
		{
			throw new Exception('Não foi possível abrir o diretório ' . $dir . '!');
		}

		// busca todos os arquivos do diretório
		while (($arq = readdir($abrir)) !== false)
		{
			// verifica se a extensão do arquivo é .txt
			if ((substr(strtolower($arq), -4) == '.txt'))
			{
				// array contendo o caminho/nome completo de cada arquivo
				$arquivos[] = $dir . '/' . $arq;
			} // fecha if
		} // fecha while


		// caso exista arquivo(s) .txt no diretório:
		// 	1. Varre o conteúdo de cada arquivo
		// 	2. Grava o conteúdo de cada arquivo no banco
		// 	3. Deleta o arquivo do diretório
		if (isset($arquivos) && count($arquivos) > 0)
		{

			// ========== INÍCIO - VARRE O ARQUIVO DETALHADAMENTE ==========
			foreach ($arquivos as $arquivoTXT) :

				// abre o arquivo para leitura
				$abrir_arquivo = fopen($arquivoTXT, 'r');

				// contador para os dados das constantes
				$cont1 = 0; // constante 1
				$cont2 = 0; // constante 2
				$cont3 = 0; // constante 3
				$cont9 = 0; // constante 9

				// arrays com os dados de cada constante
				$movimentacao1 = array(); // constante 1
				$movimentacao2 = array(); // constante 2
				$movimentacao3 = array(); // constante 3
				$movimentacao9 = array(); // constante 9

				$id_ultima_movimentacao      = 0; // id contendo a última movimentação
				$id_ultima_movimentacao_item = 0; // id contendo o último item movimentação

				$erros = array(); // verificador de erros

				// início while de leitura do arquivo linha por linha
				while (!feof($abrir_arquivo))
				{
					// pega a linha do arquivo
					$linha = fgets($abrir_arquivo, 4096);
					//echo $linha . '<br />';


					// ========== INÍCIO CONSTANTE 1 (HEADER) ==========
					if (substr($linha, 0, 1) == 1) :
						$movimentacao1['nr_banco'][$cont1]               = substr($linha, 1, 3); // NÚMERO DO BANCO
						$movimentacao1['nm_arquivo'][$cont1]             = substr($linha, 4, 40); // NOME DO ARQUIVO
						$movimentacao1['dt_arquivo'][$cont1]             = substr($linha, 44, 8); // DATA DA GERAÇÃO DO ARQUIVO
						$movimentacao1['dt_inicio_movimentacao'][$cont1] = substr($linha, 52, 8); // DATA DE INÍCIO DA MOVIMENTAÇÃO BANCÁRIA
						$movimentacao1['dt_fim_movimentacao'][$cont1]    = substr($linha, 60, 8); // DATA FINAL DA MOVIMENTAÇÃO BANCÁRIA

						// gravar no banco os dados do arquivo
						$dados_movimentacao = array(
							'nrBanco'            => $movimentacao1['nr_banco'][$cont1]
							,'nmArquivo'         => $movimentacao1['nm_arquivo'][$cont1]
							,'dtArquivo'         => Data::dataAmericana(Mascara::addMaskDataBrasileira($movimentacao1['dt_arquivo'][$cont1]))
							,'dtInicioMovimento' => Data::dataAmericana(Mascara::addMaskDataBrasileira($movimentacao1['dt_inicio_movimentacao'][$cont1]))
							,'dtFimMovimento'    => Data::dataAmericana(Mascara::addMaskDataBrasileira($movimentacao1['dt_fim_movimentacao'][$cont1]))
							,'idAgente'          => $this->getIdUsuario);
						$this->tbMovimentacaoBancaria = new tbMovimentacaoBancaria();
						$id_ultima_movimentacao       = $this->tbMovimentacaoBancaria->cadastrarDados($dados_movimentacao);

						$cont1++; //incrementa o contador
					endif;
					// ========== FIM CONSTANTE 1 (HEADER) ==========


					// ========== INÍCIO CONSTANTE 2 ==========
					if (substr($linha, 0, 1) == 2) :
						$movimentacao2['agencia'][$cont2]           = substr($linha, 1, 4); // PREFIXO DA AGÊNCIA
						$movimentacao2['dv_agencia'][$cont2]        = substr($linha, 5, 1); // DIGITO VERIFICADOR DA AGÊNCIA
						$movimentacao2['conta'][$cont2]             = substr($linha, 6, 9); // NÚMERO DA CONTA CORRENTE
						$movimentacao2['dv_conta'][$cont2]          = substr($linha, 15, 1); // DIGITO VERIFICADOR DA CONTA CORRENTE
						$movimentacao2['titulo_razao'][$cont2]      = substr($linha, 16, 12); // CÓDIGO TÍTULO RAZÃO DA CONTA CORRENTE
						$movimentacao2['nome_abreviado'][$cont2]    = substr($linha, 28, 30); // NOME ABREVIADO
						$movimentacao2['dt_abertura'][$cont2]       = substr($linha, 58, 8); // DATA DA ABERTURA DA CONTA CORRENTE
						$movimentacao2['cnpj_cpf'][$cont2]          = substr($linha, 66, 14); // CNPJ OU CPF DA CONTA CORRENTE
						$movimentacao2['saldo_inicial'][$cont2]     = (float) substr($linha, 80, 18); // SALDO INICIAL DA CONTA CORRENTE
						$movimentacao2['dc_saldo_inicial'][$cont2]  = substr($linha, 98, 1); // DEBITO OU CREDITO DO SALDO INICIAL
						$movimentacao2['saldo_final'][$cont2]       = (float) substr($linha, 99, 18); // SALDO FINAL NA CONTA CORRENTE
						$movimentacao2['dc_saldo_final'][$cont2]    = substr($linha, 117, 1); // DEBITO OU CREDITO DO SALDO FINAL

						// gravar no banco os dados da movimentação
						$dados_movimentacao_item = array(
							'tpRegistro'              => substr($linha, 0, 1)
							,'nrAgencia'              => $movimentacao2['agencia'][$cont2].$movimentacao2['dv_agencia'][$cont2]
							,'nrDigitoConta'          => '00' . $movimentacao2['conta'][$cont2].$movimentacao2['dv_conta'][$cont2]
							,'nmTituloRazao'          => $movimentacao2['titulo_razao'][$cont2]
							,'nmAbreviado'            => $movimentacao2['nome_abreviado'][$cont2]
							,'dtAberturaConta'        => Data::dataAmericana(Mascara::addMaskDataBrasileira($movimentacao2['dt_abertura'][$cont2]))
							,'nrCNPJCPF'              => $movimentacao2['cnpj_cpf'][$cont2]
							,'vlSaldoInicial'         => number_format($movimentacao2['saldo_inicial'][$cont2] / 100, 2, '.', '')
							,'tpSaldoInicial'         => $movimentacao2['dc_saldo_inicial'][$cont2]
							,'vlSaldoFinal'           => number_format($movimentacao2['saldo_final'][$cont2] / 100, 2, '.', '')
							,'tpSaldoFinal'           => $movimentacao2['dc_saldo_final'][$cont2]
							,'idMovimentacaoBancaria' => $id_ultima_movimentacao);
						$this->tbMovimentacaoBancariaItem  = new tbMovimentacaoBancariaItem();
						$id_ultima_movimentacao_item       = $this->tbMovimentacaoBancariaItem->cadastrarDados($dados_movimentacao_item);


						// busca os dados (nome) do cnpj ou cpf da conta corrente
						$this->Nomes = new Nomes();
						$dadosAgente = $this->Nomes->buscarNomePorCPFCNPJ($movimentacao2['cnpj_cpf'][$cont2], null, null, null, false);
						if (!$dadosAgente)
						{
							// filtra somente por cpf (retira os 3 primeiros caracteres)
							$dadosAgente = $this->Nomes->buscarNomePorCPFCNPJ(substr($movimentacao2['cnpj_cpf'][$cont2], 3, 14), null, null, null, false);
							if (!$dadosAgente)
							{
								// grava no banco a inconsistência
								$dados_inconsistencia = array(
									'idMovimentacaoBancariaItem' => $id_ultima_movimentacao_item
									,'idTipoInconsistencia'      => '6');
								$this->tbMovimentacaoBancariaItemxTipoInconsistencia = new tbMovimentacaoBancariaItemxTipoInconsistencia();
								$cadastrar_inconsistencia                            = $this->tbMovimentacaoBancariaItemxTipoInconsistencia->cadastrarDados($dados_inconsistencia);
							}
						}
						$movimentacao2['nm_agente'][$cont2] = ($dadosAgente) ? $dadosAgente['Nome'] : ''; // NOME DO AGENTE


						// busca o pronac de acordo com a agência e a conta do projeto
						// obs: a conta tem 12 carateres na tabela SAC.dbo.ContaBancaria
						$agencia      = $movimentacao2['agencia'][$cont2] . $movimentacao2['dv_agencia'][$cont2];
						$conta        = '00' . $movimentacao2['conta'][$cont2] . $movimentacao2['dv_conta'][$cont2];
						$this->ContaBancaria = new ContaBancaria();
						$dadosProjeto        = $this->ContaBancaria->buscarDados(null, null, $agencia, $conta, false);				
						if (!$dadosProjeto)
						{
							// busca somente pela conta
							$dadosProjeto = $this->ContaBancaria->buscarDados(null, null, null, $conta, false);
							if (!$dadosProjeto)
							{
								// grava no banco a inconsistência
								$dados_inconsistencia = array(
									'idMovimentacaoBancariaItem' => $id_ultima_movimentacao_item
									,'idTipoInconsistencia'      => '7');
								$this->tbMovimentacaoBancariaItemxTipoInconsistencia = new tbMovimentacaoBancariaItemxTipoInconsistencia();
								$cadastrar_inconsistencia                            = $this->tbMovimentacaoBancariaItemxTipoInconsistencia->cadastrarDados($dados_inconsistencia);
							}
						}
						$movimentacao2['ano_projeto'][$cont2] = ($dadosProjeto) ? $dadosProjeto['AnoProjeto'] : '';
						$movimentacao2['sequencial'][$cont2]  = ($dadosProjeto) ? $dadosProjeto['Sequencial'] : '';


						// busca o enquadramento do projeto
						$pronac = $movimentacao2['ano_projeto'][$cont2] . $movimentacao2['sequencial'][$cont2];
						if (!empty($pronac))
						{
							$this->Enquadramento = new Enquadramento();
							$dadosEnquadramento  = $this->Enquadramento->buscarDados(null, $pronac, false);
						}
						else
						{
							$dadosEnquadramento = false;

							// grava no banco a inconsistência
							$dados_inconsistencia = array(
								'idMovimentacaoBancariaItem' => $id_ultima_movimentacao_item
								,'idTipoInconsistencia'      => '9');
							$this->tbMovimentacaoBancariaItemxTipoInconsistencia = new tbMovimentacaoBancariaItemxTipoInconsistencia();
							$cadastrar_inconsistencia                            = $this->tbMovimentacaoBancariaItemxTipoInconsistencia->cadastrarDados($dados_inconsistencia);
						}

						if (!$dadosEnquadramento)
						{
							// grava no banco a inconsistência
							$dados_inconsistencia = array(
								'idMovimentacaoBancariaItem' => $id_ultima_movimentacao_item
								,'idTipoInconsistencia'      => '8');
							$this->tbMovimentacaoBancariaItemxTipoInconsistencia = new tbMovimentacaoBancariaItemxTipoInconsistencia();
							$cadastrar_inconsistencia                            = $this->tbMovimentacaoBancariaItemxTipoInconsistencia->cadastrarDados($dados_inconsistencia);
						}

						$cont2++; //incrementa o contador
					endif;
					// ========== FIM CONSTANTE 2 ==========


					// ========== INÍCIO CONSTANTE 3 ==========
					if (substr($linha, 0, 1) == 3) :
						$movimentacao3['agencia'][$cont3]            = substr($linha, 1, 4); // PREFIXO DA AGÊNCIA
						$movimentacao3['dv_agencia'][$cont3]         = substr($linha, 5, 1); // DIGITO VERIFICADOR DA AGÊNCIA
						$movimentacao3['conta'][$cont3]              = substr($linha, 6, 9); // NÚMERO DA CONTA CORRENTE
						$movimentacao3['dv_conta'][$cont3]           = substr($linha, 15, 1); // DIGITO VERIFICADOR DA CONTA CORRENTE
						$movimentacao3['dt_movimento'][$cont3]       = substr($linha, 16, 8); // DATA DO MOVIMENTO NA CONTA CORRENTE
						$movimentacao3['cod_historico'][$cont3]      = substr($linha, 24, 4); // CÓDIGO DO HISTÓRICO DO BANCO
						$movimentacao3['historico'][$cont3]          = substr($linha, 28, 15); // HISTÓRICO DO BANCO
						$movimentacao3['nr_documento'][$cont3]       = substr($linha, 43, 10); // NÚMERO DO DOCUMENTO PARA O BANCO
						$movimentacao3['valor_movimento'][$cont3]    = (float) substr($linha, 53, 18); // VALOR DO MOVIMENTO NA CONTA CORRENTE
						$movimentacao3['dc_valor_movimento'][$cont3] = substr($linha, 71, 1); // DEBITO OU CREDITO DO MOVIMENTO NA CONTA CORRENTE

						// gravar no banco os dados da movimentação
						$dados_movimentacao_item = array(
							'tpRegistro'              => substr($linha, 0, 1)
							,'nrAgencia'              => $movimentacao3['agencia'][$cont3].$movimentacao3['dv_agencia'][$cont3]
							,'nrDigitoConta'          => '00' . $movimentacao3['conta'][$cont3].$movimentacao3['dv_conta'][$cont3]
							,'dtMovimento'            => Data::dataAmericana(Mascara::addMaskDataBrasileira($movimentacao3['dt_movimento'][$cont3]))
							,'cdHistorico'            => $movimentacao3['cod_historico'][$cont3]
							,'dsHistorico'            => $movimentacao3['historico'][$cont3]
							,'nrDocumento'            => $movimentacao3['nr_documento'][$cont3]
							,'vlMovimento'            => number_format($movimentacao3['valor_movimento'][$cont3]/100, 2, '.', '')
							,'cdMovimento'            => $movimentacao3['dc_valor_movimento'][$cont3]
							,'idMovimentacaoBancaria' => $id_ultima_movimentacao);
						$this->tbMovimentacaoBancariaItem  = new tbMovimentacaoBancariaItem();
						$id_ultima_movimentacao_item       = $this->tbMovimentacaoBancariaItem->cadastrarDados($dados_movimentacao_item);


						// busca o pronac de acordo com a agência e a conta do projeto
						// obs: a conta tem 12 carateres na tabela SAC.dbo.ContaBancaria
						$agencia      = $movimentacao3['agencia'][$cont3] . $movimentacao3['dv_agencia'][$cont3];
						$conta        = '00' . $movimentacao3['conta'][$cont3] . $movimentacao3['dv_conta'][$cont3];
						$this->ContaBancaria = new ContaBancaria();
						$dadosProjeto        = $this->ContaBancaria->buscarDados(null, null, $agencia, $conta, false);
						if (!$dadosProjeto)
						{
							// busca somente pela conta
							$dadosProjeto = $this->ContaBancaria->buscarDados(null, null, null, $conta, false);
							if (!$dadosProjeto)
							{
								// grava no banco a inconsistência
								$dados_inconsistencia = array(
									'idMovimentacaoBancariaItem' => $id_ultima_movimentacao_item
									,'idTipoInconsistencia'      => '7');
								$this->tbMovimentacaoBancariaItemxTipoInconsistencia = new tbMovimentacaoBancariaItemxTipoInconsistencia();
								$cadastrar_inconsistencia                            = $this->tbMovimentacaoBancariaItemxTipoInconsistencia->cadastrarDados($dados_inconsistencia);
							}
						}
						$movimentacao3['ano_projeto'][$cont3] = ($dadosProjeto) ? $dadosProjeto['AnoProjeto'] : '';
						$movimentacao3['sequencial'][$cont3]  = ($dadosProjeto) ? $dadosProjeto['Sequencial'] : '';


						// busca o enquadramento do projeto
						// verifica se o projeto está com a data de execução vigente
						// verifica se o projeto está com a data de captação vigente
						$pronac = $movimentacao3['ano_projeto'][$cont3] . $movimentacao3['sequencial'][$cont3];
						if (!empty($pronac))
						{
							$this->Enquadramento  = new Enquadramento();
							$dadosEnquadramento   = $this->Enquadramento->buscarDados(null, $pronac, false);
							$this->Projetos       = new Projetos();
							$dadosPeriodoExecucao = $this->Projetos->buscarPeriodoExecucao(null, $pronac, Data::dataAmericana(Mascara::addMaskDataBrasileira($movimentacao3['dt_movimento'][$cont3])));
							$dadosPeriodoCaptacao = $this->Projetos->buscarPeriodoCaptacao(null, $pronac, Data::dataAmericana(Mascara::addMaskDataBrasileira($movimentacao3['dt_movimento'][$cont3])), false);
						}
						else
						{
							$dadosEnquadramento   = false;
							$dadosPeriodoExecucao = false;
							$dadosPeriodoCaptacao = false;

							// grava no banco a inconsistência
							$dados_inconsistencia = array(
								'idMovimentacaoBancariaItem' => $id_ultima_movimentacao_item
								,'idTipoInconsistencia'      => '9');
							$this->tbMovimentacaoBancariaItemxTipoInconsistencia = new tbMovimentacaoBancariaItemxTipoInconsistencia();
							$cadastrar_inconsistencia                            = $this->tbMovimentacaoBancariaItemxTipoInconsistencia->cadastrarDados($dados_inconsistencia);
						}

						if (!$dadosEnquadramento)
						{
							// grava no banco a inconsistência
							$dados_inconsistencia = array(
								'idMovimentacaoBancariaItem' => $id_ultima_movimentacao_item
								,'idTipoInconsistencia'      => '8');
							$this->tbMovimentacaoBancariaItemxTipoInconsistencia = new tbMovimentacaoBancariaItemxTipoInconsistencia();
							$cadastrar_inconsistencia                            = $this->tbMovimentacaoBancariaItemxTipoInconsistencia->cadastrarDados($dados_inconsistencia);
						}
						if (!$dadosPeriodoExecucao)
						{
							// grava no banco a inconsistência
							$dados_inconsistencia = array(
								'idMovimentacaoBancariaItem' => $id_ultima_movimentacao_item
								,'idTipoInconsistencia'      => '1');
							$this->tbMovimentacaoBancariaItemxTipoInconsistencia = new tbMovimentacaoBancariaItemxTipoInconsistencia();
							$cadastrar_inconsistencia                            = $this->tbMovimentacaoBancariaItemxTipoInconsistencia->cadastrarDados($dados_inconsistencia);
						}
						if (!$dadosPeriodoCaptacao)
						{
							// grava no banco a inconsistência
							$dados_inconsistencia = array(
								'idMovimentacaoBancariaItem' => $id_ultima_movimentacao_item
								,'idTipoInconsistencia'      => '2');
							$this->tbMovimentacaoBancariaItemxTipoInconsistencia = new tbMovimentacaoBancariaItemxTipoInconsistencia();
							$cadastrar_inconsistencia                            = $this->tbMovimentacaoBancariaItemxTipoInconsistencia->cadastrarDados($dados_inconsistencia);
						}

						$cont3++; //incrementa o contador
					endif;
					// ========== FIM CONSTANTE 3 ==========


					// ========== INÍCIO CONSTANTE 9 ==========
					if (substr($linha, 0, 1) == 9) :
						$movimentacao9['qtd_registros'][$cont9] = substr($linha, 1, 7); // QUANTIDADE DE REGISTROS
						$cont9++; //incrementa o contador
					endif;
					// ========== FIM CONSTANTE 9 ==========

				} // fim while de leitura do arquivo linha por linha

				// fecha o arquivo
				fclose($abrir_arquivo);

				// exclui o arquivo
				unlink($arquivoTXT);

			endforeach;
			// ========== FIM - VARRE O ARQUIVO DETALHADAMENTE ==========

		} // fecha if (caso exista arquivo(s) .txt no diretório)

		parent::message('Arquivo enviado com sucesso!', 'controlarmovimentacaobancaria/upload', 'CONFIRM');
	} // fecha método salvararquivobbAction()

} // fecha class