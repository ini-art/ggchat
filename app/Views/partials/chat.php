<div id="chat-container"
     class="flex-1 flex flex-col bg-white md:translate-x-0 translate-x-full fixed md:static inset-0 md:z-auto z-10">

  <?= view('partials/topbar') ?>

  <div id="chat-box" class="flex-1 overflow-y-auto p-4 space-y-2 bg-gray-50"></div>

  <form id="chat-form" class="p-3 border-t border-gray-300 flex bg-white">
    <?= csrf_field() ?>
    <input type="hidden" name="receiver_id" id="receiver_id">
    <input id="message-input" name="message" class="flex-1 border rounded p-2" placeholder="Type a message...">
    <button type="submit" class="ml-2 bg-green-500 text-white px-4 py-2 rounded">Send</button>
  </form>
</div>
