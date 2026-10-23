<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoBasicsTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_xml_is_valid_and_lists_the_homepage(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSeeText(url('/'), false);

        $xml = simplexml_load_string($response->getContent());
        $this->assertNotFalse($xml, 'sitemap.xml geçerli bir XML olmalı.');
    }

    public function test_homepage_has_structured_data_and_open_graph_tags(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"SportsActivityLocation"', false);
        $response->assertSee('og:title', false);
        $response->assertSee('rel="canonical"', false);
    }
}
