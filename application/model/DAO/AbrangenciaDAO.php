<?php
/**
 * DAO AbrangenciaDAO
 * @author Equipe RUP - Politec
 * @since 28/04/2010
 * @version 1.0
 * @package application
 * @subpackage application.model.DAO
 * @copyright © 2010 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://www.cultura.gov.br
 */

class AbrangenciaDAO extends Zend_Db_Table 
{
	/* dados da tabela */
	protected $_schema  = 'dbo';
	protected $_name    = 'Abrangencia';
	protected $_primary = 'idAbrangencia';

	/**
	 * Método para cadastrar
	 * @access public
	 * @static
	 * @param array $dados
	 * @return bool
	 */
	public static function cadastrar($dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$cadastrar = $db->insert("SAC.dbo.Abrangencia", $dados);

		if ($cadastrar)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método cadastrar()



        	/**
	 * Método para excluir
	 * @access public
	 * @static
	 * @param array $dados
	 * @return bool
	 */
	public static function excluir($where)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

        $where   = array("idAbrangencia = ? " => $where, "stAbrangencia = ?" => 1);
        
        // limpa a associação antes de excluir
        $alterar = $db->update("SAC.dbo.tbAbrangencia", array("idAbrangenciaAntiga" => NULL), array("idAbrangenciaAntiga = ? " => $where));

		$excluir = $db->delete("SAC.dbo.Abrangencia", $where);

		if ($excluir)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método excluir()



        	/**
	 * Método para alterar
	 * @access public
	 * @static
	 * @param array $dados
	 * @return bool
	 */
	public static function alterar($dados, $where)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

                $where   = "idAbrangencia = $where";
		$alterar = $db->update("SAC.dbo.Abrangencia", $dados, $where);

		if ($alterar)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método alterar()


        public static function buscarAbrangenciasAtuais($idProjeto, $idPais, $idUF, $idMunicipioIBGE){
            $sql = "SELECT * from SAC.dbo.Abrangencia
                    WHERE
                        idProjeto = $idProjeto
                        and idPais = $idPais
                        and idUF = $idUF
                        and idMunicipioIBGE = $idMunicipioIBGE 
                        and stAbrangencia = 1
                    ";
            $db  = Zend_Registry::get('db');
            $db->setFetchMode(Zend_DB::FETCH_OBJ);
            $resultado = $db->fetchAll($sql);

            return $resultado;
        }



        public static function buscarDadosAbrangenciaAlteracao($idpedidoalteracao, $avaliacao)
        {
        if ($avaliacao == "SEM_AVALIACAO")
        {
        $sql = "
            SELECT *, CAST(dsjustificativa AS text) as dsjustificativa FROM
            (
            SELECT
                    distinct (abran.idAbrangencia),
                    pais.Descricao pais,
                    uf.Descricao as uf,
                    mun.Descricao as mun,
                    abran.tpAcao as tpoperacao,
                    tpa.dsjustificativa,
taipa.idAvaliacaoItemPedidoAlteracao,
asipa.idAvaliacaoSubItemPedidoAlteracao,
asipa.stAvaliacaoSubItemPedidoAlteracao as avaliacao,
--CAST(asipa.dsAvaliacaoSubItemPedidoAlteracao AS TEXT) as dsAvaliacao
asipa.dsAvaliacaoSubItemPedidoAlteracao as dsAvaliacao,
abran.dsExclusao
                FROM
                    SAC.dbo.tbAbrangencia abran
                    INNER JOIN BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto proj on proj.idPedidoAlteracao = abran.idPedidoAlteracao
                    INNER JOIN SAC.dbo.Projetos pr on pr.IdPRONAC = proj.IdPRONAC
                    INNER JOIN BDCORPORATIVO.scSAC.tbPedidoAlteracaoXTipoAlteracao tpa on tpa.idPedidoAlteracao = abran.idPedidoAlteracao
                    INNER JOIN BDCORPORATIVO.scSAC.tbTipoAlteracaoProjeto ta on ta.tpAlteracaoProjeto = tpa.tpAlteracaoProjeto
                    INNER JOIN SAC.dbo.Abrangencia ab on ab.idProjeto = pr.idProjeto AND ab.stAbrangencia = 1
                    INNER JOIN Agentes.dbo.Pais	pais on pais.idPais = abran.idPais
            LEFT JOIN AGENTES.dbo.Uf uf on uf.idUF = abran.idUF
            LEFT JOIN AGENTES.dbo.Municipios mun on mun.idMunicipioIBGE = abran.idMunicipioIBGE
--INNER JOIN BDCORPORATIVO.scSAC.tbAvaliacaoItemPedidoAlteracao taipa ON taipa.idPedidoAlteracao = tpa.idPedidoAlteracao
--INNER JOIN BDCORPORATIVO.scSAC.tbAcaoAvaliacaoItemPedidoAlteracao taaipa ON taipa.idAvaliacaoItemPedidoAlteracao = taaipa.idAvaliacaoItemPedidoAlteracao
LEFT JOIN BDCORPORATIVO.scSAC.tbAvaliacaoItemPedidoAlteracao taipa ON taipa.idPedidoAlteracao = tpa.idPedidoAlteracao
LEFT JOIN BDCORPORATIVO.scSAC.tbAcaoAvaliacaoItemPedidoAlteracao taaipa ON taipa.idAvaliacaoItemPedidoAlteracao = taaipa.idAvaliacaoItemPedidoAlteracao

LEFT JOIN BDCORPORATIVO.scSAC.tbAvaliacaoSubItemPedidoAlteracao asipa ON (taipa.idAvaliacaoItemPedidoAlteracao = asipa.idAvaliacaoItemPedidoAlteracao
	AND asipa.idAvaliacaoSubItemPedidoAlteracao = abran.idAbrangencia )
                WHERE
                    proj.IdPRONAC = $idpedidoalteracao and tpa.tpAlteracaoProjeto = 4 and taipa.tpAlteracaoProjeto = 4 and abran.tpAcao != 'N'
               --ORDER BY pais.Descricao, uf.Descricao, mun.Descricao, taipa.idAvaliacaoItemPedidoAlteracao DESC
            ) as minhaTabela
            ORDER BY pais, uf, mun, idAvaliacaoItemPedidoAlteracao DESC
            ";
        } // fecha if
        else
        {

        $sql = "
            SELECT *, CAST(dsjustificativa AS text) as dsjustificativa FROM
            (
            SELECT
                    distinct (abran.idAbrangencia),
                    pais.Descricao pais,
                    uf.Descricao as uf,
                    mun.Descricao as mun,
                    abran.tpAcao as tpoperacao,
                    tpa.dsjustificativa,
taipa.idAvaliacaoItemPedidoAlteracao,
abran.dsExclusao,
tasia.idAvaliacaoSubItemPedidoAlteracao,
tasipa.stAvaliacaoSubItemPedidoAlteracao as avaliacao,
tasipa.dsAvaliacaoSubItemPedidoAlteracao as dsAvaliacao,
taipa.stAvaliacaoItemPedidoAlteracao
                FROM
                    SAC.dbo.tbAbrangencia abran
                    INNER JOIN BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto proj on proj.idPedidoAlteracao = abran.idPedidoAlteracao
                    INNER JOIN SAC.dbo.Projetos pr on pr.IdPRONAC = proj.IdPRONAC
                    INNER JOIN BDCORPORATIVO.scSAC.tbPedidoAlteracaoXTipoAlteracao tpa on tpa.idPedidoAlteracao = abran.idPedidoAlteracao
                    INNER JOIN BDCORPORATIVO.scSAC.tbTipoAlteracaoProjeto ta on ta.tpAlteracaoProjeto = tpa.tpAlteracaoProjeto
                    INNER JOIN SAC.dbo.Abrangencia ab on ab.idProjeto = pr.idProjeto AND ab.stAbrangencia = 1
                    INNER JOIN Agentes.dbo.Pais	pais on pais.idPais = abran.idPais
            LEFT JOIN AGENTES.dbo.Uf uf on uf.idUF = abran.idUF
            LEFT JOIN AGENTES.dbo.Municipios mun on mun.idMunicipioIBGE = abran.idMunicipioIBGE
--INNER JOIN BDCORPORATIVO.scSAC.tbAvaliacaoItemPedidoAlteracao taipa ON taipa.idPedidoAlteracao = tpa.idPedidoAlteracao
--INNER JOIN BDCORPORATIVO.scSAC.tbAcaoAvaliacaoItemPedidoAlteracao taaipa ON taipa.idAvaliacaoItemPedidoAlteracao = taaipa.idAvaliacaoItemPedidoAlteracao
LEFT JOIN BDCORPORATIVO.scSAC.tbAvaliacaoItemPedidoAlteracao taipa ON taipa.idPedidoAlteracao = tpa.idPedidoAlteracao
LEFT JOIN BDCORPORATIVO.scSAC.tbAcaoAvaliacaoItemPedidoAlteracao taaipa ON taipa.idAvaliacaoItemPedidoAlteracao = taaipa.idAvaliacaoItemPedidoAlteracao

LEFT JOIN BDCORPORATIVO.scSAC.tbAvaliacaoSubItemPedidoAlteracao asipa ON (taipa.idAvaliacaoItemPedidoAlteracao = asipa.idAvaliacaoItemPedidoAlteracao
	AND asipa.idAvaliacaoSubItemPedidoAlteracao = abran.idAbrangencia )

LEFT JOIN BDCORPORATIVO.scSAC.tbAvaliacaoSubItemAbragencia tasia ON (tasia.idAbrangencia = abran.idAbrangencia AND tasia.idAvaliacaoItemPedidoAlteracao = taipa.idAvaliacaoItemPedidoAlteracao)
LEFT JOIN BDCORPORATIVO.scSAC.tbAvaliacaoSubItemPedidoAlteracao tasipa ON (tasipa.idAvaliacaoSubItemPedidoAlteracao = tasia.idAvaliacaoSubItemPedidoAlteracao AND tasipa.idAvaliacaoItemPedidoAlteracao = taipa.idAvaliacaoItemPedidoAlteracao)
                WHERE
                    proj.IdPRONAC = $idpedidoalteracao and tpa.tpAlteracaoProjeto = 4 and taipa.tpAlteracaoProjeto = 4 and abran.tpAcao != 'N'
                    --AND taipa.stAvaliacaoItemPedidoAlteracao in ('EA', 'AG')
               --ORDER BY pais.Descricao, uf.Descricao, mun.Descricao, taipa.idAvaliacaoItemPedidoAlteracao DESC
            ) as minhaTabela
            ORDER BY pais, uf, mun, idAvaliacaoItemPedidoAlteracao DESC
            ";
        } // fecha else

        $db  = Zend_Registry::get('db');
	$db->setFetchMode(Zend_DB::FETCH_OBJ);
	$resultado = $db->fetchAll($sql);

	return $resultado;
    }


    public static function buscarDadosAbrangenciaAlteracaoCoord($idpedidoalteracao, $avaliacao)
    {
        if ($avaliacao == "SEM_AVALIACAO")
        {
        $sql = "SELECT * , CAST(dsjustificativa AS text) AS dsjustificativa , CAST(dsjustificativa AS text) AS dsjustificativa  FROM  (
                    SELECT
                    distinct (abran.idAbrangencia),
                    pais.Descricao pais,
                    uf.Descricao as uf,
                    mun.Descricao as mun,
                    abran.tpAcao as tpoperacao,
                    tpa.dsjustificativa,
taipa.idAvaliacaoItemPedidoAlteracao,
asipa.idAvaliacaoSubItemPedidoAlteracao,
asipa.stAvaliacaoSubItemPedidoAlteracao as avaliacao,
asipa.dsAvaliacaoSubItemPedidoAlteracao,
abran.dsExclusao
                FROM
                    SAC.dbo.tbAbrangencia abran
                    INNER JOIN BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto proj on proj.idPedidoAlteracao = abran.idPedidoAlteracao
                    INNER JOIN SAC.dbo.Projetos pr on pr.IdPRONAC = proj.IdPRONAC
                    INNER JOIN BDCORPORATIVO.scSAC.tbPedidoAlteracaoXTipoAlteracao tpa on tpa.idPedidoAlteracao = abran.idPedidoAlteracao
                    INNER JOIN BDCORPORATIVO.scSAC.tbTipoAlteracaoProjeto ta on ta.tpAlteracaoProjeto = tpa.tpAlteracaoProjeto
                    INNER JOIN SAC.dbo.Abrangencia ab on ab.idProjeto = pr.idProjeto AND ab.stAbrangencia = 1
                    INNER JOIN Agentes.dbo.Pais	pais on pais.idPais = abran.idPais
            LEFT JOIN AGENTES.dbo.Uf uf on uf.idUF = abran.idUF
            LEFT JOIN AGENTES.dbo.Municipios mun on mun.idMunicipioIBGE = abran.idMunicipioIBGE
--INNER JOIN BDCORPORATIVO.scSAC.tbAvaliacaoItemPedidoAlteracao taipa ON taipa.idPedidoAlteracao = tpa.idPedidoAlteracao
--INNER JOIN BDCORPORATIVO.scSAC.tbAcaoAvaliacaoItemPedidoAlteracao taaipa ON taipa.idAvaliacaoItemPedidoAlteracao = taaipa.idAvaliacaoItemPedidoAlteracao
LEFT JOIN BDCORPORATIVO.scSAC.tbAvaliacaoItemPedidoAlteracao taipa ON taipa.idPedidoAlteracao = tpa.idPedidoAlteracao
LEFT JOIN BDCORPORATIVO.scSAC.tbAcaoAvaliacaoItemPedidoAlteracao taaipa ON taipa.idAvaliacaoItemPedidoAlteracao = taaipa.idAvaliacaoItemPedidoAlteracao

LEFT JOIN BDCORPORATIVO.scSAC.tbAvaliacaoSubItemPedidoAlteracao asipa ON (taipa.idAvaliacaoItemPedidoAlteracao = asipa.idAvaliacaoItemPedidoAlteracao
	AND asipa.idAvaliacaoSubItemPedidoAlteracao = abran.idAbrangencia )
                WHERE
                    proj.IdPRONAC = $idpedidoalteracao and tpa.tpAlteracaoProjeto = 4 and abran.tpAcao != 'N'
               ) AS TABELA ORDER BY pais, uf, mun, idAvaliacaoItemPedidoAlteracao DESC ";
        } // fecha if
        else
        {

        $sql = "SELECT * , CAST(dsjustificativa AS text) AS dsjustificativa, CAST(dsAvaliacao AS text) AS dsAvaliacao  FROM  (
                    SELECT
                    distinct (abran.idAbrangencia),
                    pais.Descricao pais,
                    uf.Descricao as uf,
                    mun.Descricao as mun,
                    abran.tpAcao as tpoperacao,
                    tpa.dsjustificativa,
                    taipa.idAvaliacaoItemPedidoAlteracao,
                    abran.dsExclusao,
                    tasia.idAvaliacaoSubItemPedidoAlteracao,
                    tasipa.stAvaliacaoSubItemPedidoAlteracao as avaliacao,
                    tasipa.dsAvaliacaoSubItemPedidoAlteracao as dsAvaliacao,
                    taipa.stAvaliacaoItemPedidoAlteracao
                FROM
                    SAC.dbo.tbAbrangencia abran
                    INNER JOIN BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto proj on proj.idPedidoAlteracao = abran.idPedidoAlteracao
                    INNER JOIN SAC.dbo.Projetos pr on pr.IdPRONAC = proj.IdPRONAC
                    INNER JOIN BDCORPORATIVO.scSAC.tbPedidoAlteracaoXTipoAlteracao tpa on tpa.idPedidoAlteracao = abran.idPedidoAlteracao
                    INNER JOIN BDCORPORATIVO.scSAC.tbTipoAlteracaoProjeto ta on ta.tpAlteracaoProjeto = tpa.tpAlteracaoProjeto
                    INNER JOIN SAC.dbo.Abrangencia ab on ab.idProjeto = pr.idProjeto AND ab.stAbrangencia = 1
                    INNER JOIN Agentes.dbo.Pais	pais on pais.idPais = abran.idPais
            LEFT JOIN AGENTES.dbo.Uf uf on uf.idUF = abran.idUF
            LEFT JOIN AGENTES.dbo.Municipios mun on mun.idMunicipioIBGE = abran.idMunicipioIBGE
--INNER JOIN BDCORPORATIVO.scSAC.tbAvaliacaoItemPedidoAlteracao taipa ON taipa.idPedidoAlteracao = tpa.idPedidoAlteracao
--INNER JOIN BDCORPORATIVO.scSAC.tbAcaoAvaliacaoItemPedidoAlteracao taaipa ON taipa.idAvaliacaoItemPedidoAlteracao = taaipa.idAvaliacaoItemPedidoAlteracao
LEFT JOIN BDCORPORATIVO.scSAC.tbAvaliacaoItemPedidoAlteracao taipa ON taipa.idPedidoAlteracao = tpa.idPedidoAlteracao
LEFT JOIN BDCORPORATIVO.scSAC.tbAcaoAvaliacaoItemPedidoAlteracao taaipa ON taipa.idAvaliacaoItemPedidoAlteracao = taaipa.idAvaliacaoItemPedidoAlteracao

LEFT JOIN BDCORPORATIVO.scSAC.tbAvaliacaoSubItemPedidoAlteracao asipa ON (taipa.idAvaliacaoItemPedidoAlteracao = asipa.idAvaliacaoItemPedidoAlteracao
	AND asipa.idAvaliacaoSubItemPedidoAlteracao = abran.idAbrangencia )

LEFT JOIN BDCORPORATIVO.scSAC.tbAvaliacaoSubItemAbragencia tasia ON (tasia.idAbrangencia = abran.idAbrangencia AND tasia.idAvaliacaoItemPedidoAlteracao = taipa.idAvaliacaoItemPedidoAlteracao)
LEFT JOIN BDCORPORATIVO.scSAC.tbAvaliacaoSubItemPedidoAlteracao tasipa ON (tasipa.idAvaliacaoSubItemPedidoAlteracao = tasia.idAvaliacaoSubItemPedidoAlteracao AND tasipa.idAvaliacaoItemPedidoAlteracao = taipa.idAvaliacaoItemPedidoAlteracao)
                WHERE
                    proj.IdPRONAC = $idpedidoalteracao and tpa.tpAlteracaoProjeto = 4  and abran.tpAcao != 'N' 
                    --AND taipa.stAvaliacaoItemPedidoAlteracao in ('EA', 'AG')
                ) as tabelas ORDER BY pais, uf, mun, idAvaliacaoItemPedidoAlteracao DESC  ";
        } // fecha else

        $db  = Zend_Registry::get('db');
	$db->setFetchMode(Zend_DB::FETCH_OBJ);
	$resultado = $db->fetchAll($sql);

	return $resultado;
    }


    public static function buscarDadosAbrangencia($idpedidoalteracao){
        $sql = "select
                    distinct (abran.idAbrangencia),
                    pais.Descricao pais,
                    uf.Descricao as uf,
                    mun.Descricao as mun,
                    paxta.dsJustificativa
                from
                    SAC.dbo.Abrangencia abran
                    INNER JOIN SAC.dbo.Projetos pro on pro.idProjeto = abran.idProjeto
                    INNER JOIN BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto pap on pap.IdPRONAC = pro.IdPRONAC
                    INNER JOIN BDCORPORATIVO.scSAC.tbPedidoAlteracaoXTipoAlteracao paxta on paxta.idPedidoAlteracao = pap.idPedidoAlteracao
                    INNER JOIN BDCORPORATIVO.scSAC.tbTipoAlteracaoProjeto tap on tap.tpAlteracaoProjeto = paxta.tpAlteracaoProjeto
                    INNER JOIN AGENTES.dbo.Uf uf on uf.idUF = abran.idUF
                    INNER JOIN AGENTES.dbo.Municipios mun on mun.idMunicipioIBGE = abran.idMunicipioIBGE
                    INNER JOIN Agentes.dbo.Pais	pais on pais.idPais = abran.idPais
                where
                    pro.IdPRONAC  = $idpedidoalteracao and tap.tpAlteracaoProjeto = 4 and abran.stAbrangencia = 1
                ";
        $db  = Zend_Registry::get('db');
	$db->setFetchMode(Zend_DB::FETCH_OBJ);
	$resultado = $db->fetchAll($sql);

        return $resultado;
    }

    public static function buscarDadosAbrangenciaSolicitada($idpedidoalteracao){
            $sql = "SELECT pais.Descricao pais,
                            uf.Descricao uf,
                            mun.Descricao mun,
                            paxta.dsJustificativa
                    FROM
                        AGENTES.dbo.Pais pais,
                        AGENTES.dbo.UF uf,
                        AGENTES.dbo.Municipios mun,
                        SAC.dbo.tbAbrangencia ta,
                        BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto tpa,
                        BDCORPORATIVO.scSAC.tbPedidoAlteracaoXTipoAlteracao paxta
                    WHERE 
                        tpa.idPronac = $idpedidoalteracao AND
                        uf.idUF = ta.idUF AND
                        mun.idMunicipioIBGE = ta.idMunicipioIBGE and
                        pais.idPais = ta.idPais AND
                        ta.idPedidoAlteracao = tpa.idPedidoAlteracao AND
                        paxta.idPedidoAlteracao = tpa.idPedidoAlteracao
                        --AND paxta.tpAlteracaoProjeto = 4
                        "
        ;

        $db  = Zend_Registry::get('db');
	$db->setFetchMode(Zend_DB::FETCH_OBJ);
	$resultado = $db->fetchAll($sql);

        return $resultado;
    }

    public static function buscarDadosAbrangenciaSolicitadaLocal($idpedidoalteracao, $tpAcao = null){
            $sql = "SELECT tpa.idPedidoAlteracao,
            				pais.Descricao pais,
                            uf.Descricao uf,
                            mun.Descricao mun,
                            paxta.dsJustificativa
                    FROM
                        AGENTES.dbo.Pais pais,
                        AGENTES.dbo.UF uf,
                        AGENTES.dbo.Municipios mun,
                        SAC.dbo.tbAbrangencia ta,
                        BDCORPORATIVO.scSAC.tbPedidoAlteracaoProjeto tpa,
                        BDCORPORATIVO.scSAC.tbPedidoAlteracaoXTipoAlteracao paxta
                    WHERE
                        tpa.idPronac = $idpedidoalteracao AND
                        uf.idUF = ta.idUF AND
                        mun.idMunicipioIBGE = ta.idMunicipioIBGE and
                        pais.idPais = ta.idPais AND
                        ta.idPedidoAlteracao = tpa.idPedidoAlteracao AND
                        paxta.idPedidoAlteracao = tpa.idPedidoAlteracao
                        AND paxta.tpAlteracaoProjeto = 4 
                        "
        ;

		if (!empty($tpAcao)) :
			$sql.= " AND ta.tpAcao = '".$tpAcao."'";
		endif;
	

        $db  = Zend_Registry::get('db');
	$db->setFetchMode(Zend_DB::FETCH_OBJ);
	$resultado = $db->fetchAll($sql);

        return $resultado;
    }

	/**
	 * Método para avaliar o local de realização
	 * @access public
	 * @static
	 * @param $dados array
	 * @return boolean
	 */
	public static function avaliarLocalRealizacao($dados)
	{
		$db = Zend_Registry::get('db');
		$db->setFetchMode(Zend_DB::FETCH_OBJ);

		$cadastrar = $db->insert("BDCORPORATIVO.scSAC.tbAvaliacaoSubItemPedidoAlteracao", $dados);

		if ($cadastrar)
		{
			return true;
		}
		else
		{
			return false;
		}
	} // fecha método avaliarLocalRealizacao()
	
	
	
	/**
	 * Método para verificar se o loca de realização já existe
	 */
	public static function verificarLocalRealizacao($idProjeto, $idMunicipio)
	{
		$sql = "SELECT idMunicipíoIBGE FROM Abrangencia WHERE idProjeto=$idProjeto AND stAbrangencia = 1 AND idMunicipioIBGE=$idMunicipio";
		return $sql;
	}

} // fecha class AvaliacaoSubItemPlanoDistribuicaoDAO