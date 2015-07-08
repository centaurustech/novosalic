<?php
require('vendor/autoload.php');

/* diretçrios */
$DIR_BANCO        = "bancos";                // Conexao 1
$DIR_BANCOP     = "conexao1";                     // 
$DIR_LIB        = "./library/";                       // bibliotecas
$DIR_CONFIG     = "./application/configs/$DIR_BANCO.ini"; // configuraççes
$DIR_CONFIGP    = "./application/configs/config.ini"; // configurações
$DIR_LAYOUT     = "./application/layout/";            // layouts
$DIR_MODELS     = "./application/model";              // models
$DIR_TO         = "./application/model/TO/";          // tos
$DIR_DAO        = "./application/model/DAO/";         // daos
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
. PATH_SEPARATOR . $DIR_TO
. PATH_SEPARATOR . $DIR_DAO
. PATH_SEPARATOR . $DIR_VIEW
. PATH_SEPARATOR . $DIR_CONTROLLER
. PATH_SEPARATOR . get_include_path());

require_once "Zend/Loader/Autoloader.php";
Zend_Loader_Autoloader::getInstance()->setFallbackAutoloader(true);

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
