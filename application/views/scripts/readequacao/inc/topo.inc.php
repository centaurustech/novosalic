<?php
/**
 * Topo com dados do Pronac e do Proponente
 * @author emanuel.sampaio <emanuelonline@gmail.com>
 * @since 30/03/2012
 * @version 1.0
 * @package application
 * @subpackage application.views.scripts.readequacao.inc
 * @copyright © 2012 - Ministério da Cultura - Todos os direitos reservados.
 * @link http://salic.cultura.gov.br
 */
?>

<table class="tabela">
    <tr>
        <th width="30%">N&ordm; Pronac</th>
        <th>Nome do Projeto</th>
    </tr>
    <tr class="linha" align="center">
        <td><?php echo isset($this->projeto->pronac)      && !empty($this->projeto->pronac)      ? $this->projeto->pronac      : '<em>Não informado</em>'; ?></td>
        <td><?php echo isset($this->projeto->NomeProjeto) && !empty($this->projeto->NomeProjeto) ? $this->projeto->NomeProjeto : '<em>Não informado</em>'; ?></td>
    </tr>
    <tr>
        <th>CNPJ/CPF</th>
        <th>Nome do Proponente</th>
    </tr>
    <tr class="linha" align="center">
        <td><?php echo isset($this->projeto->CNPJCPF)        && !empty($this->projeto->CNPJCPF)        ? Validacao::mascaraCPFCNPJ($this->projeto->CNPJCPF) : '<em>Não informado</em>'; ?></td>
        <td><?php echo isset($this->projeto->NomeProponente) && !empty($this->projeto->NomeProponente) ? $this->projeto->NomeProponente                     : '<em>Não informado</em>'; ?></td>
    </tr>
</table>