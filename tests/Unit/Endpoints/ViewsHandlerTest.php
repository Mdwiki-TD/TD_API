<?php

declare(strict_types=1);

namespace Tests\Endpoints;

use App\Endpoints\EndpointContext;
use App\Endpoints\Handlers\ViewsHandler;
use App\Http\Request;
use PHPUnit\Framework\TestCase;

final class ViewsHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        $_GET = [];
    }

    protected function tearDown(): void
    {
        $_GET = [];
    }

    public function testLangViewsDefinitionAndHandlerWithLang(): void
    {
        $_GET = ['get' => 'lang_views', 'lang' => 'ar'];

        $handler = new ViewsHandler('lang_views');
        $definition = $handler->definition();

        $request = new Request();
        $ctx = new EndpointContext('lang_views', $definition, $request);

        $this->assertFalse($ctx->hasMissingRequires());

        $spec = $handler->handle($ctx);

        $this->assertStringContainsString('WHERE v.lang = ?', $spec->sql);
        $this->assertSame(['ar'], $spec->params);
    }

    public function testLangViewsRequiresLang(): void
    {
        $_GET = ['get' => 'lang_views'];

        $handler = new ViewsHandler('lang_views');
        $definition = $handler->definition();

        $request = new Request();
        $ctx = new EndpointContext('lang_views', $definition, $request);

        $missingRequires = $ctx->hasMissingRequires();
        $this->assertNotFalse($missingRequires);
        $this->assertSame('lang param required.', $missingRequires->error);
    }

    public function testViewsEndpointFiltering(): void
    {
        $_GET = ['get' => 'views', 'lang' => 'ar'];

        $handler = new ViewsHandler('views', defaultOrder: '1 DESC');
        $definition = $handler->definition();

        $request = new Request();
        $ctx = new EndpointContext('views', $definition, $request);

        $spec = $handler->handle($ctx);

        $this->assertStringContainsString('WHERE v.lang = ?', $spec->sql);
        $this->assertSame(['ar'], $spec->params);
        $this->assertSame('1 DESC', $spec->defaultOrder);
    }
}
