<?php

return [

    'status' => [
        'draft' => 'Draft',
        'published' => 'Published',
    ],

    'sections' => [
        'general' => 'General',
        'content' => 'Content',
        'media' => 'Images & video',
    ],

    'fields' => [
        'name' => 'Name',
        'title' => 'Service name',
        'slug' => 'Slug',
        'status' => 'Status',
        'is_operating' => 'Currently operating',
        'published_at' => 'Published at',
        'sort_order' => 'Order',
        'category' => 'Dental category',
        'facility' => 'Facility',
        'cover' => 'Cover image',
        'certificates' => 'Certificate gallery',
        'address' => 'Address',
        'icon' => 'Service icon',
        'video_url' => 'Video URL',
        'video_url_hint' => 'Paste a YouTube/Vimeo link, or pick an uploaded .mp4 from the library.',
        'or_pick_file' => 'or pick a file',
        'video_poster' => 'Video poster',
        'video_caption' => 'Video caption',
        'hero_h1' => 'Detail page heading',
        'body' => 'Body',
        'comparison_html' => 'Comparison table',
        'price_table_html' => 'Price table',
    ],

    'category' => [
        'index' => 'Dental categories',
        'create' => 'Add category',
        'edit' => 'Edit category',
        'created' => 'Category created.',
        'updated' => 'Category updated.',
        'deleted' => 'Category deleted.',
        'bulk_deleted' => ':count categories deleted.',
    ],

    'facility' => [
        'index' => 'Dental facilities',
        'create' => 'Add facility',
        'edit' => 'Edit facility',
        'created' => 'Facility created.',
        'updated' => 'Facility updated.',
        'deleted' => 'Facility deleted.',
        'bulk_deleted' => ':count facilities deleted.',
    ],

    'service' => [
        'index' => 'Dental services',
        'create' => 'Add service',
        'edit' => 'Edit service',
        'created' => 'Service created.',
        'updated' => 'Service updated.',
        'deleted' => 'Service deleted.',
        'bulk_deleted' => ':count services deleted.',
    ],

    'actions' => [
        'create' => 'Create',
        'update' => 'Update',
        'edit' => 'Edit',
        'delete' => 'Delete',
        'cancel' => 'Cancel',
        'add_image' => 'Add image',
        'pick_from_library' => 'Pick file',
        'search_placeholder' => 'Type to search…',
        'translate_from_vi' => 'Translate from Vietnamese',
        'translating' => 'Translating…',
        'translate_failed' => 'Translation failed. Try again later.',
        'delete_confirm' => 'Delete the selected items?',
    ],

    'errors' => [
        'slug_taken' => 'This slug is already in use.',
    ],

];
