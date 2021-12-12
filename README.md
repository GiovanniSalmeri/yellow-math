Math 0.8.18
===========
Display mathematical expressions.

<p align="center"><img src="math-screenshot.png?raw=true" width="795" height="836" alt="Screenshot"></p>

## How to add mathematical expressions

Create a `[math]` shortcut.

The following arguments are available, all but the first argument are optional:

`Expression` = mathematical expression; wrap into quotes if there are spaces, use `\rbrack` for unbalanced `]`  
`Markup` (default: `asciimath`) = markup language used: `asciimath` or `tex`  

You can also write a mathematical expressions as a code block in markdown, with the attribute `asciimath` or `tex`.

[Asciimath](http://asciimath.org/) is a very simple mathematical markup language similar to Markdown. [TeX and LaTeX](https://en.wikibooks.org/wiki/LaTeX/Mathematics) are rich and specialised systems for writing mathematics.

## Examples

Displaying expressions with a shortcut:

    [math pi=3.1415926]
    [math x=(-b+-sqrt(b^2-4ac))/(2a)]
    [math "sum_(i=1)^n i^3=((n(n+1))/2)^2"]
    [math x=\frac{-b\pm\sqrt{b^2-4ac}}{2a} tex]

Displaying expressions with a code block:

    ```asciimath
    x=(-b+-sqrt(b^2-4ac))/(2a)
    ```

    ```tex
    \Re{z} =\frac{n\pi \dfrac{\theta +\psi}{2}}{
    \left(\dfrac{\theta +\psi}{2}\right)^2 + \left( \dfrac{1}{2}
    \log \left\lvert\dfrac{B}{A}\right\rvert\right)^2}.
    ```

## Settings

The following setting can be configured in file `system/extensions/yellow-system.ini`:

`MathDecimal` (default = `.`) = decimal separator  

## Installation

[Download extension](https://github.com/GiovanniSalmeri/yellow-math/archive/master.zip) and copy zip file into your `system/extensions` folder. Right click if you use Safari.

This extension uses a class derived from [asciimath2tex](https://github.com/christianp/asciimath2tex) by Christian Lawson-Perfect for translating ASCIImath in TeX, and [KaTeX](https://katex.org/) by Emily Eisenberg, Sophie Alpert and other for displaying the expressions.

## Developer

Giovanni Salmeri. [Get help](https://github.com/GiovanniSalmeri/yellow-table/issues).
