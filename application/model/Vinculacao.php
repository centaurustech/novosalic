<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of Projetos
 *
 * @author augusto
 */
class Vinculacao extends GenericModel {

    protected $_name = 'Vinculacao';
    protected $_schema = 'dbo';
    protected $_banco = 'Agentes';


    public function BuscarVinculos($idAgente) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('V' => $this->_name),
                array()
        );
        $slct->joinInner(
                array('N' => 'Nomes'),
                'N.idAgente = V.idAgente',
                array('N.Descricao as vinculado')
        );
        $slct->where('V.idVinculoPrincipal = ? ', $idAgente);

        return $this->fetchRow($slct);
    }

    public function verificarDirigente($cpfPropoenente, $cpfProcurador) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('a' => $this->_name),
                array()
        );
        $slct->joinInner(
                array('b' => 'Agentes'),
                'a.idAgente = b.idAgente',
                array('idAgente'), 'AGENTES.dbo'
        );
        $slct->joinInner(
                array('c' => 'Agentes'),
                'a.idVinculoPrincipal = c.idAgente',
                array(), 'AGENTES.dbo'
        );
        $slct->joinInner(
                array('d' => 'Visao'),
                'd.idAgente = a.idAgente',
                array(), 'AGENTES.dbo'
        );
        $slct->where('b.CNPJCPF = ? ', $cpfProcurador);
        $slct->where('c.CNPJCPF = ? ', $cpfPropoenente);
        $slct->where('d.Visao = ? ', 198);

        return $this->fetchRow($slct);
    }

    public function verificarDirigenteIdAgentes($cpfLogado) {
        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(
                array('a' => $this->_name),
                array()
        );
        $slct->joinInner(
                array('b' => 'Agentes'),
                'a.idAgente = b.idAgente',
                array(), 'AGENTES.dbo'
        );
        $slct->joinInner(
                array('c' => 'Agentes'),
                'a.idVinculoPrincipal = c.idAgente',
                array('a.idVinculoPrincipal AS idAgente'), 'AGENTES.dbo'
        );
        $slct->joinInner(
                array('d' => 'Visao'),
                'd.idAgente = a.idAgente',
                array(), 'AGENTES.dbo'
        );
        $slct->where('b.CNPJCPF = ? ', $cpfLogado);
        $slct->where('d.Visao = ? ', 198);

        return $this->fetchAll($slct);
    }
    
    public function Desvincular($where) {
        
        return $this->delete($where);
    } // fecha método excluirDados()
    
    
  
    
               
} // fecha class