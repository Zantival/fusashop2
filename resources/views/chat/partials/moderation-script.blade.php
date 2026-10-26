window.chatServerModerationMessage = '';

function normalizeChatText(value) {
  return String(value || '').toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[01345@$]/g, function (char) {
      return ({ '0': 'o', '1': 'i', '3': 'e', '4': 'a', '5': 's', '@': 'a', '$': 's' })[char];
    })
    .replace(/(.)\1{2,}/g, '$1$1');
}

function inspectChatMessage(value) {
  const text = normalizeChatText(value);
  const matched = [...new Set(badWords.map(normalizeChatText).filter(function (word) {
    if (!word) return false;
    const pattern = word.trim().split(/\s+/).map(function (part) {
      return part.split('').map(function (char) { return char.replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }).join('[^a-z0-9]*');
    }).join('[^a-z0-9]+');
    return new RegExp('(^|[^a-z0-9])' + pattern + '($|[^a-z0-9])', 'i').test(text);
  }))];

  if (!matched.length) return { allowed: true };
  if (/\b(eres|sos|usted es|maldito|callate|te voy a|voy a acabar|ojala te)\b/.test(text)) return { allowed: false };

  if (matched.length === 1 && matched[0] === 'marica'
      && /^\s*marica[,\s]+(ya voy|ya llego|mira|imaginate|no sabia|que pena|jaja|gracias|hola)\b/.test(text)) {
    return { allowed: true };
  }

  if (matched.length === 1) {
    const term = matched[0].replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    const report = new RegExp('(^|\\b)(me|nos|le|les)\\s+(dijo|escribio|mando|envio|llamo|insulto|amenazo)\\b[^.!?\\n]{0,35}\\b' + term + '\\b');
    const formalReport = new RegExp('\\b(reporto|denuncio|quiero reportar|quiero denunciar|recibi un mensaje)\\b[^.!?\\n]{0,80}\\b' + term + '\\b');
    if (report.test(text) || formalReport.test(text)) return { allowed: true };
  }

  return { allowed: false };
}

function checkProfanity() {
  if (!inputField || !sendBtn) return;
  const warning = document.getElementById('profanity-warning');
  const result = inspectChatMessage(inputField.value);
  const blocked = !result.allowed || Boolean(window.chatServerModerationMessage);
  sendBtn.disabled = blocked || !inputField.value.trim();
  sendBtn.classList.toggle('opacity-50', sendBtn.disabled);
  sendBtn.classList.toggle('grayscale', sendBtn.disabled);

  if (warning) {
    if (window.chatServerModerationMessage) {
      warning.textContent = window.chatServerModerationMessage;
      warning.classList.remove('hidden');
    } else if (blocked) {
      warning.textContent = 'El mensaje parece ofensivo. Reformúlalo; las citas de insultos recibidos para reportarlos se permiten si explicas el contexto.';
      warning.classList.remove('hidden');
    } else {
      warning.classList.add('hidden');
    }
  }
}

inputField?.addEventListener('input', function () {
  window.chatServerModerationMessage = '';
  checkProfanity();
}, true);

window.deleteMessage = async function (id, button) {
  if (!window.confirm('¿Eliminar este mensaje que enviaste?')) return;
  const message = button?.closest('[data-msg-id]');
  if (message) message.classList.add('opacity-40');
  try {
    const response = await fetch(`/chat/message/${id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' }
    });
    if (!response.ok) throw new Error('No se pudo eliminar el mensaje.');
    message?.remove();
  } catch (error) {
    message?.classList.remove('opacity-40');
    window.alert(error.message || 'No se pudo eliminar el mensaje.');
  }
};

window.attachChatSenderAvatar = function (messageRow, message) {
  if (!messageRow || message.mine) return;
  const avatar = document.createElement('div');
  avatar.className = 'w-8 h-8 rounded-full shrink-0 overflow-hidden bg-emerald-600 text-white text-xs font-bold flex items-center justify-center self-end mr-2';
  if (message.sender_avatar) {
    const image = document.createElement('img');
    image.src = message.sender_avatar;
    image.alt = message.sender_name || 'Usuario';
    image.className = 'w-full h-full object-cover';
    image.onerror = function () {
      avatar.textContent = (message.sender_name || '?').trim().charAt(0).toUpperCase();
    };
    avatar.appendChild(image);
  } else {
    avatar.textContent = (message.sender_name || '?').trim().charAt(0).toUpperCase();
  }
  messageRow.prepend(avatar);
};
