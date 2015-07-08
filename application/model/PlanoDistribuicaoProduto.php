<?php
/**
 * DAO PlanoDistribuicaoProduto
 * @since 16/03/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class PlanoDistribuicaoProduto extends GenericModel {
    protected $_banco  = "SAC";
    protected $_schema = "dbo";
    protected $_name   = "PlanoDistribuicaoProduto";


    /**
     * Método para cadastrar
     * @access public
     * @param array $dados
     * @return integer (retorna o último id cadastrado)
     */
    public function cadastrarDados($dados) {
        return $this->insert($dados);
    } // fecha método cadastrarDados()


    public function buscarPlanoDeDistribuicao($idPronac) {
        $a = $this->select();
        $a->setIntegrityCheck(false);
        $a->from(
                array('a' => $this->_name),
                array('idPlanoDistribuicao','idProduto','QtdePatrocinador','QtdeProponente','QtdeOutros')
        );
        $a->joinInner(
                array('b' => 'Projetos'), "a.idProjeto = b.idProjeto",
                array('IdPRONAC'), 'SAC.dbo'
        );
        $a->joinInner(
                array('c' => 'Produto'), "a.idProduto = c.Codigo",
                array('Descricao as Produto'), 'SAC.dbo'
        );
        $a->where('b.IdPRONAC = ?', $idPronac);
        return $this->fetchAll($a);
    }
    
    public function buscarProdutosProjeto($idPronac) {
        $a = $this->select();
        $a->setIntegrityCheck(false);
        $a->from(
                array('a' => $this->_name),
                array('stPrincipal','idProduto')
        );
        $a->joinInner(
                array('b' => 'Projetos'), "a.idProjeto = b.idProjeto",
                array(''), 'SAC.dbo'
        );
        $a->joinInner(
                array('c' => 'Produto'), "a.idProduto = c.Codigo",
                array('Descricao as Produto'), 'SAC.dbo'
        );
        $a->joinInner(
                array('d' => 'tbAnaliseDeConteudo'), "a.idProduto = d.idProduto AND b.IdPRONAC = d.idPronac",
                array('*'), 'SAC.dbo'
        );
        $a->where('b.IdPRONAC = ?', $idPronac);
        $a->where('d.idPronac = ?', $idPronac);
        $a->order(array('1 DESC', '3 ASC'));
        
        //xd($a->assemble());
        return $this->fetchAll($a);
    }

    public function comboProdutosParaInclusaoReadequacao($idPronac) {
        $a = $this->select();
        $a->setIntegrityCheck(false);
        $a->from(
            array('a' => $this->_name),
            array('idProduto')
        );
        $a->joinInner(
            array('b' => 'Produto'), "a.idProduto = b.codigo",
            array('Descricao AS Produto'), 'SAC.dbo'
        );
        $a->joinInner(
            array('c' => 'Projetos'), "a.idProjeto = c.idProjeto",
            array(''), 'SAC.dbo'
        );
        $a->where('c.IdPRONAC = ?', $idPronac);
        
        
        $b = $this->select();
        $b->setIntegrityCheck(false);
        $b->from(
            array('a' => $this->_name),
            array(
                new Zend_Db_Expr("'0', 'Administração do Projeto'")
            )
        );
        $b->joinInner(
            array('c' => 'Projetos'), "a.idProjeto = c.idProjeto",
            array(''), 'SAC.dbo'
        );
        $b->where('c.IdPRONAC = ?', $idPronac);
        
        
        $slctUnion = $this->select()
            ->union(array('('.$a.')', '('.$b.')'))
            ->order('1','2');

        //xd($slctUnion->assemble());
        return $this->fetchAll($slctUnion);
    }

} // fecha class