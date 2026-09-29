<!DOCTYPE html>
<html>
<head>
    <title>แก้ไขรีวิว</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >
</head>

<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">

            <div class="card shadow-sm">
                <div class="card-body p-4">

                    <h3 class="text-center mb-4">
                        แก้ไขรีวิว
                    </h3>

                    <form
                        action="{{ route('reviews.update', $review->Review_id) }}"
                        method="POST"
                    >
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="form-label">
                                ให้คะแนนติวเตอร์
                            </label>

                            <div class="d-flex gap-2">
                                @for ($i = 1; $i <= 5; $i++)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            type="radio"
                                            name="rating"
                                            value="{{ $i }}"
                                            id="rating{{ $i }}"
                                            {{ $review->rating == $i ? 'checked' : '' }}
                                            required
                                        >

                                        <label
                                            class="form-check-label"
                                            for="rating{{ $i }}"
                                        >
                                            {{ $i }}
                                        </label>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <div class="mb-4">
                            <label
                                for="comment"
                                class="form-label"
                            >
                                Comment
                            </label>

                            <textarea
                                class="form-control"
                                id="comment"
                                name="Comment"
                                rows="6"
                                required
                            >{{ $review->Comment }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a
                                href="{{ route('reviews.index') }}"
                                class="btn btn-secondary"
                            >
                                ยกเลิก
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                บันทึกการแก้ไข
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
</html>