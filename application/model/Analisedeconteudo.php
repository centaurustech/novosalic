<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Analisedeconteudo
 *
 * @author augusto
 */
class Analisedeconteudo extends GenericModel {

    protected $_banco = 'sac';
    protected $_name = 'tbAnaliseDeConteudo';

    /*public function dadosAnaliseconteudo($idpronac) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                $this,
                array(
                    'idAnaliseDeConteudo',
                    'idProduto',
                    'Lei8313',
                    'Artigo3',
                    'IncisoArtigo3',
                    'AlineaArtigo3',
                    'Artigo18',
                    'AlineaArtigo18',
                    'Artigo26',
                    'Lei5761',
                    'Artigo27',
                    'IncisoArtigo27_I',
                    'IncisoArtigo27_II',
                    'IncisoArtigo27_III',
                    'IncisoArtigo27_IV',
                    'ParecerFavoravel',
                    'cast(ParecerDeConteudo as TEXT) as ParecerDeConteudo'
                    )
                );
        $select->where('IdPRONAC = ?',$idpronac );
        return $this->fetchAll($select);
    }*/

    public function dadosAnaliseconteudo($idpronac, $where = array()) {
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(
                $this,
                array(
                'idAnaliseDeConteudo',
                'idProduto',
                'Lei8313',
                'Artigo3',
                'IncisoArtigo3',
                'AlineaArtigo3',
                'Artigo18',
                'AlineaArtigo18',
                'Artigo26',
                'Lei5761',
                'Artigo27',
                'IncisoArtigo27_I',
                'IncisoArtigo27_II',
                'IncisoArtigo27_III',
                'IncisoArtigo27_IV',
                'ParecerFavoravel',
                'cast(ParecerDeConteudo as TEXT) as ParecerDeConteudo'
                )
        );
        if($idpronac){
            $select->where('IdPRONAC = ?',$idpronac );
        } else {
            foreach($where as $key=>$val)
            {
                $select->where($key,$val);
            }
        }
//        xd($select->assemble());
        return $this->fetchAll($select);
    }

}

?>
