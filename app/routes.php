<?php

$app->get('/', function ($request, $response, $args){
    $this->view->render($response, 'home.twig');
})->setName('home');


$app->post('/post', function ($request, $response, $args) use ($app){

    $params = $request->getParams();
    $hash = md5(uniqid(true));

    $message = $this->db->prepare("
        INSERT INTO messages (hash, message)
        VALUES (:hash, :message)
        ");

    $message->execute([
        'hash' => $hash,
        'message' => $params['message'],
    ]);

    // Send Email
    $this->mail->sendMessage($this->config->get('services.mailgun.domain'),[
        'from'    => 'noreply@shokr.me',
        'to'      => $params['email'],
        'subject' => 'Self-deleted message',
        'html'    => $this->view->fetch('email/message.twig', [
            'hash' => $hash
        ]),
    ]);

    //return $response->withRedirect($app->router->pathFor('home'));
    return $this->view->render($response, 'home.twig');

})->setName('send');

