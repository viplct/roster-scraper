<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@latest/swagger-ui.css">
    <script src="https://unpkg.com/swagger-ui-dist@latest/swagger-ui-bundle.js"></script>
</head>
<body>
<div id="swagger-ui"></div>
<script>
    window.onload = function () {
        window.ui = SwaggerUIBundle({
            dom_id: '#swagger-ui',
            deepLinking: true,
            presets: [
                SwaggerUIBundle.presets.apis,
            ],
            layout: 'BaseLayout',
            spec: {!! file_get_contents(resource_path('oa.json')) !!}
        });
    };
</script>
</body>
</html>
