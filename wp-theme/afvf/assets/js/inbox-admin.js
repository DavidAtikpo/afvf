(function () {
  const cfg = window.afvfInbox;
  if (!cfg || !cfg.threadId) return;

  const box = document.getElementById("afvf-inbox-messages");
  const form = document.getElementById("afvf-inbox-reply-form");
  if (!box) return;

  function restUrl(route, query) {
    const base = (cfg.restHome || "/").replace(/\/?$/, "/");
    const params = new URLSearchParams();
    params.set("rest_route", "/afvf/v1/" + route.replace(/^\//, ""));
    if (query) {
      Object.keys(query).forEach((k) => params.set(k, query[k]));
    }
    return base + "?" + params.toString();
  }

  function headers() {
    return {
      Accept: "application/json",
      "Content-Type": "application/json",
      "X-WP-Nonce": cfg.nonce,
    };
  }

  function fillBubble(bubble, m) {
    if (m.attachment) {
      const att = m.attachment;
      if (att.mime && att.mime.indexOf("image/") === 0) {
        const img = document.createElement("img");
        img.className = "afvf-chat-attach-img";
        img.src = att.url;
        img.alt = att.name || "";
        img.loading = "lazy";
        bubble.appendChild(img);
      } else if (att.url) {
        const a = document.createElement("a");
        a.className = "afvf-chat-attach-file";
        a.href = att.url;
        a.target = "_blank";
        a.rel = "noopener noreferrer";
        a.textContent = att.name || att.url;
        bubble.appendChild(a);
      }
    }
    if (m.body) {
      const cap = document.createElement("span");
      cap.className = "afvf-chat-attach-caption";
      cap.textContent = m.body;
      bubble.appendChild(cap);
    }
  }

  function renderMessages(list) {
    box.innerHTML = "";
    list.forEach((m) => {
      if (m.sender === "form") {
        const wrap = document.createElement("div");
        wrap.className = "afvf-inbox-msg__form";
        wrap.innerHTML =
          '<p class="afvf-inbox-msg__form-title">' +
          (cfg.strings.formSent || "FORMULAIRE ENVOYÉ PAR CHAT") +
          "</p><pre></pre>";
        wrap.querySelector("pre").textContent = m.body;
        box.appendChild(wrap);
        return;
      }
      const row = document.createElement("div");
      row.className =
        "afvf-inbox-msg afvf-inbox-msg--" + (m.sender === "admin" ? "admin" : "visitor");
      const bubble = document.createElement("div");
      bubble.className = "afvf-inbox-msg__bubble";
      fillBubble(bubble, m);
      row.appendChild(bubble);
      box.appendChild(row);
    });
    box.scrollTop = box.scrollHeight;
  }

  async function refresh() {
    try {
      const res = await fetch(restUrl("chat/admin/thread/" + cfg.threadId), {
        headers: headers(),
        credentials: "same-origin",
      });
      if (!res.ok) return;
      const data = await res.json();
      if (data.messages) renderMessages(data.messages);
    } catch (e) {
      /* ignore */
    }
  }

  if (form) {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      const ta = form.querySelector('textarea[name="reply_body"]');
      const notify = form.querySelector('input[name="notify_email"]');
      const body = ta ? ta.value.trim() : "";
      if (!body) return;
      const btn = form.querySelector('[type="submit"]');
      if (btn) btn.disabled = true;
      try {
        const res = await fetch(restUrl("chat/admin/reply"), {
          method: "POST",
          headers: headers(),
          credentials: "same-origin",
          body: JSON.stringify({
            thread_id: cfg.threadId,
            body,
            notify_email: notify && notify.checked,
          }),
        });
        const data = await res.json();
        if (res.ok && data.messages) {
          renderMessages(data.messages);
          if (ta) ta.value = "";
        }
      } finally {
        if (btn) btn.disabled = false;
      }
    });
  }

  refresh();
  setInterval(refresh, cfg.pollMs || 2000);
})();
