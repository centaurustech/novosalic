<?php
/**
 * DAO tbDistribuicaoProduto
 * @since 16/03/2011
 * @version 1.0
 * @package application
 * @subpackage application.model
 * @copyright © 2011 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class tbDistribuicaoProduto extends GenericModel
{
	protected $_banco  = "SAC";
	protected $_schema = "dbo";
	protected $_name   = "tbDistribuicaoProduto";


        public function buscarDistribuicaoProduto($idDistribuicaoProduto){
            $slct = $this->select();
            $slct->setIntegrityCheck(false);
            $slct->from(
                        array('dp'=>$this->_name),
                        array(
                            'dp.qtDistribuicao',
                            'dp.idDocumento',
                            'dp.dsTamanhoDuracao',
                            'dp.idDistribuicaoProduto',
                            'dp.stFinsLucrativos',
                            'dp.dsDestinacaoProduto',
                            'dp.dsReceptorProduto',
                            '*'
                            )
                        );
            $slct->joinInner(
                            array('pdp'=>'PlanoDistribuicaoProduto'),
                            'dp.idPlanoDistribuicao = pdp.idPlanoDistribuicao AND pdp.stPlanoDistribuicaoProduto = 1',
                            array(
                                    'pdp.QtdeProduzida',
                                    '(pdp.QtdePatrocinador+pdp.QtdeProponente+pdp.QtdeProponente) as DistribuicaoGratuita'
                                 )
                            );
            $slct->joinInner(
                             array('pd'=>'Produto'),
                             "pd.Codigo = pdp.idProduto",
                             array('pd.Descricao')
                            );
            $slct->where('dp.idDistribuicaoProduto = ?', $idDistribuicaoProduto);
//            xd($slct->assemble());
            return $this->fetchAll($slct);

        }


        public function buscarDistribuicaoProduto2($idPlanoDistribuicao){
            $slct = $this->select();
            $slct->setIntegrityCheck(false);
            $slct->from(
                        array('dp'=>$this->_name),
                        array(
                            'dp.qtDistribuicao',
                            'dp.idDocumento',
                            'dp.dsTamanhoDuracao',
                            'dp.idDistribuicaoProduto',
                            'dp.stFinsLucrativos',
                            'dp.dsDestinacaoProduto',
                            'dp.dsReceptorProduto',
                            '*'
                            )
                        );
            $slct->joinInner(
                            array('pdp'=>'PlanoDistribuicaoProduto'),
                            'dp.idPlanoDistribuicao = pdp.idPlanoDistribuicao AND pdp.stPlanoDistribuicaoProduto = 1',
                            array(
                                    'pdp.QtdeProduzida',
                                    '(pdp.QtdePatrocinador+pdp.QtdeProponente+pdp.QtdeProponente) as DistribuicaoGratuita'
                                 )
                            );
            $slct->joinInner(
                             array('pd'=>'Produto'),
                             "pd.Codigo = pdp.idProduto",
                             array('pd.Descricao')
                            );
            $slct->where('dp.idPlanoDistribuicao = ?', $idPlanoDistribuicao);
//            xd($slct->assemble());
            return $this->fetchAll($slct);

        }

} // fecha class