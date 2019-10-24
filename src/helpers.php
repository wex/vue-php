<?php declare(strict_types = 1);

namespace Phi {

    use Closure;

    trait RunAs
    {
        public function with(callable $callable): self
        {
            $parameters = \array_slice( \func_get_args(), 1 );
            $closure    = Closure::fromCallable($callable);

            $closure->call($this, ...$parameters);

            return $this;
        }
    }

}

namespace Phi\Compiler {

    use ScssPhp\ScssPhp\Compiler;

    trait Scss
    {
        public function compileScss($stream, $includePath): void
        {
            $raw = '';
            rewind($stream);
            while (!feof($stream)) {
                $raw .= fread($stream, 4096);
            }
    
            $compiler = new Compiler;
            $compiler->addImportPath($includePath);
    
            fseek($stream, 0);
            $length = fwrite($stream, $compiler->compile($raw));
            ftruncate($stream, $length);
        }
    }
}

namespace Phi\Minifier {

    use voku\helper\HtmlMin;
    use MatthiasMullie\Minify;

    trait Html
    {
        public function minifyHtml($stream): void
        {
            $raw = '';
            rewind($stream);
            while (!feof($stream)) {
                $raw .= fread($stream, 4096);
            }
    
            $minifier = new HtmlMin;
            $minifier->doRemoveSpacesBetweenTags(true);
            $minifier->doRemoveOmittedQuotes(false);
    
            fseek($stream, 0);
            $length = fwrite($stream, $minifier->minify($raw));
            ftruncate($stream, $length);
        }
    }

    trait Css
    {
        public function minifyCss($stream): void
        {
            $raw = '';
            rewind($stream);
            while (!feof($stream)) {
                $raw .= fread($stream, 4096);
            }

            $minifier = new Minify\Css($raw);

            fseek($stream, 0);
            $length = fwrite($stream, $minifier->minify());
            ftruncate($stream, $length);
        }
    }


    trait Js
    {
        public function minifyJs($stream): void
        {
            $raw = '';
            rewind($stream);
            while (!feof($stream)) {
                $raw .= fread($stream, 4096);
            }

            $minifier = new Minify\Js($raw);

            fseek($stream, 0);
            $length = fwrite($stream, $minifier->minify());
            ftruncate($stream, $length);
        }
    }
}