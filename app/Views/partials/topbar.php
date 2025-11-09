<div id="topbar" class="flex items-center justify-between px-4 py-3 border-b border-gray-300 bg-white">
  <div class="flex items-center gap-3">
    <button id="back-btn" class="md:hidden text-gray-600 hover:text-green-600">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
           stroke-width="2" stroke="currentColor" class="w-6 h-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
      </svg>
    </button>

    <img id="chat-avatar"
         src="https://ui-avatars.com/api/?name=Chat"
         alt="avatar"
         class="w-10 h-10 rounded-full border border-gray-200">

    <div>
      <div id="chat-name" class="font-semibold text-gray-800">Select a chat</div>
      <div id="chat-status" class="text-sm text-gray-500">Offline</div>
    </div>
  </div>

  <div class="flex gap-4 text-gray-600">
    <button id="info-btn" class="hover:text-green-600" title="Chat info">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
           stroke-width="2" stroke="currentColor" class="w-5 h-5">
        <path stroke-linecap="round" stroke-linejoin="round"
              d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z"/>
      </svg>
    </button>
  </div>
</div>
