<?php
/**
 * DAO tbComprovanteTrimestral
 * @since 16/03/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbComprovanteTrimestral extends GenericModel {
    protected $_banco  = "SAC";
    protected $_schema = "dbo";
    protected $_name   = "tbComprovanteTrimestral";

    /**
     * Método para cadastrar
     * @access public
     * @param array $dados
     * @return integer (retorna o último id cadastrado)
     */
    public function cadastrarDados($dados) {
        return $this->insert($dados);
    } // fecha método cadastrarDados()


    /**
     * Método para alterar
     * @access public
     * @param array $dados
     * @param integer $where
     * @return integer (quantidade de registros alterados)
     */
    public function alterarDados($dados, $where) {
        $where = "idComprovanteTrimestral = " . $where;
        return $this->update($dados, $where);
    } // fecha método alterarDados()


    public function buscarComprovantes($where, $all = false, $order = array()) {
        // criando objeto do tipo select
        $slct = $this->select();
        $slct->from( $this->_name,
                array('idComprovanteTrimestral', 'IdPRONAC', 'dtComprovante','dtInicioPeriodo', 'dtFimPeriodo', 
                    'CAST(dsEtapasExecutadas AS TEXT) AS dsEtapasExecutadas',
                    'CAST(dsAcessibilidade AS TEXT) AS dsAcessibilidade',
                    'CAST(dsDemocratizacaoAcesso AS TEXT) AS dsDemocratizacaoAcesso',
                    'CAST(dsImpactoAmbiental AS TEXT) AS dsImpactoAmbiental',
                    'siComprovanteTrimestral', 'nrComprovanteTrimestral', 'idCadastrador',
                    'CAST(dsParecerTecnico AS TEXT) AS dsParecerTecnico',
                    'CAST(dsRecomendacao AS TEXT) AS dsRecomendacao','idTecnicoAvaliador')
        );

        // adicionando clausulas where
        foreach ($where as $coluna=>$valor) {
            $slct->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $slct->order($order);

        // retornando os registros
        if($all){
            return $this->fetchAll($slct);
        } else {
            return $this->fetchRow($slct);
        }
    } // fecha método alterarDados()


} // fecha class