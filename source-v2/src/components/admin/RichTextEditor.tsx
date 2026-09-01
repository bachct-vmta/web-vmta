'use client';

import React, { useRef } from 'react';

interface RichTextEditorProps {
  label: string;
  value: string;
  onChange: (content: string) => void;
  rows?: number;
}

export const RichTextEditor: React.FC<RichTextEditorProps> = ({
  label,
  value,
  onChange,
  rows = 10,
}) => {
  const textareaRef = useRef<HTMLTextAreaElement>(null);

  const insertTag = (openTag: string, closeTag: string) => {
    const textarea = textareaRef.current;
    if (!textarea) return;

    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = value.substring(start, end) || 'Nội dung...';
    const replacement = `${openTag}${selectedText}${closeTag}`;

    const newContent = value.substring(0, start) + replacement + value.substring(end);
    onChange(newContent);

    setTimeout(() => {
      textarea.focus();
      textarea.setSelectionRange(start + openTag.length, start + openTag.length + selectedText.length);
    }, 50);
  };

  const insertImage = () => {
    const url = prompt('Nhập URL hình ảnh (hoặc URL sau khi upload):');
    if (url) {
      const imgHtml = `<p class="my-4"><img src="${url}" alt="" class="w-full rounded-2xl shadow-md object-cover max-h-[500px]" /></p>`;
      onChange(value + '\n' + imgHtml);
    }
  };

  const insertTable = () => {
    const tableHtml = `
<div className="overflow-x-auto my-6">
  <table className="w-full text-left text-xs border-collapse border border-slate-200">
    <thead>
      <tr className="bg-slate-100 font-bold border-b border-slate-200">
        <th className="p-3 border border-slate-200">Tiêu đề 1</th>
        <th className="p-3 border border-slate-200">Tiêu đề 2</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td className="p-3 border border-slate-200">Nội dung 1</td>
        <td className="p-3 border border-slate-200">Nội dung 2</td>
      </tr>
    </tbody>
  </table>
</div>
`;
    onChange(value + '\n' + tableHtml);
  };

  return (
    <div className="space-y-2 font-utm-helve">
      <div className="flex items-center justify-between">
        <label className="block text-xs font-bold text-slate-700">{label}</label>
        <span className="text-[10px] text-slate-400 font-mono">Hỗ trợ Rich-Text HTML</span>
      </div>

      {/* Formatting Toolbar */}
      <div className="bg-slate-100 border border-slate-300 rounded-t-xl p-1.5 flex flex-wrap items-center gap-1">
        <button
          type="button"
          onClick={() => insertTag('<h2>', '</h2>')}
          className="px-2.5 py-1 bg-white hover:bg-slate-200 text-slate-800 font-bold text-xs rounded border border-slate-200"
          title="Tiêu đề 2"
        >
          H2
        </button>
        <button
          type="button"
          onClick={() => insertTag('<h3>', '</h3>')}
          className="px-2.5 py-1 bg-white hover:bg-slate-200 text-slate-800 font-bold text-xs rounded border border-slate-200"
          title="Tiêu đề 3"
        >
          H3
        </button>
        <div className="w-[1px] h-4 bg-slate-300 mx-1"></div>
        <button
          type="button"
          onClick={() => insertTag('<strong>', '</strong>')}
          className="px-2 py-1 bg-white hover:bg-slate-200 text-slate-800 font-bold text-xs rounded border border-slate-200"
          title="In đậm"
        >
          <b>B</b>
        </button>
        <button
          type="button"
          onClick={() => insertTag('<em>', '</em>')}
          className="px-2 py-1 bg-white hover:bg-slate-200 text-slate-800 italic font-bold text-xs rounded border border-slate-200"
          title="In nghiêng"
        >
          <i>I</i>
        </button>
        <button
          type="button"
          onClick={() => insertTag('<u>', '</u>')}
          className="px-2 py-1 bg-white hover:bg-slate-200 text-slate-800 underline font-bold text-xs rounded border border-slate-200"
          title="Gạch chân"
        >
          <u>U</u>
        </button>
        <div className="w-[1px] h-4 bg-slate-300 mx-1"></div>
        <button
          type="button"
          onClick={() => insertTag('<blockquote className="border-l-4 border-[#0b7f7c] pl-4 italic my-4 text-slate-600">', '</blockquote>')}
          className="px-2.5 py-1 bg-white hover:bg-slate-200 text-slate-800 font-bold text-xs rounded border border-slate-200"
          title="Trích dẫn"
        >
          “ ”
        </button>
        <button
          type="button"
          onClick={() => insertTag('<ul className="list-disc pl-5 space-y-1 my-3">\n  <li>', '</li>\n</ul>')}
          className="px-2.5 py-1 bg-white hover:bg-slate-200 text-slate-800 font-bold text-xs rounded border border-slate-200"
          title="Danh sách dấu chấm"
        >
          • Danh sách
        </button>
        <button
          type="button"
          onClick={() => insertTag('<a href="#" className="text-[#0b7f7c] font-bold underline">', '</a>')}
          className="px-2.5 py-1 bg-white hover:bg-slate-200 text-slate-800 font-bold text-xs rounded border border-slate-200"
          title="Chèn liên kết"
        >
          🔗 Link
        </button>
        <button
          type="button"
          onClick={insertImage}
          className="px-2.5 py-1 bg-white hover:bg-slate-200 text-slate-800 font-bold text-xs rounded border border-slate-200"
          title="Chèn hình ảnh"
        >
          🖼️ Chèn ảnh
        </button>
        <button
          type="button"
          onClick={insertTable}
          className="px-2.5 py-1 bg-white hover:bg-slate-200 text-slate-800 font-bold text-xs rounded border border-slate-200"
          title="Chèn bảng biểu"
        >
          📊 Chèn Bảng
        </button>
      </div>

      {/* Editor Content Input */}
      <textarea
        ref={textareaRef}
        rows={rows}
        value={value || ''}
        onChange={(e) => onChange(e.target.value)}
        className="w-full rounded-b-xl border-x border-b border-slate-300 p-4 text-xs font-mono focus:border-[#0b7f7c] outline-none leading-relaxed bg-white"
        placeholder="Nhập nội dung bài viết HTML..."
      />
    </div>
  );
};
