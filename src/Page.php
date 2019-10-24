<?php declare(strict_types = 1);

namespace Phi\VuePHP;

use Phi\RunAs;
use Phi\VuePHP\Page\Resource;

class Page
{
    use RunAs;

    protected   $_resources = [];
    protected   $_filename;
    protected   $_name;

    public function __construct(string $file, string $name)
    {
        $this->_filename    = $file;
        $this->_name        = $name;
    }

    public function add(Resource $resource): self
    {
        $this->_resources[ \spl_object_hash($resource) ] = $resource;

        return $this;
    }

    public function getFilename(): string
    {
        return $this->_filename;
    }

    public function getName(): string
    {
        return $this->_name;
    }

    public function build(Layout &$layout): void
    {
        foreach ( $this->_resources as $resource ) {
            $resource->build( $layout );
        }

        $stream = $layout->stream('html');
        $source = \fopen("{$layout->getSourcePath()}/{$this->_filename}", 'r');

        \fputs($stream, $this->header());
        \stream_copy_to_stream($source, $stream);
        \fputs($stream, $this->footer());

        \fclose($source);
    }

    protected function header(): string
    {
        return <<<EOF
<main v-else-if="page === '{$this->getName()}'" id="page-{$this->getName()}">
EOF;
    }
    
    protected function footer(): string
    {
        return <<<EOF
</main>
EOF;
    }   
}