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
        'from'    => 'noreply@sandboxd45c1529129b4dd080b0dd4140e6e54f.mailgun.org',
        'to'      => $params['email'],
        'subject' => 'Self-deleted message',
        'html'    => $this->view->fetch('email/message.twig', [
            'hash' => $hash
        ]),
    ]);

    //return $response->withRedirect($app->router->pathFor('home'));
    return $this->view->render($response, 'home.twig');

})->setName('send');


$app->get('/message/{hash}', function ($request, $response, $args){

    $message = $this->db->prepare("
        SELECT message
        FROM messages
        WHERE hash = :hash;
        DELETE FROM messages
        WHERE hash = :hash;
    ");

    $message->execute([
    'hash' => $args['hash'],
    ]);

    $message = $message->fetch(PDO::FETCH_OBJ);

    return $this->view->render($response, 'message/show.twig', [
        'message' => $message,
    ]);

})->setName('message');

