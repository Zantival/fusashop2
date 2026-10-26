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
