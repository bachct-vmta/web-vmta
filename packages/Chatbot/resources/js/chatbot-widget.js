/**
 * Alpine.js chatbot widget with SSE streaming via fetch + ReadableStream.
 * Uses POST /chatbot/message so EventSource (GET-only) is not suitable here.
 */

import { marked } from 'marked';
import DOMPurify from 'dompurify';

marked.setOptions({ gfm: true, breaks: true });

const cfg = window.__chatbotConfig ?? {};

// Safe tag/attr allowlist for chat bubbles. Blocks <script>, <iframe>, event
// handlers, `javascript:` URLs — defends against LLM prompt-injection XSS.
const PURIFY_CONFIG = {
    ALLOWED_TAGS: [
        'p', 'br', 'strong', 'em', 'u', 's', 'code', 'pre', 'blockquote',
        'ul', 'ol', 'li', 'a', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr',
        'table', 'thead', 'tbody', 'tr', 'th', 'td', 'span', 'div',
    ],
    ALLOWED_ATTR: ['href', 'title', 'target', 'rel'],
    ALLOWED_URI_REGEXP: /^(?:(?:https?|mailto|tel):|[^a-z]|[a-z+.-]+(?:[^a-z+.\-:]|$))/i,
};

/** Render bot markdown to sanitized HTML. */
function renderMarkdown(src) {
    const html = marked.parse(String(src ?? ''));
    return DOMPurify.sanitize(html, PURIFY_CONFIG);
}

function chatbotWidget() {
    return {
        open: false,
        messages: [],
        input: '',
        streaming: false,
        overflow: false,
        remaining: null,

        get remainingLabel() {
            if (this.remaining === null) return '';
            return cfg.remainingTpl.replace('__COUNT__', this.remaining);
        },

        async init() {
            try {
                const data = await this.fetchSession();
                this.remaining = data.remaining ?? null;
                this.overflow = data.is_overflow ?? false;
                // Rehydrate prior conversation messages so the widget survives
                // page reloads. Greeting in the blade stays visible regardless.
                if (Array.isArray(data.messages) && data.messages.length > 0) {
                    this.messages = data.messages.map(m => {
                        const role = m.role === 'user' ? 'user' : 'bot';
                        const raw = String(m.content ?? '');
                        return {
                            role,
                            html: role === 'bot' ? renderMarkdown(raw) : this.escapeHtml(raw),
                        };
                    });
                    this.scrollToBottom();
                }
            } catch (e) {
                console.warn('[chatbot] session init failed', e);
            }
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => this.$refs.input?.focus());
            }
        },

        async send() {
            const content = this.input.trim();
            if (!content || this.streaming || this.overflow) return;

            this.input = '';
            this.messages.push({ role: 'user', html: this.escapeHtml(content) });
            this.streaming = true;
            this.scrollToBottom();

            try {
                const res = await fetch(cfg.messageUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': cfg.csrfToken,
                        'Accept': 'text/event-stream',
                    },
                    body: JSON.stringify({ content }),
                });

                if (res.status === 403) {
                    const json = await res.json();
                    this.overflow = true;
                    this.remaining = 0;
                    this.streaming = false;
                    return;
                }

                if (!res.ok) {
                    this.pushBotError();
                    this.streaming = false;
                    return;
                }

                await this.readSseStream(res.body);
            } catch (e) {
                console.error('[chatbot] send error', e);
                this.pushBotError();
            } finally {
                this.streaming = false;
                this.scrollToBottom();
                this.$nextTick(() => this.$refs.input?.focus());
            }
        },

        async readSseStream(body) {
            const reader = body.getReader();
            const decoder = new TextDecoder();
            let buffer = '';
            let botText = '';
            let msgIndex = null;

            const flush = (text) => {
                if (msgIndex === null) {
                    this.messages.push({ role: 'bot', html: '' });
                    msgIndex = this.messages.length - 1;
                }
                botText += text;
                // Re-render the full markdown accumulator on each chunk so
                // partial syntax (e.g. an unclosed **) still produces sensible
                // intermediate HTML.
                this.messages[msgIndex].html = renderMarkdown(botText);
                this.scrollToBottom();
            };

            try {
                while (true) {
                    const { done, value } = await reader.read();
                    if (done) break;

                    buffer += decoder.decode(value, { stream: true });
                    const lines = buffer.split('\n');
                    buffer = lines.pop() ?? '';

                    for (const line of lines) {
                        if (!line.startsWith('data:')) continue;
                        const raw = line.slice(5).trim();
                        if (!raw) continue;

                        try {
                            const event = JSON.parse(raw);
                            if (event.type === 'text' && (event.data || event.content)) {
                                flush(event.data || event.content);
                            } else if (event.type === 'done') {
                                if (typeof event.remaining === 'number') {
                                    this.remaining = event.remaining;
                                } else if (this.remaining !== null) {
                                    this.remaining = Math.max(0, this.remaining - 1);
                                }
                                if (this.remaining <= 0) {
                                    this.overflow = true;
                                }
                            } else if (event.type === 'error') {
                                this.pushBotError(event.message);
                            }
                        } catch {
                            // Non-JSON data line — relay as raw text
                            flush(raw);
                        }
                    }
                }
            } finally {
                reader.releaseLock();
            }
        },

        async fetchSession() {
            const res = await fetch(cfg.sessionUrl, {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin',
            });
            return res.json();
        },

        async newChat() {
            try {
                await fetch(cfg.newChatUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': cfg.csrfToken,
                        'Accept': 'application/json',
                    },
                });
            } catch (e) {
                console.warn('[chatbot] new-chat request failed', e);
            }

            // Reset local state and re-init with a fresh session.
            this.messages = [];
            this.overflow = false;
            this.streaming = false;
            this.remaining = null;
            this.input = '';

            await this.init();
            this.$nextTick(() => this.$refs.input?.focus());
        },

        pushBotError(msg) {
            const text = msg ?? (window.__chatbotConfig?.errorMsg ?? 'Service unavailable.');
            this.messages.push({ role: 'bot', html: `<span class="text-red-500">${this.escapeHtml(text)}</span>` });
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const el = this.$refs.messages;
                if (el) el.scrollTop = el.scrollHeight;
            });
        },

        escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/\n/g, '<br>');
        },
    };
}

document.addEventListener('alpine:init', () => {
    Alpine.data('chatbotWidget', chatbotWidget);
});
