<?php
//https://simplehtmldom.sourceforge.io/docs/1.9/index.html

namespace App\Helpers;

use Drnxloc\LaravelHtmlDom\HtmlDomParser;
use App\Helpers\StringHelper;
use App\Helpers\HtmlHelper;
use App\Models\Page;
use App\Models\PageTitle;

class PageHelper
{
    public static function process($content)
    {
        $content = self::toEditor($content);

        $data = [
            'editor' => $content,
            'preview' => self::toPreview($content),
            'text' => self::toText($content),
            'markdown' => self::toMarkdown($content),
            'pdf' => self::toPdf($content),
            'components' => [
                'titles' => self::getTitles($content),
            ],
        ];

        return $data;
    }
    public static function refresh($slug)
    {
        $page = Page::where('slug', $slug)->first();
        if ($page) {
            $content_data = PageHelper::process($page['content_editor']);

            $page['content_editor'] = $content_data['editor'];
            $page['content_preview'] = $content_data['preview'];
            $page['content_text'] = $content_data['text'];
            $page['content_markdown'] = $content_data['markdown'];
            $page['content_pdf'] = $content_data['pdf'];

            $page->save();

            $id = $page->id;
            $slug = $page->slug;

            PageTitle::where('page_id', $id)->delete();
            foreach ($content_data["components"]["titles"] as $title) {
                $title["title"] = html_entity_decode($title["title"]);

                $data = [
                    'page_id' => $id,
                    'slug' => $slug,
                    'header' => $title["title"],
                    'header_slug' => StringHelper::toSlug($title["title"]),
                    'level' => $title["level"],
                    'sequence' => $title["sequence"],
                    'content' => $title["content"],
                ];
                PageTitle::create($data);
            }
        }
    }
    public static function toEditor($content)
    {
        //Remove tags maliciosas, scripts ou css
        $content = HtmlHelper::removeTag($content, 'style');
        $content = HtmlHelper::removeTag($content, 'script');

        // Remove tags <p> dentro de listas <li>
        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find("li > p") as $el) {
            $el->parent()->innertext = HtmlHelper::removeTag($el->parent()->innertext, 'p');
        }
        $content = $dom->save();
        $dom->clear();

        // Ajusta ordem dos títulos e limpa formatação dentro deles
        $dom = HtmlDomParser::str_get_html($content);
        $n = 0;
        foreach ($dom->find("h1,h2,h3,h4,h5,h6") as $el) {
            $level = +preg_replace('/\D/', '', $el->tag);
            if ($level - $n > 1) {
                $level = ($n + 1);
                $el->tag = "h" . $level;
            }
            $el->innertext = strip_tags($el->innertext);
            $n = $level;
        }
        $content = $dom->save();
        $dom->clear();

        return $content;
    }
    public static function toPreview($content)
    {
        /* Ajusta Títulos *********************************************************************/
        $dom = HtmlDomParser::str_get_html($content);
        $i = 0;
        $items = [];
        foreach ($dom->find("h1,h2,h3,h4,h5,h6") as $el) {
            $i++;
            $name = $i . "_" . StringHelper::toSlug(html_entity_decode($el->innertext));
            $items[] = [
                "tag" => $el->tag,
                "level" => +preg_replace('/\D/', '', $el->tag),
                "title" => $el->innertext,
                "name" => $name,
            ];
            $el->setAttribute("name", $name);
        }
        $content = $dom->save();
        $dom->clear();

        $html = "";
        $level = 0;

        $html .= "<nav class='anchors sidebar-item border mb-3'>";
        $html .= "<div data-bs-target='#anchors-nav' data-bs-toggle='collapse' class='header p-2 ps-3 cursor-pointer'>";
        $html .= "<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-menu'><line x1='3' y1='12' x2='21' y2='12'></line><line x1='3' y1='6' x2='21' y2='6'></line><line x1='3' y1='18' x2='21' y2='18'></line></svg>";
        $html .= "<span class='ps-2 align-middle h5'>Tópicos</span>";
        $html .= "</div>";
        $html .= "<div id='anchors-nav' class='collapse p-3'>";
        foreach ($items as $item) {
            if ($item["level"] > $level) {
                $html .= str_repeat("<ol data-level=" . $item["level"] . ">", $item["level"] - $level);
            }
            if ($item["level"] < $level) {
                $html .= str_repeat("</ol>", $level - $item["level"]);
            }
            $html .= "<li data-level=" . $item["level"] . "><a href='#" . $item["name"] . "'>" . $item["title"] . "</a></li>";
            $level = $item["level"];
        }
        $html .= str_repeat("</ol>", $level);
        $html .= "</div>";
        $html .= "</nav>";

        $content = $html . $content;

        /******************************************************************************/

        return $content;
    }
    public static function toText($content)
    {
        function nodeToText($el, $level = 0, $param = [])
        {
            $p = [];
            $text = "";
            foreach ($el as $indx => $node) {
                $tag = $node->tag;
                $p['parent_tag'] = $tag;
                switch ($tag) {
                    case 'ul':
                        break;
                    case 'ol':
                        $p['start'] =  $node->attr['start'] ?? 1;
                        $p['type'] =  HtmlHelper::getStyle($node->attr['style'] ?? '', 'list-style-type') ?? 'decimal';
                        break;
                    case 'li':
                        if ($param['parent_tag'] == 'ul') {
                            $text .= PHP_EOL . str_repeat(' ', $level) . '- ' . $node->innertext;
                        } else {
                            $start = $param['start'] ?? 1;
                            $number = ($start + $indx);
                            if ($param['type'] == 'lower-alpha') {
                                $number = StringHelper::numberToLetter($number);
                            }
                            $text .= PHP_EOL . str_repeat(' ', $level - 1) . $number . '. ' . $node->innertext;
                        }
                        break;
                    case 'p':
                        $text .= PHP_EOL . $node->innertext;
                        break;
                    default:
                        break;
                }
                $text .= nodeToText($node->children, $level + 1, $p);
            }
            return strip_tags($text);
        }

        $dom = HtmlDomParser::str_get_html($content);
        $content = nodeToText($dom->root->children);
        $dom->clear();

        $content = str_replace('&nbsp;', ' ', $content);

        return $content;
    }
    public static function toMarkdown($content)
    {
        return $content;
    }
    public static function toPdf($content)
    {
        return $content;
    }

    public static function getTitles($content)
    {
        $ret = [];
        $sequence = 0;
        for ($i = 1; $i <= 4; $i++) {
            $hs = explode("<h$i", $content);
            foreach ($hs as $h) {
                if (strpos($h, "</h$i>") !== false) {
                    $sequence++;
                    $e = explode("</h$i>", $h);
                    $ret[] = [
                        "title" => strip_tags("</h$i" . $e[0]),
                        "content" => $e[1],
                        "level" => $i,
                        "sequence" => $sequence,
                    ];
                }
            }
        }
        return $ret;
    }
}
