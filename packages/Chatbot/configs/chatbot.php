<?php

return [
    'api_base' => env('CHATBOT_API_BASE', 'https://tourrismbotapi.onelink.vn'),
    'username' => env('CHATBOT_USERNAME', ''),
    'password' => env('CHATBOT_PASSWORD', ''),

    /*
    | Admin credentials for upstream Tourism API.
    | Required for /admin/chatbot/documents + /admin/chatbot/document-groups.
    | Leave empty in env if admin features not used.
    */
    'admin_username' => env('CHATBOT_ADMIN_USERNAME', ''),
    'admin_password' => env('CHATBOT_ADMIN_PASSWORD', ''),

    /*
    | These defaults are overridable via Core Settings admin UI (group: chatbot).
    | Setting::get('chatbot.document_group') takes precedence when set.
    */
    'document_group' => env('CHATBOT_DOCUMENT_GROUP', null),
    'ai_provider' => env('CHATBOT_AI_PROVIDER', null),
    'max_messages_per_session' => (int) env('CHATBOT_MAX_MESSAGES', 10),
    'session_ttl' => (int) env('CHATBOT_SESSION_TTL', 86400), // seconds (24h)

    /*
    | Read-timeout (idle seconds) for the streaming upstream call. The overall
    | request timeout is disabled (timeout=0) because LLM RAG responses can
    | take well over 30s; this value only fires when upstream stops sending
    | data mid-stream. Tune higher if you see premature stream truncation.
    */
    'stream_read_timeout' => (int) env('CHATBOT_STREAM_READ_TIMEOUT', 120),

    /*
    | Cache key for upstream JWT token.
    | Buffer of 1800s (30 min) before actual expiry prevents race refreshes.
    */
    'upstream_token_cache_key' => 'chatbot:upstream_token',
    'upstream_admin_token_cache_key' => 'chatbot:upstream_admin_token',
    'upstream_token_refresh_buffer' => 1800,

    /*
    | Redis key prefix for atomic message counters.
    | Full key: chatbot:msg:{session_uuid}
    */
    'counter_key_prefix' => 'chatbot:msg:',

    /*
    | Rate limits (handled by Laravel's built-in throttle middleware).
    | session_endpoint: requests/minute/IP
    | message_endpoint: requests/minute/session
    */
    'rate_limit_session' => 10,
    'rate_limit_message' => 30,
];
