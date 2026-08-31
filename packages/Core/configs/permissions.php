<?php

return [
    // Users
    [
        'name' => 'Users',
        'flag' => 'users.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'users.create',
        'parent_flag' => 'users.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'users.edit',
        'parent_flag' => 'users.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'users.delete',
        'parent_flag' => 'users.index',
    ],

    // Roles
    [
        'name' => 'Roles',
        'flag' => 'roles.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'roles.create',
        'parent_flag' => 'roles.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'roles.edit',
        'parent_flag' => 'roles.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'roles.delete',
        'parent_flag' => 'roles.index',
    ],

    // Settings
    [
        'name' => 'Settings',
        'flag' => 'settings.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'settings.edit',
        'parent_flag' => 'settings.index',
    ],

    // Media
    [
        'name' => 'Media',
        'flag' => 'media.index',
    ],
    [
        'name' => 'Upload',
        'flag' => 'media.create',
        'parent_flag' => 'media.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'media.edit',
        'parent_flag' => 'media.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'media.delete',
        'parent_flag' => 'media.index',
    ],

    // Content (Phase 2 — Page, Post, Menu)
    [
        'name' => 'Content',
        'flag' => 'content.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'content.create',
        'parent_flag' => 'content.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'content.edit',
        'parent_flag' => 'content.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'content.delete',
        'parent_flag' => 'content.index',
    ],
    [
        'name' => 'Publish',
        'flag' => 'content.publish',
        'parent_flag' => 'content.index',
    ],

    // Catalog (Phase 3 — Specialty, Destination, Hospital, Doctor, Service, Package)
    [
        'name' => 'Catalog',
        'flag' => 'catalog.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'catalog.create',
        'parent_flag' => 'catalog.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'catalog.edit',
        'parent_flag' => 'catalog.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'catalog.delete',
        'parent_flag' => 'catalog.index',
    ],

    // Dental (Khám nha — danh mục / đối tác / dịch vụ)
    [
        'name' => 'Dental',
        'flag' => 'dental.index',
    ],
    [
        'name' => 'Create',
        'flag' => 'dental.create',
        'parent_flag' => 'dental.index',
    ],
    [
        'name' => 'Edit',
        'flag' => 'dental.edit',
        'parent_flag' => 'dental.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'dental.delete',
        'parent_flag' => 'dental.index',
    ],

    // Inquiry (Phase 4 — Inquiry + Emergency + pipeline)
    [
        'name' => 'Inquiry',
        'flag' => 'inquiry.index',
    ],
    [
        'name' => 'View',
        'flag' => 'inquiry.view',
        'parent_flag' => 'inquiry.index',
    ],
    [
        'name' => 'Update Status',
        'flag' => 'inquiry.update',
        'parent_flag' => 'inquiry.index',
    ],
    [
        'name' => 'Reply',
        'flag' => 'inquiry.reply',
        'parent_flag' => 'inquiry.index',
    ],
    [
        'name' => 'Export',
        'flag' => 'inquiry.export',
        'parent_flag' => 'inquiry.index',
    ],
    [
        'name' => 'Assign',
        'flag' => 'inquiry.assign',
        'parent_flag' => 'inquiry.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'inquiry.delete',
        'parent_flag' => 'inquiry.index',
    ],

    // Newsletter (Phase 6 — subscribers list + export/delete)
    [
        'name' => 'Newsletter',
        'flag' => 'newsletter.index',
    ],
    [
        'name' => 'Export',
        'flag' => 'newsletter.export',
        'parent_flag' => 'newsletter.index',
    ],
    [
        'name' => 'Delete',
        'flag' => 'newsletter.delete',
        'parent_flag' => 'newsletter.index',
    ],

    // Home page (homepage CMS)
    [
        'name' => 'Manage home page',
        'flag' => 'home.manage',
    ],

    // About page CMS
    [
        'name' => 'Manage about page',
        'flag' => 'about.manage',
    ],

    // Contact page CMS (lien-he static content)
    [
        'name' => 'Manage contact page',
        'flag' => 'contact.manage',
    ],

    // Alliance Network page CMS
    [
        'name' => 'Manage alliance page',
        'flag' => 'alliance.manage',
    ],

    // Achievement page CMS (thanh-tuu-y-khoa)
    [
        'name' => 'Manage achievement page',
        'flag' => 'achievement.manage',
    ],

    // Chatbot
    [
        'name' => 'Chatbot settings',
        'flag' => 'chatbot.settings',
    ],
    [
        'name' => 'Chatbot document groups',
        'flag' => 'chatbot.document_groups',
    ],
    [
        'name' => 'Chatbot documents',
        'flag' => 'chatbot.documents',
    ],
    [
        'name' => 'Chatbot conversations',
        'flag' => 'chatbot.conversations',
    ],
];
