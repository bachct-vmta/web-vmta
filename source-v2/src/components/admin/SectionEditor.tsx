'use client';

import React, { useState, useEffect } from 'react';
import { MediaPicker } from '@/components/admin/MediaPicker';

interface SectionEditorProps {
  pageSlug: string;
}

export const BLUEPRINT_TEMPLATES = [
  { key: 'HeroBanner', name: '🎨 Kiểu 1: HeroBanner (Banner Đầu Trang & Marquee Stats)', icon: '🎨' },
  { key: 'FeatureCards', name: '🗂️ Kiểu 2: FeatureCards (Grid Thẻ Tính Năng / Giá Trị với Icon chung)', icon: '🗂️' },
  { key: 'ContentWithImage', name: '🖼️ Kiểu 3: ContentWithImage (Khối Văn Bản Kèm Ảnh Side-by-Side)', icon: '🖼️' },
  { key: 'VideoHero', name: '🎬 Kiểu 4: VideoHero (Khối Video & Quote Tầm Nhìn Sứ Mệnh)', icon: '🎬' },
  { key: 'ProcessSteps', name: '🪜 Kiểu 5: ProcessSteps (Khối Quy Trình 4 Bước Hoạt Động)', icon: '🪜' },
  { key: 'ContactOffices', name: '🏢 Kiểu 6: ContactOffices (Khối Trụ Sở 193 Trích Sài & Chi Nhánh)', icon: '🏢' },
  { key: 'StatsGrid', name: '📊 Kiểu 7: StatsGrid (Khối Grid Thẻ Chỉ Số Thành Tựu)', icon: '📊' },
  { key: 'CheckpointList', name: '📌 Kiểu 8: CheckpointList (Khối Danh Sách Điểm Tích Khác Biệt)', icon: '📌' },
];

const INITIAL_SECTION_BLUEPRINT_MAP: Record<string, { key: string; name: string; blueprint: string }[]> = {
  home: [
    { key: 'hero', name: 'Hero Banner Trang Chủ', blueprint: 'HeroBanner' },
    { key: 'values', name: 'Tầm Nhìn & 5 Giá Trị Cốt Lõi', blueprint: 'FeatureCards' },
    { key: 'about', name: 'Giới Thiệu Về VMTA & 3 Điểm Tích', blueprint: 'ContentWithImage' },
    { key: 'solutions', name: '3 Giải Pháp Cho Các Đơn Vị', blueprint: 'FeatureCards' },
    { key: 'vision_mission', name: 'Tầm Nhìn, Sứ Mệnh & Video', blueprint: 'VideoHero' },
    { key: 'benefits', name: '4 Quyền Lợi Thành Viên', blueprint: 'FeatureCards' },
  ],
  about: [
    { key: 'hero', name: 'Hero Banner Trang Giới Thiệu', blueprint: 'HeroBanner' },
    { key: 'architect', name: 'VMTA Là Ai & 3 Trụ Cột', blueprint: 'ContentWithImage' },
    { key: 'how_it_works', name: 'Quy Trình 4 Bước Hoạt Động', blueprint: 'ProcessSteps' },
    { key: 'difference', name: 'Khác Biệt Của VMTA', blueprint: 'CheckpointList' },
  ],
  products: [
    { key: 'hero', name: 'Hero Banner Thành Tựu Y Khoa', blueprint: 'HeroBanner' },
    { key: 'stats', name: '3 Thẻ Chỉ Số Thành Tựu', blueprint: 'StatsGrid' },
    { key: 'cases', name: 'Ca Phẫu Thuật Đặc Biệt', blueprint: 'FeatureCards' },
  ],
  alliance: [
    { key: 'hero', name: 'Hero Banner Mạng Lưới Liên Minh', blueprint: 'HeroBanner' },
    { key: 'overview', name: '4 Trụ Cột Liên Minh', blueprint: 'FeatureCards' },
    { key: 'standards', name: '4 Tiêu Chuẩn Thẩm Định', blueprint: 'ContentWithImage' },
    { key: 'map', name: 'Bản Đồ Mạng Lưới Toàn Quốc', blueprint: 'ContentWithImage' },
    { key: 'join', name: 'Kêu Gọi Tham Gia Liên Minh', blueprint: 'ContentWithImage' },
  ],
  contact: [
    { key: 'hero', name: 'Hero Banner Trang Liên Hệ', blueprint: 'HeroBanner' },
    { key: 'offices', name: 'Trụ Sở 193 Trích Sài & Chi Nhánh', blueprint: 'ContactOffices' },
    { key: 'form', name: 'Form Đăng Ký Hệ Sinh Thái', blueprint: 'ContentWithImage' },
  ],
};

export const SectionEditor: React.FC<SectionEditorProps> = ({ pageSlug }) => {
  const [sectionsList, setSectionsList] = useState<{ key: string; name: string; blueprint: string }[]>([]);
  const [selectedKey, setSelectedKey] = useState<string>('hero');

  // Modal State for Adding New Section
  const [showAddModal, setShowAddModal] = useState(false);
  const [newSecName, setNewSecName] = useState('');
  const [newSecKey, setNewSecKey] = useState('');
  const [newSecBlueprint, setNewSecBlueprint] = useState('FeatureCards');

  // Section Data States
  const [viData, setViData] = useState<any>({});
  const [enData, setEnData] = useState<any>({});
  const [videoUrl, setVideoUrl] = useState<string>('');

  const [uploadingLocale, setUploadingLocale] = useState<string | null>(null);
  const [translating, setTranslating] = useState(false);
  const [saving, setSaving] = useState(false);
  const [message, setMessage] = useState<{ type: 'success' | 'error'; text: string } | null>(null);

  // Load Sections list from database API
  const fetchSectionsList = async () => {
    try {
      const res = await fetch(`/api/admin/sections?page_slug=${pageSlug}`);
      if (res.ok) {
        const data = await res.json();
        if (data.sections && data.sections.length > 0) {
          const dbList = data.sections.map((s: any) => {
            const viTr = s.translations?.find((t: any) => t.locale === 'vi');
            let blueprint = 'FeatureCards';
            if (s.section_key === 'hero') blueprint = 'HeroBanner';
            else if (s.section_key === 'vision_mission') blueprint = 'VideoHero';
            else if (s.section_key === 'how_it_works') blueprint = 'ProcessSteps';
            else if (s.section_key === 'offices') blueprint = 'ContactOffices';
            else if (s.section_key === 'stats') blueprint = 'StatsGrid';
            else if (s.section_key === 'difference') blueprint = 'CheckpointList';
            else if (['about', 'architect', 'standards', 'map', 'join', 'form'].includes(s.section_key)) blueprint = 'ContentWithImage';

            return {
              key: s.section_key,
              name: viTr?.title || s.section_key.toUpperCase(),
              blueprint,
            };
          });
          setSectionsList(dbList);
          if (!dbList.find((item: any) => item.key === selectedKey)) {
            setSelectedKey(dbList[0].key);
          }
          return;
        }
      }
    } catch (err) {
      console.error('Error loading sections list:', err);
    }

    // Fallback to initial section list
    const defaults = INITIAL_SECTION_BLUEPRINT_MAP[pageSlug] || INITIAL_SECTION_BLUEPRINT_MAP['home'];
    setSectionsList(defaults);
    if (defaults.length > 0) setSelectedKey(defaults[0].key);
  };

  useEffect(() => {
    fetchSectionsList();
  }, [pageSlug]);

  // Load Selected Section Data
  useEffect(() => {
    async function loadSectionData() {
      if (!selectedKey) return;
      setMessage(null);
      try {
        const res = await fetch(`/api/admin/sections?page_slug=${pageSlug}`);
        if (res.ok) {
          const data = await res.json();
          const found = data.sections?.find((s: any) => s.section_key === selectedKey);
          if (found) {
            const viTr = found.translations?.find((t: any) => t.locale === 'vi') || {};
            const enTr = found.translations?.find((t: any) => t.locale === 'en') || {};

            let parsedViExtra: any = {};
            let parsedEnExtra: any = {};

            if (viTr.extra_json) {
              try { parsedViExtra = JSON.parse(viTr.extra_json); } catch {}
            }
            if (enTr.extra_json) {
              try { parsedEnExtra = JSON.parse(enTr.extra_json); } catch {}
            }

            setVideoUrl(parsedViExtra.video_url || 'https://test-streams.mux.dev/x36xhzz/x36xhzz.m3u8');

            setViData({
              title: viTr.title || '',
              subtitle: viTr.subtitle || '',
              body: viTr.body || '',
              image_url: parsedViExtra.image_url || parsedViExtra.image_url_vi || '',
              cta_label: parsedViExtra.cta_label || parsedViExtra.cta_primary_label || '',
              cta_url: parsedViExtra.cta_url || parsedViExtra.cta_primary_url || '',
              items: parsedViExtra.items || [],
              bullets: parsedViExtra.bullets || [],
              ...parsedViExtra,
            });

            setEnData({
              title: enTr.title || '',
              subtitle: enTr.subtitle || '',
              body: enTr.body || '',
              image_url: parsedEnExtra.image_url || parsedEnExtra.image_url_en || '',
              cta_label: parsedEnExtra.cta_label || parsedEnExtra.cta_primary_label || '',
              cta_url: parsedEnExtra.cta_url || parsedEnExtra.cta_primary_url || '',
              items: parsedEnExtra.items || [],
              bullets: parsedEnExtra.bullets || [],
              ...parsedEnExtra,
            });
            return;
          }
        }
      } catch (err) {
        console.error('Error loading section content:', err);
      }

      setViData({ title: '', subtitle: '', body: '', image_url: '', cta_label: '', cta_url: '', items: [], bullets: [] });
      setEnData({ title: '', subtitle: '', body: '', image_url: '', cta_label: '', cta_url: '', items: [], bullets: [] });
      setVideoUrl('');
    }

    loadSectionData();
  }, [pageSlug, selectedKey]);

  // Reorder Sections (Move Up / Move Down)
  const handleReorder = async (direction: 'up' | 'down') => {
    const idx = sectionsList.findIndex((s) => s.key === selectedKey);
    if (idx === -1) return;

    const newIdx = direction === 'up' ? idx - 1 : idx + 1;
    if (newIdx < 0 || newIdx >= sectionsList.length) return;

    const newList = [...sectionsList];
    const temp = newList[idx];
    newList[idx] = newList[newIdx];
    newList[newIdx] = temp;

    setSectionsList(newList);

    // Save reorder to API
    try {
      await fetch('/api/admin/sections', {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          page_slug: pageSlug,
          ordered_keys: newList.map((s) => s.key),
        }),
      });
      setMessage({ type: 'success', text: 'Đã cập nhật vị trí xếp thứ tự của Khối Section!' });
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi khi lưu vị trí xếp thứ tự.' });
    }
  };

  // Delete Current Section Block
  const handleDeleteSection = async () => {
    if (!selectedKey) return;
    if (!confirm(`Bạn có chắc chắn muốn xóa hẳn Khối Section [${selectedKey}] khỏi trang [${pageSlug}]?`)) return;

    try {
      const res = await fetch(`/api/admin/sections?page_slug=${pageSlug}&section_key=${selectedKey}`, {
        method: 'DELETE',
      });

      if (res.ok) {
        const newList = sectionsList.filter((s) => s.key !== selectedKey);
        setSectionsList(newList);
        if (newList.length > 0) setSelectedKey(newList[0].key);
        setMessage({ type: 'success', text: `Đã xóa Khối Section [${selectedKey}] thành công!` });
      } else {
        setMessage({ type: 'error', text: 'Lỗi khi xóa khối Section.' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi kết nối xóa khối Section.' });
    }
  };

  // Add New Section Block via Blueprint Picker Modal
  const handleAddNewSection = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newSecName || !newSecKey) return;

    const formattedKey = newSecKey.toLowerCase().replace(/[^a-z0-9_]/g, '_');

    try {
      const res = await fetch('/api/admin/sections', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          page_slug: pageSlug,
          section_key: formattedKey,
          order: sectionsList.length + 1,
          vi: {
            title: newSecName,
            subtitle: `Phụ đề khối ${newSecName}`,
            body: `Mô tả khối ${newSecName}`,
            extra_json: { items: [], bullets: [] },
          },
          en: {
            title: newSecName,
            subtitle: `Subtitle for ${newSecName}`,
            body: `Description for ${newSecName}`,
            extra_json: { items: [], bullets: [] },
          },
        }),
      });

      if (res.ok) {
        const newItem = { key: formattedKey, name: newSecName, blueprint: newSecBlueprint };
        setSectionsList((prev) => [...prev, newItem]);
        setSelectedKey(formattedKey);
        setShowAddModal(false);
        setNewSecName('');
        setNewSecKey('');
        setMessage({ type: 'success', text: `Đã khởi tạo Khối Section Mới [${formattedKey}] theo kiểu ${newSecBlueprint}!` });
      } else {
        setMessage({ type: 'error', text: 'Lỗi khởi tạo khối Section mới.' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi kết nối tạo khối Section.' });
    }
  };

  // Handle Image Upload
  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>, localeKey: 'vi' | 'en') => {
    const file = e.target.files?.[0];
    if (!file) return;

    setUploadingLocale(localeKey);
    setMessage(null);

    const uploadData = new FormData();
    uploadData.append('file', file);

    try {
      const res = await fetch('/api/admin/upload', {
        method: 'POST',
        body: uploadData,
      });

      if (res.ok) {
        const data = await res.json();
        if (localeKey === 'vi') {
          setViData((prev: any) => ({ ...prev, image_url: data.url }));
        } else {
          setEnData((prev: any) => ({ ...prev, image_url: data.url }));
        }
        setMessage({ type: 'success', text: `Tải ảnh ${localeKey.toUpperCase()} thành công! Bấm LƯU CẤU HÌNH để áp dụng.` });
      } else {
        setMessage({ type: 'error', text: 'Lỗi tải ảnh lên.' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi kết nối khi tải ảnh.' });
    } finally {
      setUploadingLocale(null);
    }
  };

  // Global Auto Translate for Current Section Structure
  const handleAutoTranslate = async () => {
    setTranslating(true);
    setMessage(null);

    const keys: string[] = [];
    const valuesToTranslate: string[] = [];

    const collectStrings = (obj: any, prefix = '') => {
      if (typeof obj === 'string') {
        if (!prefix.includes('image_url') && !prefix.includes('icon_url') && !prefix.includes('video_url')) {
          keys.push(prefix);
          valuesToTranslate.push(obj);
        }
      } else if (Array.isArray(obj)) {
        obj.forEach((item, idx) => collectStrings(item, `${prefix}[${idx}]`));
      } else if (typeof obj === 'object' && obj !== null) {
        Object.keys(obj).forEach((k) => {
          if (k !== 'image_url' && k !== 'icon_url' && k !== 'video_url') {
            collectStrings(obj[k], prefix ? `${prefix}.${k}` : k);
          }
        });
      }
    };

    collectStrings(viData);

    try {
      const res = await fetch('/api/admin/translate', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ fields: valuesToTranslate }),
      });

      if (res.ok) {
        const data = await res.json();
        const translatedValues: string[] = data.translated || [];

        const newEnData = JSON.parse(JSON.stringify(viData));

        const setDeepValue = (obj: any, path: string, val: string) => {
          const parts = path.replace(/\]/g, '').split(/\.|\[/);
          let curr = obj;
          for (let i = 0; i < parts.length - 1; i++) {
            const key = parts[i];
            if (!curr[key]) return;
            curr = curr[key];
          }
          curr[parts[parts.length - 1]] = val;
        };

        keys.forEach((path, idx) => {
          setDeepValue(newEnData, path, translatedValues[idx] || '');
        });

        newEnData.image_url = enData.image_url || viData.image_url;
        setEnData(newEnData);
        setMessage({ type: 'success', text: 'Đã dịch tự động toàn bộ cấu trúc Section & Nút bấm từ Tiếng Việt sang Tiếng Anh!' });
      } else {
        setMessage({ type: 'error', text: 'Dịch thất bại, vui lòng thử lại sau.' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi kết nối dịch tự động.' });
    } finally {
      setTranslating(false);
    }
  };

  // Handle Save
  const handleSave = async (e: React.FormEvent) => {
    e.preventDefault();
    setSaving(true);
    setMessage(null);

    const currentOrder = sectionsList.findIndex((s) => s.key === selectedKey) + 1;

    const extraVi = { ...viData, video_url: videoUrl, image_url_vi: viData.image_url || '' };
    const extraEn = { ...enData, video_url: videoUrl, image_url_en: enData.image_url || viData.image_url || '' };

    try {
      const res = await fetch('/api/admin/sections', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          page_slug: pageSlug,
          section_key: selectedKey,
          order: currentOrder,
          vi: { title: viData.title || '', subtitle: viData.subtitle || '', body: viData.body || '', extra_json: extraVi },
          en: { title: enData.title || '', subtitle: enData.subtitle || '', body: enData.body || '', extra_json: extraEn },
        }),
      });

      if (res.ok) {
        setMessage({ type: 'success', text: `Lưu cấu hình Section [${selectedKey}] thành công! Dữ liệu đã lưu vào CSDL.` });
      } else {
        setMessage({ type: 'error', text: 'Lỗi khi lưu cấu hình Section.' });
      }
    } catch (err) {
      setMessage({ type: 'error', text: 'Lỗi kết nối tới máy chủ.' });
    } finally {
      setSaving(false);
    }
  };

  // Render Locale Image Input
  const renderLocaleImageInput = (localeKey: 'vi' | 'en') => {
    const data = localeKey === 'vi' ? viData : enData;
    const setData = localeKey === 'vi' ? setViData : setEnData;

    return (
      <MediaPicker
        label={`🖼️ HÌNH ẢNH BANNER / MINH HỌA SECTION (${localeKey.toUpperCase()})`}
        value={data.image_url || ''}
        onChange={(url) => setData({ ...data, image_url: url })}
      />
    );
  };

  // --- TAILORED FORM RENDERER PER LOCALE ---
  const renderTailoredForm = (localeKey: 'vi' | 'en') => {
    const data = localeKey === 'vi' ? viData : enData;
    const setData = localeKey === 'vi' ? setViData : setEnData;

    const handleFieldChange = (field: string, val: any) => {
      setData((prev: any) => ({ ...prev, [field]: val }));
    };

    const handleArrayItemChange = (arrName: string, idx: number, field: string, val: string) => {
      const arr = [...(data[arrName] || [])];
      if (!arr[idx]) arr[idx] = {};
      if (typeof arr[idx] === 'string') {
        arr[idx] = val;
      } else {
        arr[idx][field] = val;
      }

      if (field === 'icon_url') {
        const otherSetData = localeKey === 'vi' ? setEnData : setViData;
        otherSetData((prev: any) => {
          const otherArr = [...(prev[arrName] || [])];
          if (!otherArr[idx]) otherArr[idx] = {};
          otherArr[idx].icon_url = val;
          return { ...prev, [arrName]: otherArr };
        });
      }

      setData((prev: any) => ({ ...prev, [arrName]: arr }));
    };

    const addArrayItem = (arrName: string, defaultObj: any) => {
      const arrVi = [...(viData[arrName] || []), defaultObj];
      const arrEn = [...(enData[arrName] || []), defaultObj];
      setViData((prev: any) => ({ ...prev, [arrName]: arrVi }));
      setEnData((prev: any) => ({ ...prev, [arrName]: arrEn }));
    };

    const removeArrayItem = (arrName: string, idx: number) => {
      setViData((prev: any) => ({ ...prev, [arrName]: (prev[arrName] || []).filter((_: any, i: number) => i !== idx) }));
      setEnData((prev: any) => ({ ...prev, [arrName]: (prev[arrName] || []).filter((_: any, i: number) => i !== idx) }));
    };

    const currentBlueprint = sectionsList.find((s) => s.key === selectedKey)?.blueprint || 'FeatureCards';

    return (
      <div className="space-y-4">
        {/* PER-LOCALE IMAGE CONTROL */}
        {renderLocaleImageInput(localeKey)}

        {/* TITLE FIELD */}
        <div>
          <label className="block text-xs font-bold text-slate-700 mb-1">Tiêu đề chính ({localeKey.toUpperCase()})</label>
          <input
            type="text"
            value={data.title || ''}
            onChange={(e) => handleFieldChange('title', e.target.value)}
            placeholder="Nhập tiêu đề..."
            className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-bold text-[#0b7f7c]"
          />
        </div>

        {/* SUBTITLE FIELD */}
        <div>
          <label className="block text-xs font-bold text-slate-700 mb-1">Phụ đề / Dòng dẫn ({localeKey.toUpperCase()})</label>
          <input
            type="text"
            value={data.subtitle || ''}
            onChange={(e) => handleFieldChange('subtitle', e.target.value)}
            placeholder="Nhập phụ đề..."
            className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm"
          />
        </div>

        {/* BODY FIELD */}
        <div>
          <label className="block text-xs font-bold text-slate-700 mb-1">Đoạn mô tả chi tiết ({localeKey.toUpperCase()})</label>
          <textarea
            rows={4}
            value={data.body || ''}
            onChange={(e) => handleFieldChange('body', e.target.value)}
            placeholder="Nhập nội dung..."
            className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm resize-none"
          />
        </div>

        {/* CTA BUTTON FIELDS */}
        {(currentBlueprint === 'HeroBanner' || currentBlueprint === 'ContentWithImage') && (
          <div className="p-3.5 bg-slate-100 rounded-xl border border-slate-200 space-y-2">
            <label className="block text-[11px] font-bold text-[#0b7f7c] uppercase">🔘 NÚT BẤM HÀNH ĐỘNG (CTA BUTTON - {localeKey.toUpperCase()})</label>
            <div className="grid grid-cols-2 gap-2">
              <input
                type="text"
                value={data.cta_label || ''}
                onChange={(e) => handleFieldChange('cta_label', e.target.value)}
                placeholder="Tên nút (VD: KHÁM PHÁ NGAY)..."
                className="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-bold"
              />
              <input
                type="text"
                value={data.cta_url || ''}
                onChange={(e) => handleFieldChange('cta_url', e.target.value)}
                placeholder="Link nút (VD: /vi/lien-he)..."
                className="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-mono"
              />
            </div>
          </div>
        )}

        {/* SUB-ITEMS LIST WITH SHARED ICON */}
        {(data.items || []).length > 0 && (
          <div className="pt-2 border-t border-slate-200 space-y-3">
            <div className="flex items-center justify-between">
              <label className="text-xs font-bold text-[#0b7f7c] uppercase">Danh sách Thẻ con ({data.items.length}) với Icon chung</label>
              <button type="button" onClick={() => addArrayItem('items', { title: '', body: '', icon_url: '' })} className="text-[11px] font-bold text-[#0b7f7c] underline">
                + Thêm thẻ
              </button>
            </div>
            {data.items.map((it: any, idx: number) => (
              <div key={idx} className="bg-white p-3.5 rounded-xl border border-slate-200 space-y-2">
                <div className="flex justify-between items-center text-[11px] font-bold text-slate-400">
                  <span>Thẻ #{idx + 1}</span>
                  <button type="button" onClick={() => removeArrayItem('items', idx)} className="text-red-500 hover:underline">Xóa</button>
                </div>

                {/* Shared Icon Picker */}
                <div className="flex items-center gap-2 bg-slate-50 p-2 rounded-lg border border-slate-200">
                  <span className="text-[11px] font-bold text-slate-500 shrink-0">🎨 Icon chung:</span>
                  <input
                    type="text"
                    value={it.icon_url || ''}
                    onChange={(e) => handleArrayItemChange('items', idx, 'icon_url', e.target.value)}
                    placeholder="/images/home/values/icon-1.png"
                    className="flex-1 rounded-md border border-slate-300 px-2.5 py-1 text-xs font-mono"
                  />
                  {it.icon_url && <img src={it.icon_url} alt="" className="h-6 w-6 object-contain" />}
                </div>

                <input
                  type="text"
                  value={it.title || ''}
                  onChange={(e) => handleArrayItemChange('items', idx, 'title', e.target.value)}
                  placeholder={`Tiêu đề thẻ (${localeKey.toUpperCase()})...`}
                  className="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-bold text-[#d31e45]"
                />
                <textarea
                  rows={2}
                  value={it.body || ''}
                  onChange={(e) => handleArrayItemChange('items', idx, 'body', e.target.value)}
                  placeholder={`Mô tả thẻ (${localeKey.toUpperCase()})...`}
                  className="w-full rounded-lg border border-slate-200 px-3 py-1.5 text-xs resize-none"
                />
              </div>
            ))}
          </div>
        )}

        {/* BULLETS LIST */}
        {(data.bullets || []).length > 0 && (
          <div className="pt-2 border-t border-slate-200 space-y-2">
            <div className="flex items-center justify-between">
              <label className="text-xs font-bold text-[#0b7f7c] uppercase">Gạch đầu dòng điểm tích ({data.bullets.length})</label>
              <button type="button" onClick={() => addArrayItem('bullets', '')} className="text-[11px] font-bold text-[#0b7f7c] underline">
                + Thêm dòng
              </button>
            </div>
            {data.bullets.map((b: string, idx: number) => (
              <div key={idx} className="flex items-center gap-2">
                <span className="text-xs text-slate-400">#{idx + 1}</span>
                <input
                  type="text"
                  value={b || ''}
                  onChange={(e) => handleArrayItemChange('bullets', idx, '', e.target.value)}
                  placeholder={`Gạch đầu dòng (${localeKey.toUpperCase()})...`}
                  className="flex-1 rounded-lg border border-slate-200 px-3 py-1.5 text-xs"
                />
                <button type="button" onClick={() => removeArrayItem('bullets', idx)} className="text-red-500 hover:underline text-xs">Xóa</button>
              </div>
            ))}
          </div>
        )}
      </div>
    );
  };

  const selectedBlueprintName = BLUEPRINT_TEMPLATES.find(
    (b) => b.key === (sectionsList.find((s) => s.key === selectedKey)?.blueprint || 'FeatureCards')
  )?.name;

  return (
    <div className="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6 font-utm-helve">
      {/* Dynamic Section Management Header with Reorder, Delete & Add Controls */}
      <div className="border-b border-slate-200 pb-5 space-y-3">
        <div className="flex flex-wrap items-center justify-between gap-3">
          <div>
            <label className="block text-xs font-bold uppercase text-slate-500">
              DANH SÁCH KHỐI SECTION CỦA TRANG [{pageSlug.toUpperCase()}]
            </label>
            <p className="text-[11px] text-slate-400">
              Biên soạn, sắp xếp thứ tự 🔼 🔽 hoặc thêm/xóa khối Section tùy biến kiểu Landing Page
            </p>
          </div>
          <button
            type="button"
            onClick={() => setShowAddModal(true)}
            className="inline-flex items-center gap-1.5 px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition shadow-md"
          >
            <span>➕ THÊM KHỐI SECTION MỚI</span>
          </button>
        </div>

        {/* Section List Tabs */}
        <div className="flex flex-wrap gap-2 pt-1">
          {sectionsList.map((sec, idx) => {
            const isSelected = selectedKey === sec.key;
            const bPrintName = BLUEPRINT_TEMPLATES.find((b) => b.key === sec.blueprint)?.name || sec.blueprint;
            return (
              <button
                key={sec.key}
                type="button"
                onClick={() => setSelectedKey(sec.key)}
                className={`px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 ${
                  isSelected
                    ? 'bg-[#0b7f7c] text-white shadow-md'
                    : 'bg-slate-100 text-slate-700 hover:bg-slate-200'
                }`}
              >
                <span>#{idx + 1}</span>
                <span>{sec.name}</span>
                <span className={`text-[10px] px-1.5 py-0.5 rounded ${isSelected ? 'bg-teal-900/40 text-teal-100' : 'bg-slate-200 text-slate-600'}`}>
                  {sec.blueprint}
                </span>
              </button>
            );
          })}
        </div>

        {/* Selected Section Blueprint Badge & Reorder/Delete Toolbar */}
        {selectedKey && (
          <div className="flex flex-wrap items-center justify-between gap-3 pt-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
            <div className="flex items-center gap-2 text-xs">
              <span className="font-bold text-slate-500">Đang chọn Khối:</span>
              <span className="font-bold text-[#0b7f7c] uppercase">[{selectedKey}]</span>
              <span className="font-bold text-slate-700 bg-white px-2.5 py-1 rounded-md border border-slate-200 shadow-xs">
                {selectedBlueprintName}
              </span>
            </div>

            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={() => handleReorder('up')}
                className="px-3 py-1.5 bg-slate-200 text-slate-800 rounded-lg text-xs font-bold hover:bg-slate-300 transition"
              >
                ⬆️ Đưa Lên Trên
              </button>
              <button
                type="button"
                onClick={() => handleReorder('down')}
                className="px-3 py-1.5 bg-slate-200 text-slate-800 rounded-lg text-xs font-bold hover:bg-slate-300 transition"
              >
                ⬇️ Đưa Xuống Dưới
              </button>
              <button
                type="button"
                onClick={handleDeleteSection}
                className="px-3 py-1.5 bg-rose-500 text-white rounded-lg text-xs font-bold hover:bg-rose-600 transition"
              >
                🗑️ Xóa Khối Này
              </button>
            </div>
          </div>
        )}
      </div>

      {message && (
        <div
          className={`p-4 rounded-xl text-xs font-bold ${
            message.type === 'success'
              ? 'bg-emerald-50 border border-emerald-200 text-emerald-800'
              : 'bg-red-50 border border-red-200 text-red-800'
          }`}
        >
          {message.text}
        </div>
      )}

      <form onSubmit={handleSave} className="space-y-8">
        {/* VIDEO CONTROLS FOR VIDEO SECTIONS */}
        {selectedKey === 'vision_mission' && (
          <div className="bg-amber-50 p-4 rounded-2xl border border-amber-200 space-y-2">
            <label className="block text-xs font-bold uppercase text-amber-900">
              🎬 LINK VIDEO PHÁT TRỰC TIẾP (HLS / MP4 / YOUTUBE URL)
            </label>
            <input
              type="text"
              value={videoUrl}
              onChange={(e) => setVideoUrl(e.target.value)}
              placeholder="https://www.youtube.com/watch?v=... hoặc https://...m3u8"
              className="w-full rounded-xl border border-amber-300 bg-white px-4 py-2.5 text-xs font-mono text-amber-950 outline-none"
            />
          </div>
        )}

        {/* SIDE-BY-SIDE LOCALE TAILORED FORM (VI vs EN) */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
          {/* Vietnamese Column */}
          <div className="space-y-4 bg-slate-50 p-6 rounded-2xl border border-slate-200">
            <h3 className="font-bold text-sm text-[#0b7f7c] uppercase flex items-center gap-2 border-b border-slate-200 pb-3">
              <span>🇻🇳</span> CẤU TRÚC TIẾNG VIỆT (VIETNAMESE)
            </h3>
            {renderTailoredForm('vi')}
          </div>

          {/* English Column */}
          <div className="space-y-4 bg-slate-50 p-6 rounded-2xl border border-slate-200">
            <div className="flex items-center justify-between border-b border-slate-200 pb-3">
              <h3 className="font-bold text-sm text-[#0b7f7c] uppercase flex items-center gap-2">
                <span>🇬🇧</span> CẤU TRÚC TIẾNG ANH (ENGLISH)
              </h3>
              <button
                type="button"
                onClick={handleAutoTranslate}
                disabled={translating}
                className="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-blue-700 bg-blue-50 border border-blue-200 rounded-xl hover:bg-blue-100 transition disabled:opacity-50 shadow-sm"
              >
                {translating ? <span>⏳ Đang dịch...</span> : <span>🌐 DỊCH TỰ ĐỘNG CẤU TRÚC (VI ➔ EN)</span>}
              </button>
            </div>
            {renderTailoredForm('en')}
          </div>
        </div>

        <div className="pt-4 flex justify-end">
          <button
            type="submit"
            disabled={saving}
            className="rounded-xl bg-[#0b7f7c] px-8 py-3.5 font-bold uppercase text-white hover:bg-[#096d6a] transition shadow-lg text-xs"
          >
            {saving ? '⏳ Đang lưu...' : '💾 LƯU CẤU HÌNH SECTION NÀY'}
          </button>
        </div>
      </form>

      {/* MODAL TO ADD NEW SECTION WITH BLUEPRINT SELECTION */}
      {showAddModal && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
          <div className="w-full max-w-lg bg-white rounded-2xl shadow-2xl border border-slate-200 p-6 space-y-5 animate-in fade-in zoom-in duration-200">
            <div className="flex justify-between items-center border-b border-slate-200 pb-3">
              <h3 className="text-sm font-bold text-[#0b7f7c] uppercase">
                ➕ THÊM KHỐI SECTION MỚI CHO TRANG [{pageSlug.toUpperCase()}]
              </h3>
              <button type="button" onClick={() => setShowAddModal(false)} className="text-slate-400 hover:text-slate-600 text-lg font-bold">
                ✕
              </button>
            </div>

            <form onSubmit={handleAddNewSection} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Tên hiển thị khối Section</label>
                <input
                  type="text"
                  required
                  value={newSecName}
                  onChange={(e) => {
                    setNewSecName(e.target.value);
                    if (!newSecKey) setNewSecKey(e.target.value.toLowerCase().replace(/[^a-z0-9_]/g, '_'));
                  }}
                  placeholder="VD: Khối Thẻ Đối Tác Tiêu Biểu..."
                  className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-bold"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Mã định danh Section Key (Chữ viết liền không dấu)</label>
                <input
                  type="text"
                  required
                  value={newSecKey}
                  onChange={(e) => setNewSecKey(e.target.value)}
                  placeholder="VD: partner_cards..."
                  className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-mono"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-slate-700 mb-1">Chọn Kiểu Mẫu Section Blueprint (Phân Loại)</label>
                <select
                  value={newSecBlueprint}
                  onChange={(e) => setNewSecBlueprint(e.target.value)}
                  className="w-full rounded-xl border border-slate-300 px-4 py-2.5 text-xs font-bold bg-white focus:border-[#0b7f7c] outline-none"
                >
                  {BLUEPRINT_TEMPLATES.map((b) => (
                    <option key={b.key} value={b.key}>
                      {b.name}
                    </option>
                  ))}
                </select>
              </div>

              <div className="pt-3 flex justify-end gap-3 border-t border-slate-200">
                <button
                  type="button"
                  onClick={() => setShowAddModal(false)}
                  className="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold hover:bg-slate-200"
                >
                  Hủy Bỏ
                </button>
                <button
                  type="submit"
                  className="px-6 py-2 bg-[#0b7f7c] text-white rounded-xl text-xs font-bold hover:bg-[#086a67] transition shadow-md"
                >
                  Tạo Khối Section
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
