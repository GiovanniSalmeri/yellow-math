<?php
// Math extension, https://github.com/GiovanniSalmeri/yellow-math

class YellowMath {
    const VERSION = "0.9.3";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }

    // Handle page content element
    public function onParseContentElement($page, $name, $text, $attributes, $type) {
        $output = null;
        if ($name=="math" && ($type=="block" || $type=="inline" || $type=="code")) {
            $expression = $text;
            if ($type=="code") {
                $label = preg_match('/(?:^|\s)#(\S+)/', $attributes, $matches) ? $matches[1] : null;
            } else {
                $expression = html_entity_decode($expression, ENT_HTML5);
            }
            if ($type=="inline") {
                $output = "<span class=\"math\">".htmlspecialchars($expression)."</span>";
            } else {
                $output = "<div class=\"math\">\n".htmlspecialchars($expression)."\n</div>\n";
                if (isset($label)) {
                    $page->mathLabels = true;
                    $output = "<div class=\"math-display\" id=\"".htmlspecialchars($label)."\">\n<span class=\"math-label\">[##$label]</span>\n".$output."\n</div>\n";
                }
            }
        }
        return $output;
    }

    // Handle page content in HTML format
    public function onParseContentHtml($page, $text) {
        $output = null;
        if (!empty($page->mathLabels)) {
            $ids = [];
            $output = preg_replace_callback('/\[##(\S+?)\]/', function($m) use (&$ids) {
                static $currentId = 0;
                if (!isset($ids[$m[1]])) $ids[$m[1]] = ++$currentId;
                return $ids[$m[1]];
            }, $text);
            $output = preg_replace_callback('/\[#(\S+?)\]/', function($m) use ($ids) {
                if (isset($ids[$m[1]])) {
                    return "<a class=\"math-label\" href=\"#".htmlspecialchars($m[1])."\">{$ids[$m[1]]}</a>";
                } else {
                    return $m[0];
                }
            }, $output);
        }
        return $output;
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="header") {
            $assetLocation = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("coreAssetLocation");
            $output .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"{$assetLocation}math-katex.min.css\" />\n";
            $output .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"{$assetLocation}math.css\" />\n";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$assetLocation}math-katex.min.js\"></script>\n";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$assetLocation}math.js\"></script>\n";
        }
        return $output;
    }
}
