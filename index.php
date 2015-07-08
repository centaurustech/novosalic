<?php
/**
 * Arquivo principal da aplicação (bootstrap)
 * Define todos os caminhos onde os arquivos estão armazenados
 * Carrega as classes do Zend utilizadas durante toda a aplicação
 * @author Equipe RUP - Politec
 * @since 29/03/2010
 * @version 1.0
 * @copyright ç 2010 - Ministçrio da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

define('APPLICATION_PATH', realpath(__DIR__ . DIRECTORY_SEPARATOR . 'application'));
/*
   Favor não modificar a conexao.
   Equipe de desenvolvimento eh para usar o banco de desenvolvimento.
 */


/* diretçrios */
$DIR_BANCO      = "banco";                  // Conexao 1
//$DIR_BANCO      = "banco";                  // Conexao 2
//$DIR_BANCO      = "banco";                  // Conexao 3
//$DIR_BANCO      = "banco";                  // Conexao 4

//$DIR_BANCOP     = "conexao_01";                     // 
//$DIR_BANCOP     = "conexao_02";                     // 
//$DIR_BANCOP     = "conexao_03";                     // 
//$DIR_BANCOP     = "conexao_04";                     //
//$DIR_BANCOP     = "conexao_05";                     // 
$DIR_LIB        = "./library/";                       // bibliotecas
$DIR_CONFIG     = "./application/configs/$DIR_BANCO.ini"; // configuraççes
$DIR_CONFIGP    = "./application/configs/config.ini"; // configurações
$DIR_LAYOUT     = "./application/layout/";            // layouts
$DIR_MODELS     = "./application/model";              // models
$DIR_SERVICE     = "./application/model/Servico";     // services
$DIR_TO         = "./application/model/TO/";          // tos
$DIR_DAO        = "./application/model/DAO/";         // daos
$DIR_TABLE        = "./application/model/Table/";         // table
$DIR_VIEW       = "./application/views/";             // visçes
$DIR_CONTROLLER = "./application/controller/";        // controles



/* ambientes: (DES: desenvolvimento - TES: teste - PRO: producao) */
$AMBIENTE = 'DES';



/* formato, idioma e localização */
setlocale(LC_ALL, 'pt_BR');
setlocale(LC_CTYPE, 'de_DE.iso-8859-1');
date_default_timezone_set('America/Sao_Paulo');



/* configuração do caminho dos includes */
set_include_path('.' . PATH_SEPARATOR . $DIR_LIB
                                         . PATH_SEPARATOR . $DIR_CONFIG
                                         . PATH_SEPARATOR . $DIR_LAYOUT
                                         . PATH_SEPARATOR . $DIR_MODELS
                                         . PATH_SEPARATOR . $DIR_SERVICE
                                         . PATH_SEPARATOR . $DIR_TO
                                         . PATH_SEPARATOR . $DIR_DAO
                                         . PATH_SEPARATOR . $DIR_TABLE
                                         . PATH_SEPARATOR . $DIR_VIEW
                                         . PATH_SEPARATOR . $DIR_CONTROLLER
                                         . PATH_SEPARATOR . get_include_path());



/* componente obrigatçrio para carregar arquivos, classes e recursos */
require_once "Zend/Loader/Autoloader.php";
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);



/* classes pessoais do ministçrio da cultura */
require_once "MinC/Loader.php";

//Registrando variçveis
Zend_Registry::set('DIR_CONFIG', $DIR_CONFIG); // registra


/* configura para exibir as mensagens de erro */
if ($AMBIENTE == 'DES') { error_reporting(E_ALL | E_STRICT); }
Zend_Registry::set('ambiente', $AMBIENTE); // registra




/* manipulação de sessão */
Zend_Session::start();
Zend_Registry::set('session', new Zend_Session_Namespace()); // registra


/* configurações do banco de dados */
$config = new Zend_Config_Ini($DIR_CONFIGP, $DIR_BANCOP);
$registry = Zend_Registry::getInstance();
$registry->set('config', $config); // registra

$db = Zend_Db::factory($config->db);
Zend_Db_Table::setDefaultAdapter($db);
Zend_Registry::set('db', $db); // registra
$profiler = $db->getProfiler();
$profiler->setEnabled(false);

/* configuraççes do layout padrão do sistema */
Zend_Layout::startMvc(array(
        'layout'     => 'layout',
        'layoutPath' => $DIR_LAYOUT,
        'contentKey' => 'content'));

# paginacao
Zend_View_Helper_PaginationControl::setDefaultViewPartial('paginacao/paginacaoMinc.phtml');

/* variçveis para pegar dados vindos via get e post */
$filter = new Zend_Filter();
$filter->addFilter(new Zend_Filter_StringTrim()); // retira espaãos antes e depois
$filter->addFilter(new Zend_Filter_StripTags()); // retira cçdigo html e etc
$options = array('escapeFilter' => $filter);

/* registra */
Zend_Registry::set('post', new Zend_Filter_Input(NULL, NULL, $_POST, $options));
Zend_Registry::set('get',  new Zend_Filter_Input(NULL, NULL, $_GET,  $options));

/* registra a conexão para mudar em ambiente scriptcase */
Zend_Registry::set('conexao_banco', $DIR_BANCOP);

/* Registra currency que será usado automáticamente pelo zend */
Zend_Registry::set('Zend_Currency', new Zend_Currency('pt_BR'));

/* configura o controlador */
$controller = Zend_Controller_Front::getInstance();
$controller->setControllerDirectory($DIR_CONTROLLER);
$controller->dispatch();
