'use client';

import React, { useState, useEffect } from 'react';

interface ChatbotWidgetProps {
  locale: string;
}

export const ChatbotWidget: React.FC<ChatbotWidgetProps> = ({ locale }) => {
  const [isOpen, setIsOpen] = useState(false);
  const [scripts, setScripts] = useState<any[]>([]);
  const [messages, setMessages] = useState<Array<{ sender: 'bot' | 'user'; text: string }>>([]);

  const [customQuestion, setCustomQuestion] = useState('');
  const [email, setEmail] = useState('');
  const [phone, setPhone] = useState('');
  const [isEscalating, setIsEscalating] = useState(false);

  const [loading, setLoading] = useState(false);
  const isVi = locale === 'vi';

  useEffect(() => {
    fetch('/api/admin/chatbot')
      .then((res) => res.json())
      .then((data) => {
        setScripts(data.scripts || []);
      })
      .catch(() => {});

    setMessages([
      {
        sender: 'bot',
        text: isVi
          ? 'Xin chào! Tôi là Trợ lý Ảo VMTA. Vui lòng chọn câu hỏi bên dưới hoặc nhập nội dung bạn cần tư vấn:'
          : 'Hello! I am VMTA Virtual Assistant. Please select a question below or type your inquiry:',
      },
    ]);
  }, [isVi]);

  const handleSelectScript = (s: any) => {
    const questionText = isVi ? s.question_vi : s.question_en;
    const answerText = isVi ? s.answer_vi : s.answer_en;

    setMessages((prev) => [
      ...prev,
      { sender: 'user', text: questionText },
      { sender: 'bot', text: answerText },
    ]);
  };

  const handleSendCustomEscalation = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!customQuestion || !email) return;
    setLoading(true);

    try {
      const res = await fetch('/api/admin/chatbot', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action: 'log_escalation',
          user_email: email,
          user_phone: phone,
          user_message: customQuestion,
        }),
      });

      if (res.ok) {
        setMessages((prev) => [
          ...prev,
          { sender: 'user', text: customQuestion },
          {
            sender: 'bot',
            text: isVi
              ? `Cảm ơn bạn! Yêu cầu của bạn đã được tiếp nhận. Chuyên viên tư vấn VMTA sẽ liên hệ qua Email ${email} trong thời gian sớm nhất.`
              : `Thank you! Your inquiry has been received. A VMTA specialist will contact you via ${email} shortly.`,
          },
        ]);
        setCustomQuestion('');
        setIsEscalating(false);
      }
    } catch {
      // ignore
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed bottom-6 right-6 z-50 font-utm-helve">
      {/* Trigger Button */}
      {!isOpen && (
        <button
          onClick={() => setIsOpen(true)}
          className="flex items-center gap-3 bg-[#0b7f7c] text-white px-5 py-3.5 rounded-full shadow-2xl hover:bg-[#086a67] transition transform hover:scale-105 border-2 border-white"
        >
          <span className="text-xl">🤖</span>
          <span className="text-xs font-bold uppercase tracking-wider">
            {isVi ? 'Tư Vấn Y Tế 24/7' : '24/7 Medical Assistant'}
          </span>
        </button>
      )}

      {/* Chatbot Window */}
      {isOpen && (
        <div className="w-[360px] sm:w-[400px] h-[520px] bg-white rounded-3xl shadow-2xl border border-slate-200 flex flex-col justify-between overflow-hidden">
          {/* Header */}
          <div className="bg-[#0b7f7c] p-4 text-white flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center text-xl">
                🤖
              </div>
              <div>
                <h3 className="font-bold text-xs uppercase">TRỢ LÝ ẢO VMTA</h3>
                <p className="text-[10px] text-teal-200">🟢 {isVi ? 'Đang hoạt động' : 'Online'}</p>
              </div>
            </div>
            <button
              onClick={() => setIsOpen(false)}
              className="text-white/80 hover:text-white font-bold text-lg p-1"
            >
              ✕
            </button>
          </div>

          {/* Messages Container */}
          <div className="flex-1 p-4 overflow-y-auto space-y-3 bg-slate-50 text-xs">
            {messages.map((m, idx) => (
              <div
                key={idx}
                className={`flex ${m.sender === 'user' ? 'justify-end' : 'justify-start'}`}
              >
                <div
                  className={`max-w-[82%] p-3 rounded-2xl ${
                    m.sender === 'user'
                      ? 'bg-[#0b7f7c] text-white rounded-br-none font-bold'
                      : 'bg-white text-slate-800 border border-slate-200 rounded-bl-none shadow-xs'
                  }`}
                >
                  {m.text}
                </div>
              </div>
            ))}
          </div>

          {/* Quick Script Chips */}
          <div className="p-3 bg-white border-t border-slate-100 space-y-2">
            <p className="text-[10px] font-bold text-slate-400 uppercase">
              {isVi ? '💡 Câu hỏi thường gặp:' : '💡 Suggested questions:'}
            </p>
            <div className="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto">
              {scripts.map((s) => (
                <button
                  key={s.id}
                  onClick={() => handleSelectScript(s)}
                  className="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-teal-50 text-[#0b7f7c] border border-teal-200 hover:bg-teal-100 transition text-left"
                >
                  {isVi ? s.question_vi : s.question_en}
                </button>
              ))}
            </div>

            {!isEscalating ? (
              <button
                onClick={() => setIsEscalating(true)}
                className="w-full mt-2 py-2 bg-[#d31e45] text-white text-xs font-bold rounded-xl hover:bg-[#b01838] transition uppercase"
              >
                ✉️ {isVi ? 'Yêu cầu tư vấn riêng 1-1 với Bác sĩ' : 'Request 1-1 Specialist Callback'}
              </button>
            ) : (
              <form onSubmit={handleSendCustomEscalation} className="space-y-2 pt-2 border-t border-slate-200">
                <input
                  type="text"
                  required
                  placeholder={isVi ? 'Nội dung thắc mắc...' : 'Your inquiry detail...'}
                  value={customQuestion}
                  onChange={(e) => setCustomQuestion(e.target.value)}
                  className="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs"
                />
                <div className="grid grid-cols-2 gap-2">
                  <input
                    type="email"
                    required
                    placeholder="Email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    className="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-mono"
                  />
                  <input
                    type="tel"
                    placeholder={isVi ? 'SĐT (không bắt buộc)' : 'Phone'}
                    value={phone}
                    onChange={(e) => setPhone(e.target.value)}
                    className="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs"
                  />
                </div>
                <div className="flex justify-end gap-2 pt-1">
                  <button
                    type="button"
                    onClick={() => setIsEscalating(false)}
                    className="px-3 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded-lg"
                  >
                    Hủy
                  </button>
                  <button
                    type="submit"
                    disabled={loading}
                    className="px-4 py-1 bg-[#0b7f7c] text-white text-xs font-bold rounded-lg"
                  >
                    {loading ? '⏳' : 'Gửi Yêu Cầu'}
                  </button>
                </div>
              </form>
            )}
          </div>
        </div>
      )}
    </div>
  );
};
