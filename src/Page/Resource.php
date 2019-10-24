<?php declare(strict_types = 1);

namespace Phi\VuePHP\Page;

use Phi\RunAs;
use Phi\VuePHP\Layout;

abstract class Resource
{
    use RunAs;

    const       TYPE_JAVASCRIPT = 'js';
    const       TYPE_SCSS       = 'scss';
    const       TYPE_CSS        = 'css';

    protected   $_filename;

    public function __construct(string $filename)
    {
        $this->_filename    = $filename;
    }

    public function getType(): string
    {   
        return static::TYPE;
    }

    public function getFilename(): string
    {
        return $this->_filename;
    }

    public function build(Layout &$layout): void
    {
        $path = static::PATH;
        $stream = $layout->stream(static::TYPE);
        $source = \fopen("{$layout->getSourcePath()}/{$path}/{$this->_filename}", 'r');

        \stream_copy_to_stream($source, $stream);

        \fclose($source);
    }
}