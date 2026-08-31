<?php

namespace Packages\Inquiry\Src\Enums;

enum InquirySource: string
{
    case ContactForm = 'contact_form';
    case PartnerInquiry = 'partner_inquiry';
    case Emergency = 'emergency';
    case CatalogQuick = 'catalog_quick';
    case ChatbotOverflow = 'chatbot_overflow';
}
