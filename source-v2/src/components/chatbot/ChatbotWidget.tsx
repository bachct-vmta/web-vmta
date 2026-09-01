'use client';

import React, { useState, useRef, useEffect } from 'react';
import { marked } from 'marked';
import DOMPurify from 'dompurify';

interface Message {
  id: string;
  sender: 'user' | 'assistant';
  content: string;
}

interface ChatbotWidgetProps {
  locale: string;
}

export const ChatbotWidget: React.FC<ChatbotWidgetProps> = ({ locale }) => {
  const [isOpen, setIsOpen] = useState(false);
  const [messages, setMessages] = useState<Message[]>([
    {
      id: 'welcome',
      sender: 'assistant',
      content:
        locale === 'vi'
          ? 'Xin chào! Tôi là Trợ lý AI Du Lịch Y Tế VMTA. Bạn cần tư vấn về dịch vụ khám chữa bệnh, chuyên khoa hay cơ sở y tế nào tại Việt Nam?'
          : 'Hello! I am VMTA Medical Tourism AI Assistant. How can I help you with medical specialties, treatments, or healthcare in Vietnam?',
    },
  ]);
  const [input, setInput] = useState('');
  const [isStreaming, setIsStreaming] = useState(false);
  const [conversationId, setConversationId] = useState<string | null>(null);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    scrollToBottom();
  }, [messages, isStreaming]);

  const handleSend = async (customPrompt?: string) => {
    const textToSend = customPrompt || input;
    if (!textToSend.trim() || isStreaming) return;

    const userMessage: Message = {
      id: Date.now().toString(),
      sender: 'user',
      content: textToSend,
    };

    setMessages((prev) => [...prev, userMessage]);
    if (!customPrompt) setInput('');
    setIsStreaming(true);

    const assistantMessageId = (Date.now() + 1).toString();
    setMessages((prev) => [...prev, { id: assistantMessageId, sender: 'assistant', content: '' }]);

    try {
      const response = await fetch('/api/chatbot/stream', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          content: textToSend,
          conversation_id: conversationId,
        }),
      });

      const newConversationId = response.headers.get('X-Conversation-Id');
      if (newConversationId) {
        setConversationId(newConversationId);
      }

      if (!response.ok || !response.body) {
        throw new Error('Streaming failed');
      }

      const reader = response.body.getReader();
      const decoder = new TextDecoder();
      let accumulatedContent = '';

      while (true) {
        const { done, value } = await reader.read();
        if (done) break;

        const chunk = decoder.decode(value, { stream: true });
        // Handle SSE data stream formatting
        const lines = chunk.split('\n');
        for (const line of lines) {
          if (line.startsWith('data: ')) {
            const dataStr = line.replace('data: ', '').trim();
            if (dataStr === '[DONE]') continue;
            try {
              const parsed = JSON.parse(dataStr);
              const delta = parsed.delta || parsed.content || parsed.text || '';
              accumulatedContent += delta;
            } catch {
              accumulatedContent += dataStr;
            }
          } else if (line.trim() && !line.startsWith('event:')) {
            accumulatedContent += line;
          }
        }

        setMessages((prev) =>
          prev.map((msg) =>
            msg.id === assistantMessageId ? { ...msg, content: accumulatedContent } : msg
          )
        );
      }
    } catch {
      setMessages((prev) =>
        prev.map((msg) =>
          msg.id === assistantMessageId
            ? {
                ...msg,
                content:
                  locale === 'vi'
                    ? 'Xin lỗi, không thể kết nối tới máy chủ AI RAG lúc này. Vui lòng thử lại sau.'
                    : 'Sorry, unable to connect to AI RAG server right now. Please try again later.',
              }
            : msg
        )
      );
    } finally {
      setIsStreaming(false);
    }
  };

  const renderMarkdown = (content: string) => {
    try {
      const rawHtml = marked.parse(content) as string;
      return { __html: DOMPurify.sanitize(rawHtml) };
    } catch {
      return { __html: content };
    }
  };

  return (
    <>
      {/* Floating Chat Icon */}
      <button
        onClick={() => setIsOpen(!isOpen)}
        className="fixed bottom-6 right-6 z-50 flex items-center justify-center w-14 h-14 rounded-full bg-[#0b7f7c] text-white shadow-2xl hover:scale-105 transition-transform"
        aria-label="Open AI Chatbot"
      >
        {isOpen ? (
          <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        ) : (
          <svg className="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path
              strokeLinecap="round"
              strokeLinejoin="round"
              strokeWidth="2"
              d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"
            />
          </svg>
        )}
      </button>

      {/* Chat Window */}
      {isOpen && (
        <div className="fixed bottom-24 right-6 z-50 w-80 sm:w-96 h-[520px] bg-white rounded-2xl shadow-2xl border border-slate-200 flex flex-col overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-300">
          {/* Header */}
          <div className="bg-[#0b7f7c] text-white px-5 py-3.5 flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center font-bold text-xs">
                AI
              </div>
              <div>
                <h3 className="font-bold text-sm leading-tight">
                  {locale === 'vi' ? 'Trợ lý AI Du Lịch Y Tế' : 'VMTA AI Assistant'}
                </h3>
                <span className="text-[11px] text-teal-100 flex items-center gap-1">
                  <span className="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                  RAG FastAPI Online
                </span>
              </div>
            </div>
            <button
              onClick={() => setIsOpen(false)}
              className="text-white/80 hover:text-white p-1"
            >
              ✕
            </button>
          </div>

          {/* Messages */}
          <div className="flex-1 overflow-y-auto p-4 space-y-3 bg-slate-50 text-sm">
            {messages.map((msg) => (
              <div
                key={msg.id}
                className={`flex ${msg.sender === 'user' ? 'justify-end' : 'justify-start'}`}
              >
                <div
                  className={`max-w-[85%] rounded-2xl px-4 py-2.5 shadow-sm leading-relaxed ${
                    msg.sender === 'user'
                      ? 'bg-[#0b7f7c] text-white rounded-br-none'
                      : 'bg-white text-slate-800 border border-slate-200/80 rounded-bl-none prose prose-sm max-w-none prose-p:my-1 prose-ul:my-1'
                  }`}
                  dangerouslySetInnerHTML={renderMarkdown(msg.content)}
                />
              </div>
            ))}

            {/* Quick Prompt Suggestions */}
            {messages.length === 1 && (
              <div className="pt-2 space-y-1.5">
                <p className="text-xs font-semibold text-slate-500">
                  {locale === 'vi' ? 'Gợi ý câu hỏi:' : 'Suggested questions:'}
                </p>
                <button
                  onClick={() => handleSend(locale === 'vi' ? 'Bảng giá dịch vụ nha khoa' : 'Dental service pricing')}
                  className="block w-full text-left text-xs bg-white hover:bg-teal-50 border border-slate-200 text-[#0b7f7c] px-3 py-2 rounded-xl transition"
                >
                  💡 {locale === 'vi' ? 'Bảng giá dịch vụ nha khoa' : 'Dental service pricing'}
                </button>
                <button
                  onClick={() => handleSend(locale === 'vi' ? 'Danh sách bệnh viện uy tín' : 'Top accredited hospitals')}
                  className="block w-full text-left text-xs bg-white hover:bg-teal-50 border border-slate-200 text-[#0b7f7c] px-3 py-2 rounded-xl transition"
                >
                  💡 {locale === 'vi' ? 'Danh sách bệnh viện uy tín' : 'Top accredited hospitals'}
                </button>
              </div>
            )}

            <div ref={messagesEndRef} />
          </div>

          {/* Input Box */}
          <div className="p-3 bg-white border-t border-slate-100 flex items-center gap-2">
            <input
              type="text"
              value={input}
              onChange={(e) => setInput(e.target.value)}
              onKeyDown={(e) => e.key === 'Enter' && handleSend()}
              placeholder={locale === 'vi' ? 'Nhập câu hỏi tư vấn...' : 'Type your question...'}
              disabled={isStreaming}
              className="flex-1 text-sm bg-slate-100 rounded-xl px-4 py-2 text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#0b7f7c]"
            />
            <button
              onClick={() => handleSend()}
              disabled={isStreaming || !input.trim()}
              className="p-2.5 rounded-xl bg-[#0b7f7c] text-white hover:opacity-90 disabled:opacity-50 transition"
              aria-label="Send"
            >
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth="2"
                  d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                />
              </svg>
            </button>
          </div>
        </div>
      )}
    </>
  );
};
