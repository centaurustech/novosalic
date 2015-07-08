<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Reuniao
 *
 * @author augusto
 */
class Reuniao extends GenericModel{

    protected $_banco = 'SAC';
    protected $_name  = 'dbo.tbReuniao';

    public function buscarReuniaoAberta(){
        $select = $this->select();
        $select->from(
                        $this,
                        array
                            (
                                "idNrReuniao",
                                "NrReuniao",
                                "stPlenaria",
                                "DtInicio",
                                "DtFinal"
                            )
                      );
        $select->where("stEstado = ?", 0);
        return $this->fetchRow($select);
    }
    
}
?>
