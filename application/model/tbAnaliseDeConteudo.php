<?php
/**
 * DAO tbAnaliseDeConteudo
 * @since 01/08/2013
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbAnaliseDeConteudo extends GenericModel {
    protected $_banco  = "SAC";
    protected $_schema = "dbo";
    protected $_name   = "tbAnaliseDeConteudo";

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
        $where = "idAnaliseDeConteudo = " . $where;
        return $this->update($dados, $where);
    } // fecha método alterarDados()



    public function buscarOutrasInformacoes($idPronac) {

        $select =  new Zend_Db_Expr("
                SELECT idPRONAC,p.Descricao AS Produto,
                  CASE
                     WHEN Artigo18 = 1
                          THEN 'Artigo 18'
                          ELSE 'Artigo 26'
                     END as Enquadramento,
                   CASE
                      WHEN IncisoArtigo27_I = 0
                           THEN 'Não'
                           ELSE 'Sim'
                      END as IncisoArtigo27_I,
                  CASE
                      WHEN IncisoArtigo27_II = 0
                           THEN 'Não'
                           ELSE 'Sim'
                      END as IncisoArtigo27_II,
                   CASE
                      WHEN IncisoArtigo27_III = 0
                           THEN 'Não'
                           ELSE 'Sim'
                      END as IncisoArtigo27_III,
                   CASE
                      WHEN IncisoArtigo27_IV = 0
                           THEN 'Não'
                           ELSE 'Sim'
                      END as IncisoArtigo27_IV
            FROM tbAnaliseDeConteudo a
            INNER JOIN Produto p ON (a.idProduto = p.Codigo)
            WHERE idPronac = $idPronac ORDER BY 2");
        try {
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
        } catch (Zend_Exception_Db $e) {
            $this->view->message = $e->getMessage();
        }
        return $db->fetchAll($select);
    }
    
    public function cidadoBuscarOutrasInformacoes($idPronac) {

            $select = $this->select();
            $select->setIntegrityCheck(false);
            $select->from(
                array('a' => $this->_name),
                array(
                    New Zend_Db_Expr("
                        a.idPronac,
                        p.Descricao AS Produto,
                        CASE
                            WHEN Artigo18 = 1
                                THEN 'Artigo 18'
                                ELSE 'Artigo 26'
                            END as Enquadramento,
                        CASE
                            WHEN IncisoArtigo27_I = 0
                                THEN 'Não'
                                ELSE 'Sim'
                            END as IncisoArtigo27_I,
                        CASE
                            WHEN IncisoArtigo27_II = 0
                                THEN 'Não'
                                ELSE 'Sim'
                            END as IncisoArtigo27_II,
                        CASE
                            WHEN IncisoArtigo27_III = 0
                                THEN 'Não'
                                ELSE 'Sim'
                            END as IncisoArtigo27_III,
                        CASE
                            WHEN IncisoArtigo27_IV = 0
                                THEN 'Não'
                                ELSE 'Sim'
                            END as IncisoArtigo27_IV
                    ")
                )
            );
            $select->joinInner(
                array('p' => 'Produto'),'a.idProduto = p.Codigo',
                array(''), 'SAC.dbo'
            );
            $select->where('a.idPronac = ?', $idPronac);

            //xd($select->assemble());
            return $this->fetchAll($select);
        }

} // fecha class