<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of paUsuariosDoPerfil
 */
class sVerificaValidadeCertidaoNegativa extends GenericModel {
        
    protected $_banco = 'SAC';
    protected $_name  = 'sVerificaValidadeCertidaoNegativa';

    public function buscarDados($cpfcnpj){
        $sql = "exec ".$this->_banco.".dbo.".$this->_name." '$cpfcnpj' ";
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchAll($sql);
    }
    
    public function buscarDadosSemSP($cpfcnpj){
        $sql = "
                SELECT 
                    CASE
                        WHEN c.CodigoCertidao = '49'
                        THEN 'Quitação de Tributos Federais'
                        WHEN c.CodigoCertidao = '51'
                        THEN 'FGTS'
                        WHEN c.CodigoCertidao = '52'
                        THEN 'INSS'
                        WHEN c.CodigoCertidao = '244'
                        THEN 'CADIN'
                END AS dsCertidao,
                c.CodigoCertidao, c.DtEmissao, c.DtValidade, c.AnoProjeto+c.Sequencial AS Pronac,
                CASE
                    WHEN c.CodigoCertidao = 244
                    THEN 
                        CASE
                            WHEN c.cdSituacaoCertidao = 0
                            THEN 'Pendente'
                            ELSE 'Não Pendente'
                        END
                END AS Situacao,
                CASE
                    WHEN c.CodigoCertidao = 244
                    THEN DateDiff(dy,getdate(),c.DtEmissao)
                    ELSE DateDiff(dy,getdate(),c.DtValidade)
                END AS qtDias
            FROM SAC.dbo.CertidoesNegativas c
            WHERE c.CgcCpf = '".$cpfcnpj."'
            ORDER BY 2";
        
        $db = Zend_Registry :: get('db');
        $db->setFetchMode(Zend_DB :: FETCH_OBJ);
        return $db->fetchAll($sql);
    }
}
?>
