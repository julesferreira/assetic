<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2011 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Filter;

/**
 * An abstract filter for dealing with CSS.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
abstract class BaseCssFilter implements FilterInterface
{
    /**
     * Filters all references -- url() and @import -- through a callable.
     *
     * @param string $content  The CSS
     * @param mixed  $callback A PHP callable
     *
     * @return string The filtered CSS
     */
    protected function filterReferences($content, $callback)
    {
        $content = $this->filterUrls($content, $callback);
        $content = $this->filterImports($content, $callback, false);

        return $content;
    }

    /**
     * Filters all CSS url()'s through a callable.
     *
     * @param string $content  The CSS
     * @param mixed  $callback A PHP callable
     *
     * @return string The filtered CSS
     */
    protected function filterUrls($content, $callback)
    {
        return preg_replace_callback('/url\((["\']?)(?<url>.*?)(\\1)\)/', $callback, $content);
    }

    /**
     * Filters all CSS imports through a callable.
     *
     * @param string  $content    The CSS
     * @param mixed   $callback   A PHP callable
     * @param Boolean $includeUrl Whether to include url() in the pattern
     *
     * @return string The filtered CSS
     */
    protected function filterImports($content, $callback, $includeUrl = true)
    {
        $pattern = $includeUrl
            ? '/@import +(?:url)? *\(? *([\'"])?(?<url>.*?)\1 *\)? *;?/'
            : '/@import +([\'"])(?<url>.*?)\1 *;?/';

        return preg_replace_callback($pattern, $callback, $content);
    }
}