<?php
// Math extension, https://github.com/GiovanniSalmeri/yellow-math

class YellowMath {
    const VERSION = "0.9.2";
    public $yellow;         // access to API

    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
    }

    // Handle page content element
    public function onParseContentElement($page, $name, $text, $attributes, $type) {
        $output = null;
        if ($name=="math" && ($type=="block" || $type=="inline" || $type=="code")) {
            list($expression) = $type=="code" ? [ $text ] : $this->yellow->toolbox->getTextArguments($text);
            $expression = strtr($expression, [ "%%"=>"%", "%|"=>"]" ]);
            $tag = $type=="inline" ? "span" : "div";
            $output = "<$tag class=\"math\">".htmlspecialchars($expression)."</$tag>";
        }
        return $output;
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="header") {
            $assetLocation = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("coreAssetLocation");
            $output .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"{$assetLocation}math-katex.min.css\" />\n";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$assetLocation}math-katex.min.js\"></script>\n";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$assetLocation}math.js\"></script>\n";
        }
        return $output;
    }
}
