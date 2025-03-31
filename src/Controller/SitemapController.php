<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\RouterInterface;

#[AsController]
class SitemapController
{
    private RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function index(): Response
    {
        // URLs statiques
        $urls = [
            ['loc' => $this->router->generate('app_home'), 'changefreq' => 'daily', 'priority' => '1.0'],
        ];

        // Générer le sitemap en XML
        $xml = new \SimpleXMLElement('<urlset/>');
        $xml->addAttribute('xmlns', 'https://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $url) {
            $urlElement = $xml->addChild('url');
            $urlElement->addChild('loc', 'https://www.beauty-bym.fr' . $url['loc']);
            $urlElement->addChild('changefreq', $url['changefreq']);
            $urlElement->addChild('priority', $url['priority']);
        }

        return new Response($xml->asXML(), 200, ['Content-Type' => 'application/xml']);
    }
}
