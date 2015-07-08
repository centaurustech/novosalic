<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of inserirUsuarioController
 *
 * @author augusto
 */
class InserirusuarioController extends GenericControllerNew {

    public function indexAction() {
        $cpf = $this->_request->getParam("cpf");
        $agente = new Agentes();
        $usuario = new Usuario();

        $buscarDadosAgente = $agente->buscar(array('CNPJCPF = ?' => $cpf))->current()->toArray();

        $buscarUsuario = $usuario->buscar(array(), array('usu_codigo desc'), array('1'))->current()->toArray();
        $usuarioCadastrar =  $buscarUsuario['usu_codigo']+1;
//        xd($usuarioCadastrar);
        $usurioLogin = str_replace(' ', '_', $buscarDadosAgente['Descricao']);
        $dados = array(
            'usu_codigo'=>$usuarioCadastrar,
            'usu_identificacao' => $buscarDadosAgente['CNPJCPF'],
            'usu_nome' => substr($buscarDadosAgente['Descricao'], 0, 20),
            'usu_pessoa' => 1,
            'usu_orgao' => 0,
            'usu_sala' => 0,
            'usu_ramal' => 0,
            'usu_nivel' => 9,
            'usu_exibicao' => 'S',
            'usu_SQL_login' => substr($usurioLogin, 0, 20),
            'usu_SQL_senha' => 'B',
            'usu_duracao_senha' => 30,
            'usu_data_validade' => '2020-12-31 23:59:59',
            'usu_limite_utilizacao' => '2020-12-31 23:59:59',
            'usu_senha' => 'trocar',
            'usu_validacao' => 0,
            'usu_status' => 1,
            'usu_seguranca' => 0,
            'usu_data_atualizacao' => date('Y-m-d H:i:s'),
            'usu_conta_nt' => 0,
            'usu_dica_intranet' => 10009,
            'usu_localizacao' => 0,
            'usu_andar' => 0,
            'usu_telefone' => 0,
        );
//        xd($dados);
        $senha = substr($cpf, 0,6);
        $inserir = $usuario->inserirUsuarios($dados);
        $alterarSenha = $usuario->alterarSenha($cpf, $senha);
       die('OK');
    }

}

?>
