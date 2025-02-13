<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ json_decode($formData)->title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    @vite('resources/js/app.js')
</head>
<body class="bg-gray-100">
    <div id="app" class="container mx-auto p-4">
        <published-form :form-data='{{ $formData }}'></published-form>
    </div>
</body>
</html>