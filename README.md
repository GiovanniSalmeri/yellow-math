# Math 0.9.3

Mathematical expressions with TeX/LaTeX.

<p align="center"><img src="SCREENSHOT.png" alt="Screenshot"></p>

## How to install an extension

[Download ZIP file](https://github.com/GiovanniSalmeri/yellow-math/archive/refs/heads/main.zip) and copy it into your `system/extensions` folder. [Learn more about extensions](https://github.com/annaesvensson/yellow-update).

## How to show a mathematical expression

With this extension you can write mathematical expressions with Tex/LaTeX. [TeX/LaTeX](https://en.wikibooks.org/wiki/LaTeX/Mathematics) is a rich and specialised system for writing mathematics. [KaTeX](https://katex.org/) is used for rendering the expressions.

The first option is to write a mathematical expression with a shortcut. Create a `[math]` shortcut. Right brackets `]` are not allowed inside a shortcut: use instead `\rbrack`, or `&rbrack;` when delimiting an optional argument.

The second option is to write a mathematical expression with a code block. Wrap the whole expression in `` ``` `` and add the identifier `math`.

## How to cross-refer to a mathematical expression

Write the expression with a code block and add after the identifier a label prefixed by `#` and wrapped in braces `{}`. The expression will be automatically numbered.

To refer to an expression, use the label prefixed by `#` and wrapped in brackets `[]`.

When using this method, avoid LaTeX's automatic numbering (e.g. use `gather*`, `align*`, `alignat*` instead of `gather`, `align`, `alignat`; `equation*` can be simply omitted).

## Examples

An expression using a shortcut:

    [math \pi = 3.1415926]
    [math \Delta = b^2 - 4ac]
    [math \sqrt[3&rbrack;{27} = 3]

An expression using a code block:

    ``` math
    ax^2 + bx + c = 0
    ```

    ``` math
    x_{1,2} = \frac{-b \pm \sqrt{b^2-4ac}}{2a}
    ```

An expression using a code block, numbered:

    ``` math {#quad}
    ax^2 + bx + c = 0
    ```

    ``` math {#solutions}
    x_{1,2} = \frac{-b \pm \sqrt{b^2-4ac}}{2a}
    ```

A reference to an expression:

    The solutions of the quadratic equation [#quad] are given by [#solutions].

More examples:

    [math \mathfrak{A}_{\alpha+\beta}^{\gamma+\delta}]

    [math \int_0^1\sin x\,dx]

    ``` math
    \begin{align*}
    \sum_{k=1}^5(k^2+2) & = (1^2+2)+(2^2+2)+(3^2+2)+(4^2+2)+(5^2+2)\\
    & = 3+6+11+18+27\\
    & = 65.
    \end{align*}
    ```

    ``` math
    \Re{z} =\frac{n\pi \dfrac{\theta +\psi}{2}}{
    \left(\dfrac{\theta +\psi}{2}\right)^2 + \left( \dfrac{1}{2}
    \log \left\lvert\dfrac{B}{A}\right\rvert\right)^2}.
    ```

    [math 2^k-\binom{k}{1}2^{k-1}+\binom{k}{2}2^{k-2}]

    ``` math
    \cfrac{1}{\sqrt{2}+
    \cfrac{1}{\sqrt{2}+
    \cfrac{1}{\sqrt{2}+\dotsb
    }}}
    ```

    [math \lim_{x \to +\infty}, \inf_{x > s} \text{ and } \sup_K]

    [math \sum_{k=1}^n k^2 = \frac{1}{2} n (n+1).]

    [math \int_0^4 x^2+2x-\frac{1}{2}x\,dx]

    ``` math
    \int\limits_{x^2 + y^2 \leq R^2} f(x,y)\,dx\,dy
    = \int_{\theta=0}^{2\pi} \int_{r=0}^R
    f(r\cos\theta,r\sin\theta) r\,dr\,d\theta
    ```

    ``` math
    \left(\begin{matrix}
    a & b & c \\
    d & e & f \\
    g & h & i
    \end{matrix}\right)
    ```

## Acknowledgements

The extension includes [KaTeX 0.16.10](https://github.com/KaTeX/KaTeX) by Emily Eisenberg, Sophie Alpert and other. Thank you for the good work. Most examples come from [Writing Mathematics in LaTeX by Example](https://euclid.colorado.edu/~hilljb/latex/intro.to.latex.pdf) by Jason B. Hill. Thank you for the helpful tutorial.

## Developer

Giovanni Salmeri. [Get help](https://datenstrom.se/yellow/help/).
