<?php
// src/app/Endpoints/Handlers/CategoryLangHandler.php
declare(strict_types=1);
namespace App\Endpoints\Handlers;
use App\Endpoints\Definition\EndpointDefinition;
use App\Endpoints\{DefinedEndpoint, EndpointContext, EndpointHandler};
use App\Endpoints\Definition\Param;
use App\Query\InputSanitizer;

/** أساس endpoints الفئة/اللغة: قراءة lang و category (مع الافتراضي RTT) وتعريفاتهما المشتركة */
abstract class CategoryLangHandler implements EndpointHandler, DefinedEndpoint
{
    protected const WORDS            = '/^[A-Za-z0-9- ]+$/';
    protected const DEFAULT_CATEGORY = 'RTT';

    protected function lang(EndpointContext $ctx): ?string
    {
        return InputSanitizer::match($ctx->request->get('lang'), self::WORDS);
    }

    protected function category(EndpointContext $ctx): string
    {
        $raw = $ctx->request->get('category') ?? $ctx->request->get('cat');
        return InputSanitizer::match($raw, self::WORDS) ?? self::DEFAULT_CATEGORY;
    }

    protected static function langParam(): Param
    {
        return new Param(
            name: 'lang',
            column: 't.code',
            placeholder: 'Language code',
            required: true
        );
    }

    protected static function categoryParam(bool $required = false): Param
    {
        return new Param(
            name: 'category',
            column: 'a.category',
            placeholder: 'Category',
            default: self::DEFAULT_CATEGORY,
            required: $required,
            doc: [
                'in'          => 'query',
                'name'        => 'category',
                'description' => 'Category',
                'required'    => false,
                'schema'      => ['default' => self::DEFAULT_CATEGORY, 'type' => 'string'],
            ],
        );
    }

    /** الأعمدة المشتركة بين missing و exists (exists يضيف aq.target) */
    protected const ARTICLE_JOINS = <<<'SQL'
            category_members c
            JOIN qids q                     ON q.title      = c.article_id
            LEFT JOIN all_qids_exists aq    ON aq.qid       = q.qid AND aq.code = ?
            LEFT JOIN assessments ase       ON ase.title    = c.article_id
            LEFT JOIN enwiki_pageviews ep   ON ep.title     = c.article_id
            LEFT JOIN refs_counts rc        ON rc.r_title   = c.article_id
            LEFT JOIN words w               ON w.w_title    = c.article_id
        SQL;
}
