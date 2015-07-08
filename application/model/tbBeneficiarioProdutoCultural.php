<?php
/**
 * DAO tbBeneficiarioProdutoCultural
 * @since 16/03/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbBeneficiarioProdutoCultural extends GenericModel {
    protected $_banco  = "SAC";
    protected $_schema = "dbo";
    protected $_name   = "tbBeneficiarioProdutoCultural";


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
        $where = "idBeneficiarioProdutoCultural = " . $where;
        return $this->update($dados, $where);
    } // fecha método alterarDados()

    
    public function buscarPlanosCadastrados($idPronac) {
        $a = $this->select();
        $a->setIntegrityCheck(false);
        $a->from(
                array('a' => $this->_name),
                array('idBeneficiarioProdutoCultural','idTipoBeneficiario','qtRecebida','idPlanoDistribuicao')
        );
        $a->joinInner(
                array('b' => 'PlanoDistribuicaoProduto'), "a.idPlanoDistribuicao = b.idPlanoDistribuicao",
                array(''), 'SAC.dbo'
        );
        $a->joinInner(
                array('c' => 'Produto'), "b.idProduto = c.Codigo",
                array('Descricao as Produto'), 'SAC.dbo'
        );
        $a->joinInner(
                array('d' => 'Nomes'), "a.idAgente = d.idAgente",
                array('Descricao as Beneficiario'), 'AGENTES.dbo'
        );
        $a->joinInner(
                array('e' => 'tbDocumento'), "a.idDocumento = e.idDocumento",
                array(''), 'BDCORPORATIVO.scCorp'
        );
        $a->joinInner(
                array('f' => 'tbArquivo'), "e.idArquivo = f.idArquivo",
                array('idArquivo','nmArquivo','dtEnvio'), 'BDCORPORATIVO.scCorp'
        );
        $a->joinInner(
                array('g' => 'Agentes'), "d.idAgente = g.idAgente",
                array('CNPJCPF'), 'AGENTES.dbo'
        );
        $a->where('a.IdPRONAC = ?', $idPronac);
        $a->order('c.Descricao');
        return $this->fetchAll($a);
    }

} // fecha class