<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reviews</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

@foreach ($reviews as $review)

    <div class="card mb-3">
        <div class="card-body">

            <h5>
                {{ $review->rating }}/5
            </h5>

            <p>
                {{ $review->Comment }}
            </p>

            @can('update', $review)
                <a
                    href="{{ route('reviews.edit', $review->Review_id) }}"
                    class="btn btn-warning"
                >
                    แก้ไข
                </a>
            @endcan

            @can('delete', $review)
                <form
                    action="{{ route('reviews.destroy', $review->Review_id) }}"
                    method="POST"
                    class="d-inline"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="btn btn-danger"
                        onclick="return confirm('ต้องการลบรีวิวนี้หรือไม่?')"
                    >
                        ลบ
                    </button>
                </form>
            @endcan

        </div>
    </div>

@endforeach
</html>