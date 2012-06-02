<?php

require __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();

$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app['elastica'] = $app->share(function () {
    return new Elastica_Client();
});

$app->get('/', function (Request $request) use ($app) {
    $query = $request->query->get('q');

    if ($query) {
        $index = $app['elastica']->getIndex('git-search');
        $results = $index->search($query);
    } else {
        $results = array();
    }

    $view = 'index.html.twig';
    if ($request->isXmlHttpRequest()) {
        $view = 'results.html.twig';
    }

    return $app['twig']->render($view, array(
        'query' => $query,
        'results' => $results,
    ));
});

$app->run();
