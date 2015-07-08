<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GenericModel
 *
 * @author augusto
 */
class GenericModel extends Zend_Db_Table_Abstract {

    private $_config;

    public function __construct() {
        //FECHANDO A CONEXAO EXISTENTE JA QUE UMA NOVA SERA ABERTA
        $db = Zend_Db_Table::getDefaultAdapter();
        if(!empty($db)){
            $db->closeConnection();
            unset ($db);
        }

        if (!($this->_config instanceof  Zend_Config_Ini)) {
            $this->_config = new Zend_Config_Ini(
            	Zend_Registry::get('DIR_CONFIG'),
            	'conexao_' . strtolower($this->_banco),
            	array('allowModifications' => true,)
            );
        	Zend_Registry::getInstance()->set('config', $this->_config);
            Zend_Db_Table::setDefaultAdapter(Zend_Db::factory($this->_config->db));
            parent::__construct();

            //Setar o campo texto maior que 4096 caracteres aceitaveis por padr?o no PHP
            $this->_db->query('SET TEXTSIZE 2147483647');
        }
    }

    public function  __destruct() {
        $db = Zend_Db_Table::getDefaultAdapter();
        $db->closeConnection();
    }

    /**
     * Retorna registros do banco de dados
     * @param array $where - array com dados where no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @param array $order - array com orders no formado "coluna_1 desc","coluna_2"...
     * @param int $tamanho - numero de registros que deve retornar
     * @param int $inicio - offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function buscar($where=array(), $order=array(), $tamanho=-1, $inicio=-1) { 
        $slct = $this->select();

        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }
        //adicionando linha order ao select
        $slct->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $slct->limit($tamanho, $tmpInicio);
        }
//        xd($slct->__toString());
//
        return $this->fetchAll($slct);
    }

    public function alterar($dados, $where, $dbg=false) {
        if ($dbg) {
            x($this->dbg($dados, $where));
        }
        $update = $this->update($dados, $where);
        return $update;
    }

    public function apagar($where) {
        $delete = $this->delete($where);
        return $delete;
    }

    public function inserir($dados, $dbg = null) {
        if ($dbg) {
            xd($this->dbg($dados));
        }
        $insert = $this->insert($dados);
        return $insert;
    }

    public function dbg($dados, $where=null) {
        if (!$where) {
            $sql = "INSERT INTO " . $this->_name . " (";
            $keys = array_keys($dados);
            $sql.= implode(',', $keys);
            $sql .= ")\n values ('";
            $values = array_values($dados);
            $sql .= implode("','", $values);
            $sql .= "');";
        } else {
            $sql = "UPDATE " . $this->_name . " SET ";
            foreach ($dados as $coluna => $valor) {
                $sql .= $coluna . " = '" . $valor . "', \n";
            }
            $sql .= "\n" . $where;
        }
        xd($sql);
    }

}

?>
