<?php declare(strict_types = 1);

namespace Phi\VuePHP;

use Phi\Minifier;
use Phi\Compiler;
use Phi\RunAs;

class Layout
{
    use RunAs, Minifier\Html, Minifier\Css, Minifier\Js, Compiler\Scss;

    /**
     * Storage for pages
     * Stored with object hash as key
     * @var Page[]
     */
    protected   $_pages         = [];

    protected   $_outputPath;

    protected   $_sourcePath;

    protected   $_streams       = [];

    public function __construct(string $targetPath, string $sourcePath = 'public')
    {
        $this->_outputPath  = rtrim($targetPath, DIRECTORY_SEPARATOR);
        $this->_sourcePath  = rtrim($sourcePath, DIRECTORY_SEPARATOR);
    }

    public function add(Page $page): self
    {
        $this->_pages[ \spl_object_hash($page) ] = $page;

        return $this;
    }

    public function getTargetPath(): string
    {
        return $this->_outputPath;
    }

    public function getSourcePath(): string
    {
        return $this->_sourcePath;
    }

    public function build(): bool
    {
        $stream = $this->stream('html');

        \fputs($stream, $this->header());

        foreach ( $this->_pages as $page ) {
            $page->build( $this );
        }

        \fputs($stream, $this->footer());

        $this->minimize();

        return true;
    }

    protected function streamFile(string $for): string
    {
        return "{$this->getTargetPath()}/index.{$for}";
    }

    public function stream(string $for, bool $close = false)
    {
        if (!\is_dir( $this->getTargetPath() )) {
            \mkdir( $this->getTargetPath(), 0777, true );
        }

        if (!\array_key_exists($for, $this->_streams) && !$close) {
            $this->_streams[ $for ] = \fopen( $this->streamFile($for), 'w+' );
        } else if (\array_key_exists($for, $this->_streams) && $close) {
            \fclose( $this->_streams[ $for ] );
            unset( $this->_streams[ $for ] );
        }

        return $close ? null : $this->_streams[ $for ];
    }

    protected function compile(): void
    {
        $source = $this->stream('scss');

        $path   = Page\SCSS::PATH;
        $this->compileScss($source, "{$this->getSourcePath()}/{$path}");

        rewind($source);
        $target = $this->stream('css');
        \stream_copy_to_stream($source, $target);

        $this->stream('scss', true);
        unlink( $this->streamFile('scss') );
    }

    protected function minimize(): void
    {
        $this->compile();

        foreach ($this->_streams as $type => $stream) {
            switch ($type) {
                case 'html':
                    $this->minifyHtml($stream);
                    break;

                case 'css':
                    $this->minifyCss($stream);
                    break;

                case 'js':
                    $this->minifyJs($stream);
                    break;
            }
        }
    }
   
    protected function header(): string
    {
        return <<<EOF
<!doctype html>        
<html>        
    <head>
        <link rel="stylesheet" href="index.css">
    </head>
    <body>        
        <div id="app">
            <main v-if="page === null"><i class="loader icon"></i></main>
EOF;
    }

    protected function footer(): string
    {
        return <<<EOF
            <main v-else id="page-error">
                <p>
                    Page <strong>{{ page }}</strong> is not built.
                </p>
            </main>
        </div>
        <script src="index.js"></script>
    </body>
</html>
EOF;
    }
}