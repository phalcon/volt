<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

/**
 * Deliberate divergence: `{% cache %}` / `{% endcache %}` were removed from
 * phalcon/volt (commit 23585c1), so `cache` and `endcache` are plain
 * identifiers here while cphalcon treats them as keywords.
 *
 * The goldens still record what ext-phalcon does; the expected phalcon/volt
 * outcome for each case lives in `allow-list.php`.
 */

return [
    'cache: statement'              => '{% cache sidebar %} hello {% endcache %}',
    'cache: statement lifetime'     => '{% cache sidebar 500 %} hello {% endcache %}',
    'cache: statement identifier'   => '{% cache sidebar ttl %} hello {% endcache %}',
    'cache: echo identifier'        => '{{ cache }}',
    'cache: echo endcache'          => '{{ endcache }}',
    'cache: property'               => '{{ a.cache }}',
    'cache: function'               => '{{ cache(1) }}',
    // cphalcon tests/unit/Mvc/View/Engine/Volt/Parser/CacheTest.php
    'cache: string key lifetime'    => '{% cache \'sidebar\' 3600 %}{{ content }}{% endcache %}',
    'cache: identifier lifetime'    => '{% cache cacheKey lifetime %}{{ content }}{% endcache %}',
    'cache: string key'             => '{% cache \'sidebar\' %}{{ content }}{% endcache %}',
];
