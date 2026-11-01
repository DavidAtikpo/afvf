(function () {
  const cfg = window.afvfChat;
  if (!cfg) return;

  const STORAGE_KEY = "afvf_chat_token";
  const root = document.getElementById("site-chat");
  const fab = document.querySelector("[data-chat-open]");
  if (!root || !fab) return;

  const messagesEl = root.querySelector("[data-chat-messages]");
  const contactBox = root.querySelector("[data-chat-contact]");
  const contactForm = root.querySelector("[data-chat-contact-form]");
  const composeForm = root.querySelector("[data-chat-compose]");
  const composeInput = root.querySelector("[data-chat-input]");
  const errEl = root.querySelector("[data-chat-error]");
  let pollTimer = null;
  let token = localStorage.getItem(STORAGE_KEY) || "";

  function t(key) {
    return (cfg.strings && cfg.strings[key]) || key;
  }

  function restUrl(route, query) {
    const base = (cfg.restHome || "/").replace(/\/?$/, "/");
    const params = new URLSearchParams();
    params.set("rest_route", "/afvf/v1/" + route.replace(/^\//, ""));
    if (query) {
      Object.keys(query).forEach((k) => {
        if (query[k] != null && query[k] !== "") params.set(k, query[k]);
      });
    }
    return base + "?" + params.toString();
  }

  function labels() {
    const title = root.querySelector(".site-chat__title");
    if (title) title.textContent = t("title");
    const online = root.querySelector("[data-chat-online]");
    if (online) online.textContent = t("online");
    const prompt = root.querySelector("[data-chat-contact-prompt]");
    if (prompt) prompt.textContent = t("contactPrompt");
    root.querySelector("[data-chat-label-name]").textContent = t("name");
    root.querySelector("[data-chat-label-email]").textContent = t("email");
    root.querySelector("[data-chat-label-message]").textContent = t("message");
    const okBtn = root.querySelector("[data-chat-label-ok]");
    if (okBtn) okBtn.textContent = t("ok");
    if (composeInput) composeInput.placeholder = t("placeholder");
  }

  function showError(msg) {
    if (!errEl) return;
    errEl.hidden = !msg;
    errEl.textContent = msg || "";
  }

  function headers() {
    return {
      "Content-Type": "application/json",
      Accept: "application/json",
      "X-WP-Nonce": cfg.nonce,
    };
  }

  function uploadHeaders() {
    return {
      Accept: "application/json",
      "X-WP-Nonce": cfg.nonce,
    };
  }

  async function parseResponse(res) {
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      return { message: text || t("error") };
    }
  }

  function fillMessageBubble(container, m) {
    if (m.attachment) {
      const att = m.attachment;
      if (att.mime && att.mime.indexOf("image/") === 0) {
        const img = document.createElement("img");
        img.className = "site-chat__attach-img";
        img.src = att.url;
        img.alt = att.name || "";
        img.loading = "lazy";
        container.appendChild(img);
      } else if (att.url) {
        const a = document.createElement("a");
        a.className = "site-chat__attach-file";
        a.href = att.url;
        a.target = "_blank";
        a.rel = "noopener noreferrer";
        a.textContent = att.name || att.url;
        container.appendChild(a);
      }
    }
    if (m.body) {
      const body = document.createElement("p");
      body.textContent = m.body;
      container.appendChild(body);
    }
  }

  function renderMessages(list) {
    if (!messagesEl) return;
    messagesEl.innerHTML = "";
    list.forEach((m) => {
      if (m.sender === "form") return;
      const div = document.createElement("div");
      const sender = m.sender === "admin" ? "admin" : "visitor";
      div.className = "site-chat__msg site-chat__msg--" + sender;
      fillMessageBubble(div, m);
      messagesEl.appendChild(div);
    });
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function toggleContact(show) {
    if (contactBox) contactBox.hidden = !show;
  }

  function applyPayload(data) {
    if (data.token) {
      token = data.token;
      localStorage.setItem(STORAGE_KEY, token);
    }
    if (typeof data.needs_contact === "boolean") {
      toggleContact(data.needs_contact);
    }
    renderMessages(data.messages || []);
  }

  async function fetchMessages() {
    if (!token) return;
    const url = restUrl("chat/messages", { token });
    const res = await fetch(url, { headers: headers(), credentials: "same-origin" });
    if (!res.ok) return;
    const data = await parseResponse(res);
    if (data.status === "closed") {
      showError(t("closed"));
      stopPoll();
    }
    applyPayload(data);
  }

  function startPoll() {
    stopPoll();
    pollTimer = window.setInterval(fetchMessages, cfg.pollMs || 8000);
  }

  function stopPoll() {
    if (pollTimer) window.clearInterval(pollTimer);
    pollTimer = null;
  }

  function openPanel() {
    root.hidden = false;
    fab.setAttribute("aria-expanded", "true");
    labels();
    if (token) {
      fetchMessages();
      startPoll();
    }
  }

  function closePanel() {
    root.hidden = true;
    fab.setAttribute("aria-expanded", "false");
    stopPoll();
  }

  fab.addEventListener("click", () => {
    if (root.hidden) openPanel();
    else closePanel();
  });

  root.querySelectorAll("[data-chat-close]").forEach((btn) => {
    btn.addEventListener("click", closePanel);
  });

  const emojiBtn = root.querySelector("[data-chat-emoji]");
  const emojiPanel = root.querySelector("[data-chat-emoji-panel]");

  function insertAtCursor(text) {
    if (!composeInput) return;
    const start = composeInput.selectionStart;
    const end = composeInput.selectionEnd;
    const val = composeInput.value;
    composeInput.value = val.slice(0, start) + text + val.slice(end);
    composeInput.selectionStart = composeInput.selectionEnd = start + text.length;
    composeInput.focus();
  }

  function closeEmojiPanel() {
    if (!emojiPanel || !emojiBtn) return;
    emojiPanel.hidden = true;
    emojiBtn.setAttribute("aria-expanded", "false");
  }

  const attachBtn = root.querySelector("[data-chat-attach]");
  const fileInput = root.querySelector("[data-chat-file]");

  async function uploadAttachment(file) {
    if (cfg.maxUploadBytes && file.size > cfg.maxUploadBytes) {
      throw new Error(t("attachError"));
    }
    const fd = new FormData();
    fd.append("file", file);
    if (token) fd.append("token", token);
    const caption = composeInput ? composeInput.value.trim() : "";
    if (caption) fd.append("message", caption);
    const res = await fetch(restUrl("chat/upload"), {
      method: "POST",
      headers: uploadHeaders(),
      credentials: "same-origin",
      body: fd,
    });
    const data = await parseResponse(res);
    if (!res.ok) {
      throw new Error(data.message || t("attachError"));
    }
    return data;
  }

  if (attachBtn && fileInput) {
    attachBtn.addEventListener("click", (e) => {
      e.preventDefault();
      closeEmojiPanel();
      fileInput.click();
    });
    fileInput.addEventListener("change", async () => {
      const file = fileInput.files && fileInput.files[0];
      fileInput.value = "";
      if (!file) return;
      showError("");
      attachBtn.disabled = true;
      attachBtn.setAttribute("aria-busy", "true");
      try {
        const data = await uploadAttachment(file);
        if (composeForm) composeForm.reset();
        closeEmojiPanel();
        applyPayload(data);
        startPoll();
      } catch (err) {
        showError(err.message || t("attachError"));
      } finally {
        attachBtn.disabled = false;
        attachBtn.removeAttribute("aria-busy");
      }
    });
  }

  if (emojiBtn && emojiPanel) {
    emojiBtn.addEventListener("click", () => {
      const open = emojiPanel.hidden;
      emojiPanel.hidden = !open;
      emojiBtn.setAttribute("aria-expanded", open ? "true" : "false");
    });
    emojiPanel.querySelectorAll("[data-emoji]").forEach((btn) => {
      btn.addEventListener("click", () => {
        insertAtCursor(btn.getAttribute("data-emoji") || "");
        closeEmojiPanel();
      });
    });
    document.addEventListener("click", (e) => {
      if (emojiPanel.hidden) return;
      if (emojiPanel.contains(e.target) || emojiBtn.contains(e.target)) return;
      closeEmojiPanel();
    });
  }

  if (composeInput && composeForm) {
    composeInput.addEventListener("keydown", (e) => {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        composeForm.requestSubmit();
      }
    });
  }

  if (composeForm) {
    composeForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      showError("");
      const fd = new FormData(composeForm);
      const message = (fd.get("message") || "").toString().trim();
      if (!message) return;

      try {
        const route = token ? "chat/send" : "chat/start";
        const body = token ? { token, message } : { message };
        const res = await fetch(restUrl(route), {
          method: "POST",
          headers: headers(),
          credentials: "same-origin",
          body: JSON.stringify(body),
        });
        const data = await parseResponse(res);
        if (!res.ok) {
          throw new Error(data.message || t("error"));
        }
        composeForm.reset();
        closeEmojiPanel();
        applyPayload(data);
        startPoll();
      } catch (err) {
        showError(err.message || t("error"));
      }
    });
  }

  if (contactForm) {
    contactForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      showError("");
      const fd = new FormData(contactForm);
      try {
        const res = await fetch(restUrl("chat/contact"), {
          method: "POST",
          headers: headers(),
          credentials: "same-origin",
          body: JSON.stringify({
            token,
            name: fd.get("name"),
            email: fd.get("email"),
            message: fd.get("message"),
          }),
        });
        const data = await parseResponse(res);
        if (!res.ok) {
          throw new Error(data.message || t("error"));
        }
        contactForm.reset();
        applyPayload(data);
      } catch (err) {
        showError(err.message || t("error"));
      }
    });
  }

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !root.hidden) closePanel();
  });

  labels();
})();
