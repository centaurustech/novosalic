<?php
class Conexao{
    
    protected $con;
    protected $dbOrig;

    public function __construct($pathIni, $iniSession) {
        try{
            /* configurações do banco de dados */
            $config = new Zend_Config_Ini($pathIni, $iniSession);
            Zend_Registry::set('config', $config);

            $db = Zend_Db::factory($config->db->adapter, $config->db->params->toArray());
            Zend_Db_Table_Abstract::setDefaultAdapter($db);
            Zend_Registry::set('db', $db); // registra

            $this->con = $db;
            return $this->con;
        }catch (Exception $e){
            echo "N&atilde;o foi poss&iacute;vel conectar ao banco de dados.<br>".$e;
            die;
        }
    }

    public function  backToOriginalConnection() {
        $dbOrig = Zend_Registry::get('db');
    }
}
?>
