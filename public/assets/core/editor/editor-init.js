/**
 * CKEditor 5 initializer — auto-attaches to all textarea.editor-ckeditor elements.
 * Requires CKEditor 5 superbuild (CKEDITOR global) and ckeditor-upload-adapter.js
 * to be loaded first.
 *
 * The superbuild bundles premium/collaboration plugins that are disabled here
 * via removePlugins to avoid the collaboration-missing-channelid error.
 *
 * Image insertion goes through the application's Media Gallery (Media Manager
 * popup), NOT a local file upload: the toolbar "Media Gallery" button and the
 * external perm_media button both open the gallery and insert the chosen URL.
 * The XHR upload adapter is still wired so paste / drag-drop of a new file keeps
 * working (those land in the app's media storage via the upload endpoint).
 */
(function () {
    const currentScript = document.currentScript;
    const uploadUrl     = currentScript?.dataset.editorUploadUrl || '';
    const mediaUrl      = currentScript?.dataset.editorMediaUrl  || '';

    // Premium/collaboration plugins bundled in the superbuild that we don't use
    const REMOVE_PLUGINS = [
        'AIAssistant',
        'CKBox',
        'CKFinder',
        'EasyImage',
        'RealTimeCollaborativeComments',
        'RealTimeCollaborativeTrackChanges',
        'RealTimeCollaborativeRevisionHistory',
        'PresenceList',
        'Comments',
        'TrackChanges',
        'TrackChangesData',
        'RevisionHistory',
        'Pagination',
        'WProofreader',
        'MathType',
        'SlashCommand',
        'Template',
        'DocumentOutline',
        'FormatPainter',
        'TableOfContents',
        'PasteFromOfficeEnhanced',
        'CaseChange',
        'MultiLevelList',
        'ExportPdf',
        'ExportWord',
        'ImportWord',
        'ChangelogAdapter',
    ];

    // Photo icon for the in-toolbar Media Gallery button (CKEditor expects raw SVG).
    const MEDIA_ICON = '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">'
        + '<path d="M2.5 1h15A1.5 1.5 0 0 1 19 2.5v15a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 1 17.5v-15A1.5 1.5 0 0 1 2.5 1zM2 12.293V17.5a.5.5 0 0 0 .5.5h15a.5.5 0 0 0 .5-.5v-3.207l-5-5-3.146 3.147a.5.5 0 0 1-.708 0L6 8.793l-4 3.5zM18 2.5a.5.5 0 0 0-.5-.5h-15a.5.5 0 0 0-.5.5v8.451l3.646-3.19a.5.5 0 0 1 .638.018L9 11.293l3.146-3.147a.5.5 0 0 1 .708 0L18 12.793V2.5zM13.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z"/>'
        + '</svg>';

    function getCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    function uploadAdapterPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = function (loader) {
            return new window.CKEditorUploadAdapter(loader, uploadUrl, getCsrf());
        };
    }

    /**
     * Open the application's Media Gallery (Media Manager popup) and insert the
     * picked media into `editor` as an image. Shared by the toolbar button and
     * the external perm_media button so behaviour is identical.
     */
    function openMediaGallery(editor) {
        if (!mediaUrl) return;
        const srcEl = editor.sourceElement;
        const srcId = srcEl ? srcEl.id : '';

        window.open(
            mediaUrl + '&editor_id=' + encodeURIComponent(srcId),
            'media_manager',
            'width=1100,height=700,resizable=yes,scrollbars=yes'
        );
        window._ckEditorForMedia = editor;

        window.addEventListener('message', function onMsg(e) {
            if (e.data && e.data.type === 'media_selected' && e.data.url) {
                editor.model.change(function (writer) {
                    const img = writer.createElement('imageBlock', { src: e.data.url });
                    editor.model.insertContent(img, editor.model.document.selection);
                });
                window.removeEventListener('message', onMsg);
            }
        });
    }

    /**
     * Registers an "insertMediaGallery" toolbar button. The superbuild CDN global
     * only exposes the editor classes (no ButtonView), so we harvest the ButtonView
     * constructor from an existing factory component ('undo') at build time.
     */
    // The superbuild CDN global does not export ButtonView, so it is harvested
    // once from an existing factory component ('undo') and cached here.
    let HarvestedButtonView = null;

    function resolveButtonView(editor) {
        if (HarvestedButtonView) return HarvestedButtonView;
        const probe = editor.ui.componentFactory.create('undo');
        HarvestedButtonView = probe.constructor;
        try { if (typeof probe.destroy === 'function') probe.destroy(); } catch (e) { /* ignore */ }
        return HarvestedButtonView;
    }

    function mediaGalleryButtonPlugin(editor) {
        editor.ui.componentFactory.add('insertMediaGallery', function (locale) {
            let ButtonView;
            try {
                ButtonView = resolveButtonView(editor);
            } catch (err) {
                console.error('[CKEditor] Unable to resolve ButtonView for Media Gallery button', err);
                throw err;
            }

            const view = new ButtonView(locale);
            view.set({
                label: 'Media Gallery',
                icon: MEDIA_ICON,
                tooltip: true,
                withText: false,
            });
            view.on('execute', function () {
                openMediaGallery(editor);
            });
            return view;
        });
    }

    function initEditor(textarea) {
        if (textarea.dataset.ckInitialized) return;
        textarea.dataset.ckInitialized = '1';

        const Editor = (typeof CKEDITOR !== 'undefined' && CKEDITOR.ClassicEditor)
            ? CKEDITOR.ClassicEditor
            : window.ClassicEditor;

        if (!Editor) {
            console.error('[CKEditor] ClassicEditor not found. Ensure CKEditor CDN is loaded.');
            textarea.dataset.ckInitialized = '';
            return;
        }

        Editor
            .create(textarea, {
                removePlugins: REMOVE_PLUGINS,
                extraPlugins: [uploadAdapterPlugin, mediaGalleryButtonPlugin],
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', 'strikethrough', '|',
                        'fontColor', 'fontBackgroundColor', '|',
                        'alignment', '|',
                        'bulletedList', 'numberedList', '|',
                        'outdent', 'indent', '|',
                        // insertMediaGallery opens the app Media Gallery instead of a
                        // local file upload (replaces the old `uploadImage` button).
                        'link', 'insertMediaGallery', 'blockQuote', 'insertTable', '|',
                        // sourceEditing lets admin paste raw HTML (e.g. <video data-hls-src="...">
                        // for HLS streams). htmlEmbed adds an inline embed widget too.
                        'sourceEditing', 'htmlEmbed', '|',
                        'undo', 'redo',
                    ],
                    shouldNotGroupWhenFull: false,
                },
                // Preserve raw HTML pasted via sourceEditing. Without this CKEditor
                // strips unknown elements/attrs — `<video data-hls-src>` would vanish.
                htmlSupport: {
                    allow: [
                        { name: 'video', attributes: true, classes: true, styles: true },
                        { name: 'source', attributes: true },
                        { name: 'iframe', attributes: true, classes: true, styles: true },
                    ],
                },
                image: {
                    toolbar: [
                        'imageStyle:inline', 'imageStyle:block', 'imageStyle:side',
                        '|', 'imageTextAlternative',
                    ],
                },
                table: {
                    contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells'],
                },
            })
            .then(function (editor) {
                textarea._ckEditor = editor;

                // Debug aid: log loaded plugin names so we can see what's available.
                // Uncomment when diagnosing missing toolbar items.
                // console.log('[CKEditor plugins]', Array.from(editor.plugins).map(p => p[0].pluginName).filter(Boolean));

                // Wire up "Insert HLS video" button — inserts <video data-hls-src>
                // via the data processor + model conversion. Works because the
                // htmlSupport config above allows <video> + arbitrary attributes.
                const wrapperRoot = textarea.closest('.ck-editor-wrapper');
                if (wrapperRoot) {
                    const hlsBtn = wrapperRoot.querySelector('.js-editor-hls-btn');
                    if (hlsBtn) {
                        hlsBtn.addEventListener('click', function () {
                            const url = window.prompt('Dán URL stream HLS (.m3u8):', '');
                            if (!url || !url.trim()) return;
                            const trimmed = url.trim().replace(/"/g, '&quot;');
                            const html = '<p><video controls width="100%" data-hls-src="' + trimmed + '"></video></p>';
                            const viewFragment = editor.data.processor.toView(html);
                            const modelFragment = editor.data.toModel(viewFragment);
                            editor.model.insertContent(modelFragment);
                        });
                    }
                }

                // Wire up the external "Media Gallery" (perm_media) button — same
                // flow as the in-toolbar button.
                const wrapper = textarea.closest('.ck-editor-wrapper');
                if (wrapper && mediaUrl) {
                    const btn = wrapper.querySelector('.js-editor-media-btn');
                    if (btn) {
                        btn.addEventListener('click', function () {
                            openMediaGallery(editor);
                        });
                    }
                }
            })
            .catch(function (err) {
                console.error('[CKEditor] Init error on #' + textarea.id + ':', err);
                textarea.dataset.ckInitialized = '';
            });
    }

    function initAll() {
        document.querySelectorAll('textarea.editor-ckeditor').forEach(initEditor);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
