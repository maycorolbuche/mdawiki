<?php
//https://simplehtmldom.sourceforge.io/docs/1.9/index.html

namespace App\Helpers;

use Drnxloc\LaravelHtmlDom\HtmlDomParser;
use App\Helpers\StringHelper;
use App\Helpers\HtmlHelper;
use App\Helpers\ComponentHelper;
use App\Models\Page;
use App\Models\PageTitle;
use App\Models\PageImage;
use App\Models\PageLink;
use App\Models\PageCategory;
use App\Models\PageTemplate;
use App\Models\Image;

class PageHelper
{
    public static function process($content)
    {
        $content_editor = self::toEditor($content);
        $content_preview = self::toPreview($content_editor);
        $content_preview_no_topics = self::toPreview($content_editor, false);
        $content_text = self::toText($content_preview_no_topics);
        $content_markdown = self::toMarkdown($content_preview_no_topics);
        $content_pdf = self::toPdf($content_preview_no_topics);

        $data = [
            'editor' => $content_editor,
            'preview' => $content_preview,
            'text' => $content_text,
            'markdown' => $content_markdown,
            'pdf' => $content_pdf,
            'components' => [
                'titles' => self::getTitles($content_preview_no_topics),
                'images' => self::getImages($content_preview_no_topics),
                'links' => self::getLinks($content_preview_no_topics),
                'templates' => self::getTemplates($content_editor),
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

            PageCategory::where('page_id', $id)->delete();
            if ($page["categories"]) {
                foreach ($page["categories"] as $key => $category) {
                    $category_slug = StringHelper::toSlug($category);
                    $category_name = StringHelper::toUrl($category);
                    $category_title = StringHelper::toTitle($category);
                    $page_exists = false;

                    $category_data = Page::where('slug', $category_slug)->first();
                    if ($category_data) {
                        $category_slug = $category_data["slug"];
                        $category_name = $category_data["name"];
                        $category_title = $category_data["title"];
                        $page_exists = true;
                    }

                    PageCategory::create([
                        'page_id' => $id,
                        'slug' => $slug,
                        'category_slug' => $category_slug,
                        'category_name' => $category_name,
                        'category_title' => $category_title,
                        'page_exists' => $page_exists,
                        'sequence' => $key + 1,
                    ]);
                }
            }

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

            PageImage::where('page_id', $id)->delete();
            foreach ($content_data["components"]["images"] as $image) {
                $data = [
                    'page_id' => $id,
                    'slug' => $slug,
                    'image_slug' => StringHelper::toSlug($image["slug"]),
                    'image_path' => $image["path"],
                    'image_exists' => $image["exists"],
                    'is_error' => $image["is_error"],
                    'sequence' => $image["sequence"],
                ];
                PageImage::create($data);
            }

            PageLink::where('page_id', $id)->delete();
            foreach ($content_data["components"]["links"] as $link) {
                $data = [
                    'page_id' => $id,
                    'slug' => $slug,
                    'link_slug' => ($link["slug"] <> "" ? StringHelper::toSlug($link["slug"]) : null),
                    'link_text' => $link["text"],
                    'href' => $link["href"],
                    'page_exists' => ($link["external"] ? null : $link["exists"]),
                    'external' => $link["external"],
                    'sequence' => $link["sequence"],
                ];
                PageLink::create($data);
            }

            PageTemplate::where('page_id', $id)->delete();
            foreach ($content_data["components"]["templates"] as $template) {
                $data = [
                    'page_id' => $id,
                    'slug' => $slug,
                    'template' => $template["template"],
                    'data' => $template["data"],
                    'sequence' => $template["sequence"],
                ];
                PageTemplate::create($data);
            }
        }
    }
    public static function toEditor($content)
    {
        // Remove tags maliciosas, scripts ou css
        $content = HtmlHelper::removeTag($content, 'style');
        $content = HtmlHelper::removeTag($content, 'script');

        // Remove tarja licença Froala
        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find("[data-f-id='pbf']") as $el) {
            $el->outertext = '';
        }
        $content = $dom->save();
        $dom->clear();

        // Ajusta os Componentes
        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find(".custom-component") as $el) {
            $el->outertext = ComponentHelper::refresh($el->getAttribute("data-component-name") ?: '', $el->innertext);
        }
        $content = $dom->save();
        $dom->clear();

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
            $el->removeAttribute("style");
            $n = $level;
        }
        $content = $dom->save();
        $dom->clear();

        // Verifica imagens
        $dom = HtmlDomParser::str_get_html($content);
        $n = 0;
        foreach ($dom->find("img") as $el) {
            $el = self::convertImage($el);
        }
        $content = $dom->save();
        $dom->clear();

        // Verifica Urls sem link
        $content = self::convertUrls($content);

        // Ajusta Links
        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find("a") as $el) {
            if ($el->innertext == '') {
                $el->outertext = '';
            } else {
                $url_app = asset('');
                $href = trim($el->getAttribute("href"));
                $href = urldecode($href);

                if (substr($href, 0, strlen($url_app)) == $url_app) {
                    $href = substr($href, strlen($url_app), strlen($href));
                    $e = explode("/", $href);
                    $href = $e[0];
                }

                if (substr($href, 0, 7) != "http://" && substr($href, 0, 8) != "https://") {
                    $href = StringHelper::toSlug($href);
                }
                $el->setAttribute("href", $href);

                $nbsp_after = false;
                if (substr($el->innertext, -6) == "&nbsp;" || substr($el->innertext, -1) == " ") {
                    $nbsp_after = true;
                    if (substr($el->innertext, -6) == "&nbsp;") {
                        $el->innertext = substr($el->innertext, 0, -6);
                    } else {
                        $el->innertext = substr($el->innertext, 0, -1);
                    }
                }

                if ($nbsp_after) {
                    $el->outertext = $el->outertext . ' ';
                }
            }
        }
        $content = $dom->save();
        $dom->clear();


        return $content;
    }
    public static function toPreview($content, $topics = true)
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

        /******************************************************************************/

        /* Ajusta os Componentes *********************************************************************/
        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find(".custom-component") as $el) {
            $el->outertext = ComponentHelper::componentToPreview($el->getAttribute("data-component-name") ?: '', $el->innertext);
        }
        $content = $dom->save();
        $dom->clear();

        /******************************************************************************/

        /* Ajusta Imagens *********************************************************************/

        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find("img") as $el) {
            $el = self::convertImage($el);
            $slug = $el->getAttribute("data-slug");
            if ($slug) {
                $name = $el->getAttribute("data-name");
                $page = $name ?: $slug;
                $el->setAttribute("onclick", "open_url('" . route("image", ["page" => $page]) . "')");
            }

            if ($el->parent()->parent()->tag == "span" && strpos($el->parent()->parent()->class, 'fr-img-caption') !== false) {
                $el_parent = $el->parent()->parent();
                $el_image = $el_parent->find("img")[0]->outertext;
                if ($el_parent->find(".fr-inner")) {
                    $el_caption = $el_parent->find(".fr-inner")[0]->innertext;
                } else {
                    $el_caption = "";
                }
            } else {
                $el_parent = $el;
                $el_image = $el_parent->outertext;
                $el_caption = "";
            }
            $class = "";
            $class .= (strpos($el_parent->class, 'fr-fir') !== false ? "float-end" : "");
            $class .= (strpos($el_parent->class, 'fr-fil') !== false ? "float-start" : "");

            $break = (strpos($el_parent->class, 'fr-dib') !== false);


            $html = "";
            if ($break) {
                $html .= "<div class='d-inline-block text-center w-100'>";
            }
            $html .= "<figure class='figure m-3 " . $class . " text-center'>";
            $html .= $el_image;
            if ($el_caption <> "") {
                $html .= "<figcaption class='figure-caption text-center'>" . $el_caption . "</figcaption>";
            }
            $html .= "</figure>";
            if ($break) {
                $html .= "</div>";
            }

            $el_parent->outertext = $html;
        }
        $content = $dom->save();
        $dom->clear();

        /******************************************************************************/

        /* Tabelas Dinâmicas *********************************************************************/

        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find("table.dynamic") as $el) {
            $html = "";
            foreach ($el->find("th,td") as $td) {
                $td->tag = "td";
                $td->removeAttribute("style");
                $td->removeAttribute("colspan");
                $html .= $td->outertext;
            }
            $el->innertext = "<tr>" . $html . "</tr>";
        }
        $content = $dom->save();
        $dom->clear();

        /******************************************************************************/

        /* Ajusta Links *********************************************************************/
        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find("a") as $el) {
            $href = trim($el->getAttribute("href"));
            $page = "";
            $external = false;

            if (substr($href, 0, 7) == "http://" || substr($href, 0, 8) == "https://") {
                $external = true;
            } else {
                $external = false;
            }


            if ($external) {
                $el->setAttribute("target", "_blank");
                $el->setAttribute("class", "external");
                $el->setAttribute("href", $href);
            } else {
                $el->removeAttribute("target");

                $class = "";
                $page = $href;
                $page = StringHelper::toSlug($page);
                $data = Page::where('slug', $page)->first();
                if ($data) {
                    $href = $data->name;
                } else {
                    $class = "no-exists text-danger";
                }

                $href = asset($href);
                $el->setAttribute("class", "internal $class");
                $el->setAttribute("href", $href);
                $el->setAttribute("data-page", $page);
            }
        }
        $content = $dom->save();
        $dom->clear();

        /******************************************************************************/

        /* Ajusta Tópicos *********************************************************************/

        if ($topics) {
            $html = "";
            $level = 0;
            $qtd = 0;

            $html .= "<nav class='anchors sidebar-item border mb-3'>";
            $html .= "<div data-bs-target='#anchors-nav' data-bs-toggle='collapse' class='header p-2 ps-3 cursor-pointer'>";
            $html .= "<svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' class='feather feather-menu'><line x1='3' y1='12' x2='21' y2='12'></line><line x1='3' y1='6' x2='21' y2='6'></line><line x1='3' y1='18' x2='21' y2='18'></line></svg>";
            $html .= "<span class='ps-2 align-middle h5'>Tópicos</span>";
            $html .= "</div>";
            $html .= "<div id='anchors-nav' class='collapse p-3'>";
            foreach ($items as $item) {
                $qtd++;
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

            if ($qtd >= 3) {
                $content = $html . $content;
            }
        }

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
        return '';
    }
    public static function toPdf($content)
    {
        /* Ajusta Imagens *********************************************************************/
        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find("img") as $el) {
            $el->removeAttribute("onclick");
            $el->setAttribute("src", 'storage/' . $el->getAttribute("data-path") . '?' . date('Ymdhis'));
            if (!$el->getAttribute("data-path")) {
                $url_image = route('image.text', ['text' => 'Erro ao|exibir imagem']);
                $src = str_replace(asset(''), '', $url_image);
                $el->setAttribute("src", $src);
            }
        }
        $content = $dom->save();
        $dom->clear();

        /* Remove Links *********************************************************************/
        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find("a") as $el) {
            $el->outertext = $el->innertext;
        }
        $content = $dom->save();
        $dom->clear();

        return $content;
    }

    /* ********************************************************************* */

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
    public static function getImages($content)
    {
        $ret = [];

        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find("img") as $sequence => $el) {
            $slug = $el->getAttribute("data-slug");
            $data = [
                "slug" => $slug,
                "path" => "",
                "exists" => false,
                "is_error" => true,
                "sequence" => $sequence + 1,
            ];
            if ($slug <> "") {
                $data["is_error"] = false;
                if ($el->getAttribute("data-exists")) {
                    $data["path"] = $el->getAttribute("data-path");
                    $data["exists"] = true;
                }
            }
            $ret[] = $data;
        }
        $content = $dom->save();
        $dom->clear();

        return $ret;
    }
    public static function getLinks($content)
    {
        $ret = [];

        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find("a") as $sequence => $el) {
            $data = [
                "slug" => $el->getAttribute("data-page"),
                "text" => $el->innertext,
                "href" => $el->getAttribute("href"),
                "external" => $el->hasClass("external"),
                "exists" => $el->hasClass("internal") && !$el->hasClass("no-exists"),
                "sequence" => $sequence + 1,
            ];
            $ret[] = $data;
        }
        $content = $dom->save();
        $dom->clear();

        return $ret;
    }
    public static function getTemplates($content)
    {
        $ret = [];

        $dom = HtmlDomParser::str_get_html($content);
        foreach ($dom->find(".custom-component") as $sequence => $el) {
            $data = [
                "template" => $el->getAttribute('data-component-name'),
                "data" => ComponentHelper::getParams($el->innertext),
                "sequence" => $sequence + 1,
            ];
            $ret[] = $data;
            $el->outertext = ComponentHelper::refresh($el->getAttribute("data-component-name") ?: '', $el->innertext);
        }
        $content = $dom->save();
        $dom->clear();

        return $ret;
    }

    /* ********************************************************************* */

    public static function convertUrls($text)
    {
        $urlPattern = '/(?<!href=["\']|src=["\'])\bhttps?:\/\/[^\s<>"\']+\b(?![^<]*(<\/a>|<\/src>))/i';

        $callback = function ($matches) {
            return "<a href='" . $matches[0] . "'>" . $matches[0] . "</a>";
        };

        return preg_replace_callback($urlPattern, $callback, $text);
    }
    public static function convertImage($el)
    {
        $slug = $el->getAttribute('data-slug');
        if (!$slug) {
            $el->setAttribute('src', route('image.text', ['text' => 'Não é permitido|imagens externas']));
            $el->setAttribute('data-slug', '');
            $el->setAttribute('data-path', '');
            $el->setAttribute('data-name', '');
            $el->setAttribute('alt', '');
        } else {
            $image = Image::where('slug', $slug)->first();
            if ($image) {
                $el->setAttribute('src', $image->image_url . '?' . date('Ymdhis'));
                $el->setAttribute('data-exists', true);
                $el->setAttribute('data-path', $image->path);
                $el->setAttribute('data-name', $image->name);
                $el->setAttribute('alt', $image->title);
            } else {
                $el->setAttribute('src', route('image.text', ['text' => 'Imagem não|encontrada']));
                $el->setAttribute('data-exists', false);
                $el->setAttribute('data-path', '');
                $el->setAttribute('data-name', '');
                $el->setAttribute('alt', '');
            }
        }
        return $el;

        $callback = function ($matches) {
            return "<a href='" . $matches[0] . "'>" . $matches[0] . "</a>";
        };

        return preg_replace_callback($urlPattern, $callback, $text);
    }
}
