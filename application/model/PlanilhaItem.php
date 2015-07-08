<?php
/**
 *
 */
class PlanilhaItem   extends GenericModel
{
    protected $_banco = "SAC";
    protected $_schema = "dbo";
    protected $_name = "tbPlanilhaItens";

    /**
     * @return Zend_Db_Table_Rowset_Abstract
     * @todo Ver com Romulo sobre a integridade da Planilha Item / Produto
     */
    public function pesquisar($item)
    {
        $select = "SELECT
                    pa.*,
                    (
                        SELECT sum(b1.vlComprovacao) AS vlPagamento
                        FROM BDCORPORATIVO.scSAC.tbComprovantePagamentoxPlanilhaAprovacao AS a1
                        INNER JOIN BDCORPORATIVO.scSAC.tbComprovantePagamento AS b1 ON a1.idComprovantePagamento = b1.idComprovantePagamento
                        INNER JOIN SAC.dbo.tbPlanilhaAprovacao AS c1 ON a1.idPlanilhaAprovacao = c1.idPlanilhaAprovacao
                        WHERE c1.stAtivo = 'S' AND c1.idPlanilhaAprovacao = pa.idPlanilhaAprovacao AND c1.idPronac = pa.idPronac
                        GROUP BY a1.idPlanilhaAprovacao
                    ) as valorComprovado,
                    ROUND((pa.QtItem * pa.nrOcorrencia * pa.VlUnitario),2) as valorAprovado
                FROM SAC.dbo.tbPlanilhaAprovacao AS pa
                    INNER JOIN SAC.dbo.tbPlanilhaItens as pit ON pit.idPlanilhaItens = pa.idPlanilhaItem
                    INNER JOIN SAC.dbo.tbPlanilhaEtapa AS pEtapa ON pEtapa.idPlanilhaEtapa = pa.idEtapa
                    LEFT JOIN SAC.dbo.Produto AS prod ON prod.Codigo = pa.idProduto
                WHERE 
                    pa.idPlanilhaAprovacao = ?
                    AND pa.stAtivo = 'S'
                ORDER BY prod.Descricao ASC";
        $statement = $this->getAdapter()->query($select, array($item));
        return $statement->fetchObject();
    }
}
