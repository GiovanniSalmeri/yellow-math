Math 0.8.18
===========
Display mathematical expressions.

<p align="center"><img src="math-screenshot.png?raw=true" width="795" height="836" alt="Screenshot"></p>

## How to add a mathematical expression

You can write mathematical expressions with AsciiMath or Tex. [AsciiMath](http://asciimath.org/) is a very simple mathematical markup language similar to Markdown. [TeX and LaTeX](https://en.wikibooks.org/wiki/LaTeX/Mathematics) are rich and specialised systems for writing mathematics.

Create a `[math]` shortcut (for AsciiMath) or a `[mathtex]` shortcut (for TeX/LaTeX).

The following mandatory argument is available:

`Expression` = mathematical expression; wrap into quotes if there are spaces, use `rbrack` for `]`  

You can also write a mathematical expressions as a code block in markdown, with the attribute `math` or `mathtex`.

## Examples

Displaying expressions with a shortcut:

    [math pi=3.1415926]
    [math x=(-b+-sqrt(b^2-4ac))/(2a)]
    [math "sum_(i=1)^n i^3=((n(n+1))/2)^2"]
    [mathtex x=\frac{-b\pm\sqrt{b^2-4ac}}{2a}]

Displaying an expression in AsciiMath with a code block:

    ```math
    x=(-b+-sqrt(b^2-4ac))/(2a)
    ```

Displaying an expression in TeX with a code block:

    ```mathtex
    \Re{z} =\frac{n\pi \dfrac{\theta +\psi}{2}}{
    \left(\dfrac{\theta +\psi}{2}\right)^2 + \left( \dfrac{1}{2}
    \log \left\lvert\dfrac{B}{A}\right\rvert\right)^2}.
    ```

## Settings

The following setting can be configured in file `system/extensions/yellow-system.ini`:

`MathDecimal` (default = `.`) = decimal separator  

The following setting can be configured at [the top of a page](https://github.com/datenstrom/yellow-extensions/tree/master/source/core#settings):

`MathPlainCode` (default = `none`) = parse as mathematical expression also inline code enclosed in `` ` ``; possible values `math`, `tex`  

## Installation

[Download extension](https://github.com/GiovanniSalmeri/yellow-math/archive/master.zip) and copy zip file into your `system/extensions` folder. Right click if you use Safari.

This extension uses a class derived from [asciimath2tex](https://github.com/christianp/asciimath2tex) by Christian Lawson-Perfect for translating ASCIImath in TeX, and [KaTeX](https://katex.org/) by Emily Eisenberg, Sophie Alpert and other for displaying the expressions.

## Developer

Giovanni Salmeri. [Get help](https://github.com/GiovanniSalmeri/yellow-music/issues).
