<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="flex h-screen relative">
  <?= view('partials/sidebar', ['users' => $users]) ?>
  <?= view('partials/chat') ?>
</div>

<script>
let activeReceiver = null;
let pollingInterval = null;

$('.chat-item').click(function() {
  activeReceiver = $(this).data('id');
  const name = $(this).data('name');

  $('#receiver_id').val(activeReceiver);
  $('#chat-name').text(name);
  $('#chat-status').text('Online');
  $('#chat-avatar').attr('src', 'https://ui-avatars.com/api/?name=' + encodeURIComponent(name));

  // Responsif: pindah ke tampilan chat
  $('#sidebar').addClass('translate-x-[-100%]');
  $('#chat-container').removeClass('translate-x-full');

  loadMessages();
  if (pollingInterval) clearInterval(pollingInterval);
  pollingInterval = setInterval(loadMessages, 3000);
});

$('#back-btn').click(function() {
  // Kembali ke sidebar (mobile)
  $('#sidebar').removeClass('translate-x-[-100%]');
  $('#chat-container').addClass('translate-x-full');
});

$('#chat-form').submit(function(e) {
  e.preventDefault();
  const msg = $('#message-input').val();
  if (!msg.trim() || !activeReceiver) return;

  $.post('<?= base_url('chat/sendMessage') ?>', {
    receiver_id: activeReceiver,
    message: msg,
    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
  }, function() {
    $('#message-input').val('');
    loadMessages();
  });
});

function loadMessages() {
  if (!activeReceiver) return;
  $.get('<?= base_url('chat/getMessages/') ?>' + activeReceiver, function(data) {
    let html = '';
    data.forEach(msg => {
      const align = msg.sender_id == <?= json_encode($user['id']) ?> ? 'text-right' : 'text-left';
      const bubble = msg.sender_id == <?= json_encode($user['id']) ?> ? 'bg-green-500 text-white' : 'bg-gray-200';
      html += `<div class="${align}"><div class="inline-block ${bubble} rounded-xl px-4 py-2 my-1">${msg.message}</div></div>`;
    });
    $('#chat-box').html(html);
    $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight);
  });
}
</script>

<?= $this->endSection() ?>
