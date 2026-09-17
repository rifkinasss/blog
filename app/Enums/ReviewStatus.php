<?php

namespace App\Enums;

enum ReviewStatus: string
{
    case None = 'none';
    case Pending = 'pending';
    case Approved = 'approved';
    case ChangesRequested = 'changes_requested';
}
