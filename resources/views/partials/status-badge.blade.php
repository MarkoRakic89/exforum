@php
    $color = 'secondary';
    $label = ucwords(str_replace('_', ' ', $status));
    switch($status) {
        case 'published':
            $color = 'secondary';
            break;
        case 'reserved':
        case 'reserved_partial':
        case 'reserved_full':
            $color = 'warning';
            break;
        case 'in_process':
        case 'in_progress':
            $color = 'primary';
            break;
        case 'completed':
            $color = 'success';
            break;
        case 'canceled':
            $color = 'danger';
            break;
        default:
            $color = 'secondary';
            break;
    }
@endphp
<span class="badge bg-{{ $color }}">{{ $label }}</span>