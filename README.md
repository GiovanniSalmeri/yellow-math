Math 0.8.18
===========
Mathematical expressions.

<p align="center"><img src="math-screenshot.png?raw=true" alt="Screenshot"></p>

## How to install an extension

[Download ZIP file](https://github.com/GiovanniSalmeri/yellow-math/archive/main.zip) and copy it into your `system/extensions` folder. [Learn more about extensions](https://github.com/annaesvensson/yellow-update).

## How to show a mathematical expression

You can write mathematical expressions with AsciiMath or Tex. [AsciiMath](http://asciimath.org/) is a very simple mathematical markup language, analogous to Markdown. [TeX and LaTeX](https://en.wikibooks.org/wiki/LaTeX/Mathematics) are rich and specialised systems for writing mathematics.

The first option is to write a mathematical expression with a shortcut. Create a `[math]` shortcut for AsciiMath or a `[mathtex]` shortcut for TeX/LaTeX. Wrap the whole expression in quotes if there are spaces, use `rbrack` instead of `]`.

The second option is to write a mathematical expression with a code block. Wrap the whole expression in \`\`\`. Add the language identifier `math` for AsciiMath or `mathtex` for TeX/LaTeX.

## Examples

An expression in AsciiMath with a shortcut:

    [math pi=3.1415926]
    [math x=(-b+-sqrt(b^2-4ac))/(2a)]
    [math "sum_(i=1)^n i^3=((n(n+1))/2)^2"]

An expression in TeX/LaTeX with a shortcut:

    [mathtex x=\frac{-b\pm\sqrt{b^2-4ac}}{2a}]

An expression in AsciiMath with a code block:

    ``` math
    x=(-b+-sqrt(b^2-4ac))/(2a)
    ```

An expression in TeX/LaTeX with a code block:

    ``` mathtex
    \Re{z} =\frac{n\pi \dfrac{\theta +\psi}{2}}{
    \left(\dfrac{\theta +\psi}{2}\right)^2 + \left( \dfrac{1}{2}
    \log \left\lvert\dfrac{B}{A}\right\rvert\right)^2}.
    ```

## Acknowledgements

This extension is based on the original [asciimath2tex](https://github.com/christianp/asciimath2tex) by Christian Lawson-Perfect for converting AsciiMath to TeX. The extension includes [KaTeX 0.15.1](https://github.com/KaTeX/KaTeX) by Emily Eisenberg, Sophie Alpert and other for displaying mathematical expressions. Thank you for the good work.

## Developer

Giovanni Salmeri. [Get help](https://datenstrom.se/yellow/help/).
