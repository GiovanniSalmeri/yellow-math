"use strict";
document.addEventListener("DOMContentLoaded", function() {
    // Prevent line break between inline math and subsequent chars (e.g. punctuation)
    var inlineExpressions = document.querySelectorAll("span.math");
    inlineExpressions.forEach(function(inlineExpression, i) {
        var next = inlineExpression.nextSibling;
        if (next && next.nodeType==Node.TEXT_NODE) {
            var parts = next.textContent.match(/^(\S+)(.*)$/);
            if (parts) {
                next.textContent = parts[2];
                var span = document.createElement("SPAN");
                span.style.whiteSpace = "nowrap";
                span.append(inlineExpression.cloneNode(true), parts[1]);
		inlineExpression.parentNode.replaceChild(span, inlineExpression);
            }
        }
    });
    // Call KaTeX
    var inlineExpressions = document.querySelectorAll("span.math");
    inlineExpressions.forEach(function(inlineExpression) {
        katex.render(inlineExpression.textContent, inlineExpression, { displayMode: false, throwOnError: false });
    });
    var blockExpressions = document.querySelectorAll("div.math");
    blockExpressions.forEach(function(blockExpression) {
        katex.render(blockExpression.textContent, blockExpression, { displayMode: true, throwOnError: false, fleqn: true });
    });
}, false);
