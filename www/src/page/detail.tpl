{{include file="header.tpl"}}

<head>
    <meta charset="UTF-8">
    {{brisk_require_css name="../static/css/app.css"}}
    <title>brisk demo</title>
    <!--[CSS_HOOK]-->
    {{brisk_require_js name="../static/lib/quickling.js"}}
</head>
<body>

{{brisk_widget name="../widget/detail_content.tpl" pagelet="content"}}

</body>
<!--[JS_HOOK]-->
{{brisk_page_flush}}

{{include file="footer.tpl"}}