/**
 * CKMedia.js — jQuery media manager for the Core package.
 *
 * Depends on: jQuery 3.x, Toastr, Cropper.js, jquery-cropper, GLightbox,
 *             jquery.contextMenu, Alpine.js (for panel transitions).
 *
 * Reads globals:
 *   window.MediaRoutes  — route URLs injected by the blade template
 *   window.MediaBaseUrl — base URL prefix for file permalinks (e.g. /uploads)
 */
(function ($) {
    'use strict';

    /* ------------------------------------------------------------------ */
    /* State                                                                */
    /* ------------------------------------------------------------------ */
    const state = {
        folder:     0,
        filter:     'everything',
        viewIn:     'all_media',
        sortBy:     'name-desc',
        search:     '',
        page:       1,
        loadMore:   false,
        loadType:   'file',
        loading:    false,
        selected:   null,   // { id, type, $el }
        cropperInst: null,
    };

    const ROUTES     = window.MediaRoutes  || {};
    const BASE_URL   = window.MediaBaseUrl || '/uploads';
    const CSRF       = $('[name="nkd-csrf-token"]').val()
                       || $('meta[name="csrf-token"]').attr('content')
                       || '';
    const IS_POPUP   = (new URLSearchParams(window.location.search)).get('popup') === '1';

    /* ------------------------------------------------------------------ */
    /* Helpers                                                              */
    /* ------------------------------------------------------------------ */
    function fileUrl(permalink) {
        if (!permalink) return '';
        if (permalink.startsWith('http')) return permalink;
        // Guarantee a single `/` between BASE_URL and permalink — both sides may
        // independently start/end with `/`.
        return BASE_URL.replace(/\/+$/, '') + '/' + permalink.replace(/^\/+/, '');
    }

    function formatBytes(bytes) {
        if (!bytes) return '—';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / 1048576).toFixed(1) + ' MB';
    }

    function getFileIcon(mime) {
        if (!mime) return 'insert_drive_file';
        if (mime.startsWith('image/'))  return 'image';
        if (mime.startsWith('video/'))  return 'videocam';
        if (mime.startsWith('audio/'))  return 'audiotrack';
        if (mime.includes('pdf'))       return 'picture_as_pdf';
        if (mime.includes('word') || mime.includes('document')) return 'description';
        if (mime.includes('excel') || mime.includes('sheet'))   return 'table_chart';
        if (mime.includes('zip') || mime.includes('archive'))   return 'folder_zip';
        return 'insert_drive_file';
    }

    function getIconColor(mime) {
        if (!mime) return 'text-slate-400';
        if (mime.startsWith('image/'))  return 'text-emerald-500';
        if (mime.startsWith('video/'))  return 'text-purple-500';
        if (mime.includes('pdf'))       return 'text-red-500';
        if (mime.includes('word'))      return 'text-blue-500';
        if (mime.includes('excel'))     return 'text-green-600';
        return 'text-slate-400';
    }

    function renderTemplate(id, replacements) {
        let html = document.getElementById(id)?.innerHTML || '';
        Object.entries(replacements).forEach(([k, v]) => {
            html = html.replace(new RegExp('__' + k + '__', 'g'), v ?? '');
        });
        return html;
    }

    function showLoading()  { $('#media-loading').addClass('active'); }
    function hideLoading()  { $('#media-loading').removeClass('active'); }

    function setAlpineDetail(visible) {
        try {
            const el = document.querySelector('#media-container[x-data]')
                    || document.querySelector('[x-data]');
            if (el && el._x_dataStack) {
                el._x_dataStack[0].showDetail = visible;
            }
        } catch (e) { /* Alpine not ready */ }
    }

    /* ------------------------------------------------------------------ */
    /* Load media                                                           */
    /* ------------------------------------------------------------------ */
    function loadMedia(append) {
        if (state.loading) return;
        state.loading = true;
        showLoading();

        const params = {
            folder_id:      state.folder,
            filter_type:    state.filter === 'everything' ? '' : state.filter,
            view_in:        state.viewIn,
            sort_by:        state.sortBy,
            search:         state.search,
            paged:          append ? state.page : 1,
            posts_per_page: 30,
            load_more:      append ? 'true' : 'false',
            type:           state.loadType,
        };

        if (!append) {
            state.page    = 1;
            state.loadMore = false;
        }

        $.get(ROUTES.loadMedia, params)
            .done(function (res) {
                if (!res.success) { toastr.error('Failed to load media'); return; }
                const data = res.data;

                if (append) {
                    renderAppend(data);
                } else {
                    renderGrid(data);
                }

                renderBreadcrumbs(data.breadcrumbs || []);
                state.loadMore = res.load_more;
                state.loadType = res.type || 'file';
                if (append) state.page = res.next || (state.page + 1);
            })
            .fail(function () { toastr.error('Network error'); })
            .always(function () {
                state.loading = false;
                hideLoading();
            });
    }

    function renderGrid(data) {
        const $grid = $('.media-grid');
        const $upload = $grid.find('.js-button-upload').detach();
        $grid.empty().prepend($upload);
        appendItems(data);
    }

    function renderAppend(data) {
        appendItems(data);
    }

    function appendItems(data) {
        const $grid = $('.media-grid');

        (data.folders || []).forEach(function (folder) {
            $grid.append(renderTemplate('folder-template', {
                id:   folder.id,
                name: $('<div>').text(folder.name).html(),
            }));
        });

        (data.files || []).forEach(function (file) {
            const mime   = file.mine_type || '';
            const url    = fileUrl(file.permalink || '');
            const isImg  = mime.startsWith('image/');
            const ext    = (file.permalink || '').split('.').pop().toUpperCase();
            const thumb  = isImg
                ? '<img src="' + url + '" class="media-thumbnail-img" loading="lazy" alt="">'
                : '<div class="flex items-center justify-center w-full h-full"><span class="material-symbols-rounded text-5xl ' + getIconColor(mime) + '">' + getFileIcon(mime) + '</span></div>';

            $grid.append(renderTemplate('file-template', {
                id:             file.id,
                name:           $('<div>').text(file.name).html(),
                url:            url,
                mime:           mime,
                size:           file.size || 0,
                alt:            $('<div>').text(file.alt || '').html(),
                thumbnail:      thumb,
                ext:            ext,
                size_formatted: formatBytes(file.size),
            }));
        });

        bindItemEvents();
    }

    /* ------------------------------------------------------------------ */
    /* Breadcrumbs                                                          */
    /* ------------------------------------------------------------------ */
    function renderBreadcrumbs(crumbs) {
        const $bc = $('.js-breadcrumb-items');
        $bc.empty();
        crumbs.forEach(function (crumb, i) {
            if (i === 0) return; // root is the static link in HTML
            $bc.append(
                '<span class="text-slate-300 mx-1">/</span>' +
                '<a href="#" data-folder="' + crumb.id + '" ' +
                'class="hover:text-primary transition-colors js-change-folder">' +
                $('<div>').text(crumb.name).html() + '</a>'
            );
        });
    }

    /* ------------------------------------------------------------------ */
    /* Item events (rebind after each render)                               */
    /* ------------------------------------------------------------------ */
    function bindItemEvents() {
        const $grid = $('.media-grid');

        $grid.find('.media-item').off('click dblclick').on('click', function (e) {
            if ($(e.target).is('input[type=checkbox]')) return;
            selectItem($(this));
        }).on('dblclick', function () {
            const $el = $(this);
            if ($el.data('type') === 'folder') {
                navigateTo($el.data('id'), $el.data('name'));
            }
        });

        initContextMenu();
    }

    /* ------------------------------------------------------------------ */
    /* Select item → detail panel                                           */
    /* ------------------------------------------------------------------ */
    function selectItem($el) {
        $('.media-item.selected').removeClass('selected');
        $el.addClass('selected');
        state.selected = { id: $el.data('id'), type: $el.data('type'), $el };
        showDetail($el);
    }

    function showDetail($el) {
        setAlpineDetail(true);

        const type = $el.data('type');
        const $preview = $('.js-detail-preview');
        const $content = $('.js-detail-content');

        $preview.empty();
        $content.empty();

        if (type === 'folder') {
            $preview.html('<div class="flex items-center justify-center w-full h-full">' +
                '<span class="material-symbols-rounded text-6xl text-amber-500">folder</span></div>');
            $content.html(renderTemplate('folder-detail-template', {
                name: $('<div>').text($el.data('name')).html(),
                date: '—',
            }));
        } else if (type === 'file') {
            const url  = $el.data('url') || '';
            const mime = $el.data('mime') || '';
            if (mime.startsWith('image/')) {
                $preview.html('<img src="' + url + '" class="w-full h-full object-contain" alt="">');
            } else if (mime.startsWith('video/')) {
                $preview.html('<video src="' + url + '" class="w-full h-full object-contain" controls></video>');
            } else {
                $preview.html('<div class="flex items-center justify-center w-full h-full">' +
                    '<span class="material-symbols-rounded text-6xl ' + getIconColor(mime) + '">' + getFileIcon(mime) + '</span></div>');
            }

            const tplId = state.viewIn === 'trash' ? 'trash-detail-template' : 'detail-template';
            const ext = url.split('.').pop().toUpperCase();
            $content.html(renderTemplate(tplId, {
                name:       $('<div>').text($el.data('name')).html(),
                date:       '—',
                dimensions: '—',
                size:       formatBytes($el.data('size')),
                ext:        ext,
                mime:       mime,
                url:        url,
                alt:        $el.data('alt') || '',
            }));

            // Bind detail action buttons
            if (IS_POPUP) {
                $content.find('.js-insert-to-editor').on('click', function () {
                    if (window.opener) {
                        window.opener.postMessage({
                            type: 'media_selected',
                            url: url,
                            id: $el.data('id'),
                            name: $el.data('name'),
                            alt: $el.data('alt') || '',
                        }, '*');
                        window.close();
                    }
                });
            }

            $content.find('.js-copy-url').on('click', function () {
                navigator.clipboard.writeText(url).then(function () {
                    toastr.success('URL copied!');
                });
            });

            $content.find('.js-save-changes').on('click', function () {
                saveAlt($el.data('id'), $content.find('.js-alt-input').val());
            });

            $content.find('.js-crop-action').on('click', function () {
                openCropper(url, $el.data('id'));
            });

            $content.find('.js-rename-action').on('click', function () {
                doRename($el.data('id'), $el.data('name'), false);
            });

            $content.find('.js-delete-action').on('click', function () {
                doTrash($el.data('id'), false);
            });

            $content.find('.js-restore-action').on('click', function () {
                doRestore($el.data('id'), false);
            });

            $content.find('.js-delete-permanently-action').on('click', function () {
                doDestroyFinal($el.data('id'), false);
            });
        }

        if (type === 'folder') {
            $content.find('.js-rename-action').on('click', function () {
                doRename($el.data('id'), $el.data('name'), true);
            });
            $content.find('.js-delete-action').on('click', function () {
                doTrash($el.data('id'), true);
            });
        }
    }

    /* ------------------------------------------------------------------ */
    /* Navigation                                                           */
    /* ------------------------------------------------------------------ */
    function navigateTo(id, name) {
        state.folder = id;
        setAlpineDetail(false);
        state.selected = null;
        loadMedia(false);
    }

    /* ------------------------------------------------------------------ */
    /* Upload                                                               */
    /* ------------------------------------------------------------------ */
    function uploadFiles(files) {
        if (!files || !files.length) return;
        const $grid = $('.media-grid');

        Array.from(files).forEach(function (file) {
            const fd = new FormData();
            // Field names MUST match FileRequest rules — `files[]` (plural) + `folderId` (camelCase).
            fd.append('files[]', file);
            fd.append('folderId', state.folder);
            fd.append('_token', CSRF);

            showLoading();
            $.ajax({
                url:         ROUTES.upload,
                method:      'POST',
                data:        fd,
                processData: false,
                contentType: false,
            }).done(function (res) {
                if (res && res.success !== false) {
                    toastr.success('Uploaded: ' + file.name);
                    loadMedia(false);
                } else {
                    toastr.error((res && res.message) || 'Upload failed');
                }
            }).fail(function () {
                toastr.error('Upload failed: ' + file.name);
            }).always(function () {
                hideLoading();
            });
        });
    }

    /* ------------------------------------------------------------------ */
    /* CRUD operations                                                      */
    /* ------------------------------------------------------------------ */
    function saveAlt(id, alt) {
        $.ajax({
            url:    ROUTES.updateData,
            method: 'PUT',
            data:   { _token: CSRF, id: id, alt: alt },
        }).done(function (res) {
            toastr.success('Saved');
            if (state.selected && state.selected.id == id) {
                state.selected.$el.data('alt', alt);
            }
        }).fail(function () {
            toastr.error('Failed to save');
        });
    }

    function doRename(id, currentName, isFolder) {
        const newName = prompt('Rename:', currentName);
        if (!newName || newName === currentName) return;
        $.ajax({
            url:    ROUTES.updateName,
            method: 'POST',
            data:   { _token: CSRF, id: id, name: newName, is_folder: isFolder ? 'true' : 'false' },
        }).done(function (res) {
            if (res.success) {
                toastr.success('Renamed');
                loadMedia(false);
                setAlpineDetail(false);
            } else {
                toastr.error(res.message || 'Rename failed');
            }
        }).fail(function () { toastr.error('Rename failed'); });
    }

    function doTrash(id, isFolder) {
        if (!confirm('Move to trash?')) return;
        $.ajax({
            url:    ROUTES.trash,
            method: 'DELETE',
            data:   { _token: CSRF, id: id, is_folder: isFolder ? 'true' : 'false' },
        }).done(function (res) {
            toastr.success(res.message || 'Moved to trash');
            loadMedia(false);
            setAlpineDetail(false);
        }).fail(function () { toastr.error('Failed'); });
    }

    function doRestore(id, isFolder) {
        $.ajax({
            url:    ROUTES.restore,
            method: 'PUT',
            data:   { _token: CSRF, id: id, is_folder: isFolder ? 'true' : 'false' },
        }).done(function (res) {
            toastr.success(res.message || 'Restored');
            loadMedia(false);
            setAlpineDetail(false);
        }).fail(function () { toastr.error('Failed'); });
    }

    function doDestroyFinal(id, isFolder) {
        if (!confirm('Permanently delete? This cannot be undone.')) return;
        $.ajax({
            url:    ROUTES.destroyFinal,
            method: 'DELETE',
            data:   { _token: CSRF, id: id, is_folder: isFolder ? 'true' : 'false' },
        }).done(function (res) {
            toastr.success(res.message || 'Deleted permanently');
            loadMedia(false);
            setAlpineDetail(false);
        }).fail(function () { toastr.error('Failed'); });
    }

    function doCreateFolder() {
        const name = prompt('Folder name:');
        if (!name) return;
        $.post(ROUTES.createFolder, { _token: CSRF, name: name, parent_id: state.folder })
            .done(function (res) {
                if (res.success || res.id) {
                    toastr.success('Folder created');
                    loadMedia(false);
                } else {
                    toastr.error(res.message || 'Failed to create folder');
                }
            }).fail(function () { toastr.error('Failed'); });
    }

    /* ------------------------------------------------------------------ */
    /* Cropper                                                              */
    /* ------------------------------------------------------------------ */
    function openCropper(url, fileId) {
        const $modal = $('#modal-crop');
        if (!$modal.length) { toastr.warning('Crop modal not found'); return; }

        const $img = $modal.find('#crop-image, .crop-image-target');
        if (!$img.length) { toastr.warning('Crop image element not found'); return; }

        $img.attr('src', url);

        if (state.cropperInst) {
            state.cropperInst.destroy();
            state.cropperInst = null;
        }

        $modal.removeClass('hidden').show();

        $img.on('load', function () {
            state.cropperInst = new Cropper($img[0], {
                viewMode: 1,
                autoCropArea: 0.8,
            });
        });

        $modal.find('.js-crop-confirm, .js-save-crop').off('click').on('click', function () {
            if (!state.cropperInst) return;
            const canvas = state.cropperInst.getCroppedCanvas();
            canvas.toBlob(function (blob) {
                const fd = new FormData();
                fd.append('image', blob, 'crop.png');
                fd.append('id', fileId);
                fd.append('_token', CSRF);
                showLoading();
                $.ajax({
                    url: ROUTES.updateCrop, method: 'POST',
                    data: fd, processData: false, contentType: false,
                }).done(function (res) {
                    toastr.success('Cropped and saved');
                    loadMedia(false);
                    $modal.hide();
                }).fail(function () { toastr.error('Crop save failed'); })
                  .always(hideLoading);
            });
        });

        $modal.find('.js-crop-close, .js-modal-close').off('click').on('click', function () {
            $modal.hide();
            state.cropperInst && state.cropperInst.destroy();
            state.cropperInst = null;
        });
    }

    /* ------------------------------------------------------------------ */
    /* Context menu                                                         */
    /* ------------------------------------------------------------------ */
    function initContextMenu() {
        if (typeof $.contextMenu !== 'function') return;

        $.contextMenu('destroy', '.media-item');
        $.contextMenu({
            selector: '.media-item',
            items: {
                rename:  { name: 'Rename',  icon: 'edit' },
                trash:   { name: 'Move to trash', icon: 'delete' },
                sep1:    '---',
                preview: { name: 'Preview', icon: 'eye' },
            },
            callback: function (key, options) {
                const $el = $(options.$trigger);
                const id  = $el.data('id');
                const isFolder = $el.data('type') === 'folder';
                if (key === 'rename')  doRename(id, $el.data('name'), isFolder);
                if (key === 'trash')   doTrash(id, isFolder);
                if (key === 'preview') selectItem($el);
            },
        });
    }

    /* ------------------------------------------------------------------ */
    /* Event bindings (static elements)                                     */
    /* ------------------------------------------------------------------ */
    function bindStaticEvents() {
        const $container = $('#media-container');

        // Filter / sort / view buttons
        $(document).on('click', '.js-media-change-filter', function () {
            const type  = $(this).data('type');
            const value = $(this).data('value');

            $(this).closest('div').find('.js-media-change-filter').removeClass('active bg-white dark:bg-slate-700 text-primary shadow-sm');
            $(this).addClass('active bg-white dark:bg-slate-700 text-primary shadow-sm');

            if (type === 'filter')   state.filter = value;
            if (type === 'sort_by') {
                state.sortBy = value;
                const label = $(this).text().trim();
                $('.js-sort-label').text(label);
            }
            if (type === 'view_in') state.viewIn = value;
            loadMedia(false);
        });

        // Search
        let searchTimer;
        $(document).on('input', '.js-search-input', function () {
            clearTimeout(searchTimer);
            const val = $(this).val();
            searchTimer = setTimeout(function () {
                state.search = val;
                loadMedia(false);
            }, 400);
        });

        // Navigate to folder via breadcrumb or grid
        $(document).on('click', '.js-change-folder', function (e) {
            e.preventDefault();
            navigateTo(parseInt($(this).data('folder')), $(this).text().trim());
        });

        // Refresh
        $(document).on('click', '.js-change-action[data-type="refresh"]', function () {
            loadMedia(false);
        });

        // Create folder
        $(document).on('click', '.js-create-folder-action', function () {
            doCreateFolder();
        });

        // Upload trigger
        $(document).on('click', '.js-button-upload', function () {
            $('#media-file-input').trigger('click');
        });

        $('#media-file-input').on('change', function () {
            uploadFiles(this.files);
            this.value = '';
        });

        // Close detail panel
        $(document).on('click', '.js-close-detail', function () {
            setAlpineDetail(false);
            state.selected = null;
        });

        // Drag & drop on grid
        const $gridWrap = $('.media-grid').parent();
        $gridWrap.on('dragover dragenter', function (e) {
            e.preventDefault();
            $('.media-grid').addClass('drag-over');
        }).on('dragleave drop', function (e) {
            e.preventDefault();
            $('.media-grid').removeClass('drag-over');
            if (e.type === 'drop') {
                uploadFiles(e.originalEvent.dataTransfer.files);
            }
        });

        // Infinite scroll / load more
        $('.media-grid').parent().on('scroll', function () {
            const el = this;
            if (el.scrollTop + el.clientHeight >= el.scrollHeight - 100) {
                if (state.loadMore && !state.loading) {
                    loadMedia(true);
                }
            }
        });
    }

    /* ------------------------------------------------------------------ */
    /* Boot                                                                 */
    /* ------------------------------------------------------------------ */
    $(function () {
        bindStaticEvents();
        loadMedia(false);

        // GLightbox for preview triggers (dynamically added items)
        if (typeof GLightbox !== 'undefined') {
            window.mediaLightbox = GLightbox({ selector: '.media-preview-trigger' });
        }
    });

})(jQuery);
