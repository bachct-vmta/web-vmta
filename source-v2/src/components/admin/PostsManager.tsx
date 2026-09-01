'use client';

import React, { useState, useEffect } from 'react';
import { MediaPicker } from '@/components/admin/MediaPicker';
import { RichTextEditor } from '@/components/admin/RichTextEditor';

export const PostsManager: React.FC = () => {
  const [posts, setPosts] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [translating, setTranslating] = useState(false);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

  const [activeFormTab, setActiveFormTab] = useState<'content' | 'seo'>('content');
  const [editingPost, setEditingPost] = useState<any | null>(null);
  const [isModalOpen, setIsModalOpen] = useState(false);

  const fetchPosts = async () => {
    setLoading(true);
    try {
      const res = await fetch('/api/admin/posts');
      if (res.ok) {
        const data = await res.json();
        setPosts(data.posts || []);
      }
    } catch (err) {
      console.error('Error fetching posts:', err);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchPosts();
  }, []);

  const handleOpenAdd = () => {
    setEditingPost({
      slug_vi: '',
      slug_en: '',
      status: 'published',
      category: 'Sự kiện VMTA',
      title_vi: '',
      title_en: '',
      summary_vi: '',
      summary_en: '',
      content_vi: '',
      content_en: '',
      image_url: '/images/news/lien-minh-du-lich-y-te-ra-mat.jpg',
      author: 'Ban Biên Tập VMTA',
      meta_title_vi: '',
      meta_title_en: '',
      meta_description_vi: '',
      meta_description_en: '',
      meta_keywords_vi: '',
      meta_keywords_en: '',
    });
    setActiveFormTab('content');
    setIsModalOpen(true);
  };

  const handleOpenEdit = (p: any) => {
    setEditingPost({
      ...p,
      slug_vi: p.slug_vi || p.slug || '',
      slug_en: p.slug_en || p.slug || '',
      status: p.status || 'published',
    });
    setActiveFormTab('content');
    setIsModalOpen(true);
  };

  const handleDelete = async (id: number) => {
    if (!confirm('Bạn có chắc chắn muốn xóa bài viết này?')) return;
    try {
      const res = await fetch(`/api/admin/posts?id=${id}`, { method: 'DELETE' });
      if (res.ok) {
        setMessage({ type: 'success', text: 'Đã xóa bài viết thành công!' });
        fetchPosts();
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi khi xóa bài viết.' });
    }
  };

  const handleAutoTranslateArticle = async () => {
    if (!editingPost?.title_vi && !editingPost?.summary_vi) return;
    setTranslating(true);
    try {
      const res = await fetch('/api/admin/translate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          fields: [editingPost.title_vi || '', editingPost.summary_vi || '', editingPost.content_vi || ''],
        }),
      });

      if (res.ok) {
        const data = await res.json();
        const translated = data.translated || [];
        setEditingPost((prev: any) => ({
          ...prev,
          title_en: translated[0] || prev.title_en,
          summary_en: translated[1] || prev.summary_en,
          content_en: translated[2] || prev.content_en,
        }));
        setMessage({ type: 'success', text: 'Đã dịch tự động bài viết từ Tiếng Việt sang Tiếng Anh!' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi dịch tự động bài viết.' });
    } finally {
      setTranslating(false);
    }
  };

  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingPost?.title_vi) return;
    setSaving(true);
    setMessage(null);

    try {
      const res = await fetch('/api/admin/posts', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(editingPost),
      });

      if (res.ok) {
        setMessage({ type: 'success', text: 'Lưu bài viết thành công!' });
        setIsModalOpen(false);
        fetchPosts();
      } else {
        setMessage({ type: 'error', text: 'Lỗi khi lưu bài viết.' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi kết nối lưu bài viết.' });
    } finally {
      setSaving(false);
    }
  };

  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6 font-utm-helve">
      <div className="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-4">
        <div>
          <h2 className="text-base font-bold uppercase text-[#0b7f7c]">
            📰 QUẢN LÝ BÀI VIẾT, SỰ KIỆN & TIN TỨC VMTA
          </h2>
          <p className="text-xs text-slate-500">
            Hỗ trợ Trình Soạn Thảo Rich-Text HTML, Tải ảnh trực tiếp từ máy tính, Slug VI/EN độc lập & Thẻ SEO Meta
          </p>
        </div>
        <button
          onClick={handleOpenAdd}
          className="px-4 py-2.5 bg-[#0b7f7c] text-white font-bold text-xs rounded-xl hover:bg-[#086a67] transition shadow-md"
        >
          ➕ VIẾT BÀI MỚI
        </button>
      </div>

      {message && (
        <div className={`p-4 rounded-xl text-xs font-bold ${message.type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200'}`}>
          {message.text}
        </div>
      )}

      {/* Posts List */}
      {loading ? (
        <div className="text-center py-8 text-xs font-bold text-slate-400">⏳ Đang tải danh sách bài viết...</div>
      ) : posts.length === 0 ? (
        <div className="text-center py-8 text-xs font-bold text-slate-400">Chưa có bài viết nào. Bấm "+ VIẾT BÀI MỚI" để đăng bài.</div>
      ) : (
        <div className="space-y-3">
          {posts.map((post) => (
            <div key={post.id} className="bg-slate-50 rounded-2xl border border-slate-200 p-4 flex flex-wrap items-center justify-between gap-4">
              <div className="flex items-center gap-4">
                {post.image_url && (
                  <img src={post.image_url} alt="" className="h-16 w-24 object-cover rounded-xl border border-slate-200 shrink-0 bg-white" />
                )}
                <div className="space-y-1 max-w-2xl">
                  <div className="flex items-center gap-2">
                    <span className="text-[10px] font-bold uppercase px-2 py-0.5 rounded bg-teal-100 text-teal-800">
                      {post.category}
                    </span>
                    <span className={`text-[10px] font-bold px-2 py-0.5 rounded ${post.status === 'draft' ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-900'}`}>
                      {post.status === 'draft' ? '📝 Bản nháp' : '🟢 Xuất bản'}
                    </span>
                  </div>
                  <h3 className="font-bold text-sm text-[#0b7f7c] line-clamp-1">{post.title_vi}</h3>
                  <p className="text-xs text-slate-600 line-clamp-1">{post.summary_vi}</p>
                  <p className="text-[10px] text-slate-400">
                    ✍️ Tác giả: {post.author} | 🔗 Slug: <span className="font-mono">{post.slug_vi || post.slug}</span>
                  </p>
                </div>
              </div>

              <div className="flex items-center gap-2">
                <button
                  onClick={() => handleOpenEdit(post)}
                  className="px-3 py-1.5 bg-slate-200 text-slate-700 font-bold text-xs rounded-lg hover:bg-slate-300"
                >
                  ✏️ Sửa
                </button>
                <button
                  onClick={() => handleDelete(post.id)}
                  className="px-3 py-1.5 bg-rose-500 text-white font-bold text-xs rounded-lg hover:bg-rose-600"
                >
                  🗑️ Xóa
                </button>
              </div>
            </div>
          ))}
        </div>
      )}

      {/* Add / Edit Post Modal */}
      {isModalOpen && editingPost && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="w-full max-w-4xl bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 space-y-4 max-h-[92vh] overflow-y-auto">
            <div className="flex justify-between items-center border-b border-slate-200 pb-3">
              <div className="flex items-center gap-3">
                <h3 className="font-bold text-sm text-[#0b7f7c] uppercase">
                  {editingPost.id ? '✏️ CHỈNH SỬA BÀI VIẾT' : '➕ VIẾT BÀI MỚI'}
                </h3>
                <div className="flex bg-slate-100 p-1 rounded-xl">
                  <button
                    type="button"
                    onClick={() => setActiveFormTab('content')}
                    className={`px-3 py-1 text-xs font-bold rounded-lg transition ${activeFormTab === 'content' ? 'bg-[#0b7f7c] text-white' : 'text-slate-600'}`}
                  >
                    📝 Nội dung bài viết
                  </button>
                  <button
                    type="button"
                    onClick={() => setActiveFormTab('seo')}
                    className={`px-3 py-1 text-xs font-bold rounded-lg transition ${activeFormTab === 'seo' ? 'bg-[#0b7f7c] text-white' : 'text-slate-600'}`}
                  >
                    🔍 Thẻ SEO & Đường dẫn Slug
                  </button>
                </div>
              </div>

              <div className="flex items-center gap-2">
                <button
                  type="button"
                  onClick={handleAutoTranslateArticle}
                  disabled={translating}
                  className="px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100"
                >
                  {translating ? '⏳ Đang dịch...' : '🌐 Dịch tự động BÀI VIẾT (VI ➔ EN)'}
                </button>
                <button onClick={() => setIsModalOpen(false)} className="text-slate-400 hover:text-slate-600 font-bold text-lg">✕</button>
              </div>
            </div>

            <form onSubmit={handleSave} className="space-y-4">
              {activeFormTab === 'content' ? (
                <>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">Trạng thái xuất bản</label>
                      <select
                        value={editingPost.status || 'published'}
                        onChange={(e) => setEditingPost({ ...editingPost, status: e.target.value })}
                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold bg-white"
                      >
                        <option value="published">🟢 Đã xuất bản (Published)</option>
                        <option value="draft">📝 Bản nháp (Draft)</option>
                      </select>
                    </div>

                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">Chuyên mục tin tức</label>
                      <select
                        value={editingPost.category}
                        onChange={(e) => setEditingPost({ ...editingPost, category: e.target.value })}
                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold bg-white"
                      >
                        <option value="Sự kiện VMTA">📰 Sự kiện VMTA</option>
                        <option value="Y học & Du lịch">🩺 Y học & Du lịch</option>
                        <option value="Hội thảo & Đào tạo">🎓 Hội thảo & Đào tạo</option>
                        <option value="Thông cáo Báo chí">📢 Thông cáo Báo chí</option>
                      </select>
                    </div>

                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">Tác giả bài viết</label>
                      <input
                        type="text"
                        value={editingPost.author || ''}
                        onChange={(e) => setEditingPost({ ...editingPost, author: e.target.value })}
                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"
                      />
                    </div>
                  </div>

                  {/* Media Picker for Cover Image */}
                  <MediaPicker
                    label="Ảnh bìa bài viết (Cover Image)"
                    value={editingPost.image_url || ''}
                    onChange={(url) => setEditingPost({ ...editingPost, image_url: url })}
                  />

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">Tiêu đề bài viết (Tiếng Việt)</label>
                      <input
                        type="text"
                        required
                        value={editingPost.title_vi || ''}
                        onChange={(e) => setEditingPost({ ...editingPost, title_vi: e.target.value })}
                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold text-[#0b7f7c]"
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">Tiêu đề bài viết (Tiếng Anh)</label>
                      <input
                        type="text"
                        value={editingPost.title_en || ''}
                        onChange={(e) => setEditingPost({ ...editingPost, title_en: e.target.value })}
                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-bold"
                      />
                    </div>
                  </div>

                  <div>
                    <label className="block text-xs font-bold text-slate-700 mb-1">Tóm tắt ngắn (Tiếng Việt)</label>
                    <textarea
                      rows={2}
                      value={editingPost.summary_vi || ''}
                      onChange={(e) => setEditingPost({ ...editingPost, summary_vi: e.target.value })}
                      className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs resize-none"
                    />
                  </div>

                  {/* Rich-Text Editor for Content VI & EN */}
                  <RichTextEditor
                    label="Nội dung bài viết Rich-Text HTML (Tiếng Việt)"
                    value={editingPost.content_vi || ''}
                    onChange={(content) => setEditingPost({ ...editingPost, content_vi: content })}
                    rows={8}
                  />

                  <RichTextEditor
                    label="Nội dung bài viết Rich-Text HTML (Tiếng Anh)"
                    value={editingPost.content_en || ''}
                    onChange={(content) => setEditingPost({ ...editingPost, content_en: content })}
                    rows={6}
                  />
                </>
              ) : (
                /* TAB 2: SEO METADATA & PER-LOCALE SLUGS */
                <div className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">Đường dẫn Slug (Tiếng Việt)</label>
                      <input
                        type="text"
                        value={editingPost.slug_vi || ''}
                        onChange={(e) => setEditingPost({ ...editingPost, slug_vi: e.target.value })}
                        placeholder="VD: thu-truong-bo-y-te-trao-quyet-dinh"
                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono"
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">Đường dẫn Slug (Tiếng Anh)</label>
                      <input
                        type="text"
                        value={editingPost.slug_en || ''}
                        onChange={(e) => setEditingPost({ ...editingPost, slug_en: e.target.value })}
                        placeholder="VD: deputy-minister-of-health-presents-decision"
                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs font-mono"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">Thẻ SEO Title (Tiếng Việt)</label>
                      <input
                        type="text"
                        value={editingPost.meta_title_vi || ''}
                        onChange={(e) => setEditingPost({ ...editingPost, meta_title_vi: e.target.value })}
                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">Thẻ SEO Title (Tiếng Anh)</label>
                      <input
                        type="text"
                        value={editingPost.meta_title_en || ''}
                        onChange={(e) => setEditingPost({ ...editingPost, meta_title_en: e.target.value })}
                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs"
                      />
                    </div>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">Thẻ SEO Description (Tiếng Việt)</label>
                      <textarea
                        rows={3}
                        value={editingPost.meta_description_vi || ''}
                        onChange={(e) => setEditingPost({ ...editingPost, meta_description_vi: e.target.value })}
                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs resize-none"
                      />
                    </div>
                    <div>
                      <label className="block text-xs font-bold text-slate-700 mb-1">Thẻ SEO Description (Tiếng Anh)</label>
                      <textarea
                        rows={3}
                        value={editingPost.meta_description_en || ''}
                        onChange={(e) => setEditingPost({ ...editingPost, meta_description_en: e.target.value })}
                        className="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs resize-none"
                      />
                    </div>
                  </div>
                </div>
              )}

              <div className="pt-3 flex justify-end gap-2 border-t border-slate-200">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-200"
                >
                  Hủy Bỏ
                </button>
                <button
                  type="submit"
                  disabled={saving}
                  className="px-6 py-2 bg-[#0b7f7c] text-white rounded-xl text-xs font-bold hover:bg-[#086a67]"
                >
                  {saving ? '⏳ Đang lưu...' : '💾 Đăng Bài Viết'}
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
