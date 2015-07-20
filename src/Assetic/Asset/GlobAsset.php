<?php

/*
 * This file is part of the Assetic package, an OpenSky project.
 *
 * (c) 2010-2014 OpenSky Project Inc
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Assetic\Asset;

use Assetic\Filter\FilterInterface;
use Assetic\Util\VarUtils;

/**
 * A collection of assets loaded by glob.
 *
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 */
class GlobAsset extends AssetCollection
{
    private $globs;
    private $initialized;
    private $recursive;

    /**
     * Constructor.
     *
     * @param string|array $globs   A single glob path or array of paths
     * @param array        $filters An array of filters
     * @param string       $root    The root directory
     * @param array        $vars
     * @param bool         $recursive Should the glob search recursively
     */
    public function __construct($globs, $filters = array(), $root = null, array $vars = array(), $recursive = false)
    {
        $this->globs = (array) $globs;
        $this->initialized = false;
        $this->recursive = $recursive;

        parent::__construct(array(), $filters, $root, $vars);
    }

    public function all()
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return parent::all();
    }

    public function load(FilterInterface $additionalFilter = null)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        parent::load($additionalFilter);
    }

    public function dump(FilterInterface $additionalFilter = null)
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return parent::dump($additionalFilter);
    }

    public function getLastModified()
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return parent::getLastModified();
    }

    public function getIterator()
    {
        if (!$this->initialized) {
            $this->initialize();
        }

        return parent::getIterator();
    }

    public function setValues(array $values)
    {
        parent::setValues($values);
        $this->initialized = false;
    }

    /**
     * Initializes the collection based on the glob(s) passed in.
     */
    private function initialize()
    {
        foreach ($this->globs as $glob) {
            $glob = VarUtils::resolve($glob, $this->getVars(), $this->getValues());

            // handle recursive globs
            $paths = false;
            if ($this->recursive) {
                $paths = $this->rglob($glob, GLOB_NOSORT);
            } else {
                $paths = glob($glob, GLOB_NOSORT);
            }

            if (false !== $paths) {
                foreach ($paths as $path) {
                    if (is_file($path)) {
                        $asset = new FileAsset($path, array(), $this->getSourceRoot(), null, $this->getVars());
                        $asset->setValues($this->getValues());
                        $this->add($asset);
                    }
                }
            }
        }

        $this->initialized = true;
    }

    /**
     * Recursive glob
     *
     * @param string $pattern the pattern
     * @param int    $flags   optional flags
     *
     * @return array matches
     */
    private function rglob($pattern, $flags = 0) {
        $files = glob($pattern, $flags);
        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->rglob($dir . '/' . basename($pattern), $flags));
        }
        return $files;
    }
}
