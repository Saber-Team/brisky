{{include file="header.tpl"}}

<head>
    <meta charset="UTF-8">
    {{brisk_require_css name="../static/css/app.css"}}
    <title>brisk demo111</title>
    {{brisk_style}}
    ol {
    color: red;
    }
    {{/brisk_style}}
    <!--[CSS_HOOK]-->
    {{brisk_script}}
    var bbb = 200;
    {{/brisk_script}}
    {{brisk_require_js name="../static/lib/quickling.js"}}
</head>
<body>

{{brisk_widget name="../widget/index_content.tpl" pagelet="content"}}

</body>
<!--[JS_HOOK]-->
{{brisk_require_js name="../static/js/index.js"}}
{{brisk_page_flush}}
{{include file="footer.tpl"}}