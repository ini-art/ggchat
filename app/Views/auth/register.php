<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">
  <div class="bg-white p-8 rounded-2xl shadow-md w-full max-w-md">
    <h1 class="text-2xl font-bold text-center mb-6">Register</h1>
    <?php if(session()->getFlashdata('error')): ?>
      <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
        <?= session()->getFlashdata('error') ?>
      </div>
    <?php endif; ?>
    <form action="<?= site_url('auth/register') ?>" method="POST" class="space-y-4">
      <?= csrf_field() ?>
      <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Nama</label>
        <input type="text" name="name" id="name" required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2" />
      </div>
      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input type="email" name="email" id="email" required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2" />
      </div>
      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input type="password" name="password" id="password" required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2" />
      </div>
      <div>
        <label for="confirm_password" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2" />
      </div>
      <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition">Register</button>
    </form>

    <p class="text-center text-sm text-gray-600 mt-4">Sudah punya akun?
    <a href="<?= site_url('/') ?>" class="text-indigo-600 hover:text-indigo-800">Login</a>
    </p>
  </div>
</body>
</html>