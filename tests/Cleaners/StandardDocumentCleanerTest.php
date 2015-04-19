<?php

namespace Goose\Tests\Cleaners;

use Goose\Article;
use Goose\Configuration;
use Goose\DOM\DOMElement;
use Goose\DOM\DOMDocument;
use Goose\Cleaners\StandardDocumentCleaner;

class StandardDocumentCleanerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider removeCommentsProvider
     */
    public function testRemoveComments($expected, $article, $message)
    {
        $obj = new StandardDocumentCleaner($this->config());

        $obj->clean($article);

        $this->assertEquals(
            $this->html($expected),
            $this->html($article->getDoc()),
            $message
        );
    }

    public function removeCommentsProvider() {
        return [
            [
                $this->document('<html></html>'),
                $this->generate('<html><!-- Comment --></html>'),
                'Single Line Comment'
            ],
            [
                $this->document('<html></html>'),
                $this->generate("<html><!-- \n Comment \n --></html>"),
                'Multi Line Comment'
            ],
        ];
    }

    /**
     * @dataProvider cleanTextTagsProvider
     */
    public function testCleanTextTags($expected, $article, $message)
    {
        $obj = new StandardDocumentCleaner($this->config());

        $obj->clean($article);

        $this->assertEquals(
            $this->html($expected),
            $this->html($article->getDoc()),
            $message
        );
    }

    public function cleanTextTagsProvider() {
        return [
            [
                $this->document('<html><p>a b c d e f g </p></html>'),
                $this->generate('<html><p><em>a</em><strong>b</strong><b>c</b><i>d</i><strike>e</strike><del>f</del><ins>g</ins></p></html>'),
                'Clean text tags #1'
            ],
            [
                $this->document('<html><p>a <em>b<img src="http://example.org/image.png" /></em></p></html>'),
                $this->generate('<html><p><strong>a</strong><em>b<img src="http://example.org/image.png" /></em></p></html>'),
                'Clean text tags #2'
            ],
        ];
    }

    /**
     * @dataProvider cleanUpSpanTagsInParagraphsProvider
     */
    public function testCleanUpSpanTagsInParagraphs($expected, $article, $message)
    {
        $obj = new StandardDocumentCleaner($this->config());

        $obj->clean($article);

        $this->assertEquals(
            $this->html($expected),
            $this->html($article->getDoc()),
            $message
        );
    }

    public function cleanUpSpanTagsInParagraphsProvider() {
        return [
            [
                $this->document('<html><p>Example</p></html>'),
                $this->generate('<html><p><span>Example</span></p></html>'),
                'Replace single span tag'
            ],
            [
                $this->document('<html><p>Example Tags</p></html>'),
                $this->generate('<html><p><span>Example</span> <span>Tags</span></p></html>'),
                'Replace multiple span tags'
            ],
        ];
    }

    /**
     * @dataProvider removeScriptsAndStylesProvider
     */
    public function testRemoveScriptsAndStyles($expected, $article, $message)
    {
        $obj = new StandardDocumentCleaner($this->config());

        $obj->clean($article);

        $this->assertEquals(
            $this->html($expected),
            $this->html($article->getDoc()),
            $message
        );
    }

    public function removeScriptsAndStylesProvider() {
        return [
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><script>alert("test");</script></body></html>'),
                'Script #1'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><script type="text/javascript">alert("test");</script></body></html>'),
                'Script #2'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><style>alert("test");</style></body></html>'),
                'Style #1'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><style type="text/css">alert("test");</style></body></html>'),
                'Style #2'
            ],
        ];
    }

    /**
     * @dataProvider removeDropCapsProvider
     */
    public function testRemoveDropCaps($expected, $article, $message)
    {
        $obj = new StandardDocumentCleaner($this->config());

        $obj->clean($article);

        $this->assertEquals(
            $this->html($expected),
            $this->html($article->getDoc()),
            $message
        );
    }

    public function removeDropCapsProvider() {
        return [
            [
                $this->document('<html><body>Example</body></html>'),
                $this->generate('<html><body><span class="drop_cap">Example</span></body></html>'),
                'Drop Caps #1'
            ],
            [
                $this->document('<html><body>Example</body></html>'),
                $this->generate('<html><body><span class="dropcap">Example</span></body></html>'),
                'Drop Caps #2'
            ],
            [
                $this->document('<html><body>ExampleExample</body></html>'),
                $this->generate('<html><body><span class="dropcap">Example</span><span class="drop_cap">Example</span></body></html>'),
                'Drop Caps #3'
            ],
        ];
    }

    /**
     * @dataProvider removeUselessTagsProvider
     */
    public function testRemoveUselessTags($expected, $article, $message)
    {
        $obj = new StandardDocumentCleaner($this->config());

        $obj->clean($article);

        $this->assertEquals(
            $this->html($expected),
            $this->html($article->getDoc()),
            $message
        );
    }

    public function removeUselessTagsProvider() {
        return [
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><header></header></body></html>'),
                'Useless tags'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><header><meta /></header></body></html>'),
                'Useless tags nested'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><header></header><footer></footer></body></html>'),
                'Useless tags multiple'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><header><footer><form><button><aside><input /><meta /></aside></button></form></footer></header></body></html>'),
                'Useless tags all'
            ],
        ];
    }

    /**
     * @dataProvider cleanBadTagsProvider
     */
    public function testCleanBadTags($expected, $article, $message)
    {
        $obj = new StandardDocumentCleaner($this->config());

        $obj->clean($article);

        $this->assertEquals(
            $this->html($expected),
            $this->html($article->getDoc()),
            $message
        );
    }

    public function cleanBadTagsProvider() {
        return [
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><a id="conditionalAd-test"></a></html>'),
                'Clean bad tags - id/class/name attribute starts with... #1'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><a class="publication"></a></html>'),
                'Clean bad tags - id/class/name attribute starts with... #2'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><input name="ad-test" /></html>'),
                'Clean bad tags - id/class/name attribute starts with... #3'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><a id="wp-caption-text></a></html>'),
                'Clean bad tags - id/class/name attribute contains... #1'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><a class="example-author></a></html>'),
                'Clean bad tags - id/class/name attribute contains... #2'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><input name="test-subscribe-example" /></html>'),
                'Clean bad tags - id/class/name attribute contains... #3'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><input name="subscribe-example" /></html>'),
                'Clean bad tags - id/class/name attribute contains... #4'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><a id="meta"></a></html>'),
                'Clean bad tags - id/class/name attribute ends with... #1'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><a class="test-meta"></a></html>'),
                'Clean bad tags - id/class/name attribute ends with... #2'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><input name="test-meta" /></html>'),
                'Clean bad tags - id/class/name attribute ends with... #3'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><input name="inset" /></html>'),
                'Clean bad tags - id/class/name attribute equals... #1'
            ],
        ];
    }

    /**
     * @dataProvider removeNodesViaFilterProvider
     */
    public function testRemoveNodesViaFilter($expected, $article, $message)
    {
        $obj = new StandardDocumentCleaner($this->config());

        $obj->clean($article);

        $this->assertEquals(
            $this->html($expected),
            $this->html($article->getDoc()),
            $message
        );
    }

    public function removeNodesViaFilterProvider() {
        return [
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><span class="caption">Example</span></body></html>'),
                'Remove nodes via filter #1'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><span class="test google filter">Example</span></body></html>'),
                'Remove nodes via filter #2'
            ],
            [
                $this->document('<html><body><p class="entry-more">Example</p></body></html>'),
                $this->generate('<html><body><p class="entry-more">Example</p></body></html>'),
                'Remove nodes via filter #3'
            ],
            [
                $this->document('<html><body></body></html>'),
                $this->generate('<html><body><span class="something-more">Example</span></body></html>'),
                'Remove nodes via filter #4'
            ],
        ];
    }

    /**
     * @dataProvider convertWantedTagsToParagraphsProvider
     */
    public function testConvertWantedTagsToParagraphs($expected, $article, $message)
    {
        $obj = new StandardDocumentCleaner($this->config());

        $obj->clean($article);

        $this->assertEquals(
            $this->html($expected),
            $this->html($article->getDoc()),
            $message
        );
    }

    public function convertWantedTagsToParagraphsProvider() {
        return [
            [
                $this->document('<html><body><div><img/><p> text </p><p>No children!</p><p> text</p></div></body></html>'),
                $this->generate('<html><body><div><img/> text <p>No children!</p> text</div></body></html>'),
                'Convert wanted tags to paragraphs #1'
            ],
            [
                $this->document('<html><body><p attr="value">No children!</p></body></html>'),
                $this->generate('<html><body><article attr="value">No children!</article></body></html>'),
                'Convert wanted tags to paragraphs #2'
            ],
            [
                $this->document('<html><body><div><img/><p> <a>Example<img/></a>  Text Node! </p><pre>Test!</pre></div></body></html>'),
                $this->generate('<html><body><div><img/><a>Example<img/></a> Text Node! <pre>Test!</pre></div></body></html>'),
                'Convert wanted tags to paragraphs #3'
            ],
            [
                $this->document('<html><body><div><img/><p>Text Node! </p><pre>Test!</pre><a>Example<img/></a></div></body></html>'),
                $this->generate('<html><body><div><img/>Text Node! <pre>Test!</pre> <a>Example<img/></a></div></body></html>'),
                'Convert wanted tags to paragraphs #4'
            ],
            [
                $this->document('<html><body><div><img/><p> <a>Example<img/></a>  Text Node! </p><pre>Test!</pre><a>Example<img/></a></div></body></html>'),
                $this->generate('<html><body><div><img/><a>Example<img/></a> Text Node! <pre>Test!</pre> <a>Example<img/></a></div></body></html>'),
                'Convert wanted tags to paragraphs #5'
            ],
            [
                $this->document('<html><body><p><img/><a>Example<img/></a> Text Node!</p> <pre>Test!</pre></body></html>'),
                $this->generate('<html><body><p><img/><a>Example<img/></a> Text Node!</p> <pre>Test!</pre></body></html>'),
                'Convert wanted tags to paragraphs #6'
            ],
            [
                $this->document('<html><body><div><img/><p> <a>Example<img/></a>  Text Node! </p><pre>Test!</pre><p>test <a>Example<img/></a> </p></div></body></html>'),
                $this->generate('<html><body><div><img/><a>Example<img/></a> Text Node! <pre>Test!</pre>test<a>Example<img/></a></div></body></html>'),
                'Convert wanted tags to paragraphs #7'
            ],
        ];
    }

    /*
    / **
     * @dataProvider convertDivsToParagraphsProvider
     * /
    public function testConvertDivsToParagraphs($expected, $article, $message)
    {
        $obj = new StandardDocumentCleaner($this->config());

        $this->assertEquals(
            $this->html($expected),
            $this->html($obj->convertDivsToParagraphs($article->getDoc(), 'span')),
            $message
        );
    }

    public function convertDivsToParagraphsProvider() {
        return [
            [
                $this->document('<html><body><p>Example</p></body></html>'),
                $this->generate('<html><body><span>Example</span></body></html>'),
                'Convert divs to paragraphs #1'
            ],
            [
                $this->document('<html><body><span><p>test </p><pre>Example</pre><p> test</p></span></body></html>'),
                $this->generate('<html><body><span>test <pre>Example</pre> test</span></body></html>'),
                'Convert divs to paragraphs #2'
            ],
        ];
    }
    */

    public function html($doc) {
        if ($doc instanceof DOMDocument) {
            $el = $doc->documentElement;
        } else if ($doc instanceof DOMElement) {
            $el = $doc;
            $doc = $doc->ownerDocument;
        } else if ($doc instanceof Article) {
            $doc = $doc->getDoc();
            $el = $doc->documentElement;
        }

        return $doc->saveXML($el);
    }

    private function document($html) {
        $doc = new DOMDocument(1.0);
        $doc->registerNodeClass('DOMElement', 'Goose\\DOM\\DOMElement');

        // Silence 'Tag xyz invalid in Entity' for HTML5 tags.
        libxml_use_internal_errors(true);
        $doc->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_use_internal_errors(false);

        return $doc;
    }

    private function generate($html) {
        $article = new Article();
        $article->setDoc($this->document($html));

        return $article;
    }

    private function config() {
        $config = new Configuration();

        return $config;
    }
}