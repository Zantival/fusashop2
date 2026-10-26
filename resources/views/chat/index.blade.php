@extends('layouts.app')
@section('title', 'Mis Conversaciones')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
  <div class="flex items-center justify-between mb-8">
    <h1 class="text-2xl font-black text-on-surface tracking-tight">Mensajes</h1>
    <span class="text-xs font-bold text-on-surface-variant bg-surface-container px-3 py-1 rounded-full">{{ $conversations->count() }} conversaciones</span>
  </div>

  @if($conversations->isEmpty())
    <div id="empty-conversations" class="text-center py-20 bg-white rounded-3xl border-2 border-dashed border-surface-container-highest">
      <div class="w-16 h-16 bg-surface-container-low rounded-2xl flex items-center justify-center mx-auto mb-4">
        <span class="material-symbols-outlined text-3xl text-on-surface-variant/40">forum</span>
      </div>
      <h2 class="font-bold text-on-surface mb-2">Sin conversaciones aún</h2>
      <p class="text-sm text-on-surface-variant max-w-xs mx-auto">Inicia un chat desde la página de un producto para hablar con un comerciante.</p>
      <a href="{{ route('consumer.catalog') }}" class="mt-6 inline-flex items-center gap-2 px-5 py-2.5 bg-greenhouse-gradient text-white text-sm font-bold rounded-xl shadow-sm">
        <span class="material-symbols-outlined text-[18px]">explore</span> Explorar productos
      </a>
    </div>
  @else
    <div id="conversation-list" class="space-y-2">
      @foreach($conversations as $otherUserId => $msgs)
        @php
          $lastMsg = $msgs->last();
          $otherUser = $lastMsg->sender_id == auth()->id() ? $lastMsg->receiver : $lastMsg->sender;
          $unread = $msgs->where('sender_id', $otherUserId)->where('is_read', false)->count();
        @endphp
        <a href="{{ route('chat.show', $otherUserId) }}" data-conversation-id="{{ $otherUserId }}"
           class="flex items-center gap-4 p-4 bg-white rounded-2xl border border-surface-container hover:border-primary/30 hover:shadow-md transition-all group">
          <!-- Avatar -->
          <div class="conversation-avatar relative shrink-0">
            @if($otherUser->avatar)
              <img loading="lazy" decoding="async" src="{{ $otherUser->avatarUrl() }}" alt="{{ $otherUser->name }}" class="w-12 h-12 rounded-full object-cover">
            @else
              <div class="w-12 h-12 bg-greenhouse-gradient rounded-full flex items-center justify-center text-white font-bold text-lg">
                {{ strtoupper(substr($otherUser->name, 0, 1)) }}
              </div>
            @endif
            @if($unread > 0)
              <span class="absolute -top-1 -right-1 w-5 h-5 bg-[#ba1a1a] text-white text-[9px] font-black rounded-full flex items-center justify-center border-2 border-white">{{ $unread }}</span>
            @endif
          </div>

          <!-- Info -->
          <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-0.5">
              <p class="conversation-name font-bold text-on-surface {{ $unread > 0 ? '' : 'font-semibold' }}">{{ e($otherUser->name) }}</p>
              <span class="conversation-time text-[10px] text-on-surface-variant/60 font-medium shrink-0">{{ $lastMsg->created_at->diffForHumans(null, true) }}</span>
            </div>
            <p class="conversation-preview text-xs text-on-surface-variant truncate {{ $unread > 0 ? 'font-semibold text-on-surface' : '' }}">
              @if($lastMsg->sender_id == auth()->id()) Tú: @endif
              {{ Str::limit(e($lastMsg->content), 50) }}
            </p>
            <span class="conversation-role text-[9px] font-bold text-primary uppercase tracking-wider">{{ ucfirst($otherUser->role) }}</span>
          </div>

          <span class="material-symbols-outlined text-on-surface-variant group-hover:text-primary transition-colors text-[20px] shrink-0">chevron_right</span>
        </a>
      @endforeach
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script>
window.chatInboxAfterId = {{ (int) $latestMessageId }};
const chatInboxPollUrl = @json(route('chat.inbox.poll'));
const chatShowUrlTemplate = @json(route('chat.show', ['user' => '__CHAT_USER__']));

function getOrCreateConversationRow(message) {
  let list = document.getElementById('conversation-list');
  if (!list) {
    const empty = document.getElementById('empty-conversations');
    if (!empty) return null;
    list = document.createElement('div');
    list.id = 'conversation-list';
    list.className = 'space-y-2';
    empty.replaceWith(list);
  }
  if (!list || !message.partner_id) return null;
  let row = list.querySelector(`[data-conversation-id="${message.partner_id}"]`);
  if (row) return row;

  row = document.createElement('a');
  row.dataset.conversationId = message.partner_id;
  row.href = chatShowUrlTemplate.replace('__CHAT_USER__', encodeURIComponent(message.partner_id));
  row.className = 'flex items-center gap-4 p-4 bg-white rounded-2xl border border-surface-container hover:border-primary/30 hover:shadow-md transition-all group';

  const avatar = document.createElement('div');
  avatar.className = 'conversation-avatar relative w-12 h-12 rounded-full shrink-0 overflow-hidden bg-greenhouse-gradient flex items-center justify-center text-white font-bold text-lg';
  if (message.partner_avatar) {
    const img = document.createElement('img');
    img.src = message.partner_avatar;
    img.alt = message.partner_name || 'Usuario';
    img.className = 'w-12 h-12 rounded-full object-cover';
    avatar.appendChild(img);
  } else {
    avatar.textContent = (message.partner_name || '?').trim().charAt(0).toUpperCase();
  }
  const info = document.createElement('div');
  info.className = 'flex-1 min-w-0';
  const line = document.createElement('div');
  line.className = 'flex items-center justify-between mb-0.5';
  const name = document.createElement('p');
  name.className = 'conversation-name font-bold text-on-surface truncate';
  name.textContent = message.partner_name || 'Usuario';
  const time = document.createElement('span');
  time.className = 'conversation-time text-[10px] text-on-surface-variant/60 font-medium shrink-0';
  time.textContent = 'ahora';
  line.append(name, time);
  const preview = document.createElement('p');
  preview.className = 'conversation-preview text-xs text-on-surface-variant truncate';
  const role = document.createElement('span');
  role.className = 'conversation-role text-[9px] font-bold text-primary uppercase tracking-wider';
  role.textContent = message.partner_role || '';
  info.append(line, preview, role);
  const arrow = document.createElement('span');
  arrow.className = 'material-symbols-outlined text-on-surface-variant text-[20px] shrink-0';
  arrow.textContent = 'chevron_right';
  row.append(avatar, info, arrow);
  return row;
}

async function pollConversationInbox() {
  try {
    const response = await fetch(`${chatInboxPollUrl}?after=${window.chatInboxAfterId}`, { headers: { Accept: 'application/json' } });
    if (!response.ok) return;
    const data = await response.json();
    (data.messages || []).forEach(function (message) {
      window.chatInboxAfterId = Math.max(window.chatInboxAfterId, Number(message.id) || 0);
      const row = getOrCreateConversationRow(message);
      if (!row) return;
      const preview = row.querySelector('.conversation-preview');
      if (preview) {
        preview.textContent = (message.mine ? 'Tú: ' : '') + message.content;
        preview.classList.toggle('font-semibold', !message.mine && message.unread > 0);
        preview.classList.toggle('text-on-surface', !message.mine && message.unread > 0);
      }
      const time = row.querySelector('.conversation-time');
      if (time) time.textContent = 'ahora';
      row.querySelector('.conversation-unread')?.remove();
      if (!message.mine && message.unread > 0) {
        const badge = document.createElement('span');
        badge.className = 'conversation-unread absolute -top-1 -right-1 w-5 h-5 bg-[#ba1a1a] text-white text-[9px] font-black rounded-full flex items-center justify-center border-2 border-white';
        badge.textContent = String(message.unread);
        row.querySelector('.conversation-avatar')?.appendChild(badge);
      }
      row.classList.toggle('ring-2', !message.mine && message.unread > 0);
      row.classList.toggle('ring-primary/20', !message.mine && message.unread > 0);
      row.parentElement === document.getElementById('conversation-list') && row.parentElement.prepend(row);
    });
  } catch (_) {}
}

pollConversationInbox();
setInterval(pollConversationInbox, 2500);
</script>
@endpush
