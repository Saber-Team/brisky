{{include file="./header.tpl"}}

<head>
    <meta charset="UTF-8">
    {{brisk_require_css name="CsP2r"}}
    <title>brisk demo</title>
    <!--[CSS_HOOK]-->
    {{brisk_require_js name="quickling"}}
</head>
<body>

{{brisk_widget name="src/widget/detail_content.tpl" path="../widget/detail_content.tpl" pagelet="content"}}

</body>
<!--[JS_HOOK]-->
{{brisk_page_flush}}

{{include file="./footer.tpl"}}