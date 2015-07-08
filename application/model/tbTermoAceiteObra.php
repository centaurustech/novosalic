<?php
/**
 * DAO tbTermoAceiteObra
 * @since 21/12/2012
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbTermoAceiteObra extends GenericModel {
    protected $_banco  = "SAC";
    protected $_schema = "dbo";
    protected $_name   = "tbTermoAceiteObra";

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
        $where = "idTermoAceiteObra = " . $where;
        return $this->update($dados, $where);
    } // fecha método alterarDados()


    public function buscarTermoAceiteObra($where, $all = false, $order = array()) {
        // criando objeto do tipo select
        $slct = $this->select();
        $slct->setIntegrityCheck(false);

        $slct->from(
                $this->_name,
                array('idTermoAceiteObra', 'idPronac', 'dtCadastroTermo',
                    'CAST(dsDescricaoTermoAceite AS TEXT) AS dsDescricaoTermoAceite',
                    'idDocumentoTermo','idUsuarioCadastrador','stConstrucaoCriacaoRestauro')
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

    public function buscarTermoAceiteObraArquivos($where, $all = false, $order = array()) {
        // criando objeto do tipo select
        $slct = $this->select();
        $slct->setIntegrityCheck(false);

        $slct->from(
                array('a' => $this->_name),
                array('idTermoAceiteObra', 'idPronac', 'dtCadastroTermo',
                    'CAST(dsDescricaoTermoAceite AS TEXT) AS dsDescricaoTermoAceite',
                    'idDocumentoTermo','idUsuarioCadastrador','stConstrucaoCriacaoRestauro')
        );

        $slct->joinLeft(
                array('b' => 'tbDocumento'), "a.idDocumentoTermo = b.idDocumento",
                array(''), 'BDCORPORATIVO.scCorp'
        );
        $slct->joinLeft(
                array('c' => 'tbArquivo'), "b.idArquivo = c.idArquivo",
                array('idArquivo','nmArquivo','sgExtensao','dtEnvio'), 'BDCORPORATIVO.scCorp'
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