"use strict";
document.addEventListener("DOMContentLoaded", function() {
    var inlineExpressions = document.querySelectorAll("span.math");
    inlineExpressions.forEach(function(inlineExpression, i) {
        katex.render(inlineExpression.textContent, inlineExpression, { displayMode: false, throwOnError: false });
    });
    var blockExpressions = document.querySelectorAll("div.math");
    blockExpressions.forEach(function(blockExpression, i) {
        katex.render(blockExpression.textContent, blockExpression, { displayMode: true, throwOnError: false, fleqn: true });
    });

}, false);
