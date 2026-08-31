<?php

namespace Packages\Newsletter\Src\Enums;

enum SubscriberStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Unsubscribed = 'unsubscribed';
}
