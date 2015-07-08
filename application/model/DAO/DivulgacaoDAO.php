<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DivulgacaoDAO
 *
 *
 */
class DivulgacaoDAO extends GenericModel{

    protected $_banco = 'SAC';
    protected $_schema = 'dbo';
    protected $_name  = 'PlanoDeDivulgacao';

    public static function buscarDigulgacao($idPreProjeto){
        $sql = "SELECT
                pd.idPlanoDivulgacao,
                pd.idProjeto,
                pd.idPeca,
                pd.idVeiculo,
                pd.Usuario,
                ve.descricao as Peça,
                ve1.descricao as Veiculo

                FROM
                    sac.dbo.PlanoDeDivulgacao pd
                     join SAC.dbo.Verificacao ve on ve.idVerificacao = pd.idPeca
                     join SAC.dbo.Verificacao ve1 on ve1.idVerificacao = pd.idVeiculo
                WHERE idProjeto = $idPreProjeto
                ";


        $db  = Zend_Registry::get('db');
	$db->setFetchMode(Zend_DB::FETCH_OBJ);
	$resultado = $db->fetchAll($sql);
        //Zend_Debug::dump($resultado);
	return $resultado;
    }


    public static function UpdateDivulgacao($idPlanoDivulgacao, $idPeca, $idVeiculo){
         try
        {
            $sql = "update  sac.dbo.PlanoDeDivulgacao set idPeca = $idPeca, idVeiculo = $idVeiculo where idPlanoDivulgacao = $idPlanoDivulgacao";
            $db = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_ASSOC);
            $resultado = $db->fetchRow($sql);
            
            
        }
        catch (Exception $e)
        {
            die("ERRO" . $e->getMessage());
        }

        
    }
     public static function inserirDivulgacao($divulgacao)
    {
          try
        {
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);

        $cadastrar = $db->insert("SAC.dbo.PlanoDeDivulgacao", $divulgacao);

        } catch (Exception $e){
            die("ERRO" . $e->getMessage());
        }
        
    }
    public static function  excluirdivulgacao($idPlanoDivulgacao){

        try{

        $sql = "delete sac.dbo.PlanoDeDivulgacao where idPlanoDivulgacao = $idPlanoDivulgacao";
        $db = Zend_Registry::get('db');
        $db->setFetchMode(Zend_DB::FETCH_OBJ);
        $resultado = $db->fetchAll($sql);   
        }catch (Exception $e){

           die("ERRO" . $e->getMessage());

        }
    }

    public static function consultarDivulgacao(){
        $sql = "select idVerificacao, Descricao from SAC.dbo.Verificacao where idTipo = 1 order by Descricao";


        $db  = Zend_Registry::get('db');
	$db->setFetchMode(Zend_DB::FETCH_OBJ);
	$resultado = $db->fetchAll($sql);
        ///Zend_Debug::dump($resultado);
        //xd($resultado);
	return $resultado;
    }


    public static function consultarVeiculo($pecaID){


         $sql = "SELECT
		r.idVerificacaoPeca,
		r.idVerificacaoVeiculo,
		P.Descricao as PecaDescicao,
		V.Descricao as VeiculoDescicao
		FROM SAC.dbo.VerificacaoPecaxVeiculo as r

        LEFT JOIN SAC.dbo.Verificacao as P on
            r.idVerificacaoPeca = P.idVerificacao

        LEFT JOIN SAC.dbo.Verificacao as V on
            r.idVerificacaoVeiculo = V.idVerificacao

        WHERE idVerificacaoPeca = ".$pecaID ;


        $db  = Zend_Registry::get('db');
	$db->setFetchMode(Zend_DB::FETCH_OBJ);
	$resultado = $db->fetchAll($sql);
	
        return $resultado;
    }








       public function localiza($where=array(), $order=array(), $tamanho=-1, $inicio=-1){

        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('tbr' => $this->_name));


        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }

        return $this->fetchAll($slct);
    }


















    public function buscar($where=array(), $order=array(), $tamanho=-1, $inicio=-1){

        $slct = $this->select();
        $slct->setIntegrityCheck(false);
        $slct->from(array('pd' => $this->_name));
        
        $slct->joinInner(array('v1' => 'Verificacao'),
                               'v1.idVerificacao = pd.idPeca',
                         array('v1.Descricao as Peca'));
        $slct->joinInner(
                array('v2' => 'Verificacao'),
                'v2.idVerificacao = pd.idVeiculo',
                array('v2.descricao as Veiculo')
        );
        
        //adiciona quantos filtros foram enviados
        foreach ($where as $coluna => $valor) {
            $slct->where($coluna, $valor);
        }

        //adicionando linha order ao select
        $slct->order($order);

        // paginacao
        if ($tamanho > -1) {
            $tmpInicio = 0;
            if ($inicio > -1) {
                $tmpInicio = $inicio;
            }
            $slct->limit($tamanho, $tmpInicio);
        }
        //xd($slct->query());
        return $this->fetchAll($slct);
    }
}
?>
