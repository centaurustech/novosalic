<?php
class Logomarca extends Zend_Db_Table_Abstract
{
    //protected $_db = "SAC";
    //protected $_schema = "dbo";
    protected $_name = "Verificacao";
    //protected $_primary = "idPlanoDistribuicao";

    public function __construct() {
        $db = new Conexao(Zend_Registry::get('DIR_CONFIG'), "conexao_sac");

        parent::__construct();
    }

    /**
     * Retorna registros do banco de dados
     * @param array $where - array com dados where no formato "nome_coluna_1"=>"valor_1","nome_coluna_2"=>"valor_2"
     * @param array $order - array com orders no formado "coluna_1 desc","coluna_2"...
     * @param int $tamanho - numero de registros que deve retornar
     * @param int $inicio - offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function buscar($where=array(), $order=array(), $tamanho=-1, $inicio=-1)
    {
            // criando objeto do tipo select
            $slct = $this->select();

            // adicionando clausulas where
            foreach ($where as $coluna=>$valor)
            {
                    $slct->where($coluna, $valor);
            }

            // adicionando linha order ao select
            $slct->order($order);

            // paginacao
            if ($tamanho > -1)
            {
                    $tmpInicio = 0;
                    if ($inicio > -1)
                    {
                            $tmpInicio = $inicio;
                    }
                    $slct->limit($tamanho, $tmpInicio);
            }

            // retornando os registros conforme objeto select
            return $this->fetchAll($slct);
    }
}
?>
