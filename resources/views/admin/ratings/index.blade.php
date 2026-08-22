@extends('layouts.contentNavbarLayout')

@section('content')
<h1>Ratings</h1>
<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Reservation</th>
                    <th>Rater</th>
                    <th>Ratee</th>
                    <th>Score</th>
                    <th>Comment</th>
                    <th>Visible</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @foreach($ratings as $rating)
                <tr>
                    <td>{{ $rating->id }}</td>
                    <td>{{ $rating->reservation_id }}</td>
                    <td>{{ $rating->rater->naziv }}</td>
                    <td>{{ $rating->ratee->naziv }}</td>
                    <td>{{ $rating->score }}</td>
                    <td>{{ $rating->comment }}</td>
                    <td>{{ $rating->visible ? 'Yes' : 'No' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
{{ $ratings->links('admin.bootstrap-5') }}
@endsection