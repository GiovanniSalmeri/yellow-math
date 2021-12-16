<?php
// Math extension, https://github.com/GiovanniSalmeri/yellow-math

class YellowMath {
    const VERSION = "0.8.18";
    public $yellow;         // access to API
    
    // Handle initialisation
    public function onLoad($yellow) {
        $this->yellow = $yellow;
        $this->yellow->system->setDefault("mathDecimal", ".");
        $this->mathParser = new AsciiMathParser($this->yellow->system->get("mathDecimal"));
    }
    
    // Handle page content of shortcut
    public function onParseContentShortcut($page, $name, $text, $type) {
        $output = null;
        if (($name=="math" || $name=="mathtex") && ($type=="block" || $type=="inline")) {
            list($expression) = $this->yellow->toolbox->getTextArguments($text);
            $tag = $type=="inline" ? "span" : "div";
            $content = $name=="mathtex" ? $expression : $this->mathParser->parse($expression);
            $output .= "<$tag class=\"math\">";
            $output .= htmlspecialchars($content);
            $output .= "</$tag>"; // no final \n!
        }
        return $output;
    }

    // Handle page content
    public function onParseContentHtml($page, $text) {
        $callback = function($matches) {
            if ($matches[1]=="") { // undocumented
                $parser = $this->yellow->page->get("mathPlainCode");
                $tag = "span";
            } else {
                $parser = $matches[1];
                $tag = "div";
            }
            $matches[2] = htmlspecialchars_decode($matches[2]);
            $content = $parser=="math" ? $this->mathParser->parse($matches[2]) : $matches[2];
            return "<$tag class=\"math\">".htmlspecialchars($content)."</$tag>";
        };
        if (in_array($this->yellow->page->get("mathPlainCode"), ["math", "mathtex"])) {
            // undocumented
            $text = preg_replace_callback('/<code>()(.*?)<\/code>/s', $callback, $text);
        }
        return preg_replace_callback('/<pre class="(math|mathtex)"><code>(.*?)<\/code><\/pre>/s', $callback, $text);
    }

    // Handle page extra data
    public function onParsePageExtra($page, $name) {
        $output = null;
        if ($name=="header") {
            $extensionLocation = $this->yellow->system->get("coreServerBase").$this->yellow->system->get("coreExtensionLocation");
            $output .= "<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"{$extensionLocation}math-katex.min.css\" />\n";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$extensionLocation}math-katex.min.js\"></script>\n";
            $output .= "<script type=\"text/javascript\" defer=\"defer\" src=\"{$extensionLocation}math.js\"></script>\n";
        }
        return $output;
    }
}

class AsciiMathParser {

// The following class is a translation into PHP of the original in Javascript
// Copyright 2018 Christian Lawson-Perfect
// https://github.com/christianp/asciimath2tex
//
//                                 Apache License
//                           Version 2.0, January 2004
//                        http://www.apache.org/licenses/
//
//   TERMS AND CONDITIONS FOR USE, REPRODUCTION, AND DISTRIBUTION
//
//   1. Definitions.
//
//      "License" shall mean the terms and conditions for use, reproduction,
//      and distribution as defined by Sections 1 through 9 of this document.
//
//      "Licensor" shall mean the copyright owner or entity authorized by
//      the copyright owner that is granting the License.
//
//      "Legal Entity" shall mean the union of the acting entity and all
//      other entities that control, are controlled by, or are under common
//      control with that entity. For the purposes of this definition,
//      "control" means (i) the power, direct or indirect, to cause the
//      direction or management of such entity, whether by contract or
//      otherwise, or (ii) ownership of fifty percent (50%) or more of the
//      outstanding shares, or (iii) beneficial ownership of such entity.
//
//      "You" (or "Your") shall mean an individual or Legal Entity
//      exercising permissions granted by this License.
//
//      "Source" form shall mean the preferred form for making modifications,
//      including but not limited to software source code, documentation
//      source, and configuration files.
//
//      "Object" form shall mean any form resulting from mechanical
//      transformation or translation of a Source form, including but
//      not limited to compiled object code, generated documentation,
//      and conversions to other media types.
//
//      "Work" shall mean the work of authorship, whether in Source or
//      Object form, made available under the License, as indicated by a
//      copyright notice that is included in or attached to the work
//      (an example is provided in the Appendix below).
//
//      "Derivative Works" shall mean any work, whether in Source or Object
//      form, that is based on (or derived from) the Work and for which the
//      editorial revisions, annotations, elaborations, or other modifications
//      represent, as a whole, an original work of authorship. For the purposes
//      of this License, Derivative Works shall not include works that remain
//      separable from, or merely link (or bind by name) to the interfaces of,
//      the Work and Derivative Works thereof.
//
//      "Contribution" shall mean any work of authorship, including
//      the original version of the Work and any modifications or additions
//      to that Work or Derivative Works thereof, that is intentionally
//      submitted to Licensor for inclusion in the Work by the copyright owner
//      or by an individual or Legal Entity authorized to submit on behalf of
//      the copyright owner. For the purposes of this definition, "submitted"
//      means any form of electronic, verbal, or written communication sent
//      to the Licensor or its representatives, including but not limited to
//      communication on electronic mailing lists, source code control systems,
//      and issue tracking systems that are managed by, or on behalf of, the
//      Licensor for the purpose of discussing and improving the Work, but
//      excluding communication that is conspicuously marked or otherwise
//      designated in writing by the copyright owner as "Not a Contribution."
//
//      "Contributor" shall mean Licensor and any individual or Legal Entity
//      on behalf of whom a Contribution has been received by Licensor and
//      subsequently incorporated within the Work.
//
//   2. Grant of Copyright License. Subject to the terms and conditions of
//      this License, each Contributor hereby grants to You a perpetual,
//      worldwide, non-exclusive, no-charge, royalty-free, irrevocable
//      copyright license to reproduce, prepare Derivative Works of,
//      publicly display, publicly perform, sublicense, and distribute the
//      Work and such Derivative Works in Source or Object form.
//
//   3. Grant of Patent License. Subject to the terms and conditions of
//      this License, each Contributor hereby grants to You a perpetual,
//      worldwide, non-exclusive, no-charge, royalty-free, irrevocable
//      (except as stated in this section) patent license to make, have made,
//      use, offer to sell, sell, import, and otherwise transfer the Work,
//      where such license applies only to those patent claims licensable
//      by such Contributor that are necessarily infringed by their
//      Contribution(s) alone or by combination of their Contribution(s)
//      with the Work to which such Contribution(s) was submitted. If You
//      institute patent litigation against any entity (including a
//      cross-claim or counterclaim in a lawsuit) alleging that the Work
//      or a Contribution incorporated within the Work constitutes direct
//      or contributory patent infringement, then any patent licenses
//      granted to You under this License for that Work shall terminate
//      as of the date such litigation is filed.
//
//   4. Redistribution. You may reproduce and distribute copies of the
//      Work or Derivative Works thereof in any medium, with or without
//      modifications, and in Source or Object form, provided that You
//      meet the following conditions:
//
//      (a) You must give any other recipients of the Work or
//          Derivative Works a copy of this License; and
//
//      (b) You must cause any modified files to carry prominent notices
//          stating that You changed the files; and
//
//      (c) You must retain, in the Source form of any Derivative Works
//          that You distribute, all copyright, patent, trademark, and
//          attribution notices from the Source form of the Work,
//          excluding those notices that do not pertain to any part of
//          the Derivative Works; and
//
//      (d) If the Work includes a "NOTICE" text file as part of its
//          distribution, then any Derivative Works that You distribute must
//          include a readable copy of the attribution notices contained
//          within such NOTICE file, excluding those notices that do not
//          pertain to any part of the Derivative Works, in at least one
//          of the following places: within a NOTICE text file distributed
//          as part of the Derivative Works; within the Source form or
//          documentation, if provided along with the Derivative Works; or,
//          within a display generated by the Derivative Works, if and
//          wherever such third-party notices normally appear. The contents
//          of the NOTICE file are for informational purposes only and
//          do not modify the License. You may add Your own attribution
//          notices within Derivative Works that You distribute, alongside
//          or as an addendum to the NOTICE text from the Work, provided
//          that such additional attribution notices cannot be construed
//          as modifying the License.
//
//      You may add Your own copyright statement to Your modifications and
//      may provide additional or different license terms and conditions
//      for use, reproduction, or distribution of Your modifications, or
//      for any such Derivative Works as a whole, provided Your use,
//      reproduction, and distribution of the Work otherwise complies with
//      the conditions stated in this License.
//
//   5. Submission of Contributions. Unless You explicitly state otherwise,
//      any Contribution intentionally submitted for inclusion in the Work
//      by You to the Licensor shall be under the terms and conditions of
//      this License, without any additional terms or conditions.
//      Notwithstanding the above, nothing herein shall supersede or modify
//      the terms of any separate license agreement you may have executed
//      with Licensor regarding such Contributions.
//
//   6. Trademarks. This License does not grant permission to use the trade
//      names, trademarks, service marks, or product names of the Licensor,
//      except as required for reasonable and customary use in describing the
//      origin of the Work and reproducing the content of the NOTICE file.
//
//   7. Disclaimer of Warranty. Unless required by applicable law or
//      agreed to in writing, Licensor provides the Work (and each
//      Contributor provides its Contributions) on an "AS IS" BASIS,
//      WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or
//      implied, including, without limitation, any warranties or conditions
//      of TITLE, NON-INFRINGEMENT, MERCHANTABILITY, or FITNESS FOR A
//      PARTICULAR PURPOSE. You are solely responsible for determining the
//      appropriateness of using or redistributing the Work and assume any
//      risks associated with Your exercise of permissions under this License.
//
//   8. Limitation of Liability. In no event and under no legal theory,
//      whether in tort (including negligence), contract, or otherwise,
//      unless required by applicable law (such as deliberate and grossly
//      negligent acts) or agreed to in writing, shall any Contributor be
//      liable to You for damages, including any direct, indirect, special,
//      incidental, or consequential damages of any character arising as a
//      result of this License or out of the use or inability to use the
//      Work (including but not limited to damages for loss of goodwill,
//      work stoppage, computer failure or malfunction, or any and all
//      other commercial damages or losses), even if such Contributor
//      has been advised of the possibility of such damages.
//
//   9. Accepting Warranty or Additional Liability. While redistributing
//      the Work or Derivative Works thereof, You may choose to offer,
//      and charge a fee for, acceptance of support, warranty, indemnity,
//      or other liability obligations and/or rights consistent with this
//      License. However, in accepting such obligations, You may act only
//      on Your own behalf and on Your sole responsibility, not on behalf
//      of any other Contributor, and only if You agree to indemnify,
//      defend, and hold each Contributor harmless for any liability
//      incurred by, or claims asserted against, such Contributor by reason
//      of your accepting any such warranty or additional liability.
//
//   END OF TERMS AND CONDITIONS
//
//   APPENDIX: How to apply the Apache License to your work.
//
//      To apply the Apache License to your work, attach the following
//      boilerplate notice, with the fields enclosed by brackets "[]"
//      replaced with your own identifying information. (Don't include
//      the brackets!)  The text should be enclosed in the appropriate
//      comment syntax for the file format. We also recommend that a
//      file or class name and description of purpose be included on the
//      same "printed page" as the copyright notice for easier
//      identification within third-party archives.
//
//   Copyright 2018 Christian Lawson-Perfect
//
//   Licensed under the Apache License, Version 2.0 (the "License");
//   you may not use this file except in compliance with the License.
//   You may obtain a copy of the License at
//
//       http://www.apache.org/licenses/LICENSE-2.0
//
//   Unless required by applicable law or agreed to in writing, software
//   distributed under the License is distributed on an "AS IS" BASIS,
//   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
//   See the License for the specific language governing permissions and
//   limitations under the License.


    public function __construct($decimal) {
        $this->decimalsign = $decimal;
        $this->setup_symbols();
        $this->sort_symbols();
    }

    private function setup_symbols() {
        $this->greek_letters = ['alpha', 'beta', 'gamma', 'Gamma', 'delta', 'Delta', 'epsilon', 'varepsilon', 'zeta', 'eta', 'theta', 'Theta', 'vartheta', 'iota', 'kappa', 'lambda', 'Lambda', 'mu', 'nu', 'xi', 'Xi', 'pi', 'Pi', 'rho', 'sigma', 'Sigma', 'tau', 'upsilon', 'phi', 'Phi', 'varphi', 'chi', 'psi', 'Psi', 'omega', 'Omega'];
        $this->relations = [[
            'asciimath'=>":=",
            'tex'=>":="
        ], [
            'asciimath'=>":|:",
            'tex'=>"\\|"
        ], [
            'asciimath'=>"=>",
            'tex'=>"\\Rightarrow"
        ], [
            'asciimath'=>"approx",
            'tex'=>"\\approx"
        ], [
            'asciimath'=>"~~",
            'tex'=>"\\approx"
        ], [
            'asciimath'=>"cong",
            'tex'=>"\\cong"
        ], [
            'asciimath'=>"~=",
            'tex'=>"\\cong"
        ], [
            'asciimath'=>"equiv",
            'tex'=>"\\equiv"
        ], [
            'asciimath'=>"-=",
            'tex'=>"\\equiv"
        ], [
            'asciimath'=>"exists",
            'tex'=>"\\exists"
        ], [
            'asciimath'=>"EE",
            'tex'=>"\\exists"
        ], [
            'asciimath'=>"forall",
            'tex'=>"\\forall"
        ], [
            'asciimath'=>"AA",
            'tex'=>"\\forall"
        ], [
            'asciimath'=>">=",
            'tex'=>"\\ge"
        ], [
            'asciimath'=>"ge",
            'tex'=>"\\ge"
        ], [
            'asciimath'=>"gt=",
            'tex'=>"\\geq"
        ], [
            'asciimath'=>"geq",
            'tex'=>"\\geq"
        ], [
            'asciimath'=>"gt",
            'tex'=>"\\gt"
        ], [
            'asciimath'=>"in",
            'tex'=>"\\in"
        ], [
            'asciimath'=>"<=",
            'tex'=>"\\le"
        ], [
            'asciimath'=>"le",
            'tex'=>"\\le"
        ], [
            'asciimath'=>"lt=",
            'tex'=>"\\leq"
        ], [
            'asciimath'=>"leq",
            'tex'=>"\\leq"
        ], [
            'asciimath'=>"lt",
            'tex'=>"\\lt"
        ], [
            'asciimath'=>"models",
            'tex'=>"\\models"
        ], [
            'asciimath'=>"|==",
            'tex'=>"\\models"
        ], [
            'asciimath'=>"!=",
            'tex'=>"\\ne"
        ], [
            'asciimath'=>"ne",
            'tex'=>"\\ne"
        ], [
            'asciimath'=>"notin",
            'tex'=>"\\notin"
        ], [
            'asciimath'=>"!in",
            'tex'=>"\\notin"
        ], [
            'asciimath'=>"prec",
            'tex'=>"\\prec"
        ], [
            'asciimath'=>"-lt",
            'tex'=>"\\prec"
        ], [
            'asciimath'=>"-<",
            'tex'=>"\\prec"
        ], [
            'asciimath'=>"preceq",
            'tex'=>"\\preceq"
        ], [
            'asciimath'=>"-<=",
            'tex'=>"\\preceq"
        ], [
            'asciimath'=>"propto",
            'tex'=>"\\propto"
        ], [
            'asciimath'=>"prop",
            'tex'=>"\\propto"
        ], [
            'asciimath'=>"subset",
            'tex'=>"\\subset"
        ], [
            'asciimath'=>"sub",
            'tex'=>"\\subset"
        ], [
            'asciimath'=>"subseteq",
            'tex'=>"\\subseteq"
        ], [
            'asciimath'=>"sube",
            'tex'=>"\\subseteq"
        ], [
            'asciimath'=>"succ",
            'tex'=>"\\succ"
        ], [
            'asciimath'=>">-",
            'tex'=>"\\succ"
        ], [
            'asciimath'=>"succeq",
            'tex'=>"\\succeq"
        ], [
            'asciimath'=>">-=",
            'tex'=>"\\succeq"
        ], [
            'asciimath'=>"supset",
            'tex'=>"\\supset"
        ], [
            'asciimath'=>"sup",
            'tex'=>"\\supset"
        ], [
            'asciimath'=>"supseteq",
            'tex'=>"\\supseteq"
        ], [
            'asciimath'=>"supe",
            'tex'=>"\\supseteq"
        ], [
            'asciimath'=>"vdash",
            'tex'=>"\\vdash"
        ], [
            'asciimath'=>"|--",
            'tex'=>"\\vdash"
        ]];
        $this->constants = [[
                'asciimath'=>"dt",
                'tex'=>"dt"
        ], [
            'asciimath'=>"dx",
            'tex'=>"dx"
        ], [
            'asciimath'=>"dy",
            'tex'=>"dy"
        ], [
            'asciimath'=>"dz",
            'tex'=>"dz"
        ], [
            'asciimath'=>"prime",
            'tex'=>"'"
        ], [
            'asciimath'=>"implies",
            'tex'=>"\\implies"
        ], [
            'asciimath'=>"epsi",
            'tex'=>"\\epsilon"
        ], [
            'asciimath'=>"leftrightarrow",
            'tex'=>"\\leftrightarrow"
        ], [
            'asciimath'=>"Leftrightarrow",
            'tex'=>"\\Leftrightarrow"
        ], [
            'asciimath'=>"rightarrow",
            'tex'=>"\\rightarrow"
        ], [
            'asciimath'=>"Rightarrow",
            'tex'=>"\\Rightarrow"
        ], [
            'asciimath'=>"backslash",
            'tex'=>"\\backslash"
        ], [
            'asciimath'=>"leftarrow",
            'tex'=>"\\leftarrow"
        ], [
            'asciimath'=>"Leftarrow",
            'tex'=>"\\Leftarrow"
        ], [
            'asciimath'=>"setminus",
            'tex'=>"\\setminus"
        ], [
            'asciimath'=>"bigwedge",
            'tex'=>"\\bigwedge"
        ], [
            'asciimath'=>"diamond",
            'tex'=>"\\diamond"
        ], [
            'asciimath'=>"bowtie",
            'tex'=>"\\bowtie"
        ], [
            'asciimath'=>"bigvee",
            'tex'=>"\\bigvee"
        ], [
            'asciimath'=>"bigcap",
            'tex'=>"\\bigcap"
        ], [
            'asciimath'=>"bigcup",
            'tex'=>"\\bigcup"
        ], [
            'asciimath'=>"square",
            'tex'=>"\\square"
        ], [
            'asciimath'=>"lamda",
            'tex'=>"\\lambda"
        ], [
            'asciimath'=>"Lamda",
            'tex'=>"\\Lambda"
        ], [
            'asciimath'=>"aleph",
            'tex'=>"\\aleph"
        ], [
            'asciimath'=>"angle",
            'tex'=>"\\angle"
        ], [
            'asciimath'=>"frown",
            'tex'=>"\\frown"
        ], [
            'asciimath'=>"qquad",
            'tex'=>"\\qquad"
        ], [
            'asciimath'=>"cdots",
            'tex'=>"\\cdots"
        ], [
            'asciimath'=>"vdots",
            'tex'=>"\\vdots"
        ], [
            'asciimath'=>"ddots",
            'tex'=>"\\ddots"
        ], [
            'asciimath'=>"cdot",
            'tex'=>"\\cdot"
        ], [
            'asciimath'=>"star",
            'tex'=>"\\star"
        ], [
            'asciimath'=>"|><|",
            'tex'=>"\\bowtie"
        ], [
            'asciimath'=>"circ",
            'tex'=>"\\circ"
        ], [
            'asciimath'=>"oint",
            'tex'=>"\\oint"
        ], [
            'asciimath'=>"grad",
            'tex'=>"\\nabla"
        ], [
            'asciimath'=>"quad",
            'tex'=>"\\quad"
        ], [
            'asciimath'=>"uarr",
            'tex'=>"\\uparrow"
        ], [
            'asciimath'=>"darr",
            'tex'=>"\\downarrow"
        ], [
            'asciimath'=>"downarrow",
            'tex'=>"\\downarrow"
        ], [
            'asciimath'=>"rarr",
            'tex'=>"\\rightarrow"
        ], [
            //'asciimath'=>">->>",
            //'tex'=>"\\twoheadrightarrowtail" // lacking in KaTeX
        //], [
            'asciimath'=>"larr",
            'tex'=>"\\leftarrow"
        ], [
            'asciimath'=>"harr",
            'tex'=>"\\leftrightarrow"
        ], [
            'asciimath'=>"rArr",
            'tex'=>"\\Rightarrow"
        ], [
            'asciimath'=>"lArr",
            'tex'=>"\\Leftarrow"
        ], [
            'asciimath'=>"hArr",
            'tex'=>"\\Leftrightarrow"
        ], [
            'asciimath'=>"ast",
            'tex'=>"\\ast"
        ], [
            'asciimath'=>"***",
            'tex'=>"\\star"
        ], [
            'asciimath'=>"|><",
            'tex'=>"\\ltimes"
        ], [
            'asciimath'=>"><|",
            'tex'=>"\\rtimes"
        ], [
            'asciimath'=>"^^^",
            'tex'=>"\\bigwedge"
        ], [
            'asciimath'=>"vvv",
            'tex'=>"\\bigvee"
        ], [
            'asciimath'=>"cap",
            'tex'=>"\\cap"
        ], [
            'asciimath'=>"nnn",
            'tex'=>"\\bigcap"
        ], [
            'asciimath'=>"cup",
            'tex'=>"\\cup"
        ], [
            'asciimath'=>"uuu",
            'tex'=>"\\bigcup"
        ], [
            'asciimath'=>"not",
            'tex'=>"\\neg"
        ], [
            'asciimath'=>"<=>",
            'tex'=>"\\Leftrightarrow"
        ], [
            'asciimath'=>"_|_",
            'tex'=>"\\bot"
        ], [
            'asciimath'=>"bot",
            'tex'=>"\\bot"
        ], [
            'asciimath'=>"int",
            'tex'=>"\\int"
        ], [
            'asciimath'=>"del",
            'tex'=>"\\partial"
        ], [
            'asciimath'=>"...",
            'tex'=>"\\ldots"
        ], [
            'asciimath'=>"/_\\",
            'tex'=>"\\triangle"
        ], [
            'asciimath'=>"|__",
            'tex'=>"\\lfloor"
        ], [
            'asciimath'=>"__|",
            'tex'=>"\\rfloor"
        ], [
            'asciimath'=>"dim",
            'tex'=>"\\dim"
        ], [
            'asciimath'=>"mod",
            'tex'=>"\\operatorname{mod}"
        ], [
            'asciimath'=>"lub",
            'tex'=>"\\operatorname{lub}"
        ], [
            'asciimath'=>"glb",
            'tex'=>"\\operatorname{glb}"
        ], [
            'asciimath'=>">->",
            'tex'=>"\\rightarrowtail"
        ], [
            'asciimath'=>"->>",
            'tex'=>"\\twoheadrightarrow"
        ], [
            'asciimath'=>"|->",
            'tex'=>"\\mapsto"
        ], [
            'asciimath'=>"lim",
            'tex'=>"\\lim"
        ], [
            'asciimath'=>"Lim",
            'tex'=>"\\operatorname{Lim}"
        ], [
            'asciimath'=>"and",
            'tex'=>"\\quad\\text{and}\\quad"
        ], [
            'asciimath'=>"**",
            'tex'=>"\\ast"
        ], [
            'asciimath'=>"//",
            'tex'=>"/"
        ], [
            'asciimath'=>"\\",
            'tex'=>"\\,"
        ], [
            'asciimath'=>"\\\\",
            'tex'=>"\\backslash"
        ], [
            'asciimath'=>"xx",
            'tex'=>"\\times"
        ], [
            'asciimath'=>"-:",
            'tex'=>"\\div"
        ], [
            'asciimath'=>"o+",
            'tex'=>"\\oplus"
        ], [
            'asciimath'=>"ox",
            'tex'=>"\\otimes"
        ], [
            'asciimath'=>"o.",
            'tex'=>"\\odot"
        ], [
            'asciimath'=>"^",
            'tex'=>"\\hat{}"
        ], [
            'asciimath'=>"_",
            'tex'=>"\\_"
        ], [
            'asciimath'=>"^^",
            'tex'=>"\\wedge"
        ], [
            'asciimath'=>"vv",
            'tex'=>"\\vee"
        ], [
            'asciimath'=>"nn",
            'tex'=>"\\cap"
        ], [
            'asciimath'=>"uu",
            'tex'=>"\\cup"
        ], [
            'asciimath'=>"TT",
            'tex'=>"\\top"
        ], [
            'asciimath'=>"+-",
            'tex'=>"\\pm"
        ], [
            'asciimath'=>"O/",
            'tex'=>"\\emptyset"
        ], [
            'asciimath'=>"oo",
            'tex'=>"\\infty"
        ], [
            'asciimath'=>":.",
            'tex'=>"\\therefore"
        ], [
            'asciimath'=>":'",
            'tex'=>"\\because"
        ], [
            'asciimath'=>"/_",
            'tex'=>"\\angle"
        ], [
            'asciimath'=>"|~",
            'tex'=>"\\lceil"
        ], [
            'asciimath'=>"~|",
            'tex'=>"\\rceil"
        ], [
            'asciimath'=>"CC",
            'tex'=>"\\mathbb{C}"
        ], [
            'asciimath'=>"NN",
            'tex'=>"\\mathbb{N}"
        ], [
            'asciimath'=>"QQ",
            'tex'=>"\\mathbb{Q}"
        ], [
            'asciimath'=>"RR",
            'tex'=>"\\mathbb{R}"
        ], [
            'asciimath'=>"ZZ",
            'tex'=>"\\mathbb{Z}"
        ], [
            'asciimath'=>"->",
            'tex'=>"\\to"
        ], [
            'asciimath'=>"or",
            'tex'=>"\\quad\\text{or}\\quad"
        ], [
            'asciimath'=>"if",
            'tex'=>"\\quad\\text{if}\\quad"
        ], [
            'asciimath'=>"iff",
            'tex'=>"\\iff"
        ], [
            'asciimath'=>"*",
            'tex'=>"\\cdot"
        ], [
            'asciimath'=>"@",
            'tex'=>"\\circ"
        ], [
            'asciimath'=>"%",
            'tex'=>"\\%"
        ], [
            'asciimath'=>"boxempty",
            'tex'=>"\\square"
        ], [
            'asciimath'=>"lambda",
            'tex'=>"\\lambda"
        ], [
            'asciimath'=>"Lambda",
            'tex'=>"\\Lambda"
        ], [
            'asciimath'=>"nabla",
            'tex'=>"\\nabla"
        ], [
            'asciimath'=>"uparrow",
            'tex'=>"\\uparrow"
        ], [
            'asciimath'=>"downarrow",
            'tex'=>"\\downarrow"
        ], [
            'asciimath'=>"twoheadrightarrowtail",
            'tex'=>"\\twoheadrightarrowtail"
        ], [
            'asciimath'=>"ltimes",
            'tex'=>"\\ltimes"
        ], [
            'asciimath'=>"rtimes",
            'tex'=>"\\rtimes"
        ], [
            'asciimath'=>"neg",
            'tex'=>"\\neg"
        ], [
            'asciimath'=>"partial",
            'tex'=>"\\partial"
        ], [
            'asciimath'=>"ldots",
            'tex'=>"\\ldots"
        ], [
            'asciimath'=>"triangle",
            'tex'=>"\\triangle"
        ], [
            'asciimath'=>"lfloor",
            'tex'=>"\\lfloor"
        ], [
            'asciimath'=>"rfloor",
            'tex'=>"\\rfloor"
        ], [
            'asciimath'=>"rightarrowtail",
            'tex'=>"\\rightarrowtail"
        ], [
            'asciimath'=>"twoheadrightarrow",
            'tex'=>"\\twoheadrightarrow"
        ], [
            'asciimath'=>"mapsto",
            'tex'=>"\\mapsto"
        ], [
            'asciimath'=>"times",
            'tex'=>"\\times"
        ], [
            'asciimath'=>"div",
            'tex'=>"\\div"
        ], [
            'asciimath'=>"divide",
            'tex'=>"\\div"
        ], [
            'asciimath'=>"oplus",
            'tex'=>"\\oplus"
        ], [
            'asciimath'=>"otimes",
            'tex'=>"\\otimes"
        ], [
            'asciimath'=>"odot",
            'tex'=>"\\odot"
        ], [
            'asciimath'=>"wedge",
            'tex'=>"\\wedge"
        ], [
            'asciimath'=>"vee",
            'tex'=>"\\vee"
        ], [
            'asciimath'=>"top",
            'tex'=>"\\top"
        ], [
            'asciimath'=>"pm",
            'tex'=>"\\pm"
        ], [
            'asciimath'=>"emptyset",
            'tex'=>"\\emptyset"
        ], [
            'asciimath'=>"infty",
            'tex'=>"\\infty"
        ], [
            'asciimath'=>"therefore",
            'tex'=>"\\therefore"
        ], [
            'asciimath'=>"because",
            'tex'=>"\\because"
        ], [
            'asciimath'=>"lceil",
            'tex'=>"\\lceil"
        ], [
            'asciimath'=>"rceil",
            'tex'=>"\\rceil"
        ], [
            'asciimath'=>"to",
            'tex'=>"\\to"
        ], [
            'asciimath'=>"langle",
            'tex'=>"\\langle"
        ], [
            'asciimath'=>"lceiling",
            'tex'=>"\\lceil"
        ], [
            'asciimath'=>"rceiling",
            'tex'=>"\\rceil"
        ], [
            'asciimath'=>"max",
            'tex'=>"\\max"
        ], [
            'asciimath'=>"min",
            'tex'=>"\\min"
        ], [
            'asciimath'=>"prod",
            'tex'=>"\\prod"
        ], [
            'asciimath'=>"sum",
            'tex'=>"\\sum"
        ]];
        $this->constants = array_merge($this->constants, $this->relations);
        $this->left_brackets = [[
                'asciimath'=>"langle",
                'tex'=>"\\langle"
        ], [
            'asciimath'=>"(:",
            'tex'=>"\\langle"
        ], [
            'asciimath'=>"<<",
            'tex'=>"\\langle"
        ], [
            'asciimath'=>"{:",
            'tex'=>"."
        ], [
            'asciimath'=>"(",
            'tex'=>"("
        ], [
            'asciimath'=>"[",
            'tex'=>"["
        ], [
            'asciimath'=>"lbrack", // added for Yellow shortcuts
            'tex'=>"["
        ], [
            'asciimath'=>"{",
            'tex'=>"\\lbrace"
        ], [
            'asciimath'=>"lbrace",
            'tex'=>"\\lbrace"
        ]];
        $this->right_brackets = [[
                'asciimath'=>"rangle",
                'tex'=>"\\rangle"
        ], [
            'asciimath'=>":)",
            'tex'=>"\\rangle"
        ], [
            'asciimath'=>">>",
            'tex'=>"\\rangle"
        ], [
            'asciimath'=>":}",
            'tex'=>".",
            'free_tex'=>":\\}"
        ], [
            'asciimath'=>")",
            'tex'=>")"
        ], [
            'asciimath'=>"]",
            'tex'=>"]"
        ], [
            'asciimath'=>"rbrack",  // added for Yellow shortcuts
            'tex'=>"]"
        ], [
            'asciimath'=>"}",
            'tex'=>"\\rbrace"
        ], [
            'asciimath'=>"rbrace",
            'tex'=>"\\rbrace"
        ]];
        $this->leftright_brackets = [[
                'asciimath'=>"|",
                'left_tex'=>"\\lvert",
                'right_tex'=>"\\rvert",
                'free_tex'=>"|"
        ]];
        $this->unary_symbols = [[
                'asciimath'=>"sqrt",
                'tex'=>"\\sqrt"
        ], [
            'asciimath'=>"f",
            'tex'=>"f",
            'func'=>true
        ], [
            'asciimath'=>"g",
            'tex'=>"g",
            'func'=>true
        ], [
            'asciimath'=>"sin",
            'tex'=>"\\sin",
            'func'=>true
        ], [
            'asciimath'=>"cos",
            'tex'=>"\\cos",
            'func'=>true
        ], [
            'asciimath'=>"tan",
            'tex'=>"\\tan",
            'func'=>true
        ], [
            'asciimath'=>"arcsin",
            'tex'=>"\\arcsin",
            'func'=>true
        ], [
            'asciimath'=>"arccos",
            'tex'=>"\\arccos",
            'func'=>true
        ], [
            'asciimath'=>"arctan",
            'tex'=>"\\arctan",
            'func'=>true
        ], [
            'asciimath'=>"sinh",
            'tex'=>"\\sinh",
            'func'=>true
        ], [
            'asciimath'=>"cosh",
            'tex'=>"\\cosh",
            'func'=>true
        ], [
            'asciimath'=>"tanh",
            'tex'=>"\\tanh",
            'func'=>true
        ], [
            'asciimath'=>"cot",
            'tex'=>"\\cot",
            'func'=>true
        ], [
            'asciimath'=>"coth",
            'tex'=>"\\coth",
            'func'=>true
        ], [
            'asciimath'=>"sech",
            'tex'=>"\\operatorname{sech}",
            'func'=>true
        ], [
            'asciimath'=>"csch",
            'tex'=>"\\operatorname{csch}",
            'func'=>true
        ], [
            'asciimath'=>"sec",
            'tex'=>"\\sec",
            'func'=>true
        ], [
            'asciimath'=>"csc",
            'tex'=>"\\csc",
            'func'=>true
        ], [
            'asciimath'=>"log",
            'tex'=>"\\log",
            'func'=>true
        ], [
            'asciimath'=>"ln",
            'tex'=>"\\ln",
            'func'=>true
        ], [
            'asciimath'=>"abs",
            'rewriteleftright'=>["|", "|"]
        ], [
            'asciimath'=>"norm",
            'rewriteleftright'=>["\\|", "\\|"]
        ], [
            'asciimath'=>"floor",
            'rewriteleftright'=>["\\lfloor", "\\rfloor"]
        ], [
            'asciimath'=>"ceil",
            'rewriteleftright'=>["\\lceil", "\\rceil"]
        ], [
            'asciimath'=>"Sin",
            'tex'=>"\\Sin",
            'func'=>true
        ], [
            'asciimath'=>"Cos",
            'tex'=>"\\Cos",
            'func'=>true
        ], [
            'asciimath'=>"Tan",
            'tex'=>"\\Tan",
            'func'=>true
        ], [
            'asciimath'=>"Arcsin",
            'tex'=>"\\Arcsin",
            'func'=>true
        ], [
            'asciimath'=>"Arccos",
            'tex'=>"\\Arccos",
            'func'=>true
        ], [
            'asciimath'=>"Arctan",
            'tex'=>"\\Arctan",
            'func'=>true
        ], [
            'asciimath'=>"Sinh",
            'tex'=>"\\Sinh",
            'func'=>true
        ], [
            'asciimath'=>"Cosh",
            'tex'=>"\\Cosh",
            'func'=>true
        ], [
            'asciimath'=>"Tanh",
            'tex'=>"\\Tanh",
            'func'=>true
        ], [
            'asciimath'=>"Cot",
            'tex'=>"\\Cot",
            'func'=>true
        ], [
            'asciimath'=>"Sec",
            'tex'=>"\\Sec",
            'func'=>true
        ], [
            'asciimath'=>"Csc",
            'tex'=>"\\Csc",
            'func'=>true
        ], [
            'asciimath'=>"Log",
            'tex'=>"\\Log",
            'func'=>true
        ], [
            'asciimath'=>"Ln",
            'tex'=>"\\Ln",
            'func'=>true
        ], [
            'asciimath'=>"Abs",
            'tex'=>"\\Abs",
            'rewriteleftright'=>["|", "|"]
        ], [
            'asciimath'=>"det",
            'tex'=>"\\det",
            'func'=>true
        ], [
            'asciimath'=>"exp",
            'tex'=>"\\exp",
            'func'=>true
        ], [
            'asciimath'=>"gcd",
            'tex'=>"\\gcd",
            'func'=>true
        ], [
            'asciimath'=>"lcm",
            'tex'=>"\\operatorname{lcm}",
            'func'=>true
        ], [
            'asciimath'=>"cancel",
            'tex'=>"\\cancel"
        ], [
            'asciimath'=>"Sqrt",
            'tex'=>"\\Sqrt"
        ], [
            'asciimath'=>"hat",
            'tex'=>"\\hat",
            'acc'=>true
        ], [
            'asciimath'=>"bar",
            'tex'=>"\\overline",
            'acc'=>true
        ], [
            'asciimath'=>"overline",
            'tex'=>"\\overline",
            'acc'=>true
        ], [
            'asciimath'=>"vec",
            'tex'=>"\\vec",
            'acc'=>true
        ], [
            'asciimath'=>"tilde",
            'tex'=>"\\tilde",
            'acc'=>true
        ], [
            'asciimath'=>"dot",
            'tex'=>"\\dot",
            'acc'=>true
        ], [
            'asciimath'=>"ddot",
            'tex'=>"\\ddot",
            'acc'=>true
        ], [
            'asciimath'=>"ul",
            'tex'=>"\\underline",
            'acc'=>true
        ], [
            'asciimath'=>"underline",
            'tex'=>"\\underline",
            'acc'=>true
        ], [
            'asciimath'=>"ubrace",
            'tex'=>"\\underbrace",
            'acc'=>true
        ], [
            'asciimath'=>"underbrace",
            'tex'=>"\\underbrace",
            'acc'=>true
        ], [
            'asciimath'=>"obrace",
            'tex'=>"\\overbrace",
            'acc'=>true
        ], [
            'asciimath'=>"overbrace",
            'tex'=>"\\overbrace",
            'acc'=>true
        ], [
            'asciimath'=>"bb",
            'atname'=>"mathvariant",
            'atval'=>"bold",
            'tex'=>"\\mathbf"
        ], [
            'asciimath'=>"mathbf",
            'atname'=>"mathvariant",
            'atval'=>"bold",
            'tex'=>"mathbf"
        ], [
            'asciimath'=>"sf",
            'atname'=>"mathvariant",
            'atval'=>"sans-serif",
            'tex'=>"\\mathsf"
        ], [
            'asciimath'=>"mathsf",
            'atname'=>"mathvariant",
            'atval'=>"sans-serif",
            'tex'=>"mathsf"
        ], [
            'asciimath'=>"bbb",
            'atname'=>"mathvariant",
            'atval'=>"double-struck",
            'tex'=>"\\mathbb"
        ], [
            'asciimath'=>"mathbb",
            'atname'=>"mathvariant",
            'atval'=>"double-struck",
            'tex'=>"\\mathbb"
        ], [
            'asciimath'=>"cc",
            'atname'=>"mathvariant",
            'atval'=>"script",
            'tex'=>"\\mathcal"
        ], [
            'asciimath'=>"mathcal",
            'atname'=>"mathvariant",
            'atval'=>"script",
            'tex'=>"\\mathcal"
        ], [
            'asciimath'=>"tt",
            'atname'=>"mathvariant",
            'atval'=>"monospace",
            'tex'=>"\\mathtt"
        ], [
            'asciimath'=>"mathtt",
            'atname'=>"mathvariant",
            'atval'=>"monospace",
            'tex'=>"\\mathtt"
        ], [
            'asciimath'=>"fr",
            'atname'=>"mathvariant",
            'atval'=>"fraktur",
            'tex'=>"\\mathfrak"
        ], [
            'asciimath'=>"mathfrak",
            'atname'=>"mathvariant",
            'atval'=>"fraktur",
            'tex'=>"\\mathfrak"
        ]];
        $this->binary_symbols = [[
                'asciimath'=>"root",
                'tex'=>"\\sqrt",
                'option'=>true
        ], [
            'asciimath'=>"frac",
            'tex'=>"\\frac"
        ], [
            'asciimath'=>"stackrel",
            'tex'=>"\\stackrel"
        ], [
            'asciimath'=>"overset",
            'tex'=>"\\overset"
        ], [
            'asciimath'=>"underset",
            'tex'=>"\\underset"
        ], [
            'asciimath'=>"color",
            'tex'=>"\\color",
            'rawfirst'=>true
        ]];
        $this->non_constant_symbols = ['_', '^', '/'];
    }

    private function sort_symbols() {
        $by_asciimath = function($a, $b) {
            $a = strlen($a['asciimath']); $b = strlen($b['asciimath']);
            return $b <=> $a; // spaceship
        };

        usort($this->constants, $by_asciimath);
        usort($this->relations, $by_asciimath);
        usort($this->left_brackets, $by_asciimath);
        usort($this->right_brackets, $by_asciimath);
        usort($this->leftright_brackets, $by_asciimath);
        usort($this->unary_symbols, $by_asciimath);
        usort($this->binary_symbols, $by_asciimath);
    }

    private function literal($token) {
        if ($token) {
            return [
                'tex'=>$token['token'],
                'pos'=>$token['pos'],
                'end'=>$token['end'],
                'ttype'=>"literal"
            ];
        }
    }

    private function longest($matches) {
        //$matches = array_filter($matches, function($x) { return (bool)$x; });
        usort($matches, function($x, $y) {
                $x = $x['end'];
                $y = $y['end'];
                return $y <=> $x;
                });
        return $matches[0];
    }

    private function escape_text($str) {
        return str_replace([ '{', '}' ], [ "\\{", "\\}" ], $str);
    }

    private function input($str) {
        $this->_source = $str;
        $this->brackets = [];
    }

    private function source($pos = 0, $end = null) {
        if ($end!==null) {
            return substr($this->_source, $pos, $end-$pos);
        } else {
            return substr($this->_source, $pos);
        }
    }

    private function eof($pos = 0) {
        $pos = $this->strip_space($pos);
        return $pos == strlen($this->_source);
    }

    private function unbracket($tok) {
        if (empty($tok)) {
            return null;
        }

        if (!isset($tok['bracket'])) {
            return $tok;
        }

        $skip_brackets = ['(', ')', '[', ']', '{', '}'];
        $skipleft = in_array($tok['left']['asciimath'], $skip_brackets, true);
        $skipright = in_array($tok['right']['asciimath'], $skip_brackets, true);
        $pos = $skipleft ? $tok['left']['end'] : $tok['pos'];
        $end = $skipright ? $tok['right']['pos'] : $tok['end'];
        $left = $skipleft ? '' : $tok['left']['tex'];
        $right = $skipright ? '' : $tok['right']['tex'];
        $middle = $tok['middle'] ? $tok['middle']['tex'] : '';

        if ($left || $right) {
            $left = $left ?? '.';
            $right = $right ?? '.';

            return [
                'tex'=>"\\left {$left} {$middle} \\right {$right}", // interpolation
                'pos'=>$tok['pos'],
                'end'=>$tok['end']
            ];
        } else {
            return [
                'tex'=>$middle,
                'pos'=>$tok['pos'],
                'end'=>$tok['end'],
                'middle_asciimath'=>$this->source($pos, $end)
            ];
        }
    }

    public function parse($str) {
        $this->input($str);
        $result = $this->consume();
        return $result['tex'];
    }

    private function consume($pos = 0) {
        $tex = '';
        $exprs = [];

        while (!$this->eof($pos)) {
            $expr = $this->expression_list($pos);

            if (empty($expr)) {
                $rb = $this->right_bracket($pos);

                if ($rb) {
                    if (isset($rb['def']['free_tex'])) {
                        $rb['tex'] = $rb['def']['free_tex'];
                    }

                    $expr = $rb;
                }

                $lr = $this->leftright_bracket($pos);

                if ($lr) {
                    $expr = $lr;
                    $ss = $this->subsup($lr['end']);

                    if ($ss) {
                        $expr = [
                            'tex'=>"{$expr['tex']}{$ss['tex']}", // interpolation
                            'pos'=>$pos,
                            'end'=>$ss['end'],
                            'ttype'=>"expression"
                        ];
                    }
                }
            }

            if ($expr) {
                if ($tex) {
                    $tex .= ' ';
                }

                $tex .= $expr['tex'];
                $pos = $expr['end'];
                $exprs[] = $expr;
            } elseif (!$this->eof($pos)) {
                $chr = $this->source($pos, $pos + 1);
                $exprs[] = [
                    'tex'=>$chr,
                    'pos'=>$pos,
                    'ttype'=>"character"
                ];
                $tex .= $chr;
                $pos += 1;
            }
        }

        return [
            'tex'=>$tex,
            'exprs'=>$exprs
        ];
    }

    private function strip_space($pos = 0) {
        $osource = $this->source($pos);
        $reduced = preg_replace('/^(\\s|\\\\(?!\\\\|\s))*/', '', $osource); // added |\s
        return $pos + strlen($osource) - strlen($reduced);
    }
    /* Does the given regex match next?
     */


    private function match($re, $pos) {
        $pos = $this->strip_space($pos);
        preg_match($re, $this->source($pos), $m);

        if ($m) {
            $token = $m[0];
            return [
                'token'=>$token,
                'pos'=>$pos,
                'match'=>$m,
                'end'=>$pos + strlen($token),
                'ttype'=>"regex"
            ];
        }
    }
    /* Does the exact given string occur next?
     */

    private function exact($str, $pos) {
        $pos = $this->strip_space($pos);

        if (substr($this->source($pos), 0, strlen($str)) == $str) {
            return [
                'token'=>$str,
                'pos'=>$pos,
                'end'=>$pos + strlen($str),
                'ttype'=>"exact"
            ];
        }
    }

    private function expression_list($pos = 0) {
        $expr = $this->expression($pos);

        if (!$expr) {
            return null;
        }

        $end = $expr['end'];
        $tex = $expr['tex'];
        $exprs = [$expr];

        while (!$this->eof($end)) {
            $comma = $this->exact(",", $end);

            if (!$comma) {
                break;
            }

            $tex .= ' ,';
            $end = $comma['end'];
            $expr = $this->expression($end);

            if (!$expr) {
                break;
            }

            $tex .= ' ' . $expr['tex'];
            $exprs[] = $expr;
            $end = $expr['end'];
        }
        return [
            'tex'=>$tex,
            'pos'=>$pos,
            'end'=>$end,
            'exprs'=>$exprs,
            'ttype'=>"expression_list"
        ];
    } // E ::= IE | I/I                       Expression


    private function expression($pos = 0) {
        $negative = $this->negative_expression($pos);

        if ($negative) {
            return $negative;
        }

        $first = $this->intermediate_or_fraction($pos);

        if (!$first) {
            foreach ($this->non_constant_symbols as $c) {
                $m = $this->exact($c, $pos);

                if ($m) {
                    return [
                        'tex'=>$c,
                        'pos'=>$pos,
                        'end'=>$m['end'],
                        'ttype'=>"constant"
                    ];
                }
            }

            return null;
        }

        if ($this->eof($first['end'])) {
            return $first;
        }

        $second = $this->expression($first['end']);

        if ($second) {
            return [
                'tex'=>$first['tex'] . ' ' . $second['tex'],
                'pos'=>$first['pos'],
                'end'=>$second['end'],
                'ttype'=>"expression",
                'exprs'=>[$first, $second]
            ];
        } else {
            return $first;
        }
    }

    private function negative_expression($pos = 0) {
        $dash = $this->exact("-", $pos);

        if ($dash && !$this->other_constant($pos)) {
            $expr = $this->expression($dash['end']);

            if ($expr) {
                return [
                    'tex'=>"- {$expr['tex']}", // interpolation
                    'pos'=>$pos,
                    'end'=>$expr['end'],
                    'ttype'=>"negative_expression",
                    'dash'=>$dash,
                    'expression'=>$expr
                ];
            } else {
                return [
                    'tex'=>"-",
                    'pos'=>$pos,
                    'end'=>$dash['end'],
                    'ttype'=>"constant"
                ];
            }
        }
    }

    private function intermediate_or_fraction($pos = 0) {
        $first = $this->intermediate($pos);

        if (!$first) {
            return null;
        }

        $frac = $this->match('/^\/(?!\/)/', $first['end']);

        if ($frac) {
            $second = $this->intermediate($frac['end']);

            if ($second) {
                $ufirst = $this->unbracket($first);
                $usecond = $this->unbracket($second);
                return [
                    'tex'=>"\\frac{{$ufirst['tex']}}{{$usecond['tex']}}", // interpolation
                    'pos'=>$first['pos'],
                    'end'=>$second['end'],
                    'ttype'=>"fraction",
                    'numerator'=>$ufirst,
                    'denominator'=>$usecond
                ];
            } else {
                $ufirst = $this->unbracket($first);
                return [
                    'tex'=>"\\frac{{$ufirst['tex']}}{}", // interpolation
                    'pos'=>$first['pos'],
                    'end'=>$frac['end'],
                    'ttype'=>"fraction",
                    'numerator'=>$ufirst,
                    'denominator'=>null
                ];
            }
        } else {
            return $first;
        }
    } // I ::= S_S | S^S | S_S^S | S          Intermediate expression


    private function intermediate($pos = 0) {
        $first = $this->simple($pos);

        if (!$first) {
            return null;
        }

        $ss = $this->subsup($first['end']);

        if ($ss) {
            return [
                'tex'=>"{$first['tex']}{$ss['tex']}", // interpolation
                'pos'=>$pos,
                'end'=>$ss['end'],
                'ttype'=>"intermediate",
                'expression'=>$first,
                'subsup'=>$ss
            ];
        } else {
            return $first;
        }
    }

    private function subsup($pos = 0) {
        $tex = '';
        $end = $pos;
        $sub = $this->exact('_', $pos);
        $sub_expr = null; $sup_expr = null;

        if ($sub) {
            $sub_expr = $this->unbracket($this->simple($sub['end']));

            if ($sub_expr) {
                $tex = "{$tex}_{{$sub_expr['tex']}}"; // interpolation
                $end = $sub_expr['end'];
            } else {
                $tex = "{$tex}_{}"; // interpolation
                $end = $sub['end'];
            }
        }

        $sup = $this->match('/^\^(?!\^)/', $end);

        if ($sup) {
            $sup_expr = $this->unbracket($this->simple($sup['end']));

            if ($sup_expr) {
                $tex = "{$tex}^{{$sup_expr['tex']}}"; // interpolation
                $end = $sup_expr['end'];
            } else {
                $tex = "{$tex}^{}"; // interpolation
                $end = $sup['end'];
            }
        }

        if ($sub || $sup) {
            return [
                'tex'=>$tex,
                'pos'=>$pos,
                'end'=>$end,
                'ttype'=>"subsup",
                'sub'=>$sub_expr,
                'sup'=>$sup_expr
            ];
        }
    } // S ::= v | lEr | uS | bSS             Simple expression


    private function simple($pos = 0) {
        return $this->longest([$this->matrix($pos), $this->bracketed_expression($pos), $this->binary($pos), $this->constant($pos), $this->text($pos), $this->unary($pos), $this->negative_simple($pos)]);
    }

    private function negative_simple($pos = 0) {
        $dash = $this->exact("-", $pos);

        if ($dash && !$this->other_constant($pos)) {
            $expr = $this->simple($dash['end']);

            if ($expr) {
                return [
                    'tex'=>"- {$expr['tex']}", // interpolation
                    'pos'=>$pos,
                    'end'=>$expr['end'],
                    'ttype'=>"negative_simple",
                    'dash'=>$dash,
                    'expr'=>$expr
                ];
            } else {
                return [
                    'tex'=>"-",
                    'pos'=>$pos,
                    'end'=>$dash['end'],
                    'ttype'=>"constant"
                ];
            }
        }
    } // 'matrix'=>leftbracket "(" $expr ")" ("," "(" $expr ")")* rightbracket 
    // each row must have the same number of elements


    private function matrix($pos = 0) {
        $left = $this->left_bracket($pos);
        $lr = false;

        if (!$left) {
            $left = $this->leftright_bracket($pos, 'left');

            if (!$left) {
                return null;
            }

            $lr = true;
        }

        $contents = $this->matrix_contents($left['end'], $lr);

        if (!$contents) {
            return null;
        }

        $right = $lr ? $this->leftright_bracket($contents['end'], 'right') : $this->right_bracket($contents['end']);

        if (!$right) {
            return null;
        }

        $contents_tex = implode(" \\\\ ", array_map(function($r) { return $r['tex']; }, $contents['rows']));
        $matrix_tex = $contents["is_array"] ? "\\begin{array}{{$contents['column_desc']}} {$contents_tex} \\end{array}" : "\\begin{matrix} {$contents_tex} \\end{matrix}"; // interpolation
        return [
            'tex'=>"\\left {$left['tex']} {$matrix_tex} \\right {$right['tex']}", // interpolation
            'pos'=>$pos,
            'end'=>$right['end'],
            'ttype'=>"matrix",
            'rows'=>$contents['rows'],
            'left'=>$left,
            'right'=>$right
        ];
    }

    private function matrix_contents($pos = 0, $leftright = false) {
        $rows = [];
        $end = $pos;
        $row_length = null;
        $column_desc = null;
        $is_array = false;

        while (!$this->eof($end) && !($leftright ? $this->leftright_bracket($end) : $this->right_bracket($end))) {
            if (count($rows)) {
                $comma = $this->exact(",", $end);

                if (!$comma) {
                    return null;
                }

                $end = $comma['end'];
            }

            $lb = $this->match('/^[(\[]/', $end);

                    if (!$lb) {
                    return null;
                    }

                    $cells = [];
                    $columns = [];
                    $end = $lb['end'];

                    while (!$this->eof($end)) {
                    if (count($cells)) {
                    $comma = $this->exact(",", $end);

                    if (!$comma) {
                    break;
                    }

                    $end = $comma['end'];
                    }

                    $cell = $this->matrix_cell($end);

                    if (!$cell) {
                        break;
                    }

                    if ($cell['ttype'] == 'column') {
                        $columns[] = '|';
                        $is_array = true;

                        if ($cell['expr'] !== null) {
                            $columns[] = 'r';
                            $cells[] = $cell['expr'];
                        }
                    } else {
                        $columns[] = 'r';
                        $cells[] = $cell;
                    }

                    $end = $cell['end'];
                    }

                    if (!count($cells)) {
                        return null;
                    }

                    if ($row_length === null) {
                        $row_length = count($cells);
                    } else if (count($cells) != $row_length) {
                        return null;
                    }

                    $rb = $this->match('/^[)\]]/', $end);

                    if (!$rb) {
                        return null;
                    }

                    $row_column_desc = implode('', $columns);

                    if ($column_desc === null) {
                        $column_desc = $row_column_desc;
                    } else if ($row_column_desc != $column_desc) {
                        return null;
                    }

                    $rows[] = [
                        'ttype'=>"row",
                        'tex'=>implode(' & ', array_map(function($c) { return $c['tex']; }, $cells)),
                        'pos'=>$lb['end'],
                        'end'=>$end,
                        'cells'=>$cells
                    ];
                        $end = $rb['end'];
        }

        if ($row_length === null || $row_length <= 1 && count($rows) <= 1) {
            return null;
        }

        return [
            'rows'=>$rows,
            'end'=>$end,
            'column_desc'=>$column_desc,
            'is_array'=>$is_array
        ];
    }

    private function matrix_cell($pos = 0) {
        $lvert = $this->exact('|', $pos);

        if ($lvert) {
            $middle = $this->expression($lvert['end']);

            if ($middle) {
                $rvert = $this->exact('|', $middle['end']);

                if ($rvert) {
                    $second = $this->expression($rvert['end']);

                    if ($second) {
                        return [
                            'tex'=>"\\left \\lvert {$middle['tex']} \\right \\rvert {$second['text']}", // interpolation
                            'pos'=>$lvert['pos'],
                            'end'=>$second['end'],
                            'ttype'=>"expression",
                            'exprs'=>[$middle, $second]
                        ];
                    }
                } else {
                    return [
                        'ttype'=>"column",
                        'expr'=>$middle,
                        'pos'=>$lvert['pos'],
                        'end'=>$middle['end']
                    ];
                }
            } else {
                return [
                    'ttype'=>"column",
                    'expr'=>null,
                    'pos'=>$lvert['pos'],
                    'end'=>$lvert['end']
                ];
            }
        }

        return $this->expression($pos);
    }

    private function bracketed_expression($pos = 0) {
        $l = $this->left_bracket($pos);

        if ($l) {
            $middle = $this->expression_list($l['end']);

            if ($middle) {
                $r = $this->right_bracket($middle['end']) ?? $this->leftright_bracket($middle['end'], 'right');
                if ($r) {
                    return [
                        'tex'=>"\\left{$l['tex']} {$middle['tex']} \\right {$r['tex']}", // interpolation
                        'pos'=>$pos,
                        'end'=>$r['end'],
                        'bracket'=>true,
                        'left'=>$l,
                        'right'=>$r,
                        'middle'=>$middle,
                        'ttype'=>"bracket"
                    ];
                } else if ($this->eof($middle['end'])) {
                    return [
                        'tex'=>"\\left{$l['tex']} {$middle['tex']} \\right.", // interpolation
                        'pos'=>$pos,
                        'end'=>$middle['end'],
                        'ttype'=>"bracket",
                        'left'=>$l,
                        'right'=>null,
                        'middle'=>$middle
                    ];
                } else {
                    return [
                        'tex'=>"{$l['tex']} {$middle['tex']}", // interpolation
                        'pos'=>$pos,
                        'end'=>$middle['end'],
                        'ttype'=>"expression",
                        'exprs'=>[$l, $middle]
                    ];
                }
            } else {
                $r = $this->right_bracket($l['end']) ?? $this->leftright_bracket($l['end'], 'right');
                if ($r) {
                    return [
                        'tex'=>"\\left {$l['tex']} \\right {$r['tex']}", // interpolation
                        'pos'=>$pos,
                        'end'=>$r['end'],
                        'bracket'=>true,
                        'left'=>$l,
                        'right'=>$r,
                        'middle'=>null,
                        'ttype'=>"bracket"
                    ];
                } else {
                    return [
                        'tex'=>$l['tex'],
                        'pos'=>$pos,
                        'end'=>$l['end'],
                        'ttype'=>"constant"
                    ];
                }
            }
        }

        if ($this->other_constant($pos)) {
            return null;
        }

        $left = $this->leftright_bracket($pos, 'left');

        if ($left) {
            $middle = $this->expression_list($left['end']);

            if ($middle) {
                $right = $this->leftright_bracket($middle['end'], 'right') ?? $this->right_bracket($middle['end']);
                if ($right) {
                    return [
                        'tex'=>"\\left {$left['tex']} {$middle['tex']} \\right {$right['tex']}", // interpolation
                        'pos'=>$pos,
                        'end'=>$right['end'],
                        'bracket'=>true,
                        'left'=>$left,
                        'right'=>$right,
                        'middle'=>$middle,
                        'ttype'=>"bracket"
                    ];
                }
            }
        }
    } // $r ::= ) | ] | } | :) | :} | other $right brackets


    private function right_bracket($pos = 0) {
        foreach ($this->right_brackets as $bracket) {
            $m = $this->exact($bracket['asciimath'], $pos);
    
            if ($m) {
                return [
                    'tex'=>$bracket['tex'],
                    'pos'=>$pos,
                    'end'=>$m['end'],
                    'asciimath'=>$bracket['asciimath'],
                    'def'=>$bracket,
                    'ttype'=>"right_bracket"
                ];
            }
        }
    } // $l ::= ( | [ | { | (=>| {=>| other $left brackets
    
    
    private function left_bracket($pos = 0) {
        foreach ($this->left_brackets as $bracket) {
            $m = $this->exact($bracket['asciimath'], $pos);
    
            if ($m) {
                return [
                    'tex'=>$bracket['tex'],
                    'pos'=>$pos,
                    'end'=>$m['end'],
                    'asciimath'=>$bracket['asciimath'],
                    'ttype'=>"left_bracket"
                ];
            }
        }
    }
    
    private function leftright_bracket($pos = 0, $position = null) {
        foreach ($this->leftright_brackets as $lr) {
            $b = $this->exact($lr['asciimath'], $pos);
    
            if ($b) {
                return [
                    'tex'=>$position == 'left' ? $lr['left_tex'] : ($position == 'right' ? $lr['right_tex'] : $lr['free_tex']),
                    'pos'=>$pos,
                    'end'=>$b['end'],
                    'ttype'=>"leftright_bracket"
                ];
            }
        }
    }
    
    private function text($pos = 0) {
        $quoted = $this->match('/^"([^"]*)"/', $pos);
    
        if ($quoted) {
            $text = $this->escape_text($quoted['match'][1]);
            return [
                'tex'=>"\\text{{$text}}", // interpolation
                'pos'=>$pos,
                'end'=>$quoted['end'],
                'ttype'=>"text",
                'text'=>$text
            ];
        }
    
        $textfn = $this->match('/^(?:mbox|text)\s*(\([^)]*\)?|\{[^}]*\}?|\[[^\]]*\]?)/', $pos);
    
        if ($textfn) {
            $text = $this->escape_text(substr($textfn['match'][1], 1, strlen($textfn['match'][1]) - 2));
            return [
                'tex'=>"\\text{{$text}}", // interpolation
                'pos'=>$pos,
                'end'=>$textfn['end'],
                'ttype'=>"text",
                'text'=>$text
            ];
        }
    } // $b ::= $frac | root | stackrel | other $binary symbols
    
    
    private function binary($pos = 0) {
        foreach ($this->binary_symbols as $binary) {
            $m = $this->exact($binary['asciimath'], $pos);
            list($lb1, $rb1) = isset($binary['option']) ? ['[', ']'] : ['{', '}'];
    
            if ($m) {
                $a = $this->unbracket($this->simple($m['end']));
    
                if ($a) {
                    $atex = isset($binary['rawfirst']) ? $a['middle_asciimath'] : $a['tex'];
                    $b = $this->unbracket($this->simple($a['end']));
    
                    if ($b) {
                        return [
                            'tex'=>"{$binary['tex']}{$lb1}{$atex}{$rb1}{{$b['tex']}}", // interpolation
                            'pos'=>$pos,
                            'end'=>$b['end'],
                            'ttype'=>"binary",
                            'op'=>$binary,
                            'arg1'=>$a,
                            'arg2'=>$b
                        ];
                    } else {
                        return [
                            'tex'=>"{$binary['tex']}{$lb1}{$atex}{$rb1}{}", // interpolation
                            'pos'=>$pos,
                            'end'=>$a['end'],
                            'ttype'=>"binary",
                            'op'=>$binary,
                            'arg1'=>$a,
                            'arg2'=>null
                        ];
                    }
                } else {
                    return [
                        'tex'=>"{$binary['tex']}{$lb1}{$rb1}{}", // interpolation
                        'pos'=>$pos,
                        'end'=>$m['end'],
                        'ttype'=>"binary",
                        'op'=>$binary,
                        'arg1'=>null,
                        'arg2'=>null
                    ];
                }
            }
        }
    } // $u ::= sqrt | $text | bb | other unary symbols for font commands
    
    
    private function unary($pos = 0) {
        foreach ($this->unary_symbols as $u) {
            $m = $this->exact($u['asciimath'], $pos);
    
            if ($m) {
                $ss = $this->subsup($m['end']);
                $sstex = $ss ? $ss['tex'] : '';
                $end = $ss ? $ss['end'] : $m['end'];
                $barg = $this->simple($end);
                $arg = isset($u['func']) ? $barg : $this->unbracket($barg);
                $argtex = $arg ? (isset($u['raw']) ? $arg['middle_asciimath'] : $arg['tex']) : null;
    
                if (isset($u['rewriteleftright'])) {
                    list($left, $right) = $u['rewriteleftright'];
    
                    if ($arg) {
                        return [
                            'tex'=>"\\left {$left} {$argtex} \\right {$right} {$sstex}", // interpolation
                            'pos'=>$pos,
                            'end'=>$arg['end'],
                            'ttype'=>"unary",
                            'op'=>$m,
                            'subsup'=>$ss,
                            'arg'=>$arg
                        ];
                    } else {
                        return [
                            'tex'=>"\\left {$left} \\right {$right} {$sstex}", // interpolation
                            'pos'=>$pos,
                            'end'=>$m['end'],
                            'ttype'=>"unary",
                            'op'=>$m,
                            'subsup'=>$ss,
                            'arg'=>null
                        ];
                    }
                } else {
                    if ($arg) {
                        return [
                            'tex'=>"{$u['tex']}{$sstex}{{$argtex}}", // interpolation
                            'pos'=>$pos,
                            'end'=>$arg['end'],
                            'ttype'=>"unary",
                            'op'=>$m,
                            'subsup'=>$ss,
                            'arg'=>$arg
                        ];
                    } else {
                        return [
                            'tex'=>"{$u['tex']}{$sstex}{}", // interpolation
                            'pos'=>$pos,
                            'end'=>$m['end'],
                            'ttype'=>"unary",
                            'op'=>$m,
                            'subsup'=>$ss,
                            'arg'=>null
                        ];
                    }
                }
            }
        }
    } // v ::= [A-Za-z] | greek letters | numbers | other constant symbols
    
    
    private function constant($pos = 0) {
        if ($this->right_bracket($pos)) {
            return null;
        }
    
        return $this->longest([$this->other_constant($pos), $this->greek($pos), $this->name($pos), $this->number($pos), $this->arbitrary_constant($pos)], $this->scientific($pos));
    }
    
    private function name($pos = 0) {
        return $this->literal($this->match('/^[A-Za-z]/', $pos));
    }
    
    private function greek($pos = 0) {
        $re_greek = '/^(' . implode('|', $this->greek_letters) . ')/';
        $m = $this->match($re_greek, $pos);
    
        if ($m) {
            return [
                'tex'=>"\\" . $m['token'],
                'pos'=>$pos,
                'end'=>$m['end'],
                'ttype'=>"greek"
            ];
        }
    }
    
    private function number($pos = 0) {
        $re_number = '/^\\d+(' . preg_quote($this->decimalsign, '/') . '\\d+)?/';
        return $this->literal($this->match($re_number, $pos));
    }
    
    private function other_constant($pos = 0) {
        foreach ($this->constants as $sym) {
            $m = $this->exact($sym['asciimath'], $pos);
    
            if ($m) {
                return [
                    'tex'=>"{$sym['tex']}", // interpolation
                    'pos'=>$m['pos'],
                    'end'=>$m['end'],
                    'ttype'=>"other_constant"
                ];
            }
        }
    
        foreach ($this->relations as $sym) {
            if (!preg_match('/^!/', $sym['asciimath'])) {
                $notm = $this->exact('!' . $sym['asciimath'], $pos);
    
                if ($notm) {
                    return [
                        'tex'=>"\\not {$sym['tex']}", // interpolation
                        'pos'=>$notm['pos'],
                        'end'=>$notm['end'],
                        'ttype'=>"other_constant"
                    ];
                }
            }
        }
    }
    
    private function arbitrary_constant($pos = 0) {
        if (!$this->eof($pos)) {
            if ($this->exact(",", $pos)) {
                return null;
            }
    
            foreach (array_merge($this->non_constant_symbols, array_map(function ($x) { return $x['asciimath']; }, $this->left_brackets), array_map(function($x) { return $x['asciimath']; }, $this->right_brackets), array_map(function($x) { return $x['asciimath']; }, $this->leftright_brackets)) as $nc) {
                if ($this->exact($nc, $pos)) {
                    return null;
                }
            }
    
            $spos = $this->strip_space($pos);
            $symbol = substr($this->source($spos), 0, 1);
            return [
                'tex'=>$symbol,
                'pos'=>$pos,
                'end'=>$spos + 1,
                'ttype'=>"arbitrary_constant"
            ];
        }
    }

    private function scientific($pos = 0) {
        $re_science = '/^(\\d+(?:' . preg_quote($this->decimalsign, '/') . '\d+)?)E(-?\\d+(?:' . preg_quote($this->decimalsign, '/') . '\\d+)?)/';
        $m = $this->match($re_science, $pos);
        if($m) {
            return [
               "tex"=>"{$m['match'][1]} \\times 10^{$m['match'][2]}",
               "pos"=>$m["pos"],
               "end"=>$m["end"]
            ];
        }
    }
}
