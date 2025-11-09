<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">

    <div class="w-full max-w-md bg-white p-8 rounded-2xl shadow-lg">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= session()->getFlashdata('error') ?>
            </div>
        <?php elseif(session()->getFlashdata('success')): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= session()->getFlashdata('success') ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= base_url('/auth/login') ?>">
            <?= csrf_field() ?>

            <div class="mb-4">
                <label class="block mb-1 font-medium text-gray-700">Email</label>
                <input type="email" name="email" class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <div class="mb-6">
                <label class="block mb-1 font-medium text-gray-700">Password</label>
                <input type="password" name="password" class="w-full border border-gray-300 rounded px-3 py-2" required>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Login</button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Belum punya akun?
            <a href="<?= base_url('/auth/register') ?>" class="text-blue-600 hover:underline">Daftar</a>
        </p>
    </div>

</body>
</html>
