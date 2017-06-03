<?php

namespace Local\SimpleStats\Controller;


use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use UAParser;

class SimpleStats implements ControllerProviderInterface
{
    /** @var Application $app */
    protected $app;
    /** @var array $config */
    protected $config;

    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
    }


    public function connect(Application $app)
    {
        /** @var $ctr ControllerCollection */
        $ctr = $app['controllers_factory'];

        $ctr->get('/', [$this, 'dashboard']);
        $ctr->get('/parse', [$this, 'parse']);


        return $ctr;
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return null|RedirectResponse
     */
    public function before(Request $request, Application $app)
    {
        if (!$app['users']->isAllowed('dashboard')) {
            /** @var UrlGeneratorInterface $generator */
            $generator = $app['url_generator'];
            return new RedirectResponse($generator->generate('dashboard'), Response::HTTP_SEE_OTHER);
        }
        return null;
    }

    public function dashboard()
    {
        $context = [];
        $html = $this->app['twig']->render('simplestats.twig', $context);

        return new \Twig_Markup($html, 'UTF-8');
    }

    public function parse()
    {
        $stmt = $this->app['db']->query("SELECT * FROM bolt_simplestats_log ORDER BY RAND() LIMIT 5");

        $parser = UAParser\Parser::create();


        while($row = $stmt->fetch()) {
            dump($row);
            $agentData = $parser->parse($row['browseragent']);
            dump($agentData);
        }

        return 'ok';
    }

}