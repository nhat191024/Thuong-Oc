<?php

namespace App\Enums;

enum BillDetailStatus: string
{
    case DONE = 'done';
    case WAITING = 'waiting';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            BillDetailStatus::DONE => __('Đã hoàn thành'),
            BillDetailStatus::WAITING => __('Đang chờ'),
            BillDetailStatus::CANCELLED => __('Đã hủy'),
        };
    }
}
