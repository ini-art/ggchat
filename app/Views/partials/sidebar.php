<div id="sidebar"
     class="w-full md:w-1/3 lg:w-1/4 bg-white border-r border-gray-300 flex flex-col fixed md:static inset-0 md:translate-x-0 translate-x-0 md:z-auto z-20">

  <div class="p-4 flex items-center justify-between border-b border-gray-200 bg-white sticky top-0">
    <h2 class="font-semibold text-lg"><?= esc($user['name']) ?></h2>
    <a href="<?= base_url('logout') ?>" class="text-red-500 hover:text-red-600">Logout</a>
  </div>

  <div id="chat-list" class="overflow-y-auto flex-1">
    <?php foreach ($users as $u): ?>
      <div class="p-4 hover:bg-gray-100 cursor-pointer chat-item border-b border-gray-100"
           data-id="<?= $u['id'] ?>"
           data-name="<?= esc($u['name']) ?>"
           data-email="<?= esc($u['email']) ?>">
        <div class="font-medium"><?= esc($u['name']) ?></div>
        <div class="text-sm text-gray-500"><?= esc($u['email']) ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
