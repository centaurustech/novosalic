<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EnviaemailController
 *
 * @author tisomar
 */
class EnviaemailController extends GenericControllerNew {

    public static function enviaEmail($body, $from, $to) {
        


        $config = array(
            'auth' => 'login',
            'username' => '',
            'password' => '5',
            'ssl' => 'tls',
            'port' => 25,
        );
        $transport = new Zend_Mail_Transport_Smtp('SEUSMTPSERVER', $config);

        try {
            $email = new Zend_Mail();
            $email->setBodyHtml($body);
            $email->setFrom($from);
            $email->addTo($to);
            $email->send($transport);
        } catch (Zend_Exception $e) {
            xd($e);
        }
    }

}

?>
