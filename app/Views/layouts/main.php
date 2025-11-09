<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>GGChat</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    /* Tambahan kecil agar smooth */
    #sidebar { transition: transform 0.3s ease; }
    #chat-container { transition: transform 0.3s ease; }
  </style>
</head>
<body class="bg-gray-100 h-screen overflow-hidden">
  <?= $this->renderSection('content') ?>
</body>
</html>
